<?php

namespace Htsl\Embedment\Contracts;

use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Document;
use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

/**
 * @property-read string $content Embedment content.
 */
abstract class AEmbedment
{
	use TGetter;

	/**
	 * Embed content
	 *
	 * @var string
	 *
	 * @access protected
	 */
	protected $content='';

	/**
	 * The main document which this embedment embedding into.
	 *
	 * @var \Htsl\Parser\Document
	 *
	 * @access protected
	 */
	protected $document;

	/**
	 * Constructor.
	 *
	 * @access public
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
	 * @access public
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
	 * @access public
	 *
	 * @param  \Htsl\ReadingBuffer\Line $line
	 *
	 * @return \Htsl\Embedment\Contracts
	 */
	abstract public function parseLine( Line$line ):self;
}
