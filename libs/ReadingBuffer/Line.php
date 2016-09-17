<?php

namespace Htsl\ReadingBuffer;

use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

/**
 * @property-read string                      $content       Line content without indentation.
 * @property-read string                      $fullContent   Full content of this line.
 * @property-read int                         $indentLevel   Indent level of this line.
 * @property-read \Htsl\ReadingBuffer\Line    $subIndentLine Subindent line.
 */
class Line
{
	use TGetter;

	/**
	 * Line content.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Whether this line is last line.
	 *
	 * @access private
	 *
	 * @var bool
	 */
	private $isLast=false;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string | bool $content String content for normal line and false for last line.
	 */
	public function __construct( /*string|bool*/$content )
	{
		false===$content and $this->isLast=true;

		$this->content= rtrim($content,"\n");
	}

	/**
	 * Getting content without indentation.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getContent():string
	{
		return ltrim($this->content,"\t");
	}

	/**
	 * Getting full content.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getFullContent():string
	{
		return $this->content;
	}

	/**
	 * Getting a section of content, like call substr().
	 *
	 * @access public
	 *
	 * @param  int $start
	 * @param  int ...$lengths
	 *
	 * @return string
	 */
	public function slice( int$start=0, int...$lengths ):string
	{
		return substr($this->getContent(),$start,...array_slice($lengths,0,1));
	}

	/**
	 * Getting single character from content.
	 *
	 * @access public
	 *
	 * @param  int    $offset
	 *
	 * @return string
	 */
	public function getChar( int$offset ):string
	{
		return substr($this->getcontent(),$offset,1);
	}

	/**
	 * Matching a preg pattern and return whether matches.
	 *
	 * @access public
	 *
	 * @param  string $pattern
	 *
	 * @return bool
	 */
	public function pregMatch( string$pattern ):bool
	{
		return !!preg_match($pattern,ltrim($this->content,"\t"));
	}

	/**
	 * Matching a preg pattern and return the all or one of groups of matchment.
	 *
	 * @access public
	 *
	 * @param  string       $pattern
	 * @param  int | string $match   Group index or name
	 *
	 * @return string
	 */
	public function pregGet( string$pattern, /*int|string*/$match=0 ):string
	{
		preg_match($pattern,ltrim($this->content,"\t"),$matches);
		return $matches[$match]??'';
	}

	/**
	 * Multiple matching a preg pattern and map result with a callback.
	 *
	 * @access public
	 *
	 * @param  string   $pattern
	 * @param  callable $callback
	 *
	 * @return array
	 */
	public function pregMap( string$pattern, callable$callback ):array
	{
		preg_match_all($pattern,ltrim($this->content,"\t"),$matches);
		return array_map($callback,...$matches);
	}

	/**
	 * Getting the indent level of this line, the number of starting tab characters.
	 *
	 * @access public
	 *
	 * @return int
	 */
	public function getIndentLevel():int
	{
		return strlen($this->content)-strlen(ltrim($this->content,"\t"));
	}

	/**
	 * Converting this object to string, returning content without indentation.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function __toString():string
	{
		return $this->getContent();
	}

	/**
	 * Wether this line is empty.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function isEmpty():bool
	{
		return !strlen($this->content);
	}

	/**
	 * Whether this line is last line.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function isLast():bool
	{
		return $this->isLast;
	}

	/**
	 * Whether this line is last line.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function noMore():bool
	{
		return $this->isLast;
	}

	/**
	 * Whether next line exists.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function hasMore():bool
	{
		return !$this->isLast;
	}

	/**
	 * Getting line with sub level indent( tab space tab ).
	 *
	 * @access public
	 *
	 * @return \Htsl\ReadingBuffer\Line
	 */
	public function getSubIndentLine():self
	{
		return new static(ltrim($this->getContent(),' '));
	}
}
