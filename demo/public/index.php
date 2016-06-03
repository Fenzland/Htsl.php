<?php
if( substr(phpversion(),0,1)<7 ){
	echo 'PHP 7.0 or later is requierd.';
	die;
};

$realFile= __DIR__.$_SERVER['REQUEST_URI'];
if( file_exists($realFile) && !is_dir($realFile) && $realFile!==__FILE__ )
	{ return false; }

require __DIR__.'/../../vendor/autoload.php';

$htsl= new Htsl\Htsl();

$basePath= dirname(__DIR__).'/views';

$htsl->setBasePath($basePath);

$pathInfo= $_SERVER['PATHINFO']??strtok($_SERVER['REQUEST_URI'],'?');

$filePath= $basePath.(($pathInfo==='/'?'/index':$pathInfo).'.htsl');
$compiledPath= '/tmp/htsl_compiled/'.md5($filePath);

file_exists('/tmp/htsl_compiled') or mkdir('/tmp/htsl_compiled');

if( file_exists($filePath) ){
	$htsl->compile($filePath,$compiledPath);
}else{
	$compiledPath= '/tmp/htsl_compiled/404';
	$htsl->compile('404.htsl',$compiledPath);
}
header('Content-type:'.mime_content_type($compiledPath));
include $compiledPath;


function z( $data )
{
	echo '<!--';
	var_dump($data);
	echo '-->';
	return $data;
}

function d( ...$data )
{
	var_dump(...$data);
	die;
}
