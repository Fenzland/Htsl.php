<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class PhpEmbedment extends AEmbedment
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
		$this->content.= $line->content;
		return $this;
	}
}
