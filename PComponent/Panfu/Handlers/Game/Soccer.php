<?php
namespace PComponent\Panfu\Handlers\Game;

use PComponent\Logging\Logger;
use PComponent\DatabaseManager;
use PComponent\Panfu\Packets\Packet;

class Soccer {
	public $teams = Array(Array("goals"=>0,"users"=>Array()),Array("goals"=>0,"users"=>Array()),Array("x"=>383,"y"=>274,"z"=>0));
	public $started = 1;
	public $_room = null;
	public $timer = 120000;
	
	function __construct($control) {
		$this->_room = $control;
	}
	
	function handleJoinSoccerTeam($panda, $packet)
	{
		if(count($this->teams[0]["users"]) >= 5 && count($this->teams[1]["users"]) >= 5) {
			return $panda->room->send("20;{$panda->id};1872;101;345;3|");
		}elseif(count($this->teams[0]["users"]) < count($this->teams[1]["users"])) {
			$team = 0;
		} elseif(count($this->teams[1]["users"]) < count($this->teams[0]["users"])) {
			$team = 1;
		} else {
			$team = rand(0,1);
		}
		
		Array_push($this->teams[$team]["users"],$panda->id);
		$panda->room->send("136;$team;".$panda->id."|");
	}
	
	function _end($room) {
		$this->teams[0]["users"] = Array();
		$this->teams[1]["users"] = Array();
		$this->teams[1]["goals"] = 0;
		$this->teams[0]["goals"] = 0;
		$this->teams[2]["x"] = 383;
		$this->teams[2]["y"] = 274;
		$this->teams[2]["z"] = 0;
		$this->timer = 60000;
		$this->started = 0;
		if(count($room->pandas) > 0) {
			return $room->send("134;{$this->timer}|");
		}
		return true;
	}
	
	function _start($room) {
		$this->timer = 120000;
		$this->started = 1;
		if(count($room->pandas) > 0) {
			return $room->send("131;{$this->timer}|");
		}
		return true;
	}
	
	function handleEndSoccerGame($panda, $packet)
	{
		return;
	}
	
	function handleGetTeamInfo($panda, $packet)
	{
		$panda->send("135;0;" . implode($this->teams[0]["users"]) . ";1;" . implode($this->teams[1]["users"]) . "|");
	}
	
	function handleStartSoccerGame($panda, $packet)
	{
		return;
	}
	
	function _remove($id, $room) {
		if(($key = array_search($id,$this->teams[0]["users"])) !== false) {
			unset($this->teams[0]["users"][$key]);
			$room->send("135;0;" . implode(",",$this->teams[0]["users"]) . ";1;" . implode(",",$this->teams[1]["users"]) . "|");
		} elseif(($key = array_search($id,$this->teams[1]["users"])) !== false) {
			unset($this->teams[1]["users"][$key]);
			$room->send("135;0;" . implode(",",$this->teams[0]["users"]) . ";1;" . implode(",",$this->teams[1]["users"]) . "|");
		}
	}
	
	function handleGoalGame($panda, $packet)
	{
		$this->teams[$packet[0]]["goals"]++;
		$panda->send("133;{$this->teams[0]["goals"]};{$this->teams[1]["goals"]}|");
	}
	
	function handleShootGame($panda, $packet)
	{
		//    x   y   z
		//132;230;274;196;(x=230, y=274),(x=177, y=268)
		
		$this->teams[2]["x"] = $packet[0];
		$this->teams[2]["y"] = $packet[1];
		$this->teams[2]["z"] = $packet[2];
		
		$panda->room->send("132;{$panda->id};" . $packet[0] . ";" . $packet[1] . ";" . $packet[2] . ";" . $packet[3] . "|");
	}

	function handleGetSoccerStatus($panda, $packet)
	{
		$format = "130;1;STARTED;TIMER;GOALSTEAMA;GOALSTEAMB;BALLX;BALLY|";
		$panda->send("130;1;{$this->started};{$this->timer};{$this->teams[0]["goals"]};{$this->teams[1]["goals"]};{$this->teams[2]["x"]};{$this->teams[2]["y"]}|");
	}
	
}

?>