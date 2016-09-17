<?php

namespace Htsl\Parser;

use Htsl\Helper\TGetter;

////////////////////////////////////////////////////////////////

/**
 * @property-read string        $content Content of this section.
 * @property-read string | null $content Name of this section.
 */
class Section
{
	use TGetter;

	/**
	 * Section name.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Content.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $content='';

	/**
	 * Setting name and constructing instance of Section.
	 *
	 * @access public
	 *
	 * @param string | null $name
	 */
	public function __construct( string$name=null )
	{
		$this->name = $name;
	}

	/**
	 * Appending content to this section.
	 *
	 * @access public
	 *
	 * @param  string $content
	 *
	 * @return self
	 */
	public function append( string$content ):self
	{
		$this->content.=$content;

		return $this;
	}

	/**
	 * Getting content of this section.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function getContent():string
	{
		return $this->content;
	}

	/**
	 * Getting name of this section
	 *
	 * @access public
	 *
	 * @return string | null
	 */
	public function getName()#:string|null
	{
		return $this->name;
	}
}
