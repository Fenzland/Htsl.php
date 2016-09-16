<?php

namespace Htsl\ReadingBuffer;

use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

class Line
{
    use TGetter;

    /**
     * Line content.
     *
     * @var string
     */
    private $content;

    /**
     * Whether this line is last line.
     *
     * @var bool
     */
    private $isLast = false;

    /**
     * Constructor.
     *
     * @param string | bool $content String content for normal line and false for last line.
     */
    public function __construct(/*string|bool*/$content)
    {
        false === $content and $this->isLast = true;

        $this->content = rtrim($content, "\n");
    }

    /**
     * Getting content without indentation.
     *
     * @return string
     */
    public function getContent():string
    {
        return ltrim($this->content, "\t");
    }

    /**
     * Getting full content.
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
     * @param int $start
     * @param  int ...$lengths
     *
     * @return string
     */
    public function slice(int $start = 0, int ...$lengths):string
    {
        return substr($this->getContent(), $start, ...array_slice($lengths, 0, 1));
    }

    /**
     * Getting single character from content.
     *
     * @param int $offset
     *
     * @return string
     */
    public function getChar(int $offset):string
    {
        return substr($this->getcontent(), $offset, 1);
    }

    /**
     * Matching a preg pattern and return whether matches.
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function pregMatch(string $pattern):bool
    {
        return (bool) preg_match($pattern, ltrim($this->content, "\t"));
    }

    /**
     * Matching a preg pattern and return the all or one of groups of matchment.
     *
     * @param string       $pattern
     * @param int | string $match   Group index or name
     *
     * @return string
     */
    public function pregGet(string $pattern, /*int|string*/$match = 0):string
    {
        preg_match($pattern, ltrim($this->content, "\t"), $matches);

        return $matches[$match] ?? '';
    }

    /**
     * Multiple matching a preg pattern and map result with a callback.
     *
     * @param string   $pattern
     * @param callable $callback
     *
     * @return array
     */
    public function pregMap(string $pattern, callable $callback):array
    {
        preg_match_all($pattern, ltrim($this->content, "\t"), $matches);

        return array_map($callback, ...$matches);
    }

    /**
     * Getting the indent level of this line, the number of starting tab characters.
     *
     * @return int
     */
    public function getIndentLevel():int
    {
        // return (static function( $a ):int{$i=0;while($a{$i}==="\t")++$i;return $i;})($this->content);

        return strlen($this->content) - strlen(ltrim($this->content, "\t"));
    }

    /**
     * Converting this object to string, returning content without indentation.
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
     * @return bool
     */
    public function isEmpty():bool
    {
        return !strlen($this->content);
    }

    /**
     * Whether this line is last line.
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
     * @return bool
     */
    public function noMore():bool
    {
        return $this->isLast;
    }

    /**
     * Whether next line exists.
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
     * @return \Htsl\ReadingBuffer\Line
     */
    public function getSubIndentLine():self
    {
        return new static(ltrim($this->getContent(), ' '));
    }
}
