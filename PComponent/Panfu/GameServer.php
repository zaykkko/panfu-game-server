<?php

namespace PComponent\Panfu;

use PComponent\Exceptions, PComponent\PComponent, PComponent\Events, PComponent\Saving, PComponent\Logging\Logger, PComponent\Panfu\Handlers, PComponent\Panfu\Packets\Packet;

// This game is really Beautiful. <3 But the creators of the fucking client, literally don't know the meaning of 'cheating'. Ironically It's like, the client controls the server.
// About graphics, this game uses fucking bitmaps. 2D pictures of bad quality, pixelated lmao.
// Quest were less elaborate and repetitive.
// AMF requests are fucking manipulable too, the server literally needs to have a fucking connection and verification of what the hell the user requests. Then we can make a more "secure system" and have less cheaters.
// Thought about rewrite the coin, level, shop and power timer methods to correct all the fucking cheats error they left. But I'm fucking lazy.
// That's why Club Penguin had more users than Panfu. It's sad to know but It's a reality.

final class GameServer extends Panfu {

	protected $gameCommands = array(
		"0" => "handleUserLogged",
		"301" => "handleUserSalt",
		"70" => "handleGetRoomAttendes",
		"212" => "handleGetPendejos",
		"20" => "handleAvatarMove",
		"25" => "handleChangeRoom",
		"78" => "handleChatMessage",
		"41" => "handleEmoteMessage",
		"43" => "handleSecureChatMessages",
		"50" => "handleAvatarAction",
		"113" => "handlePlayerToPlayer",
		"110" => "handleMobileThingo",
		"111" => "handleMobileThingy",
		"44" => "handleChangeProfileText",
		"112" => "handleMobileFucker",
		"38" => "handleHouseOpenClose",
		"210" => "handleMobiaThingy",
		"452" => "handleUpdateBuddyStatus",
		"1050" => "handleHeartBeat",
		"21" => "handleTeleport",
		"140" => "handleTeleSit",
		"81" => "handleSendBlock",
		"60" => "handleRecvFriendRequest",
		"29" => "handleGetPlayers",
		"2" => "handleLogout",
		"11" => "handleJoinGame",
		"16" => "handleQuitGasme",
		"26" => "handleEnterHouse",
		"130" => "handleSoccering",
		"131" => "handleSoccering",
		"114" => "handleSendReport",
		"115" => "handleLockPlayer",
		"122" => "handleChangeHotbomb",
		"123" => "handleHotbombExplode",
		"124" => "handleGetHotbombStatus",
		"125" => "handleStartHotbombGame",
		"132" => "handleSoccering",
		"133" => "handleSoccering",
		"134" => "handleSoccering",
		"135" => "handleSoccering",
		"136" => "handleSoccering",
		"14" => "handleEnterMultigame",
		"15" => "handleGameManagerMessage",
		"211" => "handlePlayerUbication",
		"756" => "handleDoLoggerVerification",
		"23" => "handleFindFriendUbication",
		"42" => "handleECardMessages",
		"33" => "handleSendUpdateRoom",
		"28" => "changeHomeRoom",
		"300" => "handleSoloGame",
		"345" => "handleGetPlayerByName",
		"346" => "handleGetPlayerById",
		"890" => "handlePacketTester",
		"891" => "handlePacketTesterMe"
	);
	
	use Handlers\Commands\Navigation, Handlers\Commands\Message, Handlers\Commands\Moderation, Handlers\Commands\Premium, Handlers\Commands\Connection, Handlers\Commands\AvatarCommands, Handlers\Commands\Buddy, Handlers\Commands\PreGame;
	
	public $items = array();
	
	public $rooms = array();
	
	public $spawnRooms = array();

	public $pandasByPlayerId = array();
	public $pandasByUsername = array();
	public $pandasByIdModerators = array();
	
	public $serverId;
	public $severLang;
	public $serverNick;
	public $serverName;
	public $continueExec = 1;
	public $restartloop;
	public $chatConfig;
	public $reward;
	public $rewardloop;
	public $_save;	
	public $maintenanceMode = false;
	
	public $amfip;
	
	public $lastBotActioning = 0;
	
	private $timeOutserver;
	
	public $pandasWaiting = array();
	
	public $premiumDay = false;
	
	public $apicon;
	
	public $acceptConnections = true;
	
	public $acceptCountdown = 0;
	
	public $_sr;
	
	public $sip;
	public $runts;
	public $spo;
	private $queue = [];
	private $timeouting = false;
	
	public $_rps = Array();
	
	function __construct($_serverId, $_lang, $_name, $_srl, $_sch, $sip, $spo) {
		if(!isset($_srl)) throw Exceptions\ServerException("LOOP ERROR");
		parent::__construct();
		
		$this->serverId = $_serverId;
		$this->severLang = $_lang;
		$this->serverNick = $this->serverName = $_name;
		$this->runts = strtotime("now");
		
		$this->sip = $sip;
		$this->spo = $spo;
		$this->chatConfig = Array(
			"iw" => explode(',',$_sch['iw']),
			"ow" => explode(',',$_sch['ow'])
		);
		$this->restartloop = $_srl;
		$this->amfip = $_sch['amf']['ip'];
		$this->amfdo = $_sch['amf']['domain'];
		$this->_save = new Saving\Saver($this,true);
		
		@$this->apicon = fsockopen($this->sip,$this->spo,$errno,$errstr,0);
		if($this->apicon) {
			try {
				$this->trackData("discordhook","Servidor iniciado.","Info bot");
			} catch(DataException $e) {
				Logger::warn($e);
			}
		}
		
		$GLOBALS['servertime'] = floor(microtime(true) * 1000) + strtotime("now +{$this->restartloop}");
		
		Logger::GS($_name);
		Events::Append("timeOutServer",Array($this,"restartServer"));
		Events::Append("botThrows",Array($this,"actioningBotty"));
		
		$this->loadFilters();
		
		echo sprintf("%c",10);
		
		Logger::info('Se establecio la configuracion del chat: '.$_sch['iw'].' ~ '.$_sch['ow'].'.');
		
		$downloadAndDecode = function($url) {
			$filename = basename($url, ".json");
			
			if(file_exists("crumbs/$filename.json")) {
				$jsonData = file_get_contents("crumbs/$filename.json");
			} else {
				$jsonData = file_get_contents($url);
				file_put_contents("crumbs/$filename.json", $jsonData);
			}
			
			$dataArray = json_decode($jsonData, true);
			return $dataArray;
		};
		
		$rooms = 152;
		for($i=0;$i<$rooms;$i++){
			if($i === 8) {
				$this->_sr = $this->rooms[$i] = new Room($i,$i+2,false);
				$this->rooms[$i]->_c();
			} else {
				$this->rooms[$i] = new Room($i,$i+2,true);
			}
		}
		
		Events::AppendInterval(1,Array($this,"checkSoccerTimer"));
		
		Logger::Info("Timer iniciado.");
		Logger::Info("Se cargaron ".count($this->rooms)." salas.");
		
		$items = json_decode(file_get_contents(dirname(__FILE__) . './crumbs/panfu_items_modules.json'),true);
		foreach($items as $in => $fo) {
			$this->items[$fo['id']] = $fo;
			unset($in);
		}
		
		Logger::Info("Se cargaron ".count($this->items)." items.");
		
		$this->pandasWaiting['fourboom'] = Array();
		
		$northPole = array(99, 28, 80, 81);
		
		$noSpawn = array_merge($northPole);
		$this->spawnRooms = array_keys(
			array_filter($this->rooms, function($room) use ($noSpawn) {
				if(!in_array($room->externalId, $noSpawn) && $room->externalId <= 99) {
					return true;
				}
			})
		);
		
		foreach($this->gameCommands as $a => $b) {
			if(!method_exists($this, $b)) {
				die("El metodo para '$a' no existe.");
			}
		}
		
		Logger::Info("Se handlearon ".count($this->gameCommands)." paquetes.");
		
		Logger::Debug("El servidor fue iniciado exitosamente. :)");
	}
	
	function trackData() {
		
		if(func_num_args() > 2) {
			
			if($this->apicon) {
				$outdata = "POST / HTTP/1.1\r\n";
				$outdata .= "Host: 127.0.0.1::{$this->serverId}\r\n";
				$outdata .= "Connection: Close\r\n";
				$outdata .= "Content-Type: " . func_get_arg(0) . "\r\n";
				$outdata .= "Message: **[** {$this->severLang} **|** {$this->serverNick} **|** {$this->serverId} **] -->** `" . func_get_arg(1) . "`\r\n";
				$outdata .= "Username: " . func_get_arg(2) . "\r\n";
				$outdata .= "Authorization: OP ovCHaC9ZVbFjmexlLt4W1tFR9VV7IovOUTYUo4fDLdu8r7GSk9L06fP\r\n";
				$outdata .= "X-Security-Key: OP 8GbGTxtLmc5ISK6Kw0ySX4tdADANHxiSsui95T9j4t952myZbWBHWCRPiaRS\r\n";
				if(fwrite($this->apicon,$outdata)) {
					return true;
				}
				
				return false;
			} else {
				array_push($this->queue,[func_get_arg(0),func_get_arg(1),func_get_arg(2)]);
				if($this->timeouting) return false;
			}
			
			Event::AppendTimeout(10000,Array($this,"queueCall"));
			$this->timeouting = true;
			
			$this->apicon = fsockopen($this->sip,$this->spo,$errno,$errstr,0);
			
			return false;
		}
		
		throw Exceptions\DataException("Track error, número de argumentos menor al pedido.");
	}
	
	function queueCall() {
		$this->timeouting = false;
		
		foreach($this->queue as $array) {
			$this->trackData($array[0],$array[1],$array[2]);
		}
		
		empty($this->queue);
		$this->queue = [];
	}
	
	function getPluginContext($a = null) {
		if($a != null && isset($this->loadedPlugins[$a])) return $this->loadedPlugins[$a];
		die("Plugin indefinido. Nombre: {$a}.");
	}
	
	function checkSoccerTimer() {
		$this->_sr->_soccer->timer = $this->_sr->_soccer->timer - 1000;
		//Logger::Info("Soccer time decreasing -> {$this->_sr->_soccer->timer}");
		if($this->_sr->_soccer->timer < 1000) {
			if($this->_sr->_soccer->started == 0) {
				$this->_sr->_soccer->_start($this->_sr);
			} else {
				$this->_sr->_soccer->_end($this->_sr);
			}
		}
	}
	
	function restartServer($t = 'SYSTEM') {
		if(count($this->pandas) > 0) {
			foreach($this->pandas as $player) {
				$this->removePandaManager($player,"260;El juego ha sido terminado por un fallo en el sistema.  |");
			}
		}
		
		$this->acceptConnections = false;
		$this->acceptCountdown = strtotime("now +1 hour");
		
		Logger::Debug("Server restarted successfully.");
	}
	
	function createGameInstance($t, $t1 = null, $t2 = null) {
		if($t2 != null && $t === "rps") {
			@$t2->game = $t1->game = "RPS";
			@$t1->gameManager = $t2->gameManager = $this->_rps[$t1->id + $t2->id] = new Handlers\Game\RPS($t1,$t2);
			@$t1->gaming = $t2->gaming = true;
		} else if($t === "4Boom") {
			//I wanted to let the bot play with the user, but It's the client who has all the fucking information about ALL the match.
			if(count($this->pandasWaiting['fourboom']) > 0) {
				Logger::Info("Panda waiting found, deleting and starting the match.");
				$t1->gaming = true;
				$_arr = rand(0,count($this->pandasWaiting['fourboom']) - 1);
				@$t1->gameManager = $this->pandasWaiting['fourboom'][$_arr]->gameManager = new Handlers\Game\FourBoom($this->pandasWaiting['fourboom'][$_arr],$t1);
				array_splice($this->pandasWaiting['fourboom'],$_arr,1);
			} else {
				Logger::Info("No pandas waiting.");
				$t1->game = "4Boom";
				$t1->gaming = true;
				array_push($this->pandasWaiting['fourboom'],$t1);
			}
		}
	}
	
	function identify($panda, array $items) {
		foreach($items as $in => $id) {
			if(is_numeric($id)) {
				$info = $this->getItemInfo($id);
				if($info != null) {
					if(boolval($info['premium']) && !$panda->premium) {
						return false;
					}
				} else {
					return false;
				}
			}
		}
		return true;
	}
	
	function getItemInfo($id) {
		if(!isset($this->items[$id])) return null;
		return $this->items[$id];
	}
	
	function tryBanFromDatabase() {
		if(func_num_args() < 1 || func_num_args() === 0 || func_num_args() < 5) return FALSE;
		$a = $this->databaseManager->original->getUserByName(func_get_arg(0));
		if($a === NULL) {
			if(is_numeric(func_get_arg(0))) {
				$b = $this->databaseManager->original->getUserById(func_get_arg(0));
				if($b === NULL) return FALSE;
				$mod = $b[9];
				if($mod > 0) {
					if(func_get_arg(4)->modLevel < 3) {
						return FALSE;
					}
				}
				$this->databaseManager->original->banUser($b[0],func_get_arg(1),$b[1],func_get_arg(2),func_get_arg(3),time());
				return TRUE;
			}
			return FALSE;
		} else {
			$mod = $a[9];
			if($mod > 0) {
				if(func_get_arg(4)->modLevel < 3) {
					return FALSE;
				}
			}
			$this->databaseManager->original->banUser($a[0],func_get_arg(1),$a[1],func_get_arg(2),func_get_arg(3),time());
			return TRUE;
		}
		return FALSE;
	}
	
	function handleGetPlayerByName($socket, $name, $request = false) {
		if(!$request) {
			$panda = $this->pandas[(int) $socket];
			if(!isset(Packet::$Duo[0]) || strlen(Packet::$Duo[0]) < 5 || strlen(Packet::$Duo[0]) >= 12 || !preg_match("/^[a-zA-Z0-9@_]+$/",Packet::$Duo[0])) {
				return $this->removePandaManager($panda,"212;null|");
			}
			
			$player = $this->getPlayerByName(Packet::$Duo[0]);
			
			if($player != null) {
				return $panda->send("345;{$player->Stringify()}|");
			} else {
				$player = $panda->database->getUserByName(Packet::$Duo[0]);
				if($player === null) {
					return $panda->send("345;null|");
				}
				
				return $panda->send("345;{$player}|");
			}
		} else {
			$player = $this->databaseManager->original->getUserByName($name);
			if($player != null) {
				return "260;Id: {$player[0]}, Nombre: {$player[2]}, Monedas: {$player[1]}, SWID: {$player[5]}, Premium: {$player[3]}, Sheriff: {$player[6]}  |";
			}
			
			return null;
		}
	}
	
	function handleGetPlayerById($socket) {
		$panda = $this->pandas[(int) $socket];
		if(!isset(Packet::$Duo[0]) || strlen(Packet::$Duo[0]) < 4 || strlen(Packet::$Duo[0]) > 10 || !is_numeric(Packet::$Duo[0])) {
			return $this->removePandaManager($panda);
		}
		
		$player = $this->getPlayerById(Packet::$Duo[0]);
		
		if($player != null) {
			return $panda->send("346;{$player->Stringify()}|");
		} else {
			$player = $panda->database->getUserById(Packet::$Duo[0]);
			if($player === null) {
				return $panda->send("346;null|");
			}
			$player = implode(';',$player);
			
			return $panda->send("346;{$player}|");
		}
	}
	
	function getPlayerById($playerId) {
		if(isset($this->pandasByPlayerId[$playerId])) {
			return $this->pandasByPlayerId[$playerId];
		}
		
		return null;
	}
	
	function getPlayerByName($playerName) {
		$lcPlayerName = strtolower($playerName);

		foreach($this->pandasByUsername as $pandaName => $pandaObject) {
			if($pandaName == $lcPlayerName) {
				return $this->pandasByUsername[$pandaName];
			}
		}
		
		return null;
	}
	
	function getPlayersString() {
		$mo = "";
		foreach($this->pandas as $player) {
			if($player->identified){
				$mo = $mo.$player->id.":".$player->username.":".$player->premiumNumber.";";
			} else if($player->isApiSocket) {
				$mo = $mo."1009:Filter Bot ^-^:3;";
			}
		}
		$mo = $mo . "1010:~ Lady Bot ~:3;";
		$mo = str_replace(";|","|",$mo."|");
		return $mo;
	}
	
	function moderation($msg, $panda) {
		Logger::warn($msg);
		foreach($this->pandasByIdModerators as $user) {
			$user->send("260;[ES][{$panda->id} - {$panda->username}] $msg  |");
		}
	}
	
	function removePandaManager($panda, $bye = "nope") {
		Events::Emit("onDisconnect",$panda);
		if($bye !== "nope") $panda->send($bye);
		$this->removeClient($panda->socket);
		
		if($panda->room !== null) {
			$panda->room->remove($panda,"logoff");
		}

		if($panda->gaming) {
			if(!isset($panda->gameManager) && $panda->game === "4Boom") {
				if(($a = array_search($panda,$this->pandasWaiting['fourboom'])) !== false) {
					array_splice($this->pandasWaiting['fourboom'],$a,1);
				}
			} else {
				$panda->gameManager->__end($panda);
			}
		}
		
		if(isset($this->pandasByPlayerId[$panda->id])) {
			Logger::Debug("Removiendo a un jugador. {".$panda->id." | ".$panda->username."}");
			
			$panda->updateLocalInfo(0,time());
			
			if($panda->moderator) unset($this->pandasByIdModerators[$panda->id]);
			
			unset($this->pandasByPlayerId[$panda->id]);
			unset($this->pandasByUsername[$panda->username]);
		}

		unset($this->pandas[$panda->socket]);
		$this->databaseManager->original->updateServer(count($this->pandas),$this->serverId);
		$this->databaseManager->remove($panda);
		
	}
	
	function canChat($day, $hour) {
		if($day === 'Domingo' || $day === 'Sábado'){
			if(is_numeric(array_search($hour,$this->chatConfig['ow']))) {
				
				return true;
			}
			
			return false;
		}
			
		if(is_numeric(array_search($hour,$this->chatConfig['iw']))) {

			return true;
		}
			
		return false;
	}
	
	function getCurrentDayName() {
		$dat = Array(
			"Mon" => "Lunes",
			"Tue" => "Martes",
			"Wed" => "Miércoles",
			"Thu" => "Jueves",
			"Fri" => "Viernes",
			"Sat" => "Sábado",
			"Sun" => "Domingo"
		);
		
		return $dat[date('D')];
	}
	
	function getNewSession($id) {
		return Hashing::regenerate($id,rand(0,95) + $id);
	}

	function handleDisconnect($socket) {
		$panda = $this->pandas[(int) $socket];

		return $this->removePandaManager($panda);
	}
	
}

?>
