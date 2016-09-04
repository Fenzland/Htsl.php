<?php

namespace Htsl\ReadingBuffer;

use Htsl\Htsl;

////////////////////////////////////////////////////////////////

class FileBuffer extends Contracts\ABuffer
{
	/**
	 * File handle.
	 *
	 * @var resource
	 */
	private $handle;

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

	public function __destruct()
	{
		fclose($this->handle);
	}

	public function getLine():Line
	{
		while( "\n"===$content= fgets($this->handle) );

		return new Line($content);
	}

	public function goSide( $filePath ):parent
	{
		$filePath= $this->htsl->getFilePath($filePath,dirname($this->filePath));

		return new static($this->htsl,$filePath);
	}
}
