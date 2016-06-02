<?php

namespace Htsl;

use Htsl\ReadingBuffer\Contracts\ABuffer;
use Htsl\ReadingBuffer\StringBuffer;
use Htsl\ReadingBuffer\FileBuffer;
use Htsl\Parser\Document;
use Htsl\Helper\DefaultConfigs;

////////////////////////////////////////////////////////////////

class Htsl
{
	use Helper\TGetter;

	/**
	 * Getter of file content.
	 *
	 * @var callable
	 */
	protected $fileGetter;

	/**
	 * The base path.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * Constructor of HTSL
	 *
	 * @param array $config
	 */
	public function __construct( array$config=[] )
	{
		$this->config= $config+$this->getDefaultConfigs();
	}

	/**
	 * To parse a HTSL code string into HTML or PHP code string.
	 *
	 * @param  string $content
	 *
	 * @return string
	 */
	public function parse( string$content ):string
	{
		return $this->execute(new StringBuffer($this,$content));
	}

	/**
	 * To compile a HTSL file into a HTML or PHP file.
	 *
	 * @param  string $fromFile
	 * @param  string $toFile
	 *
	 * @return int|string
	 */
	public function compile( string$fromFile, string$toFile=null )
	{
		$fromFile= $this->getFilePath($fromFile);

		$result= $this->execute(new FileBuffer($this,$fromFile));

		if( $toFile ){
			return file_put_contents($toFile,$result);
		}else{
			return $result;
		}
	}

	public function setFileGetter( callable$fileGetter ):self
	{
		$this->fileGetter=$fileGetter;

		return $this;
	}

	public function setBasePath( string$basePath ):self
	{
		$this->basePath= '/'===substr($basePath,-1) ? substr($basePath,0,-1) : $basePath;

		return $this;
	}

	public function isDebug():bool
	{
		return !!$this->getConfig('debug');
	}

	/**
	 * Execute the parsing.
	 *
	 * @param  \Helper\ReadingBuffer\IBuffer $buffer
	 * @return string
	 */
	protected function execute( ABuffer$buffer ):string
	{
		return (new Document($this,$buffer))->content;
	}

	/**
	 * Get the config of Htsl.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getConfig( string...$keys )
	{
		$result= $this->config;

		foreach( $keys as $key ){
			if( !isset($result[$key]) )
				{ return null; }

			$result= $result[$key];
		}

		return $result;
	}

	public function getFilePath( string$filePath, string$path=null ):string
	{
		if( !isset($this->basePath) )
			{ throw new \Exception('BasePath musbe set.'); }

		if( !strlen($filePath) )
			{ throw new \Exception('FilePath cannot be empty.'); }

		if( '/'===$filePath{0} ){
			if( is_null($path) )
				{ return $filePath; }
			else
				{ return $this->basePath.$filePath; }
		}else{
			if( !strlen($path) )
				{ return $this->basePath.'/'.$filePath; }
			elseif( '/'===substr($path,-1) )
				{ return $path.$filePath; }
			else
				{ return $path.'/'.$filePath; }
		}

	}

	public function getFileContent( string$filePath ):string
	{
		return isset($this->fileGetter) ? $this->fileGetter($filePath) : file_get_contents($filePath);
	}

	/**
	 * Get default config.
	 *
	 * @return array
	 */
	private function getDefaultConfigs():array
	{
		return DefaultConfigs::get();
	}
}
