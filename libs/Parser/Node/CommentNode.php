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

	/**
	 * Real contructor.
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode
	 */
	protected function construct():parent
	{
		$this->htmlComment= '!'!==$this->line->getChar(1);
		return $this;
	}

	/**
	 * Opening this node, and returning node opener.
	 *
	 * @return string
	 */
	public function open():string
	{
		return $this->htmlComment ?
		                         '<!--'.str_replace('-->','--'.chr(0xC).'>',substr($this->line->getContent(),1)):
		                         '<?php /* '.substr($this->line->getContent(),2);
	}

	/**
	 * Close this node, and returning node closer.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $closerLine  The line when node closed.
	 *
	 * @return string
	 */
	public function close( Line$closerLine ):string
	{
		return $this->htmlComment ? '-->' : ' */ ?>';
	}
}
