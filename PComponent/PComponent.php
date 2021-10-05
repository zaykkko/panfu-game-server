<?php

namespace PComponent;

use PComponent\Events;
use PComponent\Logging\Logger;
use PComponent\Panfu;
use PComponent\Panfu\PandaManager;
use PComponent\Panfu\Packets\Packet;

abstract class PComponent extends TCPServer {

	public $pandas = array();
	public $websockets = array();
	protected $server = null;
	private $renovar = null;
	private $commands = "";
	
	protected $maxClients = 0;
	
	function handleAccept($socket) {
		if(!$this->acceptConnections) {
			if(strtotime("now") > $this->acceptCountdown) {
				$this->acceptCountdown = 0;
				$this->acceptConnections = true;
			} else {
				return $this->removeClient($socket);
			}
		}
		
		socket_getpeername($socket, $ip);
		
		if(sizeof($this->pandas) * 2 < $this->maxClients || $ip === $this->amfip || $this->acceptConnections) {
			$Accept = Events::Emit("accept", $socket);
			if(!$Accept) {
				Logger::Notice("Plugin denied client accept");
				return $this->removeClient($socket);
			}
			
			socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,Array('sec' => 8, 'usec' => 2));
			socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,Array('sec' => 8, 'usec' => 2));
			if(socket_get_option($socket,SOL_SOCKET,SO_TYPE) == SOCK_DGRAM || socket_get_option($socket,SOL_SOCKET,SO_TYPE) == SOCK_SEQPACKET) {
				return $this->removeClient($socket);
			}
			
			if($ip !== $this->amfip) {
				foreach($this->pandas as $sock) {
					if($sock->ipAddress === $ip) {
						Logger::Notice("Multiple clients, socket disconnected.");
						return $this->removeClient($socket);
					}
				}
			}
			
			if($this->databaseManager->original->is_blocked($ip)) {
				return $this->removeClient($socket);
			}
			
			$newPandaManager = new PandaManager($socket);
			$this->pandas[(int) $socket] = $newPandaManager;
			$this->pandas[(int) $socket]->contiming = floor(microtime(true) * 1000);
		} else {
			return $this->removeClient($socket);
			Logger::Notice("Max client number reached, client denied.");
		}
	}
	
	function handleDisconnect($socket) {
		unset($this->pandas[(int) $socket]);
	}
	
	function decodePacket($_str)
	{
		Logger::Debug("Paquete CODEADO [OP] => $_str");
		$_str1 = explode('|',$_str);
		array_pop($_str1);
		array_pop($_str1);
		array_pop($_str1);
		$_str2 = implode("",$_str1);
		$_command = "";
			
		for($i=0;$i<strlen($_str2);$i++) {
			$_ord = ord($_str2{$i});
			if(isset($this->extraChr[$_ord + 14])) {
				$_command = $_command.$this->extraChr[$_ord + 14];
			} else {
				$_command = $_command.chr($_ord + 14);
			}
		}
		return $_command;
	}
	
	function handleReceive($socket, $data) {
		$Receive = Events::Emit("receive", [$socket, $data]);
		$this->server = $this->masterSocket;
		if(!$Receive || strpos($data,'%xt%') !== FALSE) {
			return;
		}
		if(!isset($this->pandas[(int) $socket])) {
			return;
		}

		$data = trim(preg_replace("/\r\n|\r|\n/", '', $data));
		
		if(strrpos($data,'GET /echo HTTP/1.1Upgrade:') !== false) {
			Logger::Info("New AMF websocket.");
			$_org = explode("Sec-WebSocket-Key1:",explode("Origin: ",$data)[1])[0];
			if($_org === $this->amfdo && explode('X-Control-Command: ',explode('Authentication-Ticket: ',$data)[1])[0] === 'a7Q1mozDczvic5701966715a976cd9219253.77746469' && str_replace(' ','',explode('Upgrape-loop:',explode('sec-websocket-version:',$data)[1])[0]) === '13') {
				if(strpos($data,'Authentication-Ticket:') !== false) {
					if(strpos($data,'X-Control-Command:') !== false) {
						$data = explode('X-Control-Command:',$data)[1];
						$data = explode('$',$data)[1];
						$this->pandas[(int) $socket]->isAmf = true;
						if($data === null || $data === ' ') return $this->removePandaManager($this->pandas[(int) $socket]);
					} else {
						Logger::Warn("ERROR ALVVVVV");
						return $this->removePandaManager($this->pandas[(int) $socket]);
					}
				}
			} else {
				Logger::Warn("Intento de exploit detectado, el socket fue removido y dicho usuario, si fue identificado, fue suspendido, resultado: ".$data);
				Logger::DiscordLogger("Intento de exploit detectado, el socket fue removido y dicho usuario, si fue identificado, fue suspendido permanentemente. Resultado: ".$data,"Server Action Bot","Nazi_Waifu");
				if($this->pandas[(int) $socket]->identified) $this->__server__->banHammer($this->pandas[(int) $socket],"perm","ATTEMPTED_GAME_MANIPULATION","SERVER_ACTION");
				return $this->removePandaManager($this->pandas[(int) $socket]);
			}
		}
		
		if($data{0} === '<') {
			$chunkedArray = Array(null,$data);
		} else {
			if(strpos($data,'|') === false || strpos($data,';') === false) {
				Logger::Warn("ERROR ALVVVVV");
				return $this->removePandaManager($this->pandas[(int) $socket]);
			}
			$tdata = $data;//$this->decodePacket($data);
			$chunkedArray = explode("|", $tdata);
		}
		
		foreach($chunkedArray as $rawData) {
			if($rawData === null || $rawData === "" || $rawData === '\0') {
			} else {
				$packet = @Packet::Parse($rawData);
				$worldPacket = @Packet::getInstance();
				
				if(Packet::$IsXML) {
					$this->handleXmlPacket($socket);
					Logger::Debug("Paquete [XML] => $rawData");
				} else {
					$Packet = Events::Emit('packet', $socket);
					Logger::Debug("Paquete [PTO] => $rawData");
					
					if(strpos($this->pandas[(int) $socket]->usedCommands,$rawData) != false) {
						return $this->removePandaManager($this->pandas[(int) $socket]);
					}
					
					$this->pandas[(int) $socket]->usedCommands = $this->pandas[(int) $socket]->usedCommands . $rawData . '	';
					
					if(!is_numeric($worldPacket::$Handler)) {
						return $this->removePandaManager($this->pandas[(int) $socket]);
					}
					
					$this->pandas[(int) $socket]->lastCommand['last'] = $rawData;
					
					if($Packet !== false) {
						$this->handleWorldPacket($socket);
					}
				}
			}
		}

		Events::Emit("received", [$socket, $data]);
	}
	
	function removePandaManager($panda) {
		$this->removeClient($panda->socket);
		unset($this->pandas[$panda->socket]);
	}
	
	abstract function handleXmlPacket($socket);
	abstract function handleWorldPacket($socket);
}
?>