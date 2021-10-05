<?php
namespace PComponent\Panfu;
set_time_limit(0);
mb_internal_encoding("UTF-8");
use PComponent\BindException;

date_default_timezone_set("America/Monterrey");

//error_reporting(E_ALL ^ E_STRICT);

if(empty($argv[1]) && empty($_GET['name'])) die();

$ar = json_decode(file_get_contents(dirname(__FILE__).'/util/worlds.json'),true);

if(isset($_GET['name'])) {
	if(!isset($_GET['runcode'])) die();
	if($_GET['runcode'] != 'MaNeLiNe') die();
	if(isset($ar[$_GET['name']])){
		$server = $ar[$_GET['name']];
		$server['name'] = $_GET['name'];
	} else {
		die(sprintf("%cServidor \"".$_GET['name']."\" no encontrado.%c",10,10));
	}
} elseif(!isset($ar[$argv[1]]) && !isset($ar[$_GET['name']]) && (isset($_GET['runcode']) || isset($argv[2]))) {
	die(sprintf("%cServidor \"".$argv[1]."\" no encontrado.%c",10,10));
} else {
	if(!isset($argv[2])) die();
	if($argv[2] != 'MaNeLiNe') die();
	$server = $ar[$argv[1]];
	$server['name'] = $argv[1];
}

if(isset($_GET['conn']) && $_GET['conn'] === 'ignore') ignore_user_abort(true); 

spl_autoload_register(function ($path) {
	$realPath = str_replace("\\", "/", $path) . ".php";
	$includeSuccess = include_once $realPath;
	
	if(!$includeSuccess) {
		die('Ocurrio un error 1');
	}
});

if($server['id']===NULL){die('Ocurrió un error.');}

function start($t)
{
	try {
		$GLOBALS['exec'] = 1;
		
		print(sprintf('%cInicializando %s...%c',10,$t['name'],10));
		
		$gsc = new GameServer($t['id'],$t['lang'],$t['name'],$t['rl'],$t,$t['services']['ip'],$t['services']['port']);
		$gsc->setCapacity($t['capacity']);
		$gsc->listen($t['ip'],$t['port'],25,true,$t['services']['ip'],$t['services']['port']);
		
		do {
			$gsc->acceptPlayerClients();
			
		} WHILE($GLOBALS['exec'] === 1);
		
		$gsc->destroyServer();
		echo 'Servidor destruido...';
		exit();
	} catch(BindException $e) {
		die($e);
	}
}

start($server);

?>