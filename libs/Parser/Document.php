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

/**
 * @property-read string                                 $content     Result of document executing.
 * @property-read string                                 $doctype     Type of this document.
 * @property-read string|bool                            $indentation Indentation of document.
 * @property-read \Htsl\Parser\Node\Contracts\ANode|bool $scope       Current scope on top of stack.
 * @property-read \Htsl\Htsl                             $htsl        Htsl main object of document.
 * @property-read int                                    $indentLevel Current indent level.
 */
class Document implements IConfigProvider
{
	use TGetter;

	/**
	 * Htsl main object owns this document.
	 *
	 * @access private
	 *
	 * @var \Htsl\Htsl
	 */
	private $htsl;

	/**
	 * Parent document.
	 *
	 * @access private
	 *
	 * @var \Htsl\Parser\Document | null
	 */
	private $parent;

	/**
	 * Reading buffer of this document.
	 *
	 * @access private
	 *
	 * @var \Htsl\ReadingBuffer\Contracts\ABuffer
	 */
	private $buffer;

	/**
	 * The indentation and whether output with linefeed and indent.
	 *
	 * @access private
	 *
	 * @var string | bool
	 */
	private $indentation;

	/**
	 * Type of this document.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $docType;

	/**
	 * Current embead script.
	 *
	 * @access private
	 *
	 * @var \Htsl\Embedment\Contract\Embedment
	 */
	private $embedment;

	/**
	 * Current indent level.
	 *
	 * @access private
	 *
	 * @var int
	 */
	private $level= 0;

	/**
	 * Section indent level.
	 *
	 * @access private
	 *
	 * @var int
	 */
	private $sectionLevel= 0;

	/**
	 * Opened nodes.
	 *
	 * @access private
	 *
	 * @var [ Htsl\Parser\Node\Contracts\ANode, ]
	 */
	private $openedNodes= [];

	/**
	 * Current scopes.
	 *
	 * @access private
	 *
	 * @var [ Htsl\Parser\Node\Contracts\ANode, ]
	 */
	private $scopes= [];

	/**
	 * Current line number.
	 *
	 * @access private
	 *
	 * @var int
	 */
	private $lineNumber= 0;

	/**
	 * Current line.
	 *
	 * @access private
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	private $currentLine;

	/**
	 * Sections that can be show.
	 *
	 * @access private
	 *
	 * @var [ Htsl\Parser\Section, ]
	 */
	private $sections=[];

	/**
	 * Whether the document is executed.
	 *
	 * @access private
	 *
	 * @var bool
	 */
	private $isExecuted;

	/**
	 * Current Section.
	 *
	 * @access private
	 *
	 * @var \Htsl\Parser\Section
	 */
	private $currentSection;

	/**
	 * The content of this document.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Constructor of the Document.
	 *
	 * @access public
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
	 * @access public
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
	 * @access public
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
	 * @access protected
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
	 * @access protected
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
	 * @access public
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
	 * @access public
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
	 * @access public
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
	 * @access public
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
	 * @access protected
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

		return $this->appendLine($docTypeContent);
	}

	/**
	 * Setting that this document extends another document.
	 *
	 * @access protected
	 *
	 * @param \Htsl\ReadingBuffer\Line $firstLine
	 *
	 * @return \Htsl\Parser\Document
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
	 * @access protected
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
	 * @access protected
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
	 * @access protected
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
	 * @access public
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
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseLine( Line$line ):self
	{
		$this->currentLine= $line;
		$this->setLevel($line->getIndentLevel());

		$this->{'parse'.ucfirst($this->getLineType($line))}($line);

		return $this;
	}

	/**
	 * Getting line type by analyzing first or first two characters of line.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return string
	 */
	protected function getLineType( Line$line ):string
	{
		$possibleType= self::POSSIBLE_LINE_TYPES;

		for( $i=0; is_array($possibleType); ++$i ){
			$possibleType= $possibleType[$line->getChar($i)]??$possibleType[' '];
		}

		return $possibleType;
	}

	/**
	 * Possible line types.
	 *
	 * @access public
	 *
	 * @const array
	 *
	 * @todo Make this const private when php 7.1
	 */
	const POSSIBLE_LINE_TYPES= [
		'`'=> [
			'='=> 'expressionHtmlLine',
			' '=> 'htmlLine',
		],
		'='=> 'expressionLine',
		'!'=> 'commentLine',
		'-'=> 'tagLine',
		'~'=> 'controlLine',
		'@'=> 'docControlLine',
		' '=> 'stringLine',
	];

	/**
	 * Parsing line as HTML content.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseHtmlLine( Line$line ):self
	{
		return $this->openNode(new StringNode($this,$line))->appendLine($line->slice(1));
	}

	/**
	 * Parsing line as string content.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseStringLine( Line$line ):self
	{
		return $this->openNode(new StringNode($this,$line))->appendLine($this->htmlEntities(trim($line->getContent())));
	}

	/**
	 * Parsing line as PHP expression.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseExpressionLine( Line$line ):self
	{
		$content=  $line->slice(1);
		$ent_flag= var_export($this->htsl->getConfig('ENT_flags',$this->docType),true);
		$charset=   var_export($this->htsl->getConfig('charset'),true);

		return $this->openNode(new StringNode($this,$line))->appendLine("<?=htmlentities($content,$ent_flag,$charset,false)?>");
	}

	/**
	 * Parsing line as PHP expression with HTML result.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseExpressionHtmlLine( Line$line ):self
	{
		$content=  $line->slice(1);

		return $this->openNode(new StringNode($this,$line))->appendLine("<?$content?>");
	}

	/**
	 * Parsing line as comment.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseCommentLine( Line$line ):self
	{
		$node= new CommentNode($this,$line);

		return $this->openNode($node)->appendLine($node->open());
	}

	/**
	 * Parsing line as HTSL tag.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseTagLine( Line$line ):self
	{
		$node= new TagNode($this,$line);

		$this->appendLine($node->open());

		$node->embed and $this->startEmbedding($node->embed);

		return $this->openNode($node);
	}

	/**
	 * Parsing line as control node of Htsl.php.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function parseControlLine( Line$line ):self
	{
		$node= new ControlNode($this,$line);

		return $this->appendLine($node->open())->openNode($node);
	}

	/**
	 * Parsing line as document control node of Htsl.php.
	 *
	 * @access protected
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
	 * @access protected
	 *
	 * @param  string $fileName
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function extend( string$fileName ):self
	{
		$this->parent= new static($this->htsl,$this->buffer->goSide($fileName));

		$this->docType= $this->parent->docType;
		$this->indentation= $this->parent->indentation;

		return $this;
	}

	/**
	 * Insert indentations into given paragraph.
	 *
	 * @access protected
	 *
	 * @param  string $input
	 *
	 * @return string
	 */
	protected function insertIndentations( string$input ):string
	{
		return preg_replace('/(?<=^|\\n)(?!$)/',str_repeat($this->indentation,$this->level-$this->sectionLevel),$input);
	}

	/**
	 * Include another document.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function include( Line$line ):self
	{
		$inclued= (new static($this->htsl,$this->buffer->goSide($line->pregGet('/(?<=\( ).*(?= \))/')),$this))->execute()->content??null;

		false===$this->indentation or $inclued= $this->insertIndentations($inclued);

		return $this->openNode(new StringNode($this,$line))->append($inclued);
	}

	/**
	 * Starting to define a section.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function defineSection( Line$line ):self
	{
		$node= new SectionNode($this,$line);

		$node->open();

		return $this->openNode($node);
	}

	/**
	 * Showing a section.
	 *
	 * @access protected
	 *
	 * @param  \Htsl\ReadingBuffer\Line   $line
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function showSection( Line$line ):self
	{
		$sectionName= $line->pregGet('/(?<=\( ).*(?= \))/');

		if( !isset($this->sections[$sectionName]) ){
			return $this->openNode(new StringNode($this,$line));
		}
		$content= $this->sections[$sectionName]->content;

		false===$this->indentation or $content= $this->insertIndentations($content);

		$this->append($content);

		$node= new NamelessSectionNode($this,$line);

		$node->open();

		return $this->openNode($node);
	}

	/**
	 * Setting document as section definer.
	 *
	 * @access public
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
			$this->throw("Section {$section->name} already defined.");
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
	 * @access protected
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
	 * @access public
	 *
	 * @param  string $input
	 *
	 * @return string
	 */
	public function htmlEntities( string$input ):string
	{
		return htmlentities($input,$this->htsl->getConfig('ENT_flags',$this->docType),$this->htsl->getConfig('charset'),false);
	}

	/**
	 * Setting indent level of this document.
	 *
	 * @access protected
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
	 * @access protected
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
	 * @access protected
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
	 * @access protected
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
	 * @access public
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
	 * @access protected
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
	 * @access protected
	 *
	 * @param  string $content
	 *
	 * @return \Htsl\Parser\Document
	 */
	protected function appendLine( string$content ):self
	{
		false===$this->indentation or $content= str_repeat($this->indentation,$this->level-$this->sectionLevel).$content."\n";

		return $this->append($content);
	}

	/**
	 * Appending some content to parsing result.
	 *
	 * @access protected
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
	 * @access public
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
	 * @access public
	 *
	 * @param  string $message
	 *
	 * @throw \Htsl\Parser\HtslParsingException
	 */
	public function throw( string$message )
	{
		throw new HtslParsingException("$message at file {$this->buffer->filePath} line $this->lineNumber");
	}
}
