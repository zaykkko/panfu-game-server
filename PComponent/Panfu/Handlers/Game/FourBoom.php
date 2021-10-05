<?php

namespace PComponent\Panfu\Handlers\Game;
use PComponent, PComponent\Logging\Logger, PComponent\Exceptions;

class FourBoom {
	// Apparently the client determine who wins. It's fucking manipulable lol.
	const InvalidChipPlacement = -1;
	const ChipPlaced = 0;
	const FoundFour = 1;
	const Tie = 2;
	const GAME_ID = 25;
	const FourNotFound = 3;
	public $_a = null;
	public $_b = null;
	public $_ida = null;
	public $_idb = null;
	public $_org = false;

	public $boardMap = array(
		array(0, 0, 0, 0, 0, 0, 0),
		array(0, 0, 0, 0, 0, 0, 0),
		array(0, 0, 0, 0, 0, 0, 0),
		array(0, 0, 0, 0, 0, 0, 0),
		array(0, 0, 0, 0, 0, 0, 0),
		array(0, 0, 0, 0, 0, 0, 0),
	);

	public $currentPlayer = 1;
	
	function __construct($a, $b) {
		$this->_a = $a;
		$this->_ida = $a->id;
		$this->_a->send("15;25;0;setPlayer;1|");
		$this->_b = $b;
		$this->_idb = $b->id;
		$this->_b->send("15;25;1;setPlayer;2|");
		//$this->start();
	}

	function convertToString() {
		return implode(",", array_map(function($row) {
			return implode(",", $row);
		}, $this->boardMap));
	}
	
	function anticheatVerify() {
		//To be honest, I don't fucking know how the hell I will make an 'Anti-Cheat' system for this game.
	}
	
	function __end($sender) {
		$this->getOther($sender)->send("15;25;{$sender->id};unsetPlayer;|15;25;{$sender->id};leave;|");
	}
	
	function recv($t, $args, $sender) {
		try {
			switch($t) {
				case 14:
					if(!$this->_org) {
						$this->_org = true;
						@$this->_a->send("15;25;0;setPlayer;1|");
						@$this->_b->send("15;25;1;setPlayer;2|");
					}
					break;
				case 15:
					switch($args[1]) {
						case "ready":
							return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};ready;|");
						case "leave":
							$this->_a->gaming = $this->_b->gaming = false;
							$this->_a->game = $this->_b->game = "none";
							unset($this->_a->gameManager);unset($this->_b->gameManager);
							$this->getOther($sender)->send("15;25;{$sender->id};" . $args[1] . ";|");
							//$sender->room->restart($sender);
							break;
						case is_numeric($args[1]):
							switch($args[2]) {
								case "FIRSTPLAYER":
									return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$sender->id};FIRSTPLAYER;{$sender->username}|");
								case "SECONDPLAYER":
									return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$sender->id};SECONDPLAYER;{$sender->username}|");
								case "ACTION":
									return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$sender->id};ACTION;" . $args[3] . "|");
								case "ACTIONRESPONSE":
									return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$sender->id};ACTIONRESPONSE|");
								case "RESTART":
									//Thought the server selected the winner, but I was rly wrong...
									/*$this->boardMap = array(
										array(0, 0, 0, 0, 0, 0, 0),
										array(0, 0, 0, 0, 0, 0, 0),
										array(0, 0, 0, 0, 0, 0, 0),
										array(0, 0, 0, 0, 0, 0, 0),
										array(0, 0, 0, 0, 0, 0, 0),
										array(0, 0, 0, 0, 0, 0, 0),
									);*/
									return $this->getOther($sender)->send("15;" . self::GAME_ID . ";{$sender->id};{$sender->id};RESTART;lol|");
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

	function changePlayer() {
		if($this->currentPlayer == 1) {
			$this->currentPlayer = 2;
		} else {
			$this->currentPlayer = 1;
		}
	}

	function validChipPlacement($column, $row) {
		if($this->boardMap[$row][$column] != 0) {
			echo "Invalid chip placement (!= 0)\n";
			var_dump($this->boardMap[$row][$column]);
			return false;
		}

		return true;
	}

	function isBoardFull() {
		foreach($this->boardMap as $row) {
			if(in_array(0, $row)) {
				return false;
			}
		}

		return true;
	}

	function determineColumnWin($column) {
		$currentPlayer = $this->currentPlayer;

		$streak = 0;

		foreach($this->boardMap as $row) {
			if($row[$column] == $currentPlayer) {
				$streak++;

				if($streak === 4) {
					return self::FoundFour;
				}
			} else {
				$streak = 0;
			}
		}

		return self::FourNotFound;
	}

	function determineVerticalWin() {
		$rows = count($this->boardMap);

		for($column = 0; $column < $rows; $column++) {
			$foundFour = $this->determineColumnWin($column);

			if($foundFour === self::FoundFour) {
				return $foundFour;
			}
		}

		return self::FourNotFound;
	}

	function determineHorizontalWin() {
		$currentPlayer = $this->currentPlayer;

		$rows = count($this->boardMap);
		$streak = 0;

		for($row = 0; $row < $rows; $row++) {
			$columns = count($this->boardMap[$row]);

			for($column = 0; $column < $columns; $column++) {
				if($this->boardMap[$row][$column] === $currentPlayer) {
					$streak++;

					if($streak === 4) {
						return self::FoundFour;
					}
				} else {
					$streak = 0;
				}
			}
		}

		return self::FourNotFound;
	}
	
	function send($a = "") {
		if($this->_a === null || $this->_b === null) {
			if($this->_a === null) {
				$this->_b->gaming = false;
				unset($this->_b->gameManager);
				return $this->_b->send("15;25;{$this->_ida};unsetPlayer;|15;25;{$this->_ida};leave;|");
			}
			$this->_a->gaming = false;
			unset($this->_a->gameManager);
			return $this->_a->send("15;25;{$this->_idb};unsetPlayer;|15;25;{$this->_idb};leave;|");
		}
		$this->_a->send($a);
		$this->_b->send($a);
	}

	function determineDiagonalWin() {
		$currentPlayer = $this->currentPlayer;

		$rows = count($this->boardMap);

		$streak = 0;

		for($row = 0; $row < $rows; $row++) {
			$columns = count($this->boardMap[$row]);

			for($column = 0; $column < $columns; $column++) {
				if($this->boardMap[$row][$column] === $currentPlayer) {
					if(@$this->boardMap[$row + 1][$column + 1] === $currentPlayer &&
						@$this->boardMap[$row + 2][$column + 2] === $currentPlayer &&
						@$this->boardMap[$row + 3][$column + 3] === $currentPlayer) {
						
						return self::FoundFour;
					} elseif(@$this->boardMap[$row - 1][$column + 1] === $currentPlayer &&
						@$this->boardMap[$row - 2][$column + 2] === $currentPlayer &&
						@$this->boardMap[$row - 3][$column + 3] === $currentPlayer) {
						
						return self::FoundFour;
					} elseif(@$this->boardMap[$row - 1][$column - 1] === $currentPlayer &&
						@$this->boardMap[$row - 2][$column - 2] === $currentPlayer &&
						@$this->boardMap[$row - 3][$column - 3] === $currentPlayer) {
						
						return self::FoundFour;
					}
				}
			}
		}

		return self::FourNotFound;
	}

	function processBoard() {
		$fullBoard = $this->isBoardFull();

		if($fullBoard === true) {
			return self::Tie;
		}

		$horizontalWin = $this->determineHorizontalWin();

		if($horizontalWin == self::FoundFour) {
			return $horizontalWin;
		}

		$verticalWin = $this->determineVerticalWin();

		if($verticalWin == self::FoundFour) {
			return $verticalWin;
		}

		$diagonalWin = $this->determineDiagonalWin();
		
		if($diagonalWin == self::FoundFour) {
			return $diagonalWin;
		}

		return self::ChipPlaced;
	}

	function getOther($a) {
		if($this->_a->id === $a->id) return $this->_b;
		if($this->_b->id === $a->id) return $this->_a;
		return null;
	}
	
	function placeChip($column, $row) {
		if($this->validChipPlacement($column, $row)) {
			$this->boardMap[$row][$column] = $this->currentPlayer;

			$gameStatus = $this->processBoard();

			if($gameStatus == self::ChipPlaced) {
				$this->changePlayer();
			}

			return $gameStatus;
		} else {
			return self::InvalidChipPlacement;
		}
	}

}

?>