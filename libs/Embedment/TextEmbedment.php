<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class TextEmbedment extends AEmbedment
{
	/**
	 * Parsing line.
	 *
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line $line
	 *
	 * @return \Htsl\Embedment\Contracts
	 */
	public function parseLine( Line$line ):parent
	{
		$this->content.= $line->fullContent."\n";
		return $this;
	}
}
