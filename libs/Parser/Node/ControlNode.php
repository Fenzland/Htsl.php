<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class ControlNode extends ANode
{
	/**
	 * The config.
	 *
	 * @var array
	 */
	private $config;

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
		$name= $this->line->pregGet('/(?<=^~)[\w]*/');
		$this->name=$name;

		$config= $this->getControlStructureConfig($name);

		if( !is_array($config) ){$this->document->throw("Control structure $name is not supported.");}

		$this->param= $this->line->pregGet('/^~[\w]*\( (.*) \)/',1);

		$this->config=$config;
		$this->structureName=$config['name']??$name;

		return $this;
	}

	public function open():string
	{
		return sprintf($this->config['opener'],$this->param);
	}

	public function close( Line$closerLine ):string
	{
		if( !isset($this->config['closer']) )
			{ return ''; }

		if( !is_array($this->config['closer']) )
			{ return $this->config['closer']; }

		foreach( $this->config['closer'] as $key=>$value ){
			if( $closerLine->pregMatch($key) ){
				return $value;
			}
		}
		return '';
	}

	protected function getControlStructureConfig( string...$name )
	{
		return $this->htsl->getConfig('control_structures',...$name);
	}
}
