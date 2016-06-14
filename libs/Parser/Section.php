<?php

namespace Htsl\Parser;

use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

class Section
{
	use TGetter;

	/**
	 * Section name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Content.
	 *
	 * @var string
	 */
	private $content='';

	public function __construct( string$name )
	{
		$this->name = $name;
	}

	public function append($content):self
	{
		$this->content.=$content;

		return $this;
	}

	public function getContent():string
	{
		return $this->content;
	}
}
