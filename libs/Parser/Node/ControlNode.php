<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class ControlNode extends ANode
{
	/**
	 * The name of the Htsl.php control structure.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The name of the complied(PHP) control structure.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $structureName;

	/**
	 * Parameters.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $param;

	/**
	 * Unique id for check whether loop executed.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Real constructor.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
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

	/**
	 * Opening this control node, and returning node opener.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function open():string
	{
		return $this->withParam($this->config['opener']);
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
	 * Close this control node, and returning node closer.
	 *
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
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

	/**
	 * Parse opener or closer with parameters.
	 *
	 * @access private
	 *
	 * @param  string $input Opener or Closer
	 *
	 * @return string
	 */
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
							preg_replace('/^\\\\_$/','',$replacement),
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
