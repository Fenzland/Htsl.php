<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class JsEmbedment extends AEmbedment
{
	protected function construct()
	{
		$this->content.="\n";
	}

	public function parseLine( Line$line ):parent
	{
		$this->content.= $line->fullContent."\n";
		return $this;
	}
}
