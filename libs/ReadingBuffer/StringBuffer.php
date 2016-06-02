<?php

namespace Htsl\ReadingBuffer;

use Htsl\Htsl;

////////////////////////////////////////////////////////////////

class StringBuffer extends Contracts\ABuffer
{
	/**
	 * Array of lines.
	 *
	 * @var array
	 */
	private $lines;

	public function __construct( Htsl$htsl, string$content, string$filePath='' )
	{
		if( false!==strpos($content,"\r") ){
			throw new \Exception("Line ending must be LF.", 1);
		}

		$this->filePath= $filePath;

		$this->lines= array_filter(explode("\n",$content));
		array_unshift($this->lines,null);

		parent::__construct($htsl);
	}

	public function getLine():Line
	{
		return new Line(next($this->lines));
	}

	public function goSide( $filePath ):parent
	{
		$filePath= $this->htsl->getFilePath($filePath,dirname($this->filePath));
		$content= $this->htsl->getFileContent($filePath);

		return new static($this->htsl,$content,$filePath);
	}
}
