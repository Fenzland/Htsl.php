<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Section;

////////////////////////////////////////////////////////////////

class NamelessSectionNode extends SectionNode
{
	/**
	 * Real constructor.
	 *
	 * @access protected
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function construct():parent
	{
		$this->name= null;

		return $this;
	}
}
