<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class TextEmbedment extends AEmbedment
{
	public function parseLine( Line$line ):parent
	{
		$this->content.= $line->fullContent."\n";
		return $this;
	}
}
