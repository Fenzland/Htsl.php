<?php

namespace Htsl\ReadingBuffer;

////////////////////////////////////////////////////////////////

interface IBuffer
{
	/**
	 * Get a line of the document.
	 * @return \Htsl\ReadingBuffer\Line
	 */
	public function getLine();
}
