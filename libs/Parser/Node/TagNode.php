<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;
use ArrayAccess;

////////////////////////////////////////////////////////////////

class TagNode extends ANode implements ArrayAccess
{
	/**
	 * The html name of the tag.
	 *
	 * @var string
	 */
	private $tagName;

	/**
	 * Whether the tag is empty.
	 *
	 * @var bool
	 */
	private $isEmpty;

	/**
	 * The attributes of the tag.
	 *
	 * @var array
	 */
	private $attributes=[];

	protected function construct():parent
	{

		$name= $this->line->pregGet('/(?<=^-)[\w-:]+/');
		$this->name=$name;

		$this->loadConfig($name,$this->document);

		$this->tagName=$this->config['name']??$name;
		$this->isEmpty= $this->line->getChar(-1)=='/' || $this->document->getConfig('empty_tags',$this->tagName);
		$this->attributes= $this->config['default_attributes']??[];

		return $this;
	}

	public function open():string
	{
		if( isset($this->config['opener']) )
			{ return $this->config['opener']; }

		if( isset($this->config['params']) )
			{ $this->parseParams(); }

		if( isset($this->config['name_value']) )
			{ $this->parseNameValue(); }

		if( isset($this->config['link']) )
			{ $this->parseLink(); }

		if( isset($this->config['target']) )
			{ $this->parseTarget(); }

		if( isset($this->config['alt']) )
			{ $this->parseAlt(); }

		$this->parseCommonAttributes();

		if( isset($this->config['in_scope']) && isset($this->config['scope_function']) && is_callable($this->config['scope_function']) )
			{ $this->config['scope_function']->call($this,$this->document->scope); }

		$finisher= $this->isEmpty ? ' />' : '>';

		return "<{$this->tagName}{$this->attributesString}{$finisher}";
	}

	public function close( Line$Line ):string
	{
		return $this->isEmpty ? '' : $this->config['closer']??"</{$this->tagName}>";
	}

	public function getEmbed():string
	{
		return $this->config['embedding']??'';
	}

	public function getScope()
	{
		return $this->config['scope']??null;
	}


	protected function parseParams():self
	{
		$params= preg_split('/(?<!\\\\)\\|/',$this->line->pregGet('/^-[\w-:]+\((.*?)\)(?= |(\\{>)?$)/',1));

		if( ($m= count($params)) != ($n= count($this->config['params'])) ){$this->document->throw("Tag $this->name has $n parameters $m given.");}

		array_map(function( $key, $value ){return $this->setAttribute($key,str_replace('\\|','|',$value));},$this->config['params'],$params);

		return $this;
	}

	protected function parseNameValue():self
	{
		$params= $this->line->pregGet('/ <(.*?)>(?= |$)/',1)
		 and $params= preg_split('/(?<!\\\\)\\|/',$params)
		  and array_map(function( $key, $value ){return isset($key)&&isset($value) ? $this->setAttribute($key,$this->checkExpression(str_replace('\\|','|',$value))) : '';},$this->config['name_value'],$params);

		return $this;
	}

	protected function parseLink():self
	{
		$link= $this->line->pregGet('/ @((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp))+?\)))(?= |$)/',1);

		if( strlen($link) ){
			if( isset($this->config['target']) && ':'===$link{0} ){
				$this->setAttribute($this->config['link'],'javascript'.$link);
			}elseif( '//'===($firstTwoLetters=substr($link,0,2)) ){
				$this->setAttribute($this->config['link'],'http:'.$link);
			}elseif( '\\\\'===$firstTwoLetters ){
				$this->setAttribute($this->config['link'],'https://'.substr($link,2));
			}else{
				$this->setAttribute($this->config['link'],$this->checkExpression($link));
			}
		}

		return $this;
	}

	protected function parseTarget():self
	{
		$target= $this->line->pregGet('/ >((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp))+?\)))(?= |$)/',1);

		if( strlen($target) ){
			$this->setAttribute($this->config['target'],$this->checkExpression($target));
		}

		return $this;
	}

	protected function parseAlt():self
	{
		$alt= $this->line->pregGet('/ _((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp))+?\)))(?= |$)/',1);

		if( strlen($alt) ){
			$this->setAttribute($this->config['alt'],$this->checkExpression($alt));
		}

		return $this;
	}

	protected function parseCommonAttributes():string
	{
		$attributes= '';

		$id= $this->line->pregGet('/ #([^ ]+|(?<exp>\((?:[^()]+|(?&exp))+?\)))(?= |$)/',1)
		 and $this->setAttribute('id',$id);

		$classes= $this->line->pregGet('/ \.[^ ]+(?= |$)/')
		 and preg_match_all('/\.((?(?!\()[^.]+|(?<exp>\((?:[^()]+|(?&exp))+?\))))/',$classes,$matches)
		  and $classes= implode(' ',array_filter(array_map(function( $className ){return $this->checkExpression($className);},$matches[1])))
		   and $this->setAttribute('class',$classes);

		$title= $this->line->pregGet('/ \^((?!\()(?:[^ ]| (?=[a-zA-Z0-9]))+|(?<exp>\((?:[^()]+|(?&exp))+?\)))(?= |$)/',1)
		 and $this->setAttribute('title',$title);

		$style= $this->line->pregGet('/ \[([^\]]+;)(?=\]( |$))/',1)
		 and $this->setAttribute('style',$style);

		$eventListeners= $this->line->pregMap('/ %(\w+)\{>(.*?)<\}(?= |$)/',function( $string, $name, $code ){
			$this->setAttribute('on'.$name,str_replace('"','&quot;',$code));
		})
		 and implode('',$eventListeners);

		$other= $this->line->pregGet('/(?<=\{).*?(?=;\}( |$))/')
		 and array_map(function( $keyValue ){
			if( strpos($keyValue,'=') ){
				list($key,$value)= [strtok($keyValue,'='),strtok(''),];
				$this->setAttribute($key,$this->checkExpression($value));
			}else{
				$this->setAttribute($keyValue,$keyValue);
			}
		},explode(';',$other));

		return $attributes;
	}

	protected function checkExpression( string$value ):string
	{
		return preg_match('/^\(.*\)$/',$value) ? '<?='.substr($value,1,-1).';?>' : str_replace('"','&quot;',$value);
	}

	protected function getAttributesString():string
	{
		ksort($this->attributes);
		return implode('',array_map(static function( $key, $value ){return " $key=\"$value\"";},array_keys($this->attributes),$this->attributes));
	}
	protected function setAttribute( string$key , string$value ):self
	{
		$this->attributes[$key]=$value;

		return $this;
	}

	public function offsetExists( $offset ):bool
	{
		return isset($this->attributes[$offset]);
	}
	public function offsetGet( $offset )
	{
		return $this->attributes[$offset]??null;
	}
	public function offsetSet( $offset, $value )
	{
		$this->attributes[$offset]= $value;
	}
	public function offsetUnset( $offset )
	{
		if( isset($this->attributes[$offset]) )
			{ unset($this->attributes[$offset]); }
	}
}
