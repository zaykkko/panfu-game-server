<?php

namespace PComponent\Panfu\Handlers\Commands;
use PComponent\Logging\Logger;
use PComponent\DatabaseManager;
use PComponent\Panfu\Packets\Packet;

trait Premium {
	
	function sendGoldActivated($id)
	{
		$panda = $this->pandasByPlayerId[$id];
		
		if($panda !== NULL) {
			return $this->removePandaManager($panda,"120;MSG_UPDATE_SUCCESS|");
		}
	}
	
	function handleMobileThingy($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$id = Packet::$Duo[0];
		
		if($panda->moderator) {
			$targetPlayer = $this->getPlayerById($id);
			if($targetPlayer !== NULL){
				$targetPlayer->send("111;$id|");
			}
		} else {
			return $this->removePandaManager($panda,"260;Oh no amiguito.  |");
		}
	}
	
	function handleMobileThingo($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$id = Packet::$Duo[0];
		
		if($panda->moderator){
		
			$targetPlayer = $this->getPlayerById($id);
			if($targetPlayer !== NULL){
				$targetPlayer->send("110;$id|");
			}
		}else{
			return $this->removePandaManager($panda,"260;Oh no amiguito.  |");
		}
	}
	
	function handleUserActivatedMembership($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if($panda->identified === false) {
			$id = Packet::$Duo['body']['membership']['user'];
			
			call_user_func(array($this, 'sendGoldActivated'), $id);
			
			return $this->removePandaManager($panda);
		} 
		return $this->banHammer(1010,$panda,strtotime("+120 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
	}
	
	function handleMobiaThingy($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$state = Packet::$Duo[0];
		
		if($panda->state === 4) $panda->lastPing = time();
		
		$panda->state = $state;
		
		$panda->send("210;$state|");
	}
	
	function handleMobileFucker($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$id = Packet::$Duo[0];
		
		if($panda->moderator) {
			$targetPlayer = $this->getPlayerById($id);
			
			if($targetPlayer !== NULL) {
				$targetPlayer->send("112;$id|");
			}
		} else {
			return $this->removePandaManager($panda,"260;Oh no amiguito.  |");
		}
	}
	
}

?>