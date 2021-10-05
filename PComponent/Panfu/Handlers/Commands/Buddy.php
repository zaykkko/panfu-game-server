<?php

namespace PComponent\Panfu\Handlers\Commands;
use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;

trait Buddy {
	
	function handleUpdateBuddyStatus($std)
	{
		$panda1 = $this->getPlayerById($std->r);
		$panda2 = $this->getPlayerById($std->s);
		
		if($panda1 != null) {
			$panda1->buddies = $std->v === '0'?str_replace(','.$std->s,'',$panda1->buddies):$panda1->buddies.$std->s;
			$panda1->send("61;".$std->s.";{$std->v}|");
		}
		if($panda2 != null) {
			$panda2->buddies = $std->v === '0'?str_replace(','.$std->r,'',$panda2->buddies):$panda2->buddies.$std->r;
			$panda2->send("61;".$std->r.";{$std->v}|");
		}
	}
	
	function handleRecvFriendRequest($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if(!preg_match("/^[áéíóúÁÉÍÓÚ!ñ?:_@\/¿¡.,a-zA-Z0-9 \s]+$/i",Packet::$Duo[1]) || strpos(Packet::$Duo[1],'<') !== false || strpos(Packet::$Duo[1],'>') !== false || strpos(Packet::$Duo[1],'#') !== false) {
			return $this->banHammer(1010,$panda,strtotime("+1 hour"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
		
		if(strpos($panda->buddies,'1010') === false && ((isset($this->pandasByPlayerId[Packet::$Duo[0]]) && strrpos(Packet::$Duo[0],$panda->requestSended) === false) || Packet::$Duo[0] === '1010')) {
			if(Packet::$Duo[0] == '1010')
			{
				return $panda->send("40;1010;Ya te gustaría, {$panda->username}.|");
			}
			
			$panda->requestSended = $panda->requestSended.",".Packet::$Duo[0];
			
			$target = $this->pandasByPlayerId[Packet::$Duo[0]];
			
			$target->send("60;{$panda->id};<font color=\"#FF0000\">".Packet::$Duo[1]."</font>|");
		}
	}
	
	function handleFindFriendUbication($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$invalidRoom = Array(45,46,47,48,51,52,53,76,69,99);
		$id = Packet::$Duo[0];
		
		if($id === $panda->id) {
			return true;
		}
		
		if(strpos($panda->buddies,$id) !== false) {
			$target = $this->pandasByPlayerId[$id];
			$roomId = $target->room->externalId;
			
			if($target === NULL) {
				$message = "offline";
			} else if($panda->gaming) {
				$message = "gaming";
			} elseif($panda->room->externalId === $roomId) {
				$message = "sameRoom";
			} elseif(array_search($roomId,$invalidRoom) !== NULL) {
				$message = "notAllowed";
			} elseif($roomId === $id) {
				if($target->activeHouse === true) {
					$message = "locked";
				}
			} else {
				$message = "allowed";
			}
			
			$panda->send("23;$id;$message;$roomId|");
		}			
	}
}

?>