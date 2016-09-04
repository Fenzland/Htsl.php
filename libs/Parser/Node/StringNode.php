<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class StringNode extends ANode
{
	/**
	 * Real constructor.
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function construct():parent
	{
		return $this;
	}

	/**
	 * Opening this node, and returning node opener.
	 *
	 * @return string
	 */
	public function open():string
	{
		return '';
	}

	/**
	 * Close this node, and returning node closer.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
	public function close( Line$closerLine ):string
	{
		return '';
	}
}
