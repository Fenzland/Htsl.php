<?php

namespace Htsl\ReadingBuffer\Contracts;

use Htsl\Htsl;
use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

abstract class ABuffer
{
	use TGetter;

	/**
	 * The htsl server.
	 *
	 * @var \Htsl\Htsl
	 */
	protected $htsl;

	/**
	 * The file name.
	 *
	 * @var string
	 */
	protected $fileName= '';

	public function __construct( Htsl$htsl )
	{
		$this->htsl= $htsl;
	}

	/**
	 * Get a line of the document.
	 * @return \Htsl\ReadingBuffer\Line
	 */
	abstract public function getLine();

	/**
	 * Get a line of the document.
	 * @return \Htsl\ReadingBuffer\Line
	 */
	public function getFileName():string
	{
		return $this->fileName;
	}

	abstract public function goSide( $fileName ):self;
}
