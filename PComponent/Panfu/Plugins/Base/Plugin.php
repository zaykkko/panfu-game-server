<?php

namespace PComponent\Panfu\Plugins\Base;

use PComponent\Panfu\Logger;
use PComponent\Panfu\Packets\Packet;

abstract class Plugin implements IPlugin {

	public $dependencies = array();
	
	public $gameCommands = array();
	
	public $_pName = null;
	
	public $xmlHandlers = array();
	
	public $loginStalker = false;
	
	public $worldStalker = false;
	
	public $loginServer = false;

	public $eventBinder = false;
	
	protected $server = null;
	
	private $pluginName;
	
	function __construct($pluginName) {
		$readableName = basename($pluginName);
		
		if($this->eventBinder !== true) {
			if(empty($this->xmlHandlers)) {
				$this->loginStalker = true;
			}

			if(empty($this->gameCommands)) {
				$this->worldStalker = true;
			}
		}

		$this->pluginName = $readableName;
	}

	abstract function onReady();
	abstract function onDisconnect($panda);
	
	function handleXmlPacket($panda, $beforeCall = true) {
		if(isset($this->xmlHandlers[Packet::$Handler])) {
			list($methodName) = $this->xmlHandlers[Packet::$Handler];
			
			if(method_exists($this, $methodName)) {
				call_user_func(array($this, $methodName), $panda);
			} else {
				Logger::Warn("Method '$methodName' doesn't exist in plugin '{$this->pluginName}'");
			}
		}
	}
	
	function handleWorldPacket($panda, $beforeCall = true) {
		if(isset($this->gameCommands[Packet::$Handler])) {
			list($methodName) = $this->gameCommands[Packet::$Handler];
			
			if(method_exists($this, $methodName)) {
				call_user_func(array($this, $methodName), $panda);
			} else {
				Logger::Warn("Method '$methodName' doesn't exist in plugin '{$this->pluginName}'");
			}
		}
	}
	
}

?>
