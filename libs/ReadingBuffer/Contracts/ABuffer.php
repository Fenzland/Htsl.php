<?php

namespace Htsl\ReadingBuffer\Contracts;

use Htsl\Htsl;
use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

/**
 * @property-read \Htsl\ReadingBuffer\Line $line
 * @property-read string                   $filePath Physical or fake path of file within this buffer.
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
	protected $filePath= '';

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
	 * Getting a line of the document.
	 *
	 * @access public
	 *
	 * @return \Htsl\ReadingBuffer\Line
	 */
	abstract public function getLine();

	/**
	 * Getting physical or fake path of file within this buffer.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getFilePath():string
	{
		return $this->filePath;
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
