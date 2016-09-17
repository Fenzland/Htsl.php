<?php

namespace Htsl\Parser;

use Htsl\Htsl;
use Htsl\ReadingBuffer\Contracts\ABuffer as Buffer;
use Htsl\ReadingBuffer\Line;
use Htsl\Helper\TGetter;
use Htsl\Helper\IConfigProvider;
use Htsl\Parser\Node\TagNode;
use Htsl\Parser\Node\StringNode;
use Htsl\Parser\Node\CommentNode;
use Htsl\Parser\Node\ControlNode;
use Htsl\Parser\Node\SectionNode;
use Htsl\Parser\Node\NamelessSectionNode;
use Htsl\Parser\Node\Contracts\ANode as Node;

////////////////////////////////////////////////////////////////

class Document implements IConfigProvider
{
	use TGetter;

	/**
	 * Htsl main object owns this document.
	 *
	 * @var \Htsl\Htsl
	 */
	private $htsl;

	/**
	 * Parent document.
	 *
	 * @var \Htsl\Parser\Document
	 */
	private $parent;

	/**
	 * Reading buffer of this document.
	 *
	 * @var \Htsl\ReadingBuffer\Contracts\ABuffer
	 */
	private $buffer;

	/**
	 * The indentation and whether output with linefeed and indent.
	 *
	 * @var string | bool
	 */
	private $indentation;

	/**
	 * Type of this document.
	 *
	 * @var string
	 */
	private $docType;

	/**
	 * Current embead script.
	 *
	 * @var \Htsl\Embedment\Contract\Embedment
	 */
	private $embedment;

	/**
	 * Current indent level.
	 *
	 * @var int
	 */
	private $level= 0;

	/**
	 * Section indent level.
	 *
	 * @var int
	 */
	private $sectionLevel= 0;

	/**
	 * Opened nodes.
	 *
	 * @var [ Htsl\Parser\Node\Contracts\ANode, ]
	 */
	private $openedNodes= [];

	/**
	 * Current scopes.
	 *
	 * @var [ Htsl\Parser\Node\Contracts\ANode, ]
	 */
	private $scopes= [];

	/**
	 * Current line number.
	 *
	 * @var int
	 */
	private $lineNumber= 0;

	/**
	 * Current line.
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	private $currentLine;

	/**
	 * Sections that can be show.
	 *
	 * @var [ Htsl\Parser\Section, ]
	 */
	private $sections=[];

	/**
	 * Whether the document is executed.
	 *
	 * @var bool
	 */
	private $isExecuted;

	/**
	 * Current Section.
	 *
	 * @var \Htsl\Parser\Section
	 */
	private $currentSection;

	/**
	 * The content of this document.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Constructor of the Document.
	 *
	 * @param \Htsl\Htsl                            $htsl
	 * @param \Htsl\ReadingBuffer\Contracts\ABuffer $buffer
	 * @param \Htsl\Parser\Document | null          $parent
	 */
	public function __construct( Htsl$htsl, Buffer$buffer, self$parent=null )
	{
		$this->htsl= $htsl;
		$this->buffer= $buffer;

		if( $parent ){
			$this->parent= $parent;
			$this->docType= $parent->docType;
			$this->indentation= $parent->indentation;
		}else{
			$this->parseFirstLine();
		}
	}

	/**
	 * Executing the document.
	 *
	 * @return \Htsl\Parser\Document
	 */
	public function execute():self
	{
		if( $this->isExecuted ){
			return $this;
		}
		return $this->lineByLine()
		            ->bubbleSections()
		;
	}

	/**
	 * Alias of getContent.
	 *
	 * @return string
	 */
	public function __toString():string
	{
		return $this->getContent();
	}

	/**
	 * Getting the result of document executing.
	 *
	 * @return string
	 */
	protected function getContent():string
	{
		if( $this->parent ){
			return $this->execute()->parent->getContent();
		}else{
			return $this->execute()->content;
		}
	}

	/**
	 * Getting the next line.
	 *
	 * @return \Htsl\ReadingBuffer\Line
	 */
	protected function getLine():Line
	{
		do{
			$line= $this->buffer->getLine();
		}while( $line->isEmpty() && $line->hasMore() );

		return $line;
	}

	/**
	 * Getting the config of type of this document.
	 *
	 * @param  [ string, ] ...$keys
	 *
	 * @return mixed
	 */
	public function getConfig( string...$keys )
	{
		return $this->htsl->getConfig(array_shift($keys),$this->docType,...$keys);
	}

	/**
	 * Getting the type of this document.
	 *
	 * @return string
	 */
	public function getDoctype():string
	{
		return $this->docType;
	}

	/**
	 * Getting the indentation.
	 *
	 * @return string | bool
	 */
	public function getIndentation()
	{
		return $this->indentation;
	}

	/**
	 * Getting the indent level.
	 *
	 * @return int
	 */
	public function getIndentLevel():int
	{
		return $this->level;
	}

	/**
	 * Parsing the first line.
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseFirstLine():self
	{
		$line= $this->getLine();

		if( '@'===$line->getChar(0) ){
			return $this->setExtending($line);
		}

		$this->docType= $line->content;
		$docTypeContent= $this->getConfig('doc_types') or $this->throw("DocType $this->docType is not supported");

		$this->indentation= $this->htsl->getConfig('indentation',$this->docType) ?? ( function( $scalarOrFalse ){ return is_scalar($scalarOrFalse)?$scalarOrFalse:false; } )($this->htsl->getConfig('indentation'));

		$this->appendLine($docTypeContent);

		return $this;
	}

	/**
	 * Setting that this document extends another document.
	 *
	 * @param \Htsl\ReadingBuffer\Line $firstLine
	 */
	protected function setExtending( Line$firstLine ):self
	{
		switch( $name= $firstLine->pregGet('/(?<=^@)[\w-:]+/') ){
			default:{
				$this->throw("The @$name is not supported.");
			}break;
			case 'extend':{
				$this->extend($firstLine->pregGet('/(?<=\( ).*(?= \))/'));
			}break;
			case 'show':
			case 'include':
			case 'section':{
				$this->throw("The @$name can not be used on first line.");
			}break;
		}

		return $this;
	}

	/**
	 * Parsing this document line by line.
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function lineByLine():self
	{
		while( ($line= $this->getLine())->hasMore() ){
			$this->lineNumber+= 1;

			if( $this->embedment ){
				$this->embeddingParse($line);
			}else{
				$this->parseLine($line);
			}
		}

		$this->embedment and $this->breakEmbedding();

		$this->closeNodes($this->level);

		$this->isExecuted= true;

		return $this;
	}

	/**
	 * Parsing embedded line.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function embeddingParse( Line$line ):self
	{
		if( $line->content==='<}' ){
			$this->breakEmbedding();
		}else{
			$this->embedment->parseLine($line->getSubIndentLine());
		}
		return $this;
	}

	/**
	 * Starting the embedding.
	 *
	 * @param  string $embedType
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function startEmbedding( string$embedType ):self
	{
		$embedmentClass= '\\Htsl\\Embedment\\'.ucfirst($embedType).'Embedment';
		class_exists($embedmentClass) or $this->throw("Embed type $embedType not exists.");

		$this->embedment= new $embedmentClass($this);

		return $this;
	}

	/**
	 * Ending the embedding.
	 *
	 * @return \Htsl\Parser\Document
	 */
	public function breakEmbedding():self
	{
		$this->append($this->embedment->getContent());
		$this->embedment= null;

		return $this;
	}

	/**
	 * Parsing line.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseLine( Line$line ):self
	{
		$this->currentLine= $line;
		$this->setLevel($line->getIndentLevel());

		switch( $line->getChar(0) ){
			default:{
				$this->parseStringLine($line);
			}break;
			case '`':{
				if( '='===$line->getChar(1) )
					{ $this->parseExpressionHtmlLine($line); }
				else
					{ $this->parseHtmlLine($line); }
			}break;
			case '=':{
				$this->parseExpressionLine($line);
			}break;
			case '!':{
				$this->parseCommentLine($line);
			}break;
			case '-':{
				$this->parseTagLine($line);
			}break;
			case '~':{
				$this->parseControlLine($line);
			}break;
			case '@':{
				$this->parseDocControlLine($line);
			}break;
		}
		return $this;
	}

	/**
	 * Parsing line as HTML content.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseHtmlLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$this->appendLine($line->slice(1));

		return $this;
	}

	/**
	 * Parsing line as string content.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseStringLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$this->appendLine($this->htmlEntities(trim($line->getContent())));

		return $this;
	}

	/**
	 * Parsing line as PHP expression.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseExpressionLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$content=  $line->slice(1);
		$ent_flag= $this->htsl->getConfig('ENT_flags',$this->docType);

		$this->appendLine("<?=htmlentities($content,'$ent_flag','UTF-8',false);?>");

		return $this;
	}

	/**
	 * Parsing line as PHP expression with HTML result.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseExpressionHtmlLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$content=  $line->slice(1);

		$this->appendLine("<?$content?>");

		return $this;
	}

	/**
	 * Parsing line as comment.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseCommentLine( Line$line ):self
	{
		$node= new CommentNode($this,$line);

		$this->openNode($node);

		$this->appendLine($node->open());

		return $this;
	}

	/**
	 * Parsing line as HTSL tag.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseTagLine( Line$line ):self
	{
		$tag= new TagNode($this,$line);

		$this->appendLine($tag->open());

		$tag->embed and $this->startEmbedding($tag->embed);

		$this->openNode($tag);

		return $this;
	}

	/**
	 * Parsing line as control node of Htsl.php.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseControlLine( Line$line ):self
	{
		$controlStructure= new ControlNode($this,$line);

		$this->appendLine($controlStructure->open());

		$this->openNode($controlStructure);

		return $this;
	}

	/**
	 * Parsing line as document control node of Htsl.php.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseDocControlLine( Line$line ):self
	{
		switch( $name= $line->pregGet('/(?<=^@)[\w-:]+/') ){
			default:{
				$this->throw("The @$name is not supported.");
			}break;
			case 'extend':{
				$this->throw('The @extend can only be used on first line.');
			}break;
			case 'include':{
				$this->include($line);
			}break;
			case 'section':{
				$this->defineSection($line);
			}break;
			case 'show':{
				$this->showSection($line);
			}break;
		}

		return $this;
	}

	/**
	 * Parsing extending defination.
	 *
	 * @param  string $fileName
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function extend( string$fileName ):self
	{
		$this->parent= new static($this->htsl,$this->buffer->goSide($fileName),null,$this->indentation);

		$this->docType= $this->parent->docType;
		$this->indentation= $this->parent->indentation;

		return $this;
	}

	/**
	 * Include another document.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function include( Line$line ):self
	{
		$inclued= (new static($this->htsl,$this->buffer->goSide($line->pregGet('/(?<=\( ).*(?= \))/')),$this,$this->indentation))->execute()->content;

		if( false!==$this->indentation ){
			$inclued= preg_replace('/(?<=^|\\n)(?!$)/',str_repeat($this->indentation,$this->level-$this->sectionLevel),$inclued);
		}

		$node= new StringNode($this,$line);

		$this->openNode($node);

		$this->append($inclued);

		return $this;
	}

	/**
	 * Starting to define a section.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function defineSection( Line$line ):self
	{
		$node= new SectionNode($this,$line);

		$node->open();

		$this->openNode($node);

		return $this;
	}

	/**
	 * Showing a section.
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function showSection( Line$line ):self
	{
		$sectionName= $line->pregGet('/(?<=\( ).*(?= \))/');

		if( !isset($this->sections[$sectionName]) ){
			$this->openNode(new StringNode($this,$line));

			return $this;
		}
		$content= $this->sections[$sectionName]->content;

		if( false!==$this->indentation ){
			$content= preg_replace('/(?<=^|\\n)(?!$)/',str_repeat($this->indentation,$this->level),$content);
		}

		$this->append($content);

		$node= new NamelessSectionNode($this,$line);

		$node->open();

		$this->openNode($node);

		return $this;
	}

	/**
	 * Setting document as section definer.
	 *
	 * @param Section | null $section
	 */
	public function setSection( Section$section=null ):self
	{
		if( !$section ){
			$this->sectionLevel= 0;
			$this->currentSection= null;

			return $this;
		}

		if( $this->currentSection ){
			$this->throw('Nesting definition of section is forbidden.');
		}

		if( isset($this->parent->sections[$section->name]) ){
			$this->throw("Section $sectionName already defined.");
		}

		$this->currentSection= $section;

		if( $section->name ){
			$this->parent->sections[$section->name]=$section;
		}

		$this->sectionLevel= $this->level+1;

		return $this;
	}

	/**
	 * Bubble the sections to parent document.
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function bubbleSections():self
	{
		if( $this->parent ){
			foreach( $this->sections as $name=>$section ){
				if( !isset($this->parent->sections[$name]) ){
					$this->parent->sections[$name]=$section;
				};
			}
		}

		return $this;
	}

	/**
	 * Escaping characters to HTML entities.
	 *
	 * @param  string $input
	 *
	 * @return string
	 */
	public function htmlEntities( string$input ):string
	{
		return htmlentities($input,$this->htsl->getConfig('ENT_flags',$this->docType),'UTF-8',false);
	}

	/**
	 * Setting indent level of this document.
	 *
	 * @param int $level
	 */
	protected function setLevel( int$level ):self
	{
		$level-= $this->level;

		if( $level<=0 ){
			$this->closeNodes(-$level);
		}elseif( $level==1 ){
			$this->level+= 1;
		}else{
			$this->throw('Indent error.');
		}

		return $this;
	}

	/**
	 * Opening a node.
	 *
	 * @param  \Htsl\Parser\Node\Contracts\ANode $node
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function openNode( Node$node ):self
	{
		array_push($this->openedNodes,$node);

		$node->scope and $this->setScope($node);

		return $this;
	}

	/**
	 * Closing open node or nodes.
	 *
	 * @param  int $level
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function closeNodes( int$level=0 ):self
	{
		if( empty($this->openedNodes) ) return $this;

		while( $level-->=0 ){
			$node= array_pop($this->openedNodes);

			$node->scope and $this->removeScope($node);

			$closer=$node->close($this->currentLine) and $this->appendLine($closer);

			$this->level-= $level>=0 ?1:0;
		}

		return $this;
	}

	/**
	 * Pushing a scope to stack.
	 *
	 * @param \Htsl\Parser\Node\Contracts\ANode $scope
	 */
	protected function setScope( Node$scope ):int
	{
		return array_unshift($this->scopes,$scope);
	}

	/**
	 * Getting current scope on top of stack.
	 *
	 * @return \Htsl\Parser\Node\Contracts\ANode | null
	 */
	public function getScope()
	{
		return $this->scopes[0]??null;
	}

	/**
	 * Pop a scope from stack.
	 *
	 * @param  \Htsl\Parser\Node\Contracts\ANode $scope
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function removeScope( Node$scope ):self
	{
		if( $scope!==array_shift($this->scopes) ){
			$this->throw('Scope nesting error');
		};

		return $this;
	}

	/**
	 * Appending a line of content to parsing result.
	 *
	 * @param  string $content
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function appendLine( string$content ):self
	{
		if( false!==$this->indentation ){
			$content= str_repeat($this->indentation,$this->level-$this->sectionLevel).$content."\n";
		}

		return $this->append($content);
	}

	/**
	 * Appending some content to parsing result.
	 *
	 * @param  string $content
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function append( string$content ):self
	{
		if( $this->currentSection ){
			$this->currentSection->append($content);
		}else{
			$this->content.=$content;
		}

		return $this;
	}

	/**
	 * Getting the Htsl main object.
	 *
	 * @return \Htsl\Htsl
	 */
	public function getHtsl()
	{
		return $this->htsl;
	}

	/**
	 * Throw exception with document name and line number.
	 *
	 * @param  string $message
	 *
	 * @throw \Htsl\Parser\HtslParsingException
	 */
	public function throw( string$message )
	{
		throw new HtslParsingException("$message at file {$this->buffer->fileName} line $this->lineNumber");
	}
}
