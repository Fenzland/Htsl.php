<?php

namespace Htsl\Embedment\Contract;

use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

abstract class Embedment
{
	protected $breaker;
	protected $content;

	public function tryBreak( Line$line ):bool
	{
		return $line->getContent===$this->breaker;
	}

	final public function getContent():string
	{
		return $content;
	}

	abstract public function parseLine( $line );
}
