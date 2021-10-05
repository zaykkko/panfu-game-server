<?php
namespace PComponent\Panfu\Plugins\AntiAd;
use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;
final class AntiAd extends Plugin {
	public $gameCommands = array(
		"78" => Array("handleSendMessage",self::Before)
	);
	public $xmlHandlers = array(null);
	public $urls;
	public $_pName = "AntiAd";
	public $excludables = array("cpps");
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
		$this->urls = Array('supercpps.com','clubpenguinbrasil.pw','disney.com','fluffypenguin.com','dinotuscpps.com','clubpenguinonline.com','cprewritten.net');
		foreach($this->urls as $url) {
			$url = explode(".", $url);
			$this->urls[] = implode("", $url);
			if(!in_array($url[0], $this->excludables)) {
				$this->urls[] = $url[0];
			}
		}
		Logger::Info("Se agregaron " . count($this->urls) . " URLs a la blacklist.",true);
	}
	
	function onDisconnect($panda) {
	}
	
	function handleSendMessage($panda) {
		$message = Packet::$Duo[0];
		if(!$this->check($message)) {
			Packet::$Handler = 'NONE';
			Logger::Warn("{$panda->username} envio un link malo => '$message'.");
		}
	}
	
	function check($string) {
		#~ reverse any dumb shit the user did to fool the system
		$normalizeChars = array(
			'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
			'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
			'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
			'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
			'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
			'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
			'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
			'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
		);
		$string = strtr($string, $normalizeChars);
		$string = strtolower($string);
		$old = $string;
		$string .= preg_replace("/(.)\\1+/", "$1", $old);
		$string = preg_replace("/[^A-Za-z0-9]/", "", $string);
		$english = array("a", "e", "s", "a", "o", "t", "l", "H", "W", "M", "D", "V", "x", ".");
		$leet = array("4", "3", "z", "4", "0", "+", "1", "|-|", "\\/\\/", "|\\/|", "|)", "\\/", "><", "dot");
		$string = str_replace($leet, $english, $string);
		#~ check if their message contains a banned URL
		foreach($this->urls as $url) {
			if(strpos($string, $url) !== false){
				return false;
			}
			$url = preg_replace("/(.)\\1+/", "$1", $url);
			if(strpos($string, $url) !== false &&  $url != $old) {
				return false;
			}
		}
		return true;
	}
}
?>