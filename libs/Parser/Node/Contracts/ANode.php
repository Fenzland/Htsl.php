<?php

namespace Htsl\Parser\Node\Contracts;

use Htsl\Htsl;
use Htsl\Parser\Document;
use Htsl\Helper\TGetter;
use Htsl\Helper\IConfigProvider;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

/**
 * @property-read string        $nodeType Type of this node.
 * @property-read string | null $scope    Whether this node contains a scope and scope name
 */
abstract class ANode
{
	use TGetter;

	/**
	 * Htsl main object.
	 *
	 * @access protected
	 *
	 * @var \Htsl\Htsl
	 */
	protected $htsl;

	/**
	 * The document.
	 *
	 * @access protected
	 *
	 * @var \Htsl\Parser\Document
	 */
	protected $document;

	/**
	 * The document.
	 *
	 * @access protected
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	protected $line;

	/**
	 * The config.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	protected $config;


	/**
	 * Fake contructor of Node.
	 *
	 * @access public
	 *
	 * @param \Htsl\Parser\Document     $document
	 * @param \Htsl\ReadingBuffer\Line  $line
	 */
	final public function __construct( Document$document, Line$line )
	{
		$this->htsl= $document->htsl;
		$this->document= $document;
		$this->line= $line;

		$this->construct();
	}

	/**
	 * Real contructor to be rewrite.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	abstract protected function construct():self;

	/**
	 * Opening this node, and returning node opener.
	 *
	 * @access public
	 *
	 * @return string
	 */
	abstract public function open():string;

	/**
	 * Close this node, and returning node closer.
	 *
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
	abstract public function close( Line$closerLine ):string;

	/**
	 * Getting whether this node contains a scope and scope name.
	 *
	 * @access public
	 *
	 * @return string | null
	 */
	public function getScope()
	{
		return null;
	}

	/**
	 * Getting the type of this node.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getNodeType()
	{
		static $nodeType;
		return $nodeType??$nodeType= $this->nodeType??(static function($className){return strtolower(preg_replace('/(?<=\\w)([A-Z])/','_$1',preg_replace('/^(?:\\w+\\\\)*(\\w+)Node$/','$1',$className)));})(get_class($this));
	}

	/**
	 * Loading node configuration.
	 *
	 * @access protected
	 *
	 * @param  string                       $name
	 * @param  \Htsl\Helper\IConfigProvider $configProvider
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function loadConfig( string$name, IConfigProvider$configProvider )
	{
		$config= $configProvider->getConfig($this->nodeType.'_nodes',$name) ?: $configProvider->getConfig($this->nodeType.'_nodes','*');

		if( isset($config['multiple']) ){
			foreach( $config['multiple'] as $value ){
				if( $this->line->pregGet($value['pattern']) ){
					$config= $value;
					break;
				}
			}
		}

		if( isset($config['in']) ){
			$config=(function( array$config )use($name){
				foreach( $config['in'] as $key=>$value ){
					if( $this->document->scope && $this->document->scope->scope===$key ){
						$value['in_scope']= $key;
						return $value;
					}
				}
				if( !isset($config['out']) ){
					$this->document->throw("The $this->nodeType node $name only use in scope ".implode(',',array_keys($config['in'])));
				}
				return $config['out'];
			})($config);
		}elseif( isset($config['only_in']) && (!$this->document->scope || !in_array($this->document->scope->scope,$config['only_in'])) ){
			$this->document->throw("The $this->nodeType node $name only use in scope ".implode(',',$config['only_in']));
		}elseif( isset($config['not_in']) && (!$this->document->scope || !in_array($this->document->scope->scope,$config['not_in'])) ){
			$this->document->throw("The $this->nodeType node $name not use in scope ".implode(',',$config['not_in']));
		}

		if( !is_array($config) ){$this->document->throw("The $this->nodeType node $name is not supported.");}

		$this->config= $config;

		return $this;
	}
}
