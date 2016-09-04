<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class ControlNode extends ANode
{
	/**
	 * The name of the htsl control structure.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The name of the complied control structure.
	 *
	 * @var string
	 */
	private $structureName;

	/**
	 * The param.
	 *
	 * @var string
	 */
	private $param;

	protected function construct():parent
	{
		$name= $this->line->pregGet('/(?<=^~)[\w-]*/');
		$this->name=$name;

		$this->loadConfig($name,$this->htsl);

		$this->param= $this->line->pregGet('/^~[\w-]*\( (.*) \)/',1);

		$this->structureName=$this->config['name']??$name;

		$this->id=strtoupper(uniqid());

		return $this;
	}

	public function open():string
	{
		return $this->withParam($this->config['opener']);
	}

	public function getScope()
	{
		return $this->config['scope']??null;
	}


	public function close( Line$closerLine ):string
	{
		if( isset($this->config['close_by']) && $closerLine->indentLevel==$this->line->indentLevel ){
			foreach( $this->config['close_by'] as $key=>$value ){
				if( $closerLine->pregMatch($key) ){
					return $this->withParam($value);
				}
			}
		}

		if( isset($this->config['closer']) )
			{ return $this->withParam($this->config['closer']); }

		return '';
	}

	private function withParam( string$input )
	{
		return str_replace('$_FLAG_$',"__HTSL_CTRL_FLAG_{$this->id}__",preg_replace_callback('/(?<!%)%s((?:\\/.+?(?<!\\\\)\\/.+?(?<!\\\\)\\/)+)?/',function( array$matches ){
			$param= $this->param;

			if( isset($matches[1]) ){
				array_map(...[
					function($replacer)use(&$param){
						list($pattern,$replacement,)= preg_split('/(?<!\\\\)\\//',$replacer);
						$param= preg_replace(...[
							"/$pattern/",
							$replacement,
							$param,
						]);
					},
					preg_split(
						'/(?<!\\\\)\\/\\//'
						,
						trim($matches[1],'/')
					),
				]);
			}
			return $param;
		},$input));
	}
}
