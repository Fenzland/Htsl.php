<?php

namespace Htsl\Parser\Node;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Line;
use Htsl\Parser\Node\Contracts\ANode;

////////////////////////////////////////////////////////////////

class CommentNode extends ANode
{
	/**
	 * Whether the comment is html comment.
	 *
	 * @var bool
	 */
	private $htmlComment;

	protected function construct():parent
	{
		$this->htmlComment= '!'!==$this->line->getChar(1);
		return $this;
	}

	public function open():string
	{
		return $this->htmlComment ?
		                         '<!--'.str_replace('-->','--'.chr(0xC).'>',substr($this->line->getContent(),1)):
		                         '<?php /* '.substr($this->line->getContent(),2);
	}

	public function close( Line$closerLine ):string
	{
		return $this->htmlComment ? '-->' : ' */ ?>';
	}
}
