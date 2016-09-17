<?php

namespace Htsl\ReadingBuffer\Contracts;

use Htsl\Htsl;
use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

/**
 * @property-read \Htsl\ReadingBuffer\Line $line
 * @property-read string                   $fileName A
 */
abstract class ABuffer
{
	use TGetter;

	/**
	 * The htsl server.
	 *
	 * @access protected
	 *
	 * @var \Htsl\Htsl
	 */
	protected $htsl;

	/**
	 * The file name.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $fileName= '';

	/**
	 * Constructing a buffer reading HTSL content from somewhere.
	 *
	 * @access public
	 *
	 * @param Htsl $htsl [description]
	 */
	public function __construct( Htsl$htsl )
	{
		$this->htsl= $htsl;
	}

	/**
	 * Get a line of the document.
	 *
	 * @access public
	 *
	 * @return \Htsl\ReadingBuffer\Line
	 */
	abstract public function getLine();

	/**
	 * Get a line of the document.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getFileName():string
	{
		return $this->fileName;
	}

	/**
	 * Getting another file reference file of this buffer.
	 *
	 * @access public
	 *
	 * @param  string $fileName
	 *
	 * @return \Htsl\ReadingBuffer\Contracts\ABuffer
	 */
	abstract public function goSide( $fileName ):self;
}
