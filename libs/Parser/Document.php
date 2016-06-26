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
	 * Htsl main object.
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
	 * Reading buffer.
	 *
	 * @var \Htsl\ReadingBuffer\IBuffer
	 */
	private $buffer;

	/**
	 * Whether output with \n and indent.
	 *
	 * @var bool
	 */
	private $indentation;

	/**
	 * The document type.
	 *
	 * @var string
	 */
	private $docType;

	/**
	 * The current embead script.
	 *
	 * @var \Htsl\Embedment\Contract\Embedment
	 */
	private $embedment;

	/**
	 * The current indent level.
	 *
	 * @var int
	 */
	private $level= 0;

	/**
	 * The section indent level.
	 *
	 * @var int
	 */
	private $sectionLevel= 0;

	/**
	 * The opened nodes.
	 *
	 * @var array
	 */
	private $openedNodes= [];

	/**
	 * The current scopes.
	 *
	 * @var array
	 */
	private $scopes= [];

	/**
	 * The current line number.
	 *
	 * @var int
	 */
	private $lineNumber= 0;

	/**
	 * The current line.
	 *
	 * @var \Htsl\ReadingBuffer\Line
	 */
	private $currentLine;

	/**
	 * The document content.
	 *
	 * @var array
	 */
	private $sections=[];

	/**
	 * Whether the document is executed.
	 *
	 * @var bool
	 */
	private $isExecuted;

	/**
	 * The document content.
	 *
	 * @var \Htsl\Parser\Section
	 */
	private $currentSection;

	/**
	 * The document content.
	 *
	 * @var string
	 */
	private $content;

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

	public function execute():self
	{
		if( $this->isExecuted ){
			return $this;
		}
		return $this->lineByLine()
		            ->bubbleSections()
		;
	}

	public function __toString():string
	{
		return $this->getContent();
	}

	protected function getContent():string
	{
		if( $this->parent ){
			return $this->execute()->parent->getContent();
		}else{
			return $this->execute()->content;
		}
	}

	protected function getLine():Line
	{
		return $this->buffer->getLine();
	}

	public function getConfig( string...$keys )
	{
		return $this->htsl->getConfig(array_shift($keys),$this->docType,...$keys);
	}

	public function getDoctype():string
	{
		return $this->docType;
	}

	public function getIndentation()
	{
		return $this->indentation;
	}

	public function getIndentLevel():int
	{
		return $this->level;
	}

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

	protected function embeddingParse( Line$line ):self
	{
		if( $line->content==='<}' ){
			$this->breakEmbedding();
		}else{
			$this->embedment->parseLine($line->getSubIndentLine());
		}
		return $this;
	}

	protected function startEmbedding( string$embedType ):self
	{
		$embedmentClass= '\\Htsl\\Embedment\\'.ucfirst($embedType).'Embedment';
		class_exists($embedmentClass) or $this->throw("Embed type $embedType not exists.");

		$this->embedment= new $embedmentClass($this);

		return $this;
	}

	public function breakEmbedding():self
	{
		$this->append($this->embedment->getContent());
		$this->embedment= null;

		return $this;
	}

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

	protected function parseHtmlLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$this->appendLine($line->slice(1));

		return $this;
	}

	protected function parseStringLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$this->appendLine($this->htmlEntities(trim($line->getContent())));

		return $this;
	}

	protected function parseExpressionLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$content=  $line->slice(1);
		$ent_flag= $this->htsl->getConfig('ENT_flags',$this->docType);

		$this->appendLine("<?=htmlentities($content,'$ent_flag','UTF-8',false);?>");

		return $this;
	}

	protected function parseExpressionHtmlLine( Line$line ):self
	{
		$node= new StringNode($this,$line);

		$this->openNode($node);

		$content=  $line->slice(1);

		$this->appendLine("<?$content?>");

		return $this;
	}

	protected function parseCommentLine( Line$line ):self
	{
		$node= new CommentNode($this,$line);

		$this->openNode($node);

		$this->appendLine($node->open());

		return $this;
	}

	protected function parseTagLine( Line$line ):self
	{
		$tag= new TagNode($this,$line);

		$this->appendLine($tag->open());

		$tag->embed and $this->startEmbedding($tag->embed);

		$this->openNode($tag);

		return $this;
	}

	protected function parseControlLine( Line$line ):self
	{
		$controlStructure= new ControlNode($this,$line);

		$this->appendLine($controlStructure->open());

		$this->openNode($controlStructure);

		return $this;
	}

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

	protected function extend( string$fileName ):self
	{
		$this->parent= new static($this->htsl,$this->buffer->goSide($fileName),null,$this->indentation);

		$this->docType= $this->parent->docType;
		$this->indentation= $this->parent->indentation;

		return $this;
	}

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

	protected function defineSection( Line$line ):self
	{
		$node= new SectionNode($this,$line);

		$node->open();

		$this->openNode($node);

		return $this;
	}

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

	protected function bubbleSections()
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

	public function htmlEntities( string$input ):string
	{
		return htmlentities($input,$this->htsl->getConfig('ENT_flags',$this->docType),'UTF-8',false);
	}

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

	protected function openNode( Node$node ):self
	{
		array_push($this->openedNodes,$node);

		$node->scope and $this->setScope($node);

		return $this;
	}

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

	protected function setScope( Node$scope ):int
	{
		return array_unshift($this->scopes,$scope);
	}

	public function getScope()
	{
		return $this->scopes[0]??null;
	}

	protected function removeScope( Node$scope ):self
	{
		if( $scope!==array_shift($this->scopes) ){
			$this->throw('Scope nesting error');
		};

		return $this;
	}

	protected function appendLine( string$content ):self
	{
		if( false!==$this->indentation ){
			$content= str_repeat($this->indentation,$this->level-$this->sectionLevel).$content."\n";
		}

		return $this->append($content);
	}

	protected function append( string$content ):self
	{
		if( $this->currentSection ){
			$this->currentSection->append($content);
		}else{
			$this->content.=$content;
		}

		return $this;
	}

	public function getHtsl()
	{
		return $this->htsl;
	}

	public function throw( string$message )
	{
		throw new HtslParsingException("$message at file {$this->buffer->fileName} line $this->lineNumber");
	}
}
