<?php

namespace Htsl\ReadingBuffer\Contracts;

use Htsl\Helper\TGetter;
use Htsl\Htsl;

////////////////////////////////////////////////////////////////

abstract class ABuffer
{
    use TGetter;

    /**
     * The htsl server.
     *
     * @var \Htsl\Htsl
     */
    protected $htsl;

    /**
     * The file name.
     *
     * @var string
     */
    protected $fileName = '';

    public function __construct(Htsl $htsl)
    {
        $this->htsl = $htsl;
    }

    /**
     * Get a line of the document.
     *
     * @return \Htsl\ReadingBuffer\Line
     */
    abstract public function getLine();

    /**
     * Get a line of the document.
     *
     * @return \Htsl\ReadingBuffer\Line
     */
    public function getFileName():string
    {
        return $this->fileName;
    }

    /**
     * Getting another file reference file of this buffer.
     *
     * @param string $filePath
     *
     * @return \Htsl\ReadingBuffer\Contracts\ABuffer
     */
    abstract public function goSide($fileName):self;
}
