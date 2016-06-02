<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class SectionNode extends ANode
{
	/**
	 * The name of the section.
	 *
	 * @var string
	 */
	private $name;

	protected function construct():parent
	{
		$this->name= $this->line->pregGet('/(?<=\( ).*(?= \))/');

		return $this;
	}

	public function open():string
	{
		$this->document->setSection($this->name);

		return '';
	}

	public function close( Line$closerLine ):string
	{
		$this->document->setSection(null);

		return '';
	}
}
