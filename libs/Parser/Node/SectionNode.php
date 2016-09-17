<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;
use Htsl\Parser\Section;

////////////////////////////////////////////////////////////////

class SectionNode extends ANode
{
	/**
	 * The name of the section.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Real constructor.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function construct():parent
	{
		$this->name= $this->line->pregGet('/(?<=\( ).*(?= \))/');

		return $this;
	}

	/**
	 * Opening this node, and returning node opener.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function open():string
	{
		$this->document->setSection(new Section($this->name));

		return '';
	}

	/**
	 * Close this node, and returning node closer.
	 *
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
	public function close( Line$closerLine ):string
	{
		$this->document->setSection(null);

		return '';
	}
}
