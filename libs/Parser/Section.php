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
    private $content = '';

    /**
     * Setting name and constructing instance of Section.
     *
     * @param string | null $name
     */
    public function __construct(string $name = null)
    {
        $this->name = $name;
    }

    /**
     * Appending content to this section.
     *
     * @param string $content
     *
     * @return self
     */
    public function append(string $content):self
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * Getting content of this section.
     *
     * @return string
     */
    public function getContent():string
    {
        return $this->content;
    }

    /**
     * Getting name of this section.
     *
     * @return string | null
     */
    public function getName()//:string|null
    {
        return $this->name;
    }
}
