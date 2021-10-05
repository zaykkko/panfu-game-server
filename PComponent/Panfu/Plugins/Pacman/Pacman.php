<?php
namespace PComponent\Panfu\Plugins\Pacman;

use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class Pacman extends Plugin {
	public $gameCommands = array(
		"78" => Array("handlePacmanMessage",self::Before)
	);
	public $_pName = "Pacman";
	public $xmlHandlers = array(null);
	public $texts;
	public $replace;
	public $server;
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
		$this->texts = Array(":'v","v':",':v','v:',':u','u:','.v','v.','8v','v8','pacman','hail','heil','holkea','hulkea','sdlg');
		$this->replace = Array('\'' => '','"' => '',' ' => '',',' => '');
		
		Logger::Info("Se agregaron " . count($this->texts) . " Pacmans a la blacklist. ;D",true);
	}
	
	function onDisconnect($panda) {
	}
	
	function handlePacmanMessage($panda) {
		if(!$panda->moderator) {
			$txt = strtolower(Packet::$Duo[0]);
			foreach($this->replace as $prp => $val) {
				$txt = str_replace($prp,$val,$txt);
			}
			
			foreach($this->texts as $word) {
				if(strpos($txt,$word) !== false) {
					Packet::$Handler = 'NONE';
					break;
				}
			}
		}
	}
	
}
?>