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
	 * @access private
	 *
	 * @var bool
	 */
	private $htmlComment;

	/**
	 * Real contructor.
	 *
	 * @access protected
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
	 * @access public
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
	 * @access public
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
