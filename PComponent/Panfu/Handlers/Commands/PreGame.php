<?php

namespace PComponent\Panfu\Handlers\Commands;

use PComponent\Panfu\Game;
use PComponent\Panfu\Packets\Packet;
use PComponent\Logging\Logger;

trait PreGame { 

	function handleGameManagerMessage($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if((int) Packet::$Duo[0] == 41) {
			if($panda->botting && $panda->gaming) {
				$this->botting(15,Packet::$Duo,$panda);
			} else if($panda->gaming) {
				$panda->gameManager->recv(15,Packet::$Duo,$panda);
			}
		} else if((int) Packet::$Duo[0] === 25) {
			if($panda->gaming && isset($panda->gameManager)) {
				$panda->gameManager->recv(15,Packet::$Duo,$panda);
			} else if(Packet::$Duo[1] == "unsetPlayer") {
				$panda->game = "none";
				$panda->gaming = false;
				if(($a = array_search($panda,$this->pandasWaiting['fourboom'])) !== false) {
					array_splice($this->pandasWaiting['fourboom'],$a,1);
				}
			}
		}
	}
	
	function handleEnterMultigame($socket)
	{
		//14;14;ec7953dcf0aab7ce0f8c5baee1cf578a;41;1013
		$panda = $this->pandas[(int) $socket];
		
		if((int) Packet::$Duo[0] == 41) {
			if($panda->botting && $panda->gaming) {
				$this->botting(14,Packet::$Duo,$panda);
			} else if($panda->gaming) {
				$panda->gameManager->recv(14,Packet::$Duo,$panda);
			}
		} else if((int) Packet::$Duo[0] === 25) {
			if(!$panda->gaming) {
				$this->createGameInstance("4Boom",$panda);
			} else {
				$panda->gameManager->recv(14,Packet::$Duo,$panda);
			}
		}
	}
	
	function botting($a, $b, $c) {
		switch($a) {// 1 => PIEDRA 2 => PAPEL 3 => TIJERA
			case 14:
				$c->send("15;41;{$c->id};setPlayer;1|15;41;2;1010;SECONDPLAYER;~ Lady Bot ~|");
				break;
			case 15:
				if(isset($b[2]) && $b[2] == "ACTION") {
					if(rand(0,3) == 1) {
						if($b[3] == "1") {
							$num = 3;
						} else if($b[3] == "2") {
							$num = 1;
						} else if($b[3] == "3") {
							$num = 2;
						} else {
							$num = rand(1,3);
						}
					} else {
						$num = rand(1,3);
					}
					
					//1 => tijera 2 => papel 3 => piedra
					
					if($num == 3 && $b[3] == "1") {
						$c->_rps['_botScore']++;
					} else if($b[3] == "3" && $num == 1) {
						$c->_rps['_userScore']++;
					} else if($num == 2 && $b[3] == "3") {
						$c->_rps['_botScore']++;
					} else if($b[3] == "2" && $num == 3) {
						$c->_rps['_userScore']++;
					} else if($num == 1 && $b[3] == "2") {
						$c->_rps['_botScore']++;
					} else if($b[3] == "1" && $num == 2) {
						$c->_rps['_userScore']++;
					}
					
					if((int)$c->_rps['_userScore'] == 3) {
						$c->database->leaderboard("rps",$c->id,2,2,false,$c->username);
						$c->_rps['_botScore'] = 0;
						$c->_rps['_userScore'] = 0;
						$c->botting = $c->gaming = false;
						Logger::Info("Leaderboard updated to: " . $c->username);
					} elseif((int)$c->_rps['_botScore'] == 3) {
						$c->database->leaderboard("rps",$c->id,1,1,false,$c->username);
						Logger::Info("Leaderboard updated to: " . $c->username);
						$c->_rps['_botScore'] = 0;
						$c->_rps['_userScore'] = 0;
						$c->botting = $c->gaming = false;
					}
					
					$c->send("15;41;1010;2;ACTION;" . $num . "|");
				}
				break;
			default:
				Logger::Warn("Succes!");
		}
	}
	
	function handleGetHotbombStatus($socket) {
	}
	
	function handleHotbombExplode($socket) {
	}
	
	function handleChangeHotbomb($socket) {
	}
	
	function handleStartHotbombGame($socket) {
	}
	
}

?>