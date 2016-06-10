<?php

namespace Htsl\Parser\Node\Contracts;

use Htsl\Htsl;
use Htsl\Parser\Document;
use Htsl\Helper\TGetter;
use Htsl\Helper\IConfigProvider;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

abstract class ANode
{
	use TGetter;

	/**
	 * Htsl main object.
	 *
	 * @var \Htsl\Htsl
	 */
	protected $htsl;

	/**
	 * The document.
	 *
	 * @var \Htsl\Parser\Document
	 */
	protected $document;

	/**
	 * The document.
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	protected $line;

	/**
	 * The config.
	 *
	 * @var array
	 */
	protected $config;


	final public function __construct( Document$document, Line$line )
	{
		$this->htsl= $document->htsl;
		$this->document= $document;
		$this->line= $line;

		$this->construct();
	}
	abstract protected function construct():self;
	abstract public function open():string;
	abstract public function close( Line$closerLine ):string;

	public function getScope()
	{
		return null;
	}

	public function getNodeType()
	{
		static $nodeType;
		return $nodeType??$nodeType= $this->nodeType??(static function($className){return strtolower(preg_replace('/(?<=\\w)([A-Z])/','_$1',preg_replace('/^(?:\\w+\\\\)*(\\w+)Node$/','$1',$className)));})(get_class($this));
	}



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
