<?php

namespace PComponent\Panfu\Handlers\Commands;

use PComponent\Panfu\Room;
use PComponent\Logging\Logger;
use PComponent\Panfu\Handlers\Commands\Game;
use PComponent\DatabaseManager;
use PComponent\Panfu\Packets\Packet;

trait Navigation {
	
	function handleQuitGasme($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$panda->gaming = false;
		return $panda->send("10;{$panda->room->externalId}|");
	}
	
	function handleSoccering($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->room->externalId != 8) return Logger::Warn("User trying to call a room command. ID: ".Packet::$Handler);
		
		switch((int)Packet::$Handler) {
			case 130:
				$this->rooms[8]->_soccer->handleGetSoccerStatus($panda,Packet::$Duo);
				break;
			case 132:
				$this->rooms[8]->_soccer->handleShootGame($panda,Packet::$Duo);
				break;
			case 133:
				$this->rooms[8]->_soccer->handleGoalGame($panda,Packet::$Duo);
				break;
			case 135:
				$this->rooms[8]->_soccer->handleGetTeamInfo($panda,Packet::$Duo);
				break;
			case 136:
				$this->rooms[8]->_soccer->handleJoinSoccerTeam($panda,Packet::$Duo);
				break;
			case 131:
			case 134:
				break;
			default:
				Logger::Warn("Invalid packet registered. Id: ".Packet::$Handler);
		}
	}
	
	function handleHouseOpenClose($socket){
		$panda = $this->pandas[(int) $socket];
		
		$panda->activeHouse = (int) Packet::$Duo[0] === 0?false:true;
		$panda->send("38;".Packet::$Duo[0]."|");
	}
	
	function handleJoinGame($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$panda->gaming = true;
		$panda->send("11;".Packet::$Duo[0]."|");
	}
	
	function handleTeleport($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if(!$panda->mouvementBlocked) {
			if(($panda->room->externalId == 99 || $panda->room->externalId == 63) || (Packet::$Duo[0] > 0 && Packet::$Duo[0] < 772 && Packet::$Duo[1] > 0 && Packet::$Duo[1] < 480)) {
			
				$panda->x = Packet::$Duo[0];
				$panda->y = Packet::$Duo[1];
				
				$panda->room->send("21;{$panda->id};{$panda->x};{$panda->y};(avatarspeed=-1,standard=\"transport\",index=\"__ROOM__\")|");
			} else {
				$panda->x = $panda->y = 345;
				return $panda->room->send("50;{$panda->id};throw;345;345;teleportation;-1;true|");
			}
		}
	}
	
	function handleSendUpdateRoom($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if($panda->room->externalId > 1010 && !isset(Packet::$Duo[0])) {
			return $panda->room->send("33|");
		}
		
		return -1;
	}
	
	function changeHomeRoom($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if($panda->room->externalId > 1010) {
			$owner = Packet::$Duo[0];
			$roomId = Packet::$Duo[1];
			$tuopse = Packet::$Duo[2];
			$lerito = Packet::$Duo[3];
			$tompes = Packet::$Duo[4];
			
			return $panda->room->send("28;$owner;$roomId;$tuopse;$lerito;$tompes|");
		}
		return -1;
	}
	
	function handleGetRoomAttendes($socket)
	{
		$panda = $this->pandas[(int) $socket];
		//$_timer = $this->getPluginContext("AntiSpam")->getData($panda->id,"70");
		$attendes = $panda->room->getAttendes();
		
		return $panda->send("70;{$panda->room->externalId};$attendes|");
	}
	
	function timing($x1, $y1, $x2, $y2) {
		$dis = floor(sqrt(pow($x2-$x1,2) + pow($y2-$y1,2)));
		if($dis <= 1) return 1;
		return ($dis / 0.1);
	}
	
	function handleAvatarMove($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$x = Packet::$Duo[0];
		$y = Packet::$Duo[1];
		$xo = $panda->x;
		$yo = $panda->y;
		$move = isset(Packet::$Duo[2])?Packet::$Duo[2]:0;
		
		if(!$panda->mouvementBlocked) {
			if(($panda->room->externalId == 99 || $panda->room->externalId == 63) || ($x > 0 && $x < 780 && $y > 0 && $y < 480)) {
				$this->lastmove = time();
				if($x === $panda->x && $y === $panda->y) {
					return;
				}
				
				$panda->x = $x;
				$panda->y = $y;//$panda->speed
				if($move === 3) {
					$_ts = 0;
					$speed = $this->timing($xo,$yo,$x,$y);
					
					if($panda->timingsp - round(microtime(true)*1000) > 0) {
						$_ts = round(($panda->timingsp-round(microtime(true)*1000))/2);
					} else {
						$panda->timingsp = round((microtime(true)*1000)+$speed+$_ts);
					}
					
					$speed = $speed + $_ts;
					
					return $panda->room->send("20;{$panda->id};$speed;$x;$y;3;(avatarspeed={$speed},standard=null,index=\"".((!$panda->isInHouse)?"__ROOM__":"__HOUSE__"). "\")|");
				}
				return $panda->room->send("20;{$panda->id};1000;$x;$y;$move;(avatarspeed=1000,standard=null,index=\"".((!$panda->isInHouse)?"__ROOM__":"__HOUSE__"). "\")|");
			} else {
				$panda->x = $panda->y = 345;
				return $panda->room->send("50;{$panda->id};throw;345;345;teleportation;-1;true|");
			}
		}
	}
	
	function handleTeleSit($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$p = Packet::$Duo[0];
		$p2 = Packet::$Duo[1];
		$p3 = isset(Packet::$Duo[2])?Packet::$Duo[2]:0;
		$p4 = isset(Packet::$Duo[3])?Packet::$Duo[3]:0;
		
		return $panda->room->send("140;$p;$p2;$p3;$p4|");
	}
	
	function handleChangeRoom($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$roomId = Packet::$Duo[0];
		
		if(isset($this->rooms[$roomId])) {
			if(count($this->rooms[$roomId]->pandas) >= 30) {
				return $panda->send("260;La sala a la que intentas ingresar está actualmente completa, inténtalo de nuevo más tarde.  |");
			}
		}
		
		$x = Packet::$Duo[1] === NULL?$panda->x:Packet::$Duo[1];
		$y = Packet::$Duo[2] === NULL?$panda->y:Packet::$Duo[2];
		$panda->isInHouse = false;
		$direction = Packet::$Duo[4] === NULL?$panda->frame:Packet::$Duo[4];
		$panda->appeared = false;
		$panda->mouvementBlocked = true;
		$panda->generatedIds = "";
		
		return $this->joinRoom($panda,$roomId,$x,$y,$direction);
	}
	
	function handleGetPlayers($socket)
	{
		$total = $this->getPlayersString();
		$panda = $this->pandas[(int) $socket];
		
		return $panda->send("29;$total");
	}
	
	function handleGetPendejos($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$panda->mouvementBlocked = false;
		return $panda->send("212;null|");
	}
	
	function handleSoloGame($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$solo = Packet::$Duo[0];
		$panda->soloGaming = true;
		
		return $panda->send("300;$solo|");
	}
	
	function handleDoLoggerVerification($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if(!$panda->identified) {
			return $this->removePandaManager($panda);
		}
		
		if(Packet::$Duo[0] !== $panda->username) {
			return $this->removePandaManager($panda);
		}
		
		if(Packet::$Duo[1] !== 'ES' && Packet::$Duo[1] !== 'EN') {
			return $this->removePandaManager($panda);
		}
		
		//$idp = Byter::writeBytes($panda->id);
		
		$verifier = MD5($panda->randKey."jI83i{0}".($panda->id - $panda->delimitions)."%\"{0}#45.WoK".MD5($panda->username."5{0}02".$panda->randKey).$panda->id."$\"/tCñ1{0},".($panda->delimitions * 10));
		
		if(Packet::$Duo[2] !== 'h6x4pbv0ufffa2f90cc157fuaio744e7o') {
			return $this->removePandaManager($panda);
		}
		
		if(Packet::$Duo[3] !== $verifier) {
			return $this->removePandaManager($panda);
		}
		
		$panda->verifiedTimes = 2;
		$panda->send("45;1|");
		$this->joinRoom($panda,$this->getOpenRoom(),rand(200,500),rand(200,400));
		$this->updateServerCount($panda);
		$panda->updateLocalInfo($this->serverId,time());
	}

	function joinHome($id, $panda)
	{
		if(!isset($this->rooms[$id])){
			$this->rooms[$id] = new Room($id,$id + 14,false);
			$this->joinRoom($panda,$id,-345,-345,2);
			return $panda->send("26;$id;-345;-345;2;0|");
		}else{
			$this->joinRoom($panda,$id,-345,-345,2);
			return $panda->send("26;$id;-345;-345;2;0|");
		}
	}
	
	function handleEnterHouse($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if((int) Packet::$Duo[0] === 1010 || (int) Packet::$Duo[0] === 1009) return $panda->send("46|");
		
		$panda->x = Packet::$Duo[1];
		$panda->y = Packet::$Duo[2];
		$target = $this->pandasByPlayerId[Packet::$Duo[0]];
		
		$open = $target === NULL?true:$target->activeHouse;
		
		if($open || $target->id === $panda->id) {
			if(!isset($this->rooms[Packet::$Duo[0]])){
				$this->rooms[Packet::$Duo[0]] = new Room(Packet::$Duo[0],Packet::$Duo[0] + 14,false);
				$this->joinRoom($panda,Packet::$Duo[0],Packet::$Duo[1],Packet::$Duo[2],Packet::$Duo[4],'false',true);
				return $panda->send("26;".Packet::$Duo[0].";".$panda->x.";".$panda->y.";0;0|");
			}
			
			$this->joinRoom($panda,Packet::$Duo[0],Packet::$Duo[1],Packet::$Duo[2],Packet::$Duo[4],'false',true);
			return $panda->send("26;".Packet::$Duo[0].";".$panda->x.";".$panda->y.";0;0|");
		}
		
		return $panda->send("46|");
	}
	
	function onBoughtItem($user) {
		$panda = $this->getPlayerById($user);
		
		if($panda !== NULL) {
			if($panda->isInHouse) {
				return $panda->send("33|");
			}
			
			return;
		}
		
		return;
	}
	
	function joinRoom($panda, $roomId, $x = 0, $y = 0, $frame = 5, $resp = "disp", $house = false) {
		if(!isset($this->rooms[$roomId])) {
			return $panda->send("260;¿Qué tipo de sala es esa? - w -  |");
		} elseif(isset($panda->room)) {
			$panda->room->remove($panda);
		}
		
		$panda->isInHouse = $house;
		$panda->frame = $frame;
		$panda->x = $x;
		$panda->y = $y;
		$this->rooms[$roomId]->add($panda,$resp);
	}
	
	// Considering making this public
	function getOpenRoom() {
		$o = Array(1,2,3,4,5,6,7,10,11,12,13,22);
		shuffle($o);
		$m = $o[rand(0,count($o)-1)];

		foreach($o as $room) {
			if(sizeof($this->rooms[$room]->pandas) < 30) {
				return $room;
			}
		}
		
		return 22;
	}
}

?>
