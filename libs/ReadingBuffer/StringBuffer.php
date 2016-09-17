<?php

namespace Htsl\ReadingBuffer;

use Htsl\Htsl;

////////////////////////////////////////////////////////////////

class StringBuffer extends Contracts\ABuffer
{
	/**
	 * Array of lines.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $lines;

	/**
	 * Constructing a string buffer to provide lines base on string content.
	 *
	 * @access public
	 *
	 * @param \Htsl\Htsl   $htsl
	 * @param string       $content
	 * @param string       $filePath Fake file path to enable document controller.
	 */
	public function __construct( Htsl$htsl, string$content, string$filePath='' )
	{
		if( false!==strpos($content,"\r") ){
			throw new \Exception("Line ending must be LF.", 1);
		}

		$this->filePath= $filePath;

		$this->lines= array_filter(explode("\n",$content),'strlen');
		array_unshift($this->lines,null);

		parent::__construct($htsl);
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
		return new Line(next($this->lines));
	}

	/**
	 * Getting another file reference fake file of this buffer.
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
		$content= $this->htsl->getFileContent($filePath);

		return new static($this->htsl,$content,$filePath);
	}
}
