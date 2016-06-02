<?php

namespace Htsl\Parser\Node\Contracts;

use Htsl\Htsl;
use Htsl\Parser\Document;
use Htsl\Helper\TGetter;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

abstract class ANode
{
	use TGetter;

	/**
	 * Htsl main object.
	 *
	 * @var \Htsl\Htsl
	 */
	protected $htsl;

	/**
	 * The document.
	 *
	 * @var \Htsl\Parser\Document
	 */
	protected $document;

	/**
	 * The document.
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	protected $line;

	final public function __construct( Document$document, Line$line )
	{
		$this->htsl= $document->htsl;
		$this->document= $document;
		$this->line= $line;

		$this->construct();
	}
	abstract protected function construct():self;
	abstract public function open():string;
	abstract public function close( Line$closerLine ):string;

	public function getScope()
	{
		return null;
	}
}
