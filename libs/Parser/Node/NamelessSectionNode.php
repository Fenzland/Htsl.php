<?php

namespace Htsl\Parser\Node;

use Htsl\Parser\Node\Contracts\ANode;
use Htsl\Parser\Section;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class NamelessSectionNode extends ANode
{
    /**
     * The name of the section.
     *
     * @var string
     */
    private $name;

    /**
     * Real constructor.
     *
     * @return \Htsl\Parser\Node\Contracts\ANode
     */
    protected function construct():parent
    {
        $this->name = null;

        return $this;
    }

    /**
     * Opening this node, and returning node opener.
     *
     * @return string
     */
    public function open():string
    {
        $this->document->setSection(new Section($this->name));

        return '';
    }

    /**
     * Close this node, and returning node closer.
     *
     * @param \Htsl\ReadingBuffer\Line $closerLine The line when node closed.
     *
     * @return string
     */
    public function close(Line $closerLine):string
    {
        $this->document->setSection(null);

        return '';
    }
}
