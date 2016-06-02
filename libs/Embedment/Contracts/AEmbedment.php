<?php

namespace Htsl\Embedment\Contracts;

use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Document;

////////////////////////////////////////////////////////////////

abstract class AEmbedment
{
	protected $content;
	protected $document;

	final public function __construct(Document $document)
	{
		$this->document= $document;

		$this->construct();
	}

	final public function getContent():string
	{
		return $this->content;
	}

	protected function construct(){}

	abstract public function parseLine( Line$line ):self;
}
