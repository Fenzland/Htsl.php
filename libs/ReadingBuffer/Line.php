<?php

namespace Htsl\ReadingBuffer;

use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

class Line
{
	use TGetter;

	private $content;
	private $isLast=false;

	public function __construct( /*string|bool*/$content )
	{
		false===$content and $this->isLast=true;

		$this->content= rtrim($content,"\n");
	}

	public function getContent():string
	{
		return ltrim($this->content,"\t");
	}

	public function getFullContent():string
	{
		return $this->content;
	}

	public function getChar( int$offset ):string
	{
		$content= $this->getcontent();
		$length= strlen($content);

		if( $offset>=$length || $offset<-$length )return '';

		return $offset>=0 ?
		                 $content{$offset}:
		                 strrev($content){-$offset};
	}

	public function pregMatch( string$pattern ):bool
	{
		return !!preg_match($pattern,ltrim($this->content,"\t"));
	}

	public function pregGet( string$pattern, /*int|string*/$match=0 ):string
	{
		preg_match($pattern,ltrim($this->content,"\t"),$matches);
		return $matches[$match]??'';
	}

	public function pregMap( string$pattern, callable$callback )
	{
		preg_match_all($pattern,ltrim($this->content,"\t"),$matches);
		return array_map($callback,...$matches);
	}

	public function getIndentLevel():int
	{
		// return (static function( $a ):int{$i=0;while($a{$i}==="\t")++$i;return $i;})($this->content);

		return strlen($this->content)-strlen(ltrim($this->content,"\t"));
	}

	public function __toString():string
	{
		return $this->getContent();
	}

	public function isLast():bool
	{
		return $this->isLast;
	}

	public function noMore():bool
	{
		return $this->isLast;
	}

	public function hasMore():bool
	{
		return !$this->isLast;
	}

	public function getSubIndentLine():self
	{
		return new static(ltrim($this->getContent(),' '));
	}
}
