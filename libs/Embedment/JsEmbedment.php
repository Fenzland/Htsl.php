<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class JsEmbedment extends AEmbedment
{
    /**
     * Real constructor.
     */
    protected function construct()
    {
        $this->content .= "\n";
    }

    /**
     * Parsing line.
     *
     * @param \Htsl\ReadingBuffer\Line $line
     *
     * @return \Htsl\Embedment\Contracts
     */
    public function parseLine(Line $line):parent
    {
        $this->content .= $line->fullContent."\n";

        return $this;
    }
}
