<?php

namespace PComponent\Panfu;

use PComponent;
use PComponent\Events;
use PComponent\Exceptions;
use PComponent\Logging\Logger;
use PComponent\DatabaseManager;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\IPlugin as Plugin;

abstract class Panfu extends PComponent\PComponent {

	private $xmlHandlers = Array(
		"policy" => "handlePolicyRequest",
		"fsua" => "handleFriendshipMessage",
		"gold" => "handleUserActivatedMembership",
		"suspicious" => "handleSuspiciousAMFPacket",
		"furniturebought" => 'handleFurnitureBought',
		"mail" => 'handleNewMailSent',
		'api.package.server.socket.CALL' => 'handleApiCommand'
	);
	
	public $blackListedIP;

	private $secure = Array(
		'friendMessage' => 'oauth:xGtdpVNc7NdGNnsw4nxDkj4uRJjbcNUZ5Qer38eEmPqdRm7ycYVnycLHQ',
		'playerMessage' => 'oauth:wwAunqLkm679rHZHBt4s7SFXXaPt267ScAmAwK3ythCXkLV4RBTHkVksZ',
		'reportMessage' => Array(
			'auth' => 'oauth:VfXPfGcMVg64trZRFYXLktTpr2xCZNMnhM3E4cGR7gCAPThr39nEvG3C8',
			'playersAuth' => 'oauth:yFZJKzvuZsKJEuRXQ3XQC4wdF2kFtA65PyHYsGPDQ32uyJdLRSFmpg53q',
			'serverAuth' => 'oauth:LUPUvwXEwPNXg4QUYQwUcCNCAXVCDEYWeqTJmNHa92MxHh2vdxXxrJyMC'
		),
		'apikey' => 'oauth:RgYfGtyaDTkFdCgs3SGATVXuWnVttJGSbsR8md4ksZBUkZrzHSqeQhCvckY6NMEQZf3eWZzDtwzKrmwdckJEkJXS4sfEnU5KSaxB',
		'goldIDMessage' => 'oauth:LBtx4s7SFXXacMVg64kjZNMnhK3PfGcMVeEmPqdRmJAiwI48oehNEKurP',
		'furniturebought' => 'oauth:7zcH2PFcKmq2aWXuKbu4mS8tFwYaDDwYFVbmUmDgnDMCBdGpfset34eM8',
		'mailSentAuth' => 'oauth:XfYEq7EJLh5EbC6cwHHGv35rBGvCyngYfCBZLxtsZ4FDGmrc4yRdfezAn'
	);
	
	public $databaseManager;
	
	public $plugins;
	
	public $loadedPlugins = array();
	
	function __construct($loadPlugins = true, $pluginsDirectory = '') {
		$tempDatabase = new PComponent\Database();
		unset($tempDatabase);

		$this->databaseManager = new DatabaseManager(true);
		
		if($loadPlugins === true) {
			try {
				$this->loadPlugins(dirname(__FILE__).'\Plugins');
			} catch(PluginsExceptions $e) {
				Logger::Warn("Ocurrio un error. ? => $e");
			}
		}
	}
	
	function pandaDis($obj) {
		$this->plugins->onDisconnect($obj);
	}
	
	function checkPluginDependencies() {
		foreach($this->loadedPlugins as $pluginClass => $pluginObject) {
			if(!empty($pluginObject->dependencies)) {
				foreach($pluginObject->dependencies as $dependencyKey => $dependencyValue) {
					if(!isset($this->loadedPlugins[$dependencyKey]) && !isset($this->loadedPlugins[$dependencyValue])) {
						$pluginDependency = is_numeric($dependencyKey) ? $dependencyValue : $dependencyKey;
						
						Logger::Warn("Un archivo externo ('$pluginDependency') que necesita el Plugin '$pluginClass' no se pudo cargar.");
						unset($this->loadedPlugins[$pluginClass]);
					}
				}
			}
		}
	}
	
	function loadPlugin($pluginClass, $pluginNamespace) {
		$pluginPath = sprintf("%s\%s", $pluginNamespace, $pluginClass);
		
		$pluginObject = $this->plugins = new $pluginPath($this);
		$this->loadedPlugins[$pluginClass] = $pluginObject;
		
		$removePlugin = false;
		
		if(empty($this->gameCommands) && $pluginObject->loginServer !== true && $pluginObject->eventBinder !== true) {
			unset($this->loadedPlugins[$pluginClass]);
			
			unset($pluginObject);
			
			return false;
		}
		
		if(!empty($pluginObject->xmlHandlers)) {
			foreach($pluginObject->xmlHandlers as $xmlHandler => $handlerProperties) {
				list($handlerCallback, $callInformation) = $handlerProperties;
				
				if($callInformation == Plugin::Override) {
					$this->xmlHandlers[$xmlHandler] = array($pluginObject, $handlerCallback);
				}
			}
		}
		
		if(!empty($pluginObject->gameCommands)) {
			foreach($pluginObject->gameCommands as $packetExtension => $extensionHandlers) {
				if($packetExtension != null && $extensionHandlers !== null) {
					foreach($extensionHandlers as $packetHandler => $handlerProperties) {
						list($handlerCallback, $callInformation) = $handlerProperties;
						
						if($callInformation == Plugin::Override) {
							$this->gameCommands[$packetExtension][$packetHandler] = array($pluginObject, $handlerCallback);
						}
					}
				}
			}
		}
		
		foreach($this->loadedPlugins as $loadedPlugin) {
			if(!empty($loadedPlugin->dependencies)) {
				if(isset($loadedPlugin->dependencies[$pluginClass])) {
					$onloadCallback = $loadedPlugin->dependencies[$pluginClass];
					
					call_user_func(array($loadedPlugin, $onloadCallback));
				}
			}
		}
		
		$pluginObject->onReady();
	}
	
	function loadPluginFolder($pluginFolder) {
		$pluginNamespace = str_replace("/", "\\", $pluginFolder);
		$pluginNamespace = rtrim($pluginNamespace, "\\");
		$pluginNamespace = explode('PComponent',$pluginNamespace)[1];
		$pluginNamespace = 'PComponent'.$pluginNamespace;
		
		$pluginFiles = scandir($pluginFolder);
		$pluginFiles = array_splice($pluginFiles, 2);
		
		// Filter directories using array_map
		$pluginFolders = array_map(
			function ($pluginFile) use ($pluginFolder) {
				$lePath = sprintf("%s%s", $pluginFolder, $pluginFile);
				
				if(is_dir($lePath)) {					
					return $lePath;
				}
			}, $pluginFiles
		);
		
		$pluginFiles = array_diff($pluginFiles, $pluginFolders);
		
		$pluginClasses = array_map(
			function($pluginFile) {
				return basename($pluginFile, ".php");
			}, $pluginFiles
		);
		
		// Load plugins by class
		foreach($pluginClasses as $pluginClass) {
			if(!isset($this->loadedPlugins[$pluginClass])) {
				if(in_array($pluginClass . ".php", $pluginFiles)) {
					$this->loadPlugin($pluginClass, $pluginNamespace);
				}
			}
		}
		
		// Load plugin folders
		foreach($pluginFolders as $pluginFolder) {
			if($pluginFolder !== null) {
				$this->loadPluginFolder($pluginFolder);
			}
		}
	}
	
	function loadPlugins($pluginsDirectory) {
		if(!is_dir($pluginsDirectory)) {
			throw new Exceptions\PluginsExceptions("El directorio para el Plugin $pluginsDirectory, NO EXISTE.");
		} else {			
			$pluginFolders = scandir($pluginsDirectory);
			$pluginFolders = array_splice($pluginFolders, 2);
			
			$pluginFolders = array_filter($pluginFolders,
				function($pluginFolder) {
					if($pluginFolder != "Base") {
						return true;
					}
				}
			);
			
			foreach($pluginFolders as $pluginFolder) {
				$folderPath = sprintf("%s\%s", $pluginsDirectory, $pluginFolder);
				
				$this->loadPluginFolder($folderPath);
			}
			
			// Check dependencies
			$this->checkPluginDependencies();
			
			$pluginCount = sizeof($this->loadedPlugins);
			
			if($pluginCount != 0) {
				Logger::Info(sprintf("Se cargaron %d plugin(s): %s", $pluginCount, implode(', ', array_keys($this->loadedPlugins))));
			} else {
				Logger::Info("No plugins loaded");
			}
		}
	}
	
	function handleSuspiciousAMFPacket($socket) {
		$panda = $this->pandas[(int) $socket];
		
		echo("LEL 0");
		
		if($panda->identified === false) {
			$receivers = new \stdClass();
			//<msg t="sys"><body action="suspicious"><security id="report"><ticket><![CDATA[VfXPfGcMVg64trZRFYXLktTpr2xCZNMnhM3E4cGR7gCAPThr39nEvG3C8]]></ticket><ticketId><![CDATA[oauth:yFZJKzvuZsKJEuRXQ3XQC4wdF2kFtA65PyHYsGPDQ32uyJdLRSFmpg53q]]></tickerId><reportTicket><![CDATA[oauth:LUPUvwXEwPNXg4QUYQwUcCNCAXVCDEYWeqTJmNHa92MxHh2vdxXxrJyMC]]></reportTicket></security><message><reason><![CDATA[Intento de manipulación lel, hackeado.]]></reason><user><![CDATA[1013]]></user><info function="amfPlayerService.addBuddyList" arguments="hola,pendejo" actioning="KICK"></info></message></body></msg>
			
			$receivers->r = Packet::$Duo['body']['message']['reason'];
			$receivers->t = (int) Packet::$Duo['body']['message']['user'];
			$receivers->s = Packet::$Duo['body']['message']['info']['@attributes']['function'];
			$receivers->a = Packet::$Duo['body']['message']['info']['@attributes']['arguments'];
			$receivers->d = Packet::$Duo['body']['message']['info']['@attributes']['actioning'];
			
			if(!is_numeric($receivers->t)) {
				return $this->removePandaManager($panda);
			}
			
			if(strlen($receivers->r) < 4) {
				return $this->removePandaManager($panda);
			}
			
			if(strlen($receivers->s) < 4) {
				return $this->removePandaManager($panda);
			}
			
			if(strlen($receivers->d) < 2) {
				return $this->removePandaManager($panda);
			}
			
			call_user_func(array($this, 'onReportUser'), $receivers);
			
			return $this->removePandaManager($panda);
		} else {
			return $this->banHammer(1010,$panda,strtotime("+72 hours"),"ATTEMPTED_XML_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function handleFriendshipMessage($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->identified === false) {
			
			call_user_func(array($this, 'onStatusAccepted'),Array('r'=>Packet::$Duo['body']['friendship']['accepter'],'s'=>Packet::$Duo['body']['friendship']['sender'],'v'=>Packet::$Duo['body']['friendship']['value']));
			
			return $this->removePandaManager($panda);
		} else {
			return $this->banHammer(1010,$panda,strtotime("+72 hours"),"ATTEMPTED_XML_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function handlePolicyRequest($socket) {
		$panda = $this->pandas[(int) $socket];
		if(!$panda->isAmf) return $this->removePandaManager($panda,"<cross-domain-policy><allow-access-from domain='*' to-ports='{$this->port}'/></cross-domain-policy>");
		$panda->send("<cross-domain-policy><allow-access-from domain='*' to-ports='{$this->port}'/></cross-domain-policy>");
	}
	
	function handleFurnitureBought($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->identified === false) {
			$sed = Packet::$Duo['body']['user'];
			
			call_user_func(array($this, 'onBoughtItem'), $sed);
			
			return $this->removePandaManager($panda);
		} else {
			return $this->banHammer(1010,$panda,strtotime("+72 hours"),"ATTEMPTED_XML_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function handleNewMailSent($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->identified === false) {
			$sender = Packet::$Duo['body']['mailservice']['sender'];
			$target = Packet::$Duo['body']['mailservice']['recipent'];
			$type = Packet::$Duo['body']['mailservice']['type'];
			$count = Packet::$Duo['body']['mailservice']['read'];
			
			if(strrpos($target,",") !== false) {
				$targets = explode(",",$target);
				for($a = 0;$a < count($targets);$a++) {
					$user = $this->getPlayerById((int)$targets[$a]);
					if($user != null) {
						$user->send("270;$count|");
					}
				}
			} else {
				$socket = $this->getPlayerById($target);
				
				if($socket != null) {
					$socket->send("270;$count|");
				}
			}
			
			return $this->removePandaManager($panda);
		} else {
			return $this->banHammer(1010,$panda,strtotime("+72 hours"),"ATTEMPTED_XML_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function handleApiCommand($socket) {
		$panda = $this->pandas[(int) $socket];
		
		$type = Packet::$Duo['body']['package']['@attributes']["type"];
		$room = Packet::$Duo['body']['package']['@attributes']["room"];
		$args = Packet::$Duo['body']['package']["args"];
		$id = Packet::$Duo['body']['package']['@attributes']["id"];
		$cb = Packet::$Duo['body']['package']['@attributes']["callback"];
		
		switch($type) {
			case "advise":
				$panda->salted = true;
				$panda->isApiSocket = true;
				return $panda->send("10#$cb|");
			case "room_cmd":
				$this->rooms[$room]->send($args);
				return $panda->send("11#$cb|");
			case "user_room_cmd":
				$player = $this->getPlayerById($id);
				if($player != null) $player->room->send($args);
				return $panda->send("11#$cb|");
			case "server_cmd":
				foreach($this->rooms as $obj) {
					$obj->send($args);
				}
				return $panda->send("11#$cb|");
			case "userid_cmd":
				$player = $this->getPlayerById($id);
				if($player != null) $player->send($args);
				return $panda->send("12#$cb|");
			default:
				return $panda->send("13#$cb|");
		}
	}
	
	function handleXmlPacket($socket) {
		$xmlPacket = @Packet::GetInstance();
		$panda = $this->pandas[(int) $socket];
		if(isset($this->xmlHandlers[$xmlPacket::$Handler])) {
			$handlerCallback = $this->xmlHandlers[$xmlPacket::$Handler];
			if($xmlPacket::$Handler !== 'policy') {
				switch($xmlPacket::$Handler) {
					case 'mail':
						if($xmlPacket::$Duo['body']['security']['ticket'] !== $this->secure['mailSentAuth']) {
							Logger::Warn("Mail hax... KICKED");
							return $this->removePandaManager($panda);
						}
						break;
					case 'api.package.server.socket.CALL':
						if($xmlPacket::$Duo['body']['security']['@attributes']["action"] === "verify") {
							if($xmlPacket::$Duo['body']['security']['key'] !== $this->secure['apikey']) {
								return $this->removePandaManager($panda);
							}
						} else {
							return $this->removePandaManager($panda);
						}
						break;
					case 'fsua':
						if($xmlPacket::$Duo['body']['security']['ticket'] !== $this->secure['friendMessage']) {
							Logger::Warn("Friend hax... KICKED");
							return $this->removePandaManager($panda);
						}
						break;
					case 'gold':
						if($xmlPacket::$Duo['body']['security']['ticket'] !== $this->secure['goldIDMessage']) {
							Logger::Warn("Gold hax... KICKED");
							return $this->removePandaManager($panda);
						}
						break;
					case 'furniturebought':
						if($xmlPacket::$Duo['body']['security']['ticket'] !== $this->secure['furniturebought']) {
							Logger::Warn("Furniture hax... KICKED");
							return $this->removePandaManager($panda);
						}
						break;
					case 'suspicious':
						if($xmlPacket::$Duo['body']['security']['ticket'] === $this->secure['reportMessage']['auth']) {
							if($xmlPacket::$Duo['body']['security']['ticketId'] === $this->secure['reportMessage']['playersAuth']) {
								if($xmlPacket::$Duo['body']['security']['reportTicket'] !== $this->secure['reportMessage']['serverAuth']) {
									//LLogger::Warn("Report hax... KICKED");
									return $this->removePandaManager($panda);
								}
							} else {
								//LLogger::Warn("Report hax... KICKED");
								return $this->removePandaManager($panda);
							}
						} else {
							//LLogger::Warn("Report hax... KICKED");
							return $this->removePandaManager($panda);
						}
						break;
					default:
						return $this->removePandaManager($panda); 
				}
			}
			
			if(is_array($handlerCallback)) {
				call_user_func($handlerCallback, $panda);
			} elseif(method_exists($this, $handlerCallback)) {
				call_user_func(array($this, $handlerCallback), $socket);
			}
		} else {
			Logger::Warn("La funcion pa' {$xmlPacket::$Handler} no existe wey. :(");
			return $this->removePandaManager($panda);
		}
	}
	
	function handleWorldPacket($socket) {
		if(!isset($this->pandas[(int) $socket])) {
			return $this->removeClient($socket);
		}
		
		if($this->pandas[(int) $socket]->counts != 0 || ($this->pandas[(int) $socket]->identified && $this->pandas[(int) $socket]->verifiedTimes === 2) || Packet::$Handler == "0" || Packet::$Handler == '301') {
			if(Packet::$Handler === '0') {
				if($this->pandas[(int) $socket]->counts != 0 || $this->pandas[(int) $socket]->verifiedTimes !== 0 || $this->pandas[(int) $socket]->identified || Packet::$Duo[0] === null || Packet::$Duo[0] < 1011 || Packet::$Duo[0] > 99999 || strlen(Packet::$Duo[1]) < 9 || strlen(Packet::$Duo[2]) < 0 || strlen(Packet::$Duo[2]) > 9) {
					
					Logger::Warn("Paquete sospechoso con el prefijo '0' fue eliminado.");
					
					return $this->removePandaManager($this->pandas[(int) $socket]);
				}
			} elseif(Packet::$Handler === '301') {
				if($this->pandas[(int) $socket]->salted) {
					
					Logger::Warn("Paquete sospechoso con el prefijo '301' fue eliminado.");
					
					return $this->removePandaManager($this->pandas[(int) $socket]);
				}
			}
			
			$worldPacket = @Packet::GetInstance();
			
			$panda = $this->pandas[(int) $socket];
			
			if(Packet::$Handler != '0' && Packet::$Handler != '301') {
				if(!$panda->salted) {
					return $this->removePandaManager($panda);
				}
			}
			
			if(!isset($worldPacket::$count) || !isset($worldPacket::$packHash)) {
				$panda->send("260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
				return $this->removePandaManager($this->pandas[(int) $socket]);
			}
			
			if(!is_numeric($worldPacket::$count) || $this->pandas[(int) $socket]->counts + 1 != $worldPacket::$count || $this->pandas[(int) $socket]->counts + 1 < $worldPacket::$count) {
				return $this->removePandaManager($this->pandas[(int) $socket],"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
			} else {
				$this->pandas[(int) $socket]->lastCommand['ctime'] = floor(microtime(true)*1000);
				$this->pandas[(int) $socket]->counts = $this->pandas[(int) $socket]->counts + 1;
				if($this->pandas[(int) $socket]->counts >= 2500) {
					return $this->removePandaManager($this->pandas[(int) $socket],"260;Tu sesión ha expirado. Por favor, vuelve a registrarte.  |"); 
				}
			}
			
			if(md5($this->pandas[(int) $socket]->counts.'$%&2'.$worldPacket::$Handler) != $worldPacket::$packHash) {
				return $this->removePandaManager($this->pandas[(int) $socket],"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
			}
			
			if($panda->timeouted && !$panda->moderator) {
				if(isset($this->loadedPlugins['TimeoutCheck']->gameCommands[$worldPacket::$Handler])) {
					$this->loadedPlugins['TimeoutCheck']->handleWorldPacket($panda);
					if($worldPacket::$Handler === 'NONE' || $worldPacket::$Handler === 'none') return -1;
				}
			}
			
			foreach($this->loadedPlugins as $pluginName => $loadedPlugin) {
				if($pluginName != 'TimeoutCheck') {
					if(isset($loadedPlugin->gameCommands[$worldPacket::$Handler])) {
						list($handlerCallback, $callInformation) = $loadedPlugin->gameCommands[$worldPacket::$Handler];
						
						if($callInformation == Plugin::Before) {
							$loadedPlugin->handleWorldPacket($panda);
						}
					}
				}
			}
			if($worldPacket::$Handler === 'NONE' || $worldPacket::$Handler === 'none') return;
			
			if($worldPacket::$Handler === null || $worldPacket::$Handler === "\0" || $worldPacket::$Handler === "" || $worldPacket::$Handler === " ") {
				return $this->removePandaManager($panda);
			} elseif(isset($this->gameCommands[$worldPacket::$Handler]) && is_numeric($worldPacket::$Handler)) {
				$handlerCallback = $this->gameCommands[$worldPacket::$Handler];
				
				if(is_array($handlerCallback)) {
					call_user_func($handlerCallback, $panda);
				} elseif(method_exists($this, $handlerCallback)) {
					call_user_func(array($this, $handlerCallback), $socket);
				} else {	
					Logger::Warn("No se ha podido handlear a {$worldPacket::$Handler}, pobrecillo. :(");
				}
			} else {
				$this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
				Logger::Warn("La funcion pa' {$worldPacket::$Handler} no existe wey. :(");
			}
			
		} else {
			return $this->removePandaManager($this->pandas[(int) $socket]);
		}
	}
	
}
?>