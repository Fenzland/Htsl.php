<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class CssEmbedment extends AEmbedment
{
	/**
	 * Parsing line.
	 *
	 * @param  \Htsl\ReadingBuffer\Line $line
	 *
	 * @return \Htsl\Embedment\Contracts
	 */
	public function parseLine( Line$line ):parent
	{
		$this->content.= $line->content;
		return $this;
	}
}
