<?php

namespace PComponent;
use PComponent\Events;
use PComponent\Exceptions;
use PComponent\Logging\Logger;

class BindException extends \Exception {}

abstract class TCPServer {

	protected $sockets = array();
	protected $port;
	public $masterSocket;
	public $_discordManager;
	public $api_key;

	function accept() {
		$clientSocket = socket_accept($this->masterSocket);
		socket_set_option($clientSocket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_nonblock($clientSocket);
		$this->sockets[] = $clientSocket;
		
		return $clientSocket;
	}

	function handleAccept($socket) {
		echo "Client accepted\n";
	}

	function handleDisconnect($socket) {
		echo "Client disconnected\n";
	}

	function handleReceive($socket, $data) {
		echo "Received data: $data\n";
	}

	function removeClient($socket) {
		Events::Emit("disconnect", $socket);
		
		$client = array_search($socket, $this->sockets);
		unset($this->sockets[$client]);

		if(is_resource($socket)) {
			socket_close($socket);
		}

		Events::Emit("disconnected", $socket);
	}
	
	function setCapacity($am) {
		if((!isset($am)||!is_numeric($am))) throw new Exceptions\ServerException("CAPACITY NUMBER ERROR");
		$this->maxClients = $am;
	}
	
	function listen($address, $port, $backlog = 25, $throwException = false, $services, $services2) {
		if(!isset($address)||!isset($port)) throw new BindException("SERVER INFO ERROR.");
		$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);

		socket_set_option($socket,SOL_SOCKET,SO_REUSEADDR,1);
		socket_set_nonblock($socket);

		$success = socket_bind($socket,$address,$port);

		if($success === false) {
			if($throwException !== false){
				throw new BindException(
					"Error binding to port $port: " .
					socket_strerror(socket_last_error($socket))
				);
			} else {
				return false;
			}
		}

		socket_listen($socket, $backlog);
		$this->port = $port;
		
		$this->masterSocket = $socket;
	}
	
	function destroyServer()
	{
		foreach($this->sockets as $sock) {
			$this->removeClient($sock);
		}
		
		socket_close($this->masterSocket);
		$this->port = null;
	}

	function acceptPlayerClients() {
		if(floor(microtime(true) * 1000) >= $GLOBALS['servertime']) {
			$GLOBALS['servertime'] = floor(microtime(true) * 1000) + $this->restartloop;
			return Events::Emit(Array(Array("throwMoney", "SERVER"), Array("timeOutServer", "SERVER")));
		}
		
		foreach(Events::GetTimedEvents() as $eventIndex => $timedEvent) {
			list($callable, $interval, $lastCall, $type) = $timedEvent;

			if($lastCall === null) {
				Events::ResetInterval($eventIndex);
			} elseif(time() - $interval < $lastCall) {
				continue;
			} else {
				call_user_func($callable, $this);
				
				if($type == "timeout") {
					Events::RemoveInterval($eventIndex);
				} else {
					Events::ResetInterval($eventIndex);
				}
			}
		}
		
		foreach($this->pandas as $index => $info) {
			if($info->salted && !$info->isApiSocket) {
				if($info->lastCommand['ctime'] - floor(microtime(true) * 1000) > 180000 && $info->state != 4) {
					$this->removePandaManager($info);
					continue;
				} else {
					if($info->loginTime + 27000 <= time()) {
						$this->removePandaManager($info);
					} else if($info->lastPing + 600000 < time() && $info->state != 4) {
						$this->removePandaManager($info);
					}
					continue;
				}
			} elseif(isset($info->contiming) && !$info->isApiSocket) {
				if($info->contiming + 60000 <= floor(microtime(true) * 1000)) {
					$this->removePandaManager($info);
					continue;
				}
			}
		}

		$sockets = array_merge(array($this->masterSocket), $this->sockets);
		$changedSockets = socket_select($sockets, $write, $except, 1);
		
		if($changedSockets === 0) {
			return false;
		} else {
			if(in_array($this->masterSocket, $sockets)) {
				$clientSocket = $this->accept();
				$this->handleAccept($clientSocket);
				unset($sockets[0]);
			}
			
			foreach($sockets as $socket) {
				$ms = @socket_recv($socket, $buffer, 8192, 0);
				if($ms == null) {
					$this->handleDisconnect($socket);
					$this->removeClient($socket);
					continue;
				} else {
					$this->handleReceive($socket, $buffer);
				}
			}
		}
	}

}

ob_implicit_flush();

?>	