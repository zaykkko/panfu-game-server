<?php
namespace PComponent\Panfu\Plugins\AntiSpam;

use PComponent\Logging\Logger;
use PComponent\Events;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class AntiSpam extends Plugin {
	
	public $gameCommands = Array(
		"0" => Array("reverseSpam", self::Before),
		"301" => Array("reverseSpam", self::Before),
		"70" => Array("reverseSpam", self::Before),
		"212" => Array("reverseSpam", self::Before),
		"20" => Array("reverseSpam", self::Before),
		"25" => Array("reverseSpam", self::Before),
		"78" => Array("reverseSpam", self::Before),
		"41" => Array("reverseSpam", self::Before),
		"43" => Array("reverseSpam", self::Before),
		"50" => Array("reverseSpam", self::Before),
		"113" => Array("reverseSpam", self::Before),
		"110" => Array("reverseSpam", self::Before),
		"111" => Array("reverseSpam", self::Before),
		"44" => Array("reverseSpam", self::Before),
		"112" => Array("reverseSpam", self::Before),
		"38" => Array("reverseSpam", self::Before),
		"210" => Array("reverseSpam", self::Before),
		"452" => Array("reverseSpam", self::Before),
		"1050" => Array("reverseSpam", self::Before),
		"21" => Array("reverseSpam", self::Before),
		"140" => Array("reverseSpam", self::Before),
		"81" => Array("reverseSpam", self::Before),
		"60" => Array("reverseSpam", self::Before),
		"29" => Array("reverseSpam", self::Before),
		"11" => Array("reverseSpam", self::Before),
		"16" => Array("reverseSpam", self::Before),
		"26" => Array("reverseSpam", self::Before),
		"130" => Array("reverseSpam", self::Before),
		"131" => Array("reverseSpam", self::Before),
		"114" => Array("reverseSpam", self::Before),
		"115" => Array("reverseSpam", self::Before),
		"122" => Array("reverseSpam", self::Before),
		"123" => Array("reverseSpam", self::Before),
		"124" => Array("reverseSpam", self::Before),
		"125" => Array("reverseSpam", self::Before),
		"132" => Array("reverseSpam", self::Before),
		"133" => Array("reverseSpam", self::Before),
		"134" => Array("reverseSpam", self::Before),
		"135" => Array("reverseSpam", self::Before),
		"136" => Array("reverseSpam", self::Before),
		"14" => Array("reverseSpam", self::Before),
		"15" => Array("reverseSpam", self::Before),
		"211" => Array("reverseSpam", self::Before),
		"756" => Array("reverseSpam", self::Before),
		"23" => Array("reverseSpam", self::Before),
		"42" => Array("reverseSpam", self::Before),
		"33" => Array("reverseSpam", self::Before),
		"28" => Array("reverseSpam", self::Before),
		"300" => Array("reverseSpam", self::Before)
	);
	
	public $xmlHandlers = array(null);
	
	public $savedPros = array();
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
		Logger::Info("Se activo el sistema anti-spam.");
	}
	
	function onDisconnect($panda) {
		if(isset($this->savedPros[$panda->id])) {
			$this->savedPros[$panda->id] = null;
			unset($this->savedPros[$panda->id]);
		}
	}
	
	function getData($a, $b) {
		return (!isset($this->savedPros[$a][$b]['previous']))?null:$this->savedPros[$a][$b]['previous'];
	}
	
	function reverseSpam($panda) {
		if($panda->moderator) return -1;
		$pandaId = $panda->id;
		$packetType = Packet::$Handler;
		
		if(!isset($this->savedPros[$pandaId][$packetType])) {
			$this->savedPros[$pandaId][$packetType]['previous'] = microtime(true);
			$this->savedPros[$pandaId][$packetType]['spam'] = 0;
			return;
		}
		
		if(!isset($this->savedPros[$pandaId][$packetType]['spam'])) {
			$this->savedPros[$pandaId][$packetType]['spam'] = 2;
		}
		
		$lastData = $this->savedPros[$pandaId][$packetType]['previous'];
		
		if($lastData > (microtime(true) - 0.28)) {
			$this->savedPros[$pandaId][$packetType]['spam']++;
		}
		$spamCount = $this->savedPros[$pandaId][$packetType]['spam'];
		
		if($spamCount > 15) {
			$this->server->checkModeratorCheck(Array('timeout',$panda->username,2),null);
			Packet::$Handler = 'NONE';
		}
		
		if($spamCount > 27) {
			Packet::$Handler = 'NONE';
			$this->savedPros[$pandaId][$packetType]['spam'] = 22;
			$this->server->kickPlayer($panda,"Server");
		}
		
		foreach($this->savedPros as $checkPandaId => $spam) {
			if(isset($this->savedPros[$checkPandaId][$packetType]['previous'])) {
				$lastData = $this->savedPros[$checkPandaId][$packetType]['previous'];
				if($lastData < (microtime(true) - 10)) {
					unset($this->savedPros[$checkPandaId]);
				}
			}
		}
		
		if(isset($this->savedPros[$pandaId])) {
			$this->savedPros[$checkPandaId][$packetType]['previous'] = microtime(true);
		}
	}
	
}
?>