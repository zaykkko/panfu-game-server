<?php

namespace PComponent\Panfu;

use PComponent\Events;
use PComponent\Logging\Logger;

class Room {

	public $pandas = array();
	private static $bot_id = 1010;
	
	public $externalId;
	public $internalId;
	public $_names = "";
	
	public $_soccer;
	public $_isSocc = false;
	
	public $houses = Array();
	
	public $requestUser;
	
	function __construct($externalId, $internalId, $isGame) {
		$this->externalId = $externalId;
		$this->internalId = $internalId;
		$this->isGame = $isGame;
	}
	
	function _c() {
		$this->_soccer = new Handlers\Game\Soccer($this);
		$this->_isSocc = true;
		Logger::Info("Soccer initiated.");
	}
	
	function add($panda,$resp) {
		array_push($this->pandas, $panda);
		$this->_names = $this->_names . "," . $panda->username;
		
		$command = "10;{$this->externalId}|578;1010:{$this->externalId}:none::0x00FF00:HirukoFont:0xC66700:~ Lady Bot ~:HirukoFont:INDEFINIDO:0xFF0000:0:INDEFINIDO:0:1:delimited=false&&moderator=1&&bannedBefore=false&&hash=-1:false;{$panda->id}:{$this->externalId}:none:".implode(':',$panda->attributes).":delimited=false&&moderator=".$panda->moderator."&&bannedBefore=false&&hash=-1:false";
		
		foreach($this->pandas as $user) {
			if($user->id !== $panda->id) {
				$command = $command.";{$user->id}:{$this->externalId}:none:".implode(':',$user->attributes).":delimited=false&&moderator=".$user->moderator."&&bannedBefore=false&&hash=-1:false";
			}
		}
		$panda->send($command."|");
		
		$panda->room = $this;
		
		$panda->room->send("30;{$panda->id};{$panda->room->externalId};{$panda->x};{$panda->y};{$panda->username};{$panda->frame}|577;{$panda->id};{$panda->room->externalId};none;".implode(';',$panda->attributes).";delimited=false&&moderator=".$panda->moderator."&&bannedBefore=false&&hash=-1;false|",$panda->id);
	}
	
	function restart($dwb) {
		$this->remove($dwb);
		$this->add($dwb,'-1');
	}
	
	function remove($panda, $type = "none", $cmd = null) {
		$playerIndex = array_search($panda, $this->pandas);
		$this->_names = str_replace(",{$panda->username}","",$this->_names);
		unset($this->pandas[$playerIndex]);
		if($type === "none"){
			if($cmd != null) {
				$this->send("31;{$panda->id}|" . $cmd);
			} else {
				$this->send("31;{$panda->id}|");
			}
		}else{
			$this->send("32;{$panda->id}|");
		}
		
		if($this->_isSocc) $this->_soccer->_remove($panda->id,$this);
	}
	
	function bot($action, array $args)
	{
		switch($action) {
			case 'throw':
				$this->send("50;".self::$bot_id.";throw;".$args[0].";".$args[1].";".$args[2].";".$args[3].";false|");
				break;
			case 'move':
				$this->send("20;".self::$bot_id.";". $args[3] . ";".$args[0].";".$args[1].";".$args[2]."|");
				break;
			case 'state':
				if(isset($args[1])) {
					$this->send("113;".self::$bot_id.";14;".$args[0].";".$args[1]."|");
				} else {
					$this->send("113;".self::$bot_id.";14;".$args[0]."|");
				}
				break;
			case 'message':
				$this->send("40;".self::$bot_id.";".$args[0]."|");
				break;
		}
	}
	
	function send($data, $target = 12, $data2 = null) {
		foreach($this->pandas as $panda) {
			if($panda->id !== $target){
				$panda->send($data);
			} elseif($data2 != null) {
				$panda->send($data2);
			}
		}
	}
	
	function getAttendes()
	{
		$mom = "";
		foreach($this->pandas as $panda) {
			$mom = $mom.$panda->id.":".$panda->x.":".$panda->y.":".$panda->username.":0:".$panda->state.":".$panda->frame.";";
		}
		
		$mom = $mom."|";
		if(strrpos($mom,";|") !== false) {
			$mom = str_replace(";|","",$mom);
		}
		
		$mom = $mom.';1010:345:345:~ Lady Bot ~:0:0:6';
		
		return $mom;
	}
	
}

?>