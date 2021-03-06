<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\Helper;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;
use ArrayAccess;

////////////////////////////////////////////////////////////////

/**
 * @property-read string $embed            Whether this is embedding node and embeding type.
 * @property-read string $attributesString Attribute string with HTML syntax.
 */
class TagNode extends ANode implements ArrayAccess
{
	/**
	 * Name of this node.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The html tag name of this node.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $tagName;

	/**
	 * Whether the html tag is empty.
	 *
	 * @access private
	 *
	 * @var bool
	 */
	private $isEmpty;

	/**
	 * The attributes of this node.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $attributes=[];

	/**
	 * Real constructor.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function construct():parent
	{

		$name= $this->line->pregGet('/(?<=^-)[\w-:]+/');
		$this->name=$name;

		$this->loadConfig($name,$this->document);

		$this->tagName=$this->config['name']??$name;
		$this->isEmpty= $this->line->getChar(-1)==='/' || $this->document->getConfig('empty_tags',$this->tagName);
		isset($this->config['default_attributes']) and array_walk($this->config['default_attributes'],function( $value,$key ){ $this->setAttribute($key,$value); });

		return $this;
	}

	/**
	 * Opening this tag node, and returning node opener.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function open():string
	{
		if( isset($this->config['opener']) )
			{ return $this->config['opener']; }

		$this->parsePrivateAttributes();
		$this->parseCommonAttributes();

		if( isset($this->config['in_scope']) && isset($this->config['scope_function']) && is_callable($this->config['scope_function']) )
			{ $this->config['scope_function']->call($this,$this->document->scope); }

		$finisher= $this->isEmpty ? ' />' : '>';

		return "<{$this->tagName}{$this->attributesString}{$finisher}";
	}

	/**
	 * Close this tag node, and returning node closer.
	 *
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
	public function close( Line$closerLine ):string
	{
		return $this->isEmpty ? '' : $this->config['closer']??"</{$this->tagName}>";
	}

	/**
	 * Getting whether this is embedding node and embeding type.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getEmbed():string
	{
		return $this->config['embedding']??'';
	}

	/**
	 * Getting whether this node contains a scope and scope name.
	 *
	 * @access public
	 *
	 * @return string | null
	 */
	public function getScope()
	{
		return $this->config['scope']??null;
	}

	/**
	 * Parsing node parameters if needed.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parsePrivateAttributes():self
	{
		foreach( self::PRIVATE_ATTRIBUTES as $attribute ){
			isset($this->config[$attribute]) and $this->{'parse'.Helper\camel_case($attribute)}();
		}

		return $this;
	}

	/**
	 * Private attributes of tag.
	 *
	 * @access public
	 *
	 * @const array
	 *
	 * @todo Make this const private when php 7.1
	 */
	const PRIVATE_ATTRIBUTES= [
		'params',
		'name_value',
		'link',
		'target',
		'alt',
	];

	/**
	 * Parsing node parameters if needed.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parseParams():self
	{
		$params= preg_split('/(?<!\\\\)\\|/',$this->line->pregGet('/^-[\w-:]+\((.*?)\)(?= |(\\{>)?$)/',1));

		if( ($m= count($params)) != ($n= count($this->config['params'])) ){$this->document->throw("Tag $this->name has $n parameters $m given.");}

		array_map(function( $key, $value ){return $this->setAttribute($key,$this->checkExpression(str_replace('\\|','|',$value)));},$this->config['params'],$params);

		return $this;
	}

	/**
	 * Parsing <name|value> attributes.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parseNameValue():self
	{
		$params= $this->line->pregGet('/ <(.*?)>(?= |$)/',1)
		 and $params= preg_split('/(?<!\\\\)\\|/',$params)
		  and array_map(function( $key, $value ){return isset($key)&&isset($value) ? $this->setAttribute($key,$this->checkExpression(str_replace('\\|','|',$value))) : '';},$this->config['name_value'],$params);

		return $this;
	}

	/**
	 * Getting tag section with given leader.
	 *
	 * @access protected
	 *
	 * @param string $leader
	 * @param boool  $allowSpace
	 *
	 * @return string
	 */
	protected function sectionLedBy( string$leader, bool$allowSpace=false ):string
	{
		return $this->line->pregGet(...[
			(
				'/ '.preg_quote($leader)
				.
				($allowSpace?
					'((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+'
					:
					'([^ ]+'
				)
				.
				'|(?<exp>\((?:[^()]+|(?&exp)?)+?\)))(?= |$)/'
			),
			1,
		]);
	}

	/**
	 * Parsing @links.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parseLink():self
	{
		return $this->setMultinameAttribute('link',$this->sectionLedBy('@',true),function( string$link ){
			if( isset($this->config['target']) && ':'===$link{0} ){
				return 'javascript'.$link;
			}elseif( '//'===($firstTwoLetters=substr($link,0,2)) ){
				return 'http:'.$link;
			}elseif( '\\\\'===$firstTwoLetters ){
				return 'https://'.substr($link,2);
			}else{
				return $this->checkExpression($link);
			}
		});
	}

	/**
	 * Parsing >target.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parseTarget():self
	{
		return $this->setMultinameAttribute('target',$this->sectionLedBy('>',true));
	}

	/**
	 * Parsing _placeholders.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	protected function parseAlt():self
	{
		return $this->setMultinameAttribute('alt',$this->sectionLedBy('_',true));
	}

	/**
	 * Setting attribute whitch has same name in HTSL but different name in HTML.
	 *
	 * @access private
	 *
	 * @param string        $name      Attribute name of HTSL
	 * @param string        $value     Attribute value
	 * @param callable|null $processer
	 *
	 * @return \Htsl\Parser\Node\TagNode
	 */
	private function setMultinameAttribute( string$name, string$value, callable$processer=null ):self
	{
		if( strlen($value) && !empty($this->config[$name]) ){
			return $this->setAttribute($this->config[$name],call_user_func($processer??[$this,'checkExpression',],$value));
		}

		return $this;
	}

	/**
	 * Parsing #ids .classes ^titles [styles] %event{>listeners<} and {other attributes}
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function parseCommonAttributes():string
	{
		$attributes= '';

		$id= $this->sectionLedBy('#')
		 and $this->setAttribute('id',$id);

		$classes= $this->line->pregGet('/ \.[^ ]+(?= |$)/')
		 and preg_match_all('/\.((?(?!\()[^.]+|(?<exp>\((?:[^()]+|(?&exp)?)+?\))))/',$classes,$matches)
		  and $classes= implode(' ',array_filter(array_map(function( $className ){return $this->checkExpression($className);},$matches[1])))
		   and $this->setAttribute('class',$classes);

		$title= $this->sectionLedBy('^',true)
		 and $this->setAttribute('title',$this->checkExpression($title));

		$style= $this->line->pregGet('/ \[([^\]]+;)(?=\]( |$))/',1)
		 and $this->setAttribute('style',$style);

		$eventListeners= $this->line->pregMap('/ %(\w+)\{>(.*?)<\}(?= |$)/',function( $string, $name, $code ){
			$this->setAttribute('on'.$name,str_replace('"','&quot;',$code));
		})
		 and implode('',$eventListeners);

		$other= $this->line->pregGet('/(?<=\{).*?(?=;\}( |$))/')
		 and array_map(function( $keyValue ){
			preg_replace_callback('/^([\w-:]+)(?:\?(.+?))?(?:\=(.*))?$/',function($matches){
				$this->setAttribute($matches[1],(isset($matches[3])? $this->checkExpression($matches[3]) : $matches[1]),$matches[2]??null);
			},$keyValue);
		},explode(';',$other));

		return $attributes;
	}

	/**
	 * Checking and parse PHP expressions.
	 *
	 * @access protected
	 *
	 * @param  string $value
	 *
	 * @return string
	 */
	protected function checkExpression( string$value ):string
	{
		return preg_match('/^\(.*\)$/',$value) ? '<?=str_replace(\'"\',\'&quot;\','.substr($value,1,-1).')?>' : str_replace('"','&quot;',$value);
	}

	/**
	 * Getting attribute string with HTML syntax.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function getAttributesString():string
	{
		ksort($this->attributes);
		return implode('',array_map(static function( string$key, array$data ){
			return (isset($data['condition'])&&strlen($data['condition'])?
				"<?php if( {$data['condition']} ){?> $key=\"{$data['value']}\"<?php }?>"
				:
				" $key=\"{$data['value']}\""
			);
		},array_keys($this->attributes),$this->attributes));
	}

	/**
	 * Setting attribute.
	 *
	 * @access protected
	 *
	 * @param string      $key       Attribute name.
	 * @param string      $value     Attribute value
	 * @param string|null $condition Optional condition, If given, attribute will seted only when condition is true.
	 */
	protected function setAttribute( string$key, string$value, string$condition=null ):self
	{
		if( isset($this->attributes[$key]) )
			{ $this->document->throw("Attribute $key of $this->name cannot redeclare."); }

		$this->attributes[$key]=[
			'value'=> $value,
			'condition'=> $condition,
		];

		return $this;
	}


	/*             *\
	   ArrayAccess
	\*             */

	/**
	 * Whether the attribute isset.
	 *
	 * @access public
	 *
	 * @param  mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ):bool
	{
		return isset($this->attributes[$offset]);
	}

	/**
	 * Getting attribute with array access.
	 *
	 * @access public
	 *
	 * @param  mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset )
	{
		return $this->attributes[$offset]??null;
	}

	/**
	 * Setting Attribute with array access.
	 *
	 * @access public
	 *
	 * @param  mixed $offset
	 * @param  mixed $value
	 */
	public function offsetSet( $offset, $value )
	{
		$this->setAttribute($offset,$value);
	}

	/**
	 * Unset an attribute with array access.
	 *
	 * @access public
	 *
	 * @param  mixed $offset
	 */
	public function offsetUnset( $offset )
	{
		if( isset($this->attributes[$offset]) )
			{ unset($this->attributes[$offset]); }
	}
}
