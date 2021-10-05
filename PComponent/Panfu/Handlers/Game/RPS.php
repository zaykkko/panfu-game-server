<?php

namespace PComponent\Panfu\Handlers\Game;
use PComponent, PComponent\Logging\Logger, PComponent\Exceptions;

class RPS {

	const LEAVE = "leave";
	const UNSET_LEAVE = "unsetPlayer";
	const GAME_ID = 41;
	
	public $scoreboard = Array();

	public $currentPlayer = 1;
	
	public $_a;
	public $_b;
	
	public $_player1;
	public $_player2;
	public $_org = false;
	
	public $_playerI = Array();
	
	public $_infoSent = false;
	
	function __construct($a, $b) {
		$this->_a = $a;
		$this->_b = $b;
	}
	
	function __end() {
		$this->getOther($sender)->send("15;25;{$sender->id};unsetPlayer;|15;25;unsetPlayer;|");
	}
	
	function anticheatVerify() {
	}
	
	function recv($t, $args, $sender) {
		//15,sInst.gameID,sInst.userID,"FIRSTPLAYER",sInst.userName
		try {
			switch($t) {
				case 14:
					if(!$this->_org) {
						$this->_org = true;
						@$this->_b->send("15;25;1;setPlayer;2|");
						$this->_player2 = $this->_b;
						$this->playerI[$this->_b->id] = Array("ready"=>0,"score"=>0,"hand"=>0,"id"=>$this->_b->id);
						@$this->_a->send("15;25;0;setPlayer;1|");
						$this->_player1 = $this->_a;
						$this->playerI[$this->_a->id] = Array("ready"=>0,"score"=>0,"hand"=>0,"id"=>$this->_a->id);
					}
					break;
				case 15:
					switch($args[1]) {
						case "ready":
							$this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};ready;|");
							break;
						case "leave":
							@$this->_a->gaming = $this->_b->gaming = false;
							@$this->_a->game = $this->_b->game = "none";
							unset($this->_a->gameManager);unset($this->_b->gameManager);
							$this->getOther($sender)->send("15;41;{$sender->id};" . $args[1] . ";|");
							break;
						case is_numeric($args[1]):
							switch($args[2]) {
								case "FIRSTPLAYER":
									$this->getOther($sender)->send("15;" . self::GAME_ID . ";1;{$sender->id};FIRSTPLAYER;{$sender->username}|");
									break;
								case "SECONDPLAYER":
									$this->getOther($sender)->send("15;" . self::GAME_ID . ";2;{$sender->id};SECONDPLAYER;{$sender->username}|");
									break;
								case "ACTION":
									$this->playerI[$sender->id]["hand"] = (int) $args[3];
									$this->changePlayer();
									$this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$this->currentPlayer};ACTION;" . $args[3] . "|");
									
									if($this->currentPlayer === 2) {
										if((int)$this->playerI[$sender->id]['hand'] === 0 && (int)$this->playerI[$this->getOther($sender)->id]["hand"] !== 0) {
											$this->playerI[$this->getOther($sender)->id]["score"]++;
										} else if((int)$this->playerI[$sender->id]['hand'] !== 0 && (int)$this->playerI[$this->getOther($sender)->id]["hand"] === 0) {
											$this->playerI[$sender->id]["score"]++;
										} else if((int)$this->playerI[$sender->id]['hand'] === 3 && (int)$this->playerI[$this->getOther($sender)->id]["hand"] === 1) {
											$this->playerI[$this->getOther($sender)->id]["score"]++;
										} else if((int)$this->playerI[$this->getOther($sender)->id]["hand"] === 3 && (int)$this->playerI[$sender->id]['hand'] === 1) {
											$this->playerI[$sender->id]["score"]++;
										} else if((int)$this->playerI[$sender->id]['hand'] === 2 && (int)$this->playerI[$this->getOther($sender)->id]["hand"] === 3) {
											$this->playerI[$this->getOther($sender)->id]["score"]++;
										} else if((int)$this->playerI[$this->getOther($sender)->id]["hand"] === 2 && (int)$this->playerI[$sender->id]['hand'] === 3) {
											$this->playerI[$sender->id]["score"]++;
										} else if((int)$this->playerI[$sender->id]['hand'] === 1 && (int)$this->playerI[$this->getOther($sender)->id]["hand"] === 2) {
											$this->playerI[$this->getOther($sender)->id]["score"]++;
										} else if((int)$this->playerI[$this->getOther($sender)->id]["hand"] === 1 && (int)$this->playerI[$sender->id]['hand'] === 2) {
											$this->playerI[$sender->id]["score"]++;
										}
										
										unset($this->playerI[$sender->id]["hand"]);
										unset($this->playerI[$this->getOther($sender)->id]["hand"]);
									}
									
									if((int)$this->playerI[$sender->id]["score"] >= 3) {
										return $this->won($sender,Array(Array($sender->id,(int)$this->playerI[$sender->id]["score"],50,true,$sender->username),Array($this->getOther($sender)->id,(int)$this->playerI[$this->getOther($sender)->id]["score"],15,false,$this->getOther($sender)->username)));
									} else if((int)$this->playerI[$this->getOther($sender)->id]["score"] >= 3) {
										return $this->won($sender,Array(Array($sender->id,(int)$this->playerI[$sender->id]["score"],15,false,$sender->username),Array($this->getOther($sender)->id,(int)$this->playerI[$this->getOther($sender)->id]["score"],50,true,$this->getOther($sender)->username)));
									}
									break;
								default:
									throw new Exceptions\GameException("Malformed Packet!! Args -> " . implode(',',$args));
							}
							break;
						default:
							throw new Exceptions\GameException("Malformed Packet!! Args -> " . implode(',',$args));
					}
					break;
				default:
					throw new Exceptions\GameException("Malformed Packet!! Args -> " . implode(',',$args));
			}
		} catch(Exceptions\GameException $e) {
			return Logger::Warn($e);
		}
	}
	
	function won($z, $args) {
		foreach($args as $a) {
			$z->database->leaderboard("rps",$a[0],$a[1],$a[2],$a[3],$a[4]);
		}
		return true;
	}
	
	function getOther($a) {
		if($this->_player1->id === $a->id) return $this->_player2;
		if($this->_player2->id === $a->id) return $this->_player1;
	}

	function changePlayer() {
		if($this->currentPlayer == 1) {
			$this->currentPlayer = 2;
		} else {
			$this->currentPlayer = 1;
		}
	}

}

?>