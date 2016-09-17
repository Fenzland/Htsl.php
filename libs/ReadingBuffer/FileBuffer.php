<?php

namespace Htsl\ReadingBuffer;

use Htsl\Htsl;

////////////////////////////////////////////////////////////////

class FileBuffer extends Contracts\ABuffer
{
	/**
	 * File handle.
	 *
	 * @access private
	 *
	 * @var resource
	 */
	private $handle;

	/**
	 * Constructing a file buffer reading HTSL content from file system.
	 *
	 * @access public
	 *
	 * @param Htsl   $htsl     Main Htsl object
	 * @param string $filePath
	 */
	public function __construct( Htsl$htsl, string$filePath )
	{
		substr($filePath,-5)==='.htsl' or $filePath.= '.htsl';

		if( !file_exists($filePath) || !is_file($filePath) ){
			throw new \Exception("File $filePath not exists.", 1);
		}

		$this->filePath= $filePath;

		$this->handle= fopen($filePath,'r');

		parent::__construct($htsl);
	}

	/**
	 * Destructor
	 *
	 * @access public
	 */
	public function __destruct()
	{
		fclose($this->handle);
	}

	/**
	 * Getting first line or next line.
	 *
	 * @access public
	 *
	 * @return \Htsl\ReadingBuffer\Line
	 */
	public function getLine():Line
	{
		while( "\n"===$content= fgets($this->handle) );

		return new Line($content);
	}

	/**
	 * Getting another file reference file of this buffer.
	 *
	 * @access public
	 *
	 * @param  string $filePath
	 *
	 * @return \Htsl\ReadingBuffer\Contracts\ABuffer
	 */
	public function goSide( $filePath ):parent
	{
		$filePath= $this->htsl->getFilePath($filePath,dirname($this->filePath));

		return new static($this->htsl,$filePath);
	}
}
