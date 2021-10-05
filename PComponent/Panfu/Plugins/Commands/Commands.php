<?php

namespace PComponent\Panfu\Plugins\Commands;

use PComponent\Panfu\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class Commands extends Plugin {
	
	public $dependencies = array("AntiAd" => "loadAntiAd");
	
	public $gameCommands = array(
		"s" => array(
			"m#sm" => array("handlePlayerMessage", self::Both)
		)
	);
	
	public $_pName = "Commands";
	
	public $xmlHandlers = array(null);
	
	public $commandPrefixes = array("!", "/", "~");
	
	public $commands = array();
	
	private $mutedPenguins = array();
	
	private $patchedItems;
	private $antiAd;
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
	}
	
	function onDisconnect($panda) {
	}
	
	function loadPatchedItems() {
		$this->patchedItems = $this->server->loadedPlugins["PatchedItems"];
	}
	
	function buyItem($panda, $arguments) {
		list($itemId) = $arguments;
		
		$this->patchedItems->handleBuyInventory($panda, $itemId);
	}
	
	function joinRoom($panda, $arguments) {
		list($roomId) = $arguments;
		
		$this->server->joinRoom($panda, $roomId);
	}
	
	function addCoins($panda, $arguments) {
		list($coinAmt) = $arguments;
		
		if(is_numeric($coinAmt)) {
			if($coinAmt >= 0 && $coinAmt <= 10000) {
				$panda->addCoins($coinAmt);
			}
		}
	}
	
	function kickPlayer($panda, $arguments) {
		if($panda->moderator) {
			$targetUsername = implode(" ", $arguments);
			$targetPlayer = $this->server->getPlayerByName($targetUsername);
			
			if($targetPlayer !== null) {
				if($targetPlayer->moderator) {
					return;
				}
				
				$targetPlayer->send("%xt%moderatormessage%-1%3%");
				$this->server->removePenguin($targetPlayer);
			}
		}
	}
	
	function loadAntiAd() {
		$this->antiAd = $this->server->loadedPlugins["AntiAd"];
	}
	
	function handlePlayerMessage($panda) {
		$message = Packet::$Duo[0];
		
		$firstCharacter = substr($message, 0, 1);
		if(in_array($firstCharacter, $this->commandPrefixes)) {
			$messageParts = explode(" ", $message);
			
			$command = $messageParts[0];
			$command = substr($command, 1);
			$command = strtoupper($command);
			
			$arguments = array_splice($messageParts, 1);
		}
	}
	
}

?>
