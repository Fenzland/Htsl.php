<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;
use Htsl\Parser\Section;

////////////////////////////////////////////////////////////////

class NamelessSectionNode extends ANode
{
	/**
	 * The name of the section.
	 *
	 * @var string
	 */
	private $name;

	protected function construct():parent
	{
		$this->name= null;

		return $this;
	}

	public function open():string
	{
		$this->document->setSection(new Section($this->name));

		return '';
	}

	public function close( Line$closerLine ):string
	{
		$this->document->setSection(null);

		return '';
	}
}
