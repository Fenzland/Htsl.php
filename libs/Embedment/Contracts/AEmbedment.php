<?php

namespace Htsl\Embedment\Contracts;

use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Document;

////////////////////////////////////////////////////////////////

abstract class AEmbedment
{
	/**
	 * Embed content
	 *
	 * @var string
	 */
	protected $content='';

	/**
	 * The main document which this embedment embedding into.
	 *
	 * @var [type]
	 */
	protected $document;

	/**
	 * Constructor.
	 *
	 * @param \Htsl\Parser\Document $document
	 */
	final public function __construct( Document$document )
	{
		$this->document= $document;

		$this->construct();
	}

	/**
	 * Getting content.
	 *
	 * @return string
	 */
	final public function getContent():string
	{
		return $this->content;
	}

	/**
	 * Real constructor to be rewrite.
	 */
	protected function construct(){}

	/**
	 * Parsing line.
	 *
	 * @param  \Htsl\ReadingBuffer\Line $line
	 *
	 * @return \Htsl\Embedment\Contracts
	 */
	abstract public function parseLine( Line$line ):self;
}
