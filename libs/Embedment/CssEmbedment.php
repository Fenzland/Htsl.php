<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class CssEmbedment extends AEmbedment
{
	public function parseLine( Line$line ):parent
	{
		$this->content.= $line->content;
		return $this;
	}
}
