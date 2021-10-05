<?php
namespace PComponent\Panfu\Plugins\TimeoutCheck;

use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class TimeoutCheck extends Plugin {
	public $gameCommands = array(
		"20" => Array("handleTimeouted", self::Before),
		"25" => Array("handleTimeouted", self::Before),
		"78" => Array("handleTimeouted", self::Before),
		"41" => Array("handleTimeouted", self::Before),
		"43" => Array("handleTimeouted", self::Before),
		"210" => Array("handleTimeouted", self::Before),
		"50" => Array("handleTimeouted", self::Before),
		"113" => Array("handleTimeouted", self::Before),
		"110" => Array("handleTimeouted", self::Before),
		"111" => Array("handleTimeouted", self::Before),
		"44" => Array("handleTimeouted", self::Before),
		"112" => Array("handleTimeouted", self::Before),
		"38" => Array("handleTimeouted", self::Before),
		"452" => Array("handleTimeouted", self::Before),
		"21" => Array("handleTimeouted", self::Before),
		"140" => Array("handleTimeouted", self::Before),
		"81" => Array("handleTimeouted", self::Before),
		"60" => Array("handleTimeouted", self::Before),
		"29" => Array("handleTimeouted", self::Before),
		"11" => Array("handleTimeouted", self::Before),
		"16" => Array("handleTimeouted", self::Before),
		"26" => Array("handleTimeouted", self::Before),
		"130" => Array("handleTimeouted", self::Before),
		"131" => Array("handleTimeouted", self::Before),
		"114" => Array("handleTimeouted", self::Before),
		"115" => Array("handleTimeouted", self::Before),
		"122" => Array("handleTimeouted", self::Before),
		"123" => Array("handleTimeouted", self::Before),
		"124" => Array("handleTimeouted", self::Before),
		"125" => Array("handleTimeouted", self::Before),
		"132" => Array("handleTimeouted", self::Before),
		"133" => Array("handleTimeouted", self::Before),
		"134" => Array("handleTimeouted", self::Before),
		"135" => Array("handleTimeouted", self::Before),
		"136" => Array("handleTimeouted", self::Before),
		"14" => Array("handleTimeouted", self::Before),
		"15" => Array("handleTimeouted", self::Before),
		"211" => Array("handleTimeouted", self::Before),
		"756" => Array("handleTimeouted", self::Before),
		"23" => Array("handleTimeouted", self::Before),
		"42" => Array("handleTimeouted", self::Before),
		"33" => Array("handleTimeouted", self::Before),
		"28" => Array("handleTimeouted", self::Before),
		"300" => Array("handleTimeouted", self::Before)
	);
	public $_pName = "TimeoutCheck";
	public $xmlHandlers = array(null);
	public $server;
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
		
		Logger::Info("Se agregaron " . count($this->gameCommands) . " comandos para realizar chequeos en caso de Time Out.",true);
	}
	
	function onDisconnect($panda) {
	}
	
	function handleTimeouted($panda) {
		$time = $this->server->_save->get($panda->id,"timeout");
		if($time != -1) {
			if($time >= strtotime("now")) {
				Packet::$Handler = 'none';
			} else {
				$this->server->_save->_delete($panda->id,"timeout");
				$panda->timeouted = false;
			}
		}
	}
	
}
?>