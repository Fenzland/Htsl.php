<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class StringNode extends ANode
{
	protected function construct():parent
	{
		return $this;
	}

	public function open():string
	{
		return '';
	}

	public function close( Line$closerLine ):string
	{
		return '';
	}
}
