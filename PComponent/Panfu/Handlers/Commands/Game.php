<?php

namespace Handlers\Commands;

class Game {

	public $rpandas = array();
	public $roomCreator;
	public $name;
	
	public $turn;
	public $guest;
	
	public $stillWaiting = true;
	
	public $started = false;
	
	public $secondPlayer = false;
	public $firstPlayer = false;
	
	function __construct($name, $panda) {
		$this->name = $name;
		$this->roomCreator = $panda;
		$this->rpandas[$panda->id] = $panda;
	}
	
	function playerReady($id)
	{
		if(isset($this->rpenguisn[$id])){
			if($this->guest->id === $id){
				$this->secondPlayer = true;
			}else{
				$this->firstPlayer = true;
			}
		}
		if($this->secondPlayer && $this->firstPlayer){
			$this->turn = $this->roomCreator->id;
			$this->started = true;
		}
	}
	
	function restartGame($name, $panda)
	{
		if($this->guest->username === $name){
			$this->turn = $this->guest->id;
			$this->send("15;25;{$this->turn};-2;RESTART;$name|");
		}elseif($this->roomCreator->username === $name){
			$this->turn = $this->roomCreator->id;
			$this->send("15;25;{$this->turn};-2;RESTART;$name|");
		}else{
			$this->endGame($panda,"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function endGame($fault, $reason)
	{
		
		unset($this->pandasWaiting['fourboom'][$this->roomCreator->id]);
		if(!isset($this->guest)){
			$this->roomCreator->send("15;25;{$this->roomCreator->id};unsetPlayer|");
			$this->roomCreator->send("15;25;{$this->roomCreator->id};leave|");
			$this->roomCreator->send("260;Ah pues, quedaste solito xdxdxd|");
			$this->roomCreator->send("10;{$this->roomCreator->room->externalId}|");
			$this->roomCreator->gameManager = null;
			$this->roomCreator->gaming = false;
			$this->roomCreator->game = 0;
		}else{
			if($reason === "logoff"){
				if($fault->id === $this->roomCreator->id){
					$this->guest->send("15;25;{$this->guest->id};unsetPlayer|");
					$this->guest->send("15;25;{$this->guest->id};leave|");
					$this->guest->send("260;{$fault->username} ha cerrado sesión.|");
					$this->guest->send("10;{$this->guest->room->externalId}|");
				}else{
					$this->roomCreator->send("15;25;{$this->guest->id};unsetPlayer|");
					$this->roomCreator->send("15;25;{$this->guest->id};leave|");
					$this->roomCreator->send("260;{$fault->username} ha cerrado sesión.|");
					$this->roomCreator->send("10;{$this->roomCreator->room->externalId}|");
				}
			}elseif($reason === "ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData){
				$fault->send("80;KICK_BLACKLIST_MSG|");
				if($fault->id === $this->roomCreator->id){
					$this->guest->send("15;25;{$this->guest->id};unsetPlayer|");
					$this->guest->send("15;25;{$this->guest->id};leave|");
					$this->guest->send("260;{$fault->username} hizo trampa y fué suspendido en forma definitiva.|");
					$this->guest->send("10;{$this->guest->room->externalId}|");
				}else{
					$this->roomCreator->send("15;25;{$this->guest->id};unsetPlayer|");
					$this->roomCreator->send("15;25;{$this->guest->id};leave|");
					$this->roomCreator->send("260;{$fault->username} hizo trampa y fué suspendido en forma definitiva.|");
					$this->roomCreator->send("10;{$this->roomCreator->room->externalId}|");
				}
				$this->removePandaManager($fault);
			}else{
				$this->send("15;25;{$fault->id};unsetPlayer|");
				$this->send("15;25;{$fault->id};leave|");
				$this->send("260;{$fault->username} se rindió.|");
				$this->send("10;{$fault->room->externalId}|");
			}
			
			unset($this->rpandas[$this->roomCreator->id]);
			unset($this->rpandas[$this->guest->id]);
			$this->roomCreator->gameManager = null;
			$this->roomCreator->gaming = false;
			$this->guest->gameManager = null;
			$this->guest->gaming = false;
			$this->roomCreator->game = 0;
			$this->guest->game = 0;
			$this->guest = null;
			$this->roomCreator = null;
		}
	}
	
	function updateTurn()
	{
		if($this->turn === $this->roomCreator->id){
			$this->turn = $this->guest->id;
		}else{
			$this->turn = $this->roomCreator->id;
		}
		
		$this->rpandas[$this->turn]->send("15;25;{$this->turn};-2;ACTIONRESPONSE|");
	}
	
	function joinUser($user)
	{
		$this->stillWaiting = false;
		
		$user->gameManager = $this;
		
		$this->guest = $user;
		$this->rpandas[$user->id] = $user;
		
		$this->roomCreator->send("15;25;{$this->guest->id};setPlayer;2|");
		$this->guest->send("15;25;{$this->roomCreator->id};setPlayer;1|");
	}
	
	function send($data) {
		foreach($this->rpandas as $panda) {
			$panda->send($data);
		}
	}
	
}