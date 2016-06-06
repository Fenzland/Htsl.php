<?php

namespace Htsl\Embedment;

use Htsl\Embedment\Contracts\AEmbedment;
use Htsl\ReadingBuffer\Line;

////////////////////////////////////////////////////////////////

class CodeEmbedment extends AEmbedment
{
	public function parseLine( Line$line ):parent
	{
		$content= '<code>'.htmlentities($line->fullContent).'</code>';

		$indentation= $this->document->indentation;

		false!==$indentation and $content= str_repeat($indentation,$this->document->indentLevel).$content."\n";

		$this->content.= $content;

		return $this;
	}
}
