<?php

namespace PComponent\Panfu;

use PComponent, PComponent\Logging\Logger;

class PandaManager {

	public $id = null;
	public $username = "INDEFINIDO";

	public $identified = false;
	public $randKey;
	public $delimitions;
	
	public $_rps = Array("invites" => Array(),"_botScore"=>0,"_userScore"=>0);
	public $game = "none";
	
	public $activeHouse = false;
	public $soloGaming = false;
	public $isApiSocket = false;
	
	public $botting = false;
	public $timingsp;
	
	public $attributes;
	public $canFirstLog = false;
	public $btiming;
	public $timeouted;
	public $lastPing;
	public $lastReport;
	public $contiming;
	public $lastAttCommand;
	
	public $counts = 0;
	public $SWID;
	public $isInHouse = false;
	public $isAmf = true;
	
	public $lastCommand;
	public $usedCommands = "";
	
	public $gaming = false;
	public $verifiedTimes = 0;
	public $salted = false;
	public $age;
	
	public $_on = true;
	
	public $appeared = false;
	public $msgWarn = 0;
	public $buddies = "";

	public $requestSended = "";
	public $requestOk = "";
	
	public $creditShowed = false;
	public $lastMoodup;
	
	public $gameManager;
	
	public $avatar;
	public $avatarAttributes;
	public $salt;
	
	public $loginTime;
	public $coins;
	
	public $mouvementBlocked = false;
	public $moderator;
	public $modLevel;
	public $muted = false;
	public $state = 0;
	public $reverseBan = false;
	
	public $x = 0;
	public $y = 0;
	public $lastmove = 0;
	
	public $lastMessageTimer;
	public $lastPlayerAction;
	public $lastEmoteTimer;
	
	public $session;
	
	public $frame = 5;
	
	public $lastAction;
	public $socialLevel;
	public $firstWarn = false;
	public $room;
	
	public $generatedIds = "";
	
	public $premiumNumber;
	public $pong = true;
	
	public $isPremium;
	
	public $socket;
	public $database;
	public $ipAddress;
	
	function __construct($socket) {
		$this->socket = $socket;
		$this->lastCommand = Array('ctime'=>'','last' => '');
		$this->lastPing = strtotime("now");
		$this->timingsp = round(microtime(true)*1000);
		socket_getpeername($socket, $this->ipAddress);
	}
	
	function saveSalt()
	{
		$this->database->saveSalt($this->id,$this->salt);
	}
	
	function changeSessionAndSalt($session)
	{
		$this->database->changeLoginInfo($this->id,$session,"");

	}
	
	function getBuddies()
	{
		$buddies = $this->database->getBuddies($this->id);
		
		if($buddies === null || $buddies === '' || count($buddies) === 0) {
			return;
		}
		
		$str = "";
		
		foreach($buddies as $info) {
			$str = $str.$info['buddy_id'].",";
		}
		
		$this->buddies = $str;
	}
	
	function isBanned()
	{
		$ok = $this->database->userBanned($this->id);
		return $ok;
	}
	
	function addBuddy($id) {
		
		$this->database->addAsBuddy($this->id,$id);
	}
	
	function setCoins($coinAmount, $a = null) {
		$this->coins = $coinAmount;
		if($a != null) {
			$this->send("260;$a  |678;{$this->id};{$this->SWID};{$this->coins}|");
		} else {
			$this->send("678;{$this->id};{$this->SWID};{$this->coins}|");
		}
		
		$this->database->addCoins($this->id,$coinAmount);
	}
	
	function updateLocalInfo($serverId, $timestamp)
	{
		$this->database->updateLocalInfo($serverId,$timestamp,$this->id);
	}
	
	function changePokopetName($name, $newname, $me)
	{
		$this->database->changePetName($name,$newname,$this->id);
	}
	
	function Stringify() {
		$we = $this->activeHouse == 1?1:0;
		return $this->id . ';' . $this->username . ';' . $this->premiumNumber . ';' . $we . ';' . $this->SWID . ';' . $this->moderator . ';' . $this->x . ';' . $this->y . ';' . strtotime("now");
	}
	
	function updateMute($need, $time = 0)
	{
		if($need === false) {
			$this->database->removeMuteTime($this->id);
		} else {
			$this->database->updateMuteTime($time,$this->id);
		}
	}
	
	function startUserMuted()
	{
		$muted = $this->muted === true?0:1;
		$this->database->userMuted($this->id,$muted);
	}
	
	function verificar() {
		$this->database->verificado($this->id);
	}
	
	function updateAttributes($category, $value)
	{
		$this->database->attributing($category,$value,$this->id);
	}
	
	function send($data) {
		Logger::Debug("Respuesta[".$this->id." | ".$this->username."] ~~ $data");
		
		$data .= "\0";
		return $bytesWritten = socket_send($this->socket,$data,strlen($data),0);
	}
	
}

?>
