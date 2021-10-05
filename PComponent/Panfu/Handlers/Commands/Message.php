<?php
namespace PComponent\Panfu\Handlers\Commands;
use PComponent, PComponent\Logging\Logger, PComponent\DatabaseManager, PComponent\Panfu\Packets\Packet;

trait Message {
	
	private $blackListedES = null;
	private $filtersLoaded = false;
	private static $allowPages = null;
	private static $suspiciousWords = null;
	private static $secureChatSnippets = Array("ES"=>Array('Hola','¡Sígueme!','Sí','No','Ok','Adiós','¡Hasta luego!','¡Nos vemos mañana!','¡Hasta mañana!','¡Espero verte más tarde!','Chau','Hasta luego','Hoy','Mañana','En un mes','En una semana','En una hora','En media hora','En un año','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo','Lunes','¡Eso no fue amable!','¡Eres estúpido!','¡Oh vaya!','¡Eres un payaso!','¡Me hiciste enojar!','Parece que nunca aprenderás','Mis padres no me dejan','¿Qué tal?','¿Quieres ser mi amigo?','¿Cuál es tu juego favorito?','¿Cuál es tu sala favorita?','¿De dónde eres?','¿Cuál es tu animal favorito?','¿Dónde quieres ir?','¿Cuál es tu color favorito?','¿Quieres visitar mi casa del árbol?','¿Dónde conseguiste eso?','Adulto','Joven','Niño','Niña','Rojo','Burdeo','Bordó','Rosa','El color negro','Marrón','Gris','Verde','Blanco','Amarillo','Azul','Naranja','Bien','Más o menos','Mal','¡Déjame en paz!','Argentina','Estados Unidos de América','México','Ecuador','Venezuela','Colombia','Chile','Uruguay','Uruguay','Paraguay','Bolivia','Cuyana francesa','Reino Unido','R. Dominicana','Luxemburgo','Narnia','El país del nunca jamás','¡Qué te importa!','Canadá','Alemania','España','De un lugar','P. Sherman Calle Wallaby 42 Sidney','Tierra del fuego','Ciudad','Volcán','Establo de Ponys','San Franpanfu','Selva','Heladería','Tienda de regalos','Tienda de animales','Campo de deportes','Cueva','Piscina','Casa del árbol','Restaurante','Playa','En otro lugar','Preparar helados','Cloud number nine','Bolly hop','Balloon pop','Hubi','WOW','Minecraft','PUBG','R6S','CSGO','HIZI','Counter Strike','Half Life','Doom','Juegos de simulación','Dark Souls','Juegos de terror','Juegos Battle Royale','Juegos metroidvania','Otro tipo','South Park','Juegos competitivos','Juegos sandbox','Juegos "choices matter"','GTA V','GTA San Andreas','GTA Vice City','¿Alguien me regala alguna clave de Steam? estoy bien pobre :(','Perro','Gato','Coyote','Elefante','Tigre','Hamster','Araña','Jirafa','Pez','Conejo','Ratón','Creeper','León','Morza','Foca','Pingüino','Bolly','Pokopet','Coyote','Ocelote','Caballo','Pollo','Rana','Caracol','Abeja','Avispa','Picar','Morder','Grr','¡Me gusta tu casa del árbol!','¡Me gustan tus muebles!','¡Qué buena deocoración!','¡Me ENCANTA tu casa del árbol!','¡Qué chulo!','¡Me gusta tu ropa!','¡Me gusta tu traje!','¡Eres lindo!','¡Eres linda!','¡Te amo!','Fortnite',"League of Legends","World of Warcraft","Red Dead Redemption"));
	static $result = false;
	static $restpt = 'none';
	
	static function autocorrector($msg) {
		$data = $msg;
		$modified = false;
		foreach(self::$suspiciousWords as $index => $word) {
			if(strpos($data,$index) !== false) {
				$modified = true;
				$data = str_replace($index,$word,$data);
			}
		}
		
		return $modified === true?$data.'*':$data;
	}
	
	static function checkWebPage($msg) {
		$spaces = strpos($msg,' ') != false?true:false;
		$data = $spaces == true?explode(' ',$msg):Array($msg);
		$argIndex = 0;
		
		foreach($data as $index => $page) {
			if(strpos($page,'http') !== false && strpos($page,'https') !== false && strpos($page,'://') !== false && strpos($page,'.') !== false) {
				$prt = explode('://',$page)[0];
				if($prt != 'http' && $prt != 'https') return -1;
				$prt = "https";
				$pages = explode('/',explode('://',$page)[1])[0];
				$uri = explode('/',explode('://',$page)[1]);
				array_shift($uri);
				$uri = '/'.implode('/',$uri);
				if(array_search($pages,self::$allowPages) !== false) {
					$argIndex = $index;
					$hyperlink = '<u><font color="#0000FF"><a target="_blank" href="'.$prt.'://'.$pages.$uri.'">'.$pages.$uri.'</a></font></u>';
					$data[$index] = $hyperlink;
				} else {
					return -1;
				}
			}
		}
		
		return $spaces==true?implode(' ',$data):implode('',$data);
	}
	
	static function verifyLetters($msg) {
		$let = Array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','u','p','q','r','s','t','v','w','x','y','z','h','.',',',':','/');
		foreach($let as $word) {
			if(count(explode($word,$msg)) > 26) {
				return false;
			}
		}
		return true;
	}
	
	function powUser($a,$d,$e) {
		$used = "";
		if($d->_names === "")return $a;if($d->_names === $e)return $a;
		if(strpos($a," ") !== false) {
			$b = explode(" ",$a);
			foreach($b as $e => $c) {
				if($c != null && strpos($c,":.") !== false) {
					$c = str_replace(":.","",$c);
					if(strpos(strtolower($c),strtolower($d->_names)) !== false) {
						if(!strpos($used,strtolower($c))) {
							$used = $used.",".strtolower($c);
							if(isset($msg) && !is_array($msg)) {
								$msg = Array($msg,"<font color='#0000FF'>@$c</font>");
								$name = Array($name,$this->getPlayerByName($c));
								$index = Array($index,$e);
							} elseif(is_array($msg)) {
								array_push($msg,"<font color='#0000FF'>@$c</font>");
								array_push($name,$this->getPlayerByName($c));
								array_push($index,$e);
							} else {
								$msg = "<font color='#0000FF'>@$c</font>";
								$name = $this->getPlayerByName($c);
								$index = $e;
							}
						}
					}
				}
			}
			
			if(isset($msg)) {
				if(!is_array($msg)) {
					$b[$index] = $msg;
					$name->send("113;1010;18;annoying|");
				} else {
					foreach($name as $f => $g) {
						$b[$f] = $msg[$f];
						$g->send("113;1010;15;annoying|");
					}
				}
			}
			
			return implode(" ",$b);
		}
	}
	
	function reverse($str) {
		preg_match_all('/./us',$str,$ostr);
		return join('',array_reverse($ostr[0]));
	} 
	
	function handleChatMessage($socket) {
		$panda = $this->pandas[(int) $socket];
		
		$message = Packet::$Duo[0];
		
		$timestamp = floor(microtime(true) * 1000);
		
		$can = $this->canChat($this->getCurrentDayName(),date('H'));
		
		/* VALENTINE'S EVENT -> FLYING HEART
		if($message === 'HeartMe' || substr($message,8) === 'HeartMe') {
			$panda->lastAction = "spell";
			return $panda->send("50;{$panda->id};flyingHeart|");
		}
		*/
		
		if($can || $panda->moderator) {
			if($panda->moderator || $panda->id == 1013) {
				if(substr($message,8,1) === '/'){
					$msg = str_replace('/','',$message);
					$msg = explode(' ',strtolower($msg));
					array_shift($msg);
					$le = $this->checkModeratorCheck($msg,$panda,$message);
					return "use strict";
				} else if($panda->id == 1013 && $message{0} === '/') {
					$message = "#FF0000 " . $message;
					$msg = str_replace('/','',$message);
					$msg = explode(' ',strtolower($msg));
					array_shift($msg);
					$le = $this->checkModeratorCheck($msg,$panda,$message);
					return "use strict";
				} else {
					if($can) {
						$msg = explode(' ',$message);
						array_shift($msg);
						$msg = implode(' ',$msg);
						if(!isset($panda->lastMessageTimer) || $timestamp > $panda->lastMessageTimer) {
							$response = $this->filter($message,$panda);
							
							if($response === 'warn') {
								return;
							}
							
							if($panda->reverseBan) {
								$message = explode(" ",$message);
								$colour = $message[0];
								array_shift($message);
								$message = $this->reverse(implode(" ",$message));
								$message = $colour . " " . $message;
								if(strtotime("now") > $this->_save->get($panda->id,"reverseban")) {
									$this->_save->_delete($panda->id,"reverseban");
									$panda->reverseBan = false;
								}
							}
							
							if(strpos($message,'http') !== false || strpos($message,'://') !== false || strpos($message,'.com') !== false || strpos($message,'.onion') !== false || strpos($message,'.eu') !== false || strpos($message,'.es') !== false || strpos($message,'.tk') !== false || strpos($message,'.me') !== false || strpos($message,'.in') !== false || strpos($message,'.ly') !== false || strpos($message,'.web') !== false || strpos($message,'.online') !== false || strpos($message,'.gg') !== false || strpos($message,'base64:') !== false) {
								$res = self::checkWebPage($message);
								if($res === -1) return false;
								$message = $res;
							}
							
							if(strpos($message,".me") !== false) {
								$message = str_replace(".me",$panda->username,$message);
							}
							if(strpos($message,".id") !== false) {
								$message = str_replace(".id",$panda->id,$message);
							}
							if(strpos($message,".day") !== false) {
								$message = str_replace(".day",$this->getCurrentDayName(),$message);
							}
							
							if(strpos($message,":.") !== false) {
								$message = $this->powUser($message,$panda->room,$panda->username);
							}
							
							if(!$panda->reverseBan && strlen($message) > 3) {
								$this->databaseManager->original->saveMessage($panda->id,$panda->room->externalId,$this->serverId,$panda->username,$msg,date('Y-m-d H:i:s'),floor(microtime(true)*1000));
							}
							
							$message = self::autocorrector($message);
							$panda->room->send("40;{$panda->id};$message|");
							$panda->lastMessageTimer = floor(microtime(true)*1000)+1000;
						} 
					} else { 
						return $panda->send("80;ONLY_SAFE_CHAT_MSG|");
						return $panda->send("80;KICK_MODERATOR_MSG;25000000|");
					}
				}
			}else{
				if(!$panda->muted) {
					if(strpos($message,'<') !== false || strpos($message,'>') !== false || strpos($message,'#') !== false) {
						return $this->banHammer(1010,$panda,strtotime("+1 hour"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					
					if(!preg_match("/^[áéíóúÁÉÍÓÚ!ñ?:\/¿¡.,a-zA-Z0-9 \s]+$/i",$message)) {
						return $this->banHammer(1010,$panda,strtotime("+1 hour"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					
					if(substr($message,0,1) === '/'){
						$command = $this->checkTextIsCommand($message,$panda);
						
						if($command) return "use strict";
					}
					
					if(!isset($panda->lastMessageTimer) || $timestamp > $panda->lastMessageTimer) {
						$response = $this->filter($message,$panda);
						
						if($panda->reverseBan) {
							$message = $this->reverse($message);
							if(strtotime("now") > $this->_save->get($panda->id,"reverseban")) {
								$this->_save->_delete($panda->id,"reverseban");
								$panda->reverseBan = false;
							}
						}
						
						if($response === 'warn') {
							$panda->msgWarn = $panda->msgWarn + 1;
							if($panda->msgWarn === 5) {
								$this->checkModeratorCheck(Array('timeout',$panda->username,10),null);
							} else if($panda->msgWarn === 10) {
								return $panda->send("81|");
							}
							
							foreach($this->pandasByIdModerators as $a) {
								if($a->room->externalId == $panda->room->externalId) {
									$a->send("40;{$panda->id};Inapropiado[<font color=\"#990000\">$message</font>]|");
								}
							}
							
							return $panda->send("40;1010;<font color=\"#FF0000\">Verifica tu vocabulario antes de comentar ó podrías ser suspendido o silenciado. [{$panda->msgWarn}/10]</font>|113;1010;13;{$panda->id};hitlightningtransformation;animation|");
						} elseif($response === 'kick') {
							
							$panda->startUserMuted();
							$panda->updateMute(true,strtotime('+30 minutes'));
							return $this->removePandaManager($panda,"80;KICK_MODERATOR_MSG;30|");
						}
						
						if(strpos($message,'http') !== false || strpos($message,'://') !== false || strpos($message,'.com') !== false || strpos($message,'.onion') !== false || strpos($message,'.eu') !== false || strpos($message,'.es') !== false || strpos($message,'.tk') !== false || strpos($message,'.me') !== false || strpos($message,'.in') !== false || strpos($message,'.ly') !== false || strpos($message,'.web') !== false || strpos($message,'.online') !== false || strpos($message,'.gg') !== false || strpos($message,'base64:') !== false) {
							$res = self::checkWebPage($message);
							if($res === -1) return false;
							$message = $res;
						} elseif($message{0} == '.') {
							$ok = explode(" ",$message);
							$first = strpos($message," ")!==false?$ok[0]:$message;
							switch($first) {
								case ".small":
								case ".peque":
								case ".min":
									$msg = str_replace($first,"",$message);
									$msg = substr_replace($msg,"",0,1);
									$message = "<font size=\"8\">$msg</font>";
									break;
								case ".big":
								case ".grande":
								case ".max":
									$msg = str_replace($first,"",$message);
									$msg = substr_replace($msg,"",0,1);
									$message = "<font size=\"30\">$msg</font>";
									break;
								case ".underline":
								case ".lineabaja":
								case ".linea":
									$msg = str_replace($first,"",$message);
									$msg = substr_replace($msg,"",0,1);
									$message = "<u>$msg</u>";
									break;
								case ".color":
								case ".hex":
								case ".col":
									$hex = $ok[1];
									if($hex == null) break;
									$msg = str_replace($first,"",$message);
									$msg = substr_replace($msg,"",0,1);
									$msg = str_replace($hex,"",$msg);
									$msg = substr_replace($msg,"",0,1);
									$message = "<font color=\"#$hex\" face=\"italic\">$msg</font>";
									break;
							}
						} 
						
						if(strpos($message,".me") !== false) {
							$message = str_replace(".me",$panda->username,$message);
						}
						if(strpos($message,".id") !== false) {
							$message = str_replace(".id",$panda->id,$message);
						}
						if(strpos($message,".day") !== false) {
							$message = str_replace(".day",$this->getCurrentDayName(),$message);
						}
						
						if(strpos($message,":.") !== false) {
							$message = $this->powUser($message,$panda->room,$panda->username);
						}
						
						$message = self::autocorrector($message);
						
						if(!$panda->reverseBan && strlen($message) > 3) {
							$this->databaseManager->original->saveMessage($panda->id,$panda->room->externalId,$this->serverId,$panda->username,$msg,date('Y-m-d H:i:s'),floor(microtime(true)*1000));
						}
						
						$panda->room->send("40;{$panda->id};$message|");
						$panda->lastMessageTimer = floor(microtime(true)*1000)+1000;
					}
				} else {
					if($panda->btiming < strtotime('now')) {
						$panda->muted = false;
						$panda->startUserMuted();
						$panda->updateMute(false);
					}
					return $panda->send("80;LOCKED_SAFE_CHAT_MSG|");
				}
			}
		} else{ 
			return $panda->send("80;ONLY_SAFE_CHAT_MSG|");
		}
	}
	
	function handleSecureChatMessages($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$msg = Packet::$Duo[0];
		
		$timestamp = floor(microtime(true) * 1000);
		
		if(!isset($panda->lastMessageTimer) || $timestamp > $panda->lastMessageTimer) {
			$panda->lastMessageTimer = floor(microtime(true) * 1000) + 2000;
			
			if(!is_numeric(array_search($msg,self::$secureChatSnippets[$this->severLang]))) {
				return $this->removePandaManager($panda);
			}
			
			return $panda->room->send("40;{$panda->id};$msg|");
		}
	}
	
	function handleEmoteMessage($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$emoteId = Packet::$Duo[0];
		$timestamp = floor(microtime(true) * 1000);
		
		if(!isset($panda->lastEmoteTimer) || $timestamp > $panda->lastEmoteTimer){
			$panda->lastEmoteTimer = floor(microtime(true) * 1000) + 1000;
		
			switch($emoteId) {
				case 1:
				case 2:
				case 3:
				case 4:
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
				case 10:
				case 11:
					if(!$panda->isPremium) {
						return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
					}
					break;
				case 12:
					if($panda->socialLevel < 54 && $panda->modLevel < 2) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
					break;
				case 13:
					if($panda->socialLevel < 57 && $panda->modLevel < 2) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
					break;
				case 14:
					if($panda->socialLevel < 41 && $panda->modLevel < 2) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
					break;
				case 15:
					if($panda->socialLevel < 46 && $panda->modLevel < 2) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
					break;
			}
			
			$panda->room->send("41;{$panda->id};$emoteId|");
		}
	}
	
	function checkModeratorCheck($list, $target, $original = '¿?')
	{
		switch($list[0]){
			case 'summonmove':
				array_shift($list);
				return $this->moveSummoner([$target,implode(" ",$list)]);
			case 'summon':
				array_shift($list);
				return $this->summonPlayer([$target,implode(" ",$list)]);
			case 'timeout':
				array_shift($list);
				if($target->modLevel < 2) return $target->send("260;Tu nivel de moderación sólo te permite dar Time Outs de 10 o menos minutos.");
				if(count($list) < 1) return $target->send("260;/timeout *nombre o id* *tiempo(minutos)*  |");
				list($userIdOrName,$time) = $list;
				$targetPlayer = $this->getPlayerByName($userIdOrName);
				if($targetPlayer === NULL){
					if(is_numeric($userIdOrName)){
						$targetPlayer = $this->getPlayerById($userIdOrName);
						if($targetPlayer === NULL){
							if($target != null) $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							return;
						}
					}else{
						if($target != null) $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						return;
					}
				}
				
				try {
					$this->trackData("discordhook","[{$target->username}] -> {$targetPlayer->username} recibió un timeout de $time minutos.","Info bot");
				} catch(DataException $e) {
					Logger::warn($e);
				}
				
				if($targetPlayer->moderator && $target->modLevel < 3) return null;
				Logger::warn("Timeouted lololo -> {$targetPlayer->username} 4 $time minutes. GG.");
				$targetPlayer->timeouted = true;
				$targetPlayer->send("260;Recibiste un TimeOut de $time minutos.<br>No podrás hablar, moverte ni cambiar de sala y los demás <u>no verán</u> lo que haces.<br>Cuida tu vocabulario o serás silenciado por 24 hs.   |81;LANGUAGE_FAULT|");
				return $this->_save->save($targetPlayer->id,strtotime("now +$time minutes"),"timeout");
			case 'maintenance':
				if($target->modLevel > 1 || $target->id === 1013) {
					if(!$this->maintenanceMode) {
						$this->maintenanceMode = true;
						
						if(count($this->pandas) > 0) {
							foreach($this->pandas as $player) {
								$this->removePandaManager($player,"260;El juego ha sido terminado por un fallo en el sistema.  |");
							}
						}
						
						try {
							$this->trackData("discordhook","[{$target->username}] -> se activó el modo mantenimiento. Los usuarios no podrán iniciar sesión hasta que se desactive.","Info bot");
						} catch(DataException $e) {
							Logger::warn($e);
						}
						
						return;
					}
					
					$this->maintenanceMode = false;
					
					if(count($this->pandas) > 0) {
						foreach($this->pandas as $player) {
							$this->removePandaManager($player,"260;El juego ha sido terminado por un fallo en el sistema.  |");
						}
					}
					
					try {
						$this->trackData("discordhook","[{$target->username}] -> se desactivó el modo mantenimiento. Los usuarios podrán iniciar sesión hasta que se active.","Info bot");
					} catch(DataException $e) {
						Logger::warn($e);
					}
					
					return;
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'sleepingserver':
				if($target->modLevel > 2 || $target->id === 1013) {
					array_shift($list);
					list($minutes) = $list;
					
					try {
						$this->trackData("discordhook","[{$target->username}] -> Servidor deshabilitado por $minutes segundos.","Info bot");
					} catch(DataException $e) {
						Logger::warn($e);
					}
					
					sleep($minutes);
				}
				break;
			case 'ipunban':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName) = $list;
					if(is_numeric($userIdOrName)) {
						$data = $target->database->getColumnsById($userIdOrName,Array("username","current_ip"));
					} else {
						$data = $target->database->getColumnsByName($userIdOrName,Array("username","current_ip"));
					}
					
					if(!isset($data['username'])) {
						return;
					}
					
					
					$targetHolder->database->ip_unblock($data['current_ip']);
					$targetPlayer->database->unbanUser($data['username']);
					
					return;
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'ipban':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName) = $list;
					if(is_numeric($userIdOrName)) {
						$data = $target->database->getColumnsById($userIdOrName,Array("username","current_ip","id"));
					} else {
						$data = $target->database->getColumnsByName($userIdOrName,Array("username","current_ip","id"));
					}
					
					if(!isset($data['username'])) {
						return;
					}
					
					
					$targetHolder->database->ip_block($data['current_ip']);
					$targetPlayer->database->banUser((int)$data['id'],0,$data['username'],"USER_IP_BANNED_VERIFICATION",1010,time());
					
					$targetHolder = $this->getPlayerByName(strtolower($userIdOrName));
					if($targetHolder === NULL){
						$targetHolder = $this->getPlayerById($userIdOrName);
						if($targetHolder === NULL){
							return;
						}
					}
					
					return $this->removePandaManager($targetHolder);
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'reverseban':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName,$time) = $list;
					$targetHolder = $this->getPlayerByName(strtolower($userIdOrName));
					if($targetHolder === NULL){
						$targetHolder = $this->getPlayerById($userIdOrName);
						if($targetHolder === NULL){
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					$targetHolder->reverseBan = true;
					$this->_save->save($targetHolder->id,strtotime("now +$time minutes"),"reverseban");
					return $targetHolder->send("260;Reversebanned: ahora tendrás que esperar $time minutos para volver a escribir \"normal\".  |");
				}

				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'updatename':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName,$name) = $list;
					$targetHolder = $this->getPlayerByName(strtolower($userIdOrName));
					if($targetHolder === NULL){
						$targetHolder = $this->getPlayerById($userIdOrName);
						if($targetHolder === NULL){
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}

					$targetHolder->database->updateDisplayName($name,$targetHolder->id);
					return $this->removePandaManager($targetHolder);
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'unmute':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName) = $list;
					$targetHolder = $this->getPlayerByName(strtolower($userIdOrName));
					if($targetHolder === NULL){
						$targetHolder = $this->getPlayerById($userIdOrName);
						if($targetHolder === NULL){
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					$targetHolder->muted = false;
					$targetHolder->startUserMuted();
					$targetHolder->updateMute(false);
					
					$this->moderatorMsg("{$target->username} removió el mute de {$targetHolder->username}.");
				} else {
					return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'mybadwords':
				if($target->modLevel > 1 || $target->id === 1013) {
					$words = $this->getFilteredWords($target->id);
					
					if($words[0]) {
						return $target->send("260;{$words[1]}  |");
					}
					
					switch($words[1]) {
						case 1:
							return $target->send("260;No has colocado ninguna palabra aún.  |");
						case 2:
							return $target->send("260;¡Lamentablemente no pudimos añadir la palabra!<br>Ocurrió un error durante la conexión.<br>Inténtalo más tarde, si el error persiste contacta inmediatamente a un administrador.  |");
					}
					
					return $target->send("260;¡Lamentablemente no pudimos añadir la palabra!<br>Tal vez no has colocado ninguna palabra aún, u ocurrió un error durante la conexión.<br>Inténtalo más tarde, si el error persiste contacta inmediatamente a un administrador.  |");
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'deleteword':
				if(count($list) <= 1) return $target->send("260;/deleteWord *id de la palabra, consulta \"myBadWords\" para saberla.*  |");
				
				array_shift($list);
				list($id) = $list;
				if($target->modLevel > 1 || $target->id === 1013) {
					if(is_string($id)) {
						$id = str_replace('&','|',$id);
					}
					
					$resp = $this->deleteWord($target->id,$id);
					
					if($resp[0]) {
						switch($resp[2]) {
							case 1:
								unset(self::$suspiciousWords[explode('|',$resp[1])[0]]);
								break;
							case 2:
								if(($key = array_search($resp[1],$this->blackListedES)) !== FALSE) {
									array_splice($this->blackListedES,$key,1);
								}
								break;
							case 3:
								if(($key = array_search($resp[1],self::$allowPages)) !== FALSE) {
									array_splice(self::$allowPages,$key,1);
								}
								break;
						}
						
						return $target->send("260;Se borró correctamente la palabra.   |");
					}
					
					return $target->send("260;No se encontró la palabra a borrar.   |");
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'baninfo':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($userIdOrName) = $list;
					$_i = $this->checkExistence($userIdOrName);
					if($_i != null) {
						return $target->send("260;{$this->getBanInfo($_i)}  |");
					}
					
					return $target->send("260;El usuario no existe: \"{$userIdOrName}\".  |");
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'addword':
				if($target->modLevel > 1 || $target->id === 1013) {
					if(count($list) <= 1) return $target->send("260;/addWord *palabra* *tipo de palabra (1 => palabra sospechosa [NO HAY SUSPENSIÓN, PERO SÍ SERA CORREGIDA. EN ESTE DEBERÁS APLICAR UNA CORRECIÓN, POR EJEMPLO: (Soy puto&Soy un panda). Deberás de incluir el '&' en el medio para dividir cuál es la palabra a corregir, y la correción.], 2 => palabra inapropiada [SÍ HAY ADVERTENCIA O SUSPENSIÓN], 3 => Sítio web apropiado [NO SERÁ CENSURADO DURANTE EL CHAT])*  |");
					
					array_shift($list);
					list($word,$gramatic) = $list;
					
					if($gramatic > 3 || $gramatic < 1) return $target->send("260;El tipo de palabra no está asignada. Los únicos tipos reconocidos son [1,2,3], para más información escribe \"/addWord\".  |");
					
					if($gramatic === 3) {
						if(strrpos($word,".") === false && strrpos($word,":") === false) {
							return $target->send("260;La Id de gramática(3) no corresponde con el texto que acabas de brindarnos.  |");
						}
					}
					
					$word = str_replace('&','|',$word);
					
					$resp = $this->addGramaticalWord($word,$gramatic,$target->id);
					
					if($resp[0]) {
						switch($gramatic) {
							case 1:
								self::$suspiciousWords[explode('|',$word)[0]] = explode('|',$word)[1];
								break;
							case 2:
								array_push($this->blackListedES,$word);
								break;
							case 3:
								array_push(self::$allowPages,$word);
								break;
						}
						
						$word = str_replace('|','& [REEMPLAZADO]',$word);
						
						return $target->send("260;Se añadió la palabra ($word) correctamente, su respectiva Id es: {$resp[1]}. Deberás de utilizarla por si quieres eliminarla. De otra forma escribe '/myBadWords' para solicitar todas las palabras añadidas por tí y sus respectivas Ides.  |");
					}
					
					switch($resp[1]) {
						case 1:
							return $target->send("260;¡Lamentablemente no pudimos añadir la palabra!<br>La palabra ya existe.  |");
						case 2:
							return $target->send("260;¡Lamentablemente no pudimos añadir la palabra!<br>Ocurrió un error durante la conexión.<br>Inténtalo más tarde, si el error persiste contacta inmediatamente a un administrador.  |");
					}
					
					return $target->send("260;¡Lamentablemente no pudimos añadir la palabra!<br>Tal vez ya existía, u ocurrió un error durante la conexión.<br>Inténtalo más tarde, si el error persiste contacta inmediatamente a un administrador.  |");
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'info':
				if($target->modLevel > 1 || $target->id === 1013) {
					array_shift($list);
					list($name) = $list;
					if(!is_numeric($name)) {
						return $this->handleGetPlayerByName($target,$name,true);
					}
					
					return $this->handleGetPlayerById($target,$name,true);
				}
					
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'unban':
				if($target->modLevel > 1 || $target->id === 1013) {
					if(count($list) <= 1) return $target->send("260;/unban *nombre del jugador*  |");
					array_shift($list);
					list($name) = $list;
					if($this->unbanPan($target,$name)) {
						foreach($this->pandasByIdModerators as $a) {
							$a->send("40;1010;{$target->username} le ha quitado el veto a $name.|");
						}
						
						return $target->send("260;Se le ha quitado el veto al jugador.  |");
					}
					
					return $target->send("260;Ocurrió un error, revisa los argumentos que ingresaste.  |");
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'stopservercrack':
				if($target->modLevel > 2 || $target->id === 1013) {
					$this->continueExec = 0;
					$target->send("260;Apagado.  |");
					$this->maintenanceMode = true;
					
					try {
						$this->trackData("discordhook","[{$target->username}] -> Servidor apagado. Tiempo total encendido ~> ".(strtotime("now") - $this->runts).".","Info bot");
					} catch(DataException $e) {
						Logger::warn($e);
					}
					
					die("Server stopped! ~ Requested by " . $target->username);
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'restartserver':
				if($target->modLevel > 2 || $target->id === 1013) {
					foreach($this->pandas as $player => $info) {
						$this->removePandaManager($info);
						unset($player);
					}
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'speed':
			case 'ae':
			case 'sf':
			case 'st':
			case 'sg':
			case 'sc':
			case 'nf':
			case 'nc':
			case 'ng':
			case 'alias':
			case 'alpha':
			case 'we':
			case 'she':
			case 'he':
				$this->checkTextIsCommand('/'. $list[0] . ' ' . $list[1], $target);
				break;
			case 'bot':
				if($target->modLevel > 1) {
					if(count($list) < 1) return $target->send("260;/timeout *tipo* *argumentos*  |");
					array_shift($list);
					switch($list[0]) {
						case 'move':
							array_shift($list);
							$list = explode(' ',$original);
							array_shift($list);
							array_shift($list);
							array_shift($list);
							
							if(!isset($list[0]) || !isset($list[1])) {
								$target->send("260;Número inválido de argumentos.  |");
								return true;
							}
							
							if(!isset($list[2])) {
								array_push($list,'3');
							}
							
							$target->room->bot('move',$list);
							break;
						case 'message':
							$list = explode(' ',$original);
							array_shift($list);
							array_shift($list);
							array_shift($list);
							$list[0] = implode(' ',$list);
							if(!isset($list[0])) {
								$target->send("260;Número inválido de argumentos.  |");
								return true;
							}
							
							$target->room->bot('message',$list);
							break;
						case 'throw':
							$list = explode(' ',$original);
							array_shift($list);
							array_shift($list);
							array_shift($list);
							
							if(!isset($list[0]) || !isset($list[1]) || !isset($list[2])) {
								$target->send("260;Número inválido de argumentos.  |");
								return true;
							}
							
							if(!isset($list[3])) {
								array_push($list,'-1');
							}
							
							$target->room->bot('throw',$list);
							break;
						case 'state':
						case 'achievement':
							$list = explode(' ',$original);
							array_shift($list);
							array_shift($list);
							array_shift($list);
							
							if(!isset($list[0])) {
								$target->send("260;Número inválido de argumentos.  |");
								return true;
							}
							
							$args = Array($list[0]);
							
							if(!isset($list[1])) {
								array_push($args,'');
							} else {
								$list[0] = '';
								$tt = substr_replace(implode(' ',$list),'',0,1);
								array_push($args,$tt);
							}
							
							$target->room->bot('state',$args);
							break;
					}
				} else {
					return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'warn':
			case 'advertir':
			case 'advertencia':
				array_shift($list);
				if(count($list) < 1) return $target->send("260;/warn,advertencia,advertir nombre o id*  |");
				list($userIdOrName) = $list;
				$targetHolder = $this->getPlayerByName(strtolower($userIdOrName));
				if($targetHolder === NULL){
					$targetHolder = $this->getPlayerById($userIdOrName);
					if($targetHolder === NULL){
						$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						return;
					}
				}
				
				$targetHolder->send("81|");
				break;
			case 'global':
			case 'mensaje':
			case 'tell':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/global,mensaje,tell *tipo* *argumento*  |");
					list($tipo,$argumentos) = $list;
					
					switch($tipo){
						case 'a':
							$argumentos = explode(' ',$original);
							array_shift($argumentos);
							array_shift($argumentos);
							array_shift($argumentos);
							$argumentos = implode(' ',$argumentos);
							$target->room->send("260;$argumentos  |");
							break;
						case 'b':
							$argumentos = explode(' ',$original);
							array_shift($argumentos);
							array_shift($argumentos);
							array_shift($argumentos);
							$argumentos = implode(' ',$argumentos);
							
							foreach($this->rooms as $obj){
								$obj->send("40;1010;$argumentos|");
							}
							break;
						case 'c':
							$targetPlayer = $this->getPlayerByName($list[1]);
							
							if($targetPlayer === NULL){
								if(is_numeric($list[1])){
									$targetPlayer = $this->getPlayerById($list[1]);
									if($targetPlayer === NULL){
										return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$list[1]."'.  |");
									}
								}else{
									return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$list[1]."'.  |");
								}
							}
							
							$argumentos = explode(' ',$original);
							array_shift($argumentos);
							array_shift($argumentos);
							array_shift($argumentos);
							array_shift($argumentos);
							$argumentos = implode(' ',$argumentos);
							
							$targetPlayer->send("260;$argumentos  |");
							break;
						default:
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El tipo de mensaje no lo especificaste, los únicos existentes son: A (toda una sala), B(todo el server) o C(un jugador en específico, se necesita el nombre o ID del mismo)  |");
					}
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'level':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/level *nombre o id*  |");
					list($userIdOrName) = $list;
					$targetPlayer = $this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
								return;
							}
						}else{
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							return;
						}
					}
					
					$targetPlayer->database->updateLevel($targetPlayer->id);
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'premium':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/premium *nombre o id* *nivel(0,1,2)*  |");
					list($userIdOrName,$premLvl) = $list;
					$targetPlayer = $this->getPlayerByName($userIdOrName);
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
								return;
							}
						}else{
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							return;
						}
					}
					$targetPlayer->database->updatePremium($targetPlayer->id,$premLvl);
					if($premLvl > 0) {
						
						return $this->removePandaManager($targetPlayer,"260;¡Ahora eres <font color='#FFB653'>Panda de Oro</font>!<br><br>Ya puedes disfrutar de los beneficios de oro. ¡Vuelve a iniciar sesión para ver tus estadísticas!|"); 
					}
					
					return $this->removePandaManager($targetPlayer,"260;¡Ahora <font color='#FF0000'>no</font> eres <font color='#FFB653'>Panda de Oro</font>!<br><br>Ya <font color='#FF0000'>no</font> puedes disfrutar de los beneficios de oro. ¡Vuelve a iniciar sesión para ver tus estadísticas!|");
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'mod':
				if($target->modLevel > 2 || $target->id == 1013) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/mod *nombre o id* *nivel(0,1,2,3)*  |");
					list($userIdOrName,$lvl) = $list;
					
					if($target->id != 1013 && ($userIdOrName === 'Zayko_' || $userIdOrName === 1013)) return $target->send("260;<font color='#FF0000'>LOL</font>  |");
					
					$target->database->updateSheriff($userIdOrName,$lvl);
					
					$targetPlayer = $this->getPlayerByName($userIdOrName);
					
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
						} else {
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					return $this->removePandaManager($targetPlayer);
				}
				
				return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
			case 'social':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/social *nombre o id* *porcentaje (sin el respectivo carácter)*  |");
					list($userIdOrName,$social) = $list;
					$targetPlayer = $this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
								return;
							}
						}else{
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							return;
						}
					}
					
					if(is_numeric($social)){
						if($social > 100){
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El 'social level' debe ser IGUAL o MENOR a 100.  |");
							return;
						}
					}else{
						$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El 'social level' debe ser un número, plz. :(  |");
						return;
					}
					
					$targetPlayer->database->updateSocialLevel($targetPlayer->id,$social);
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'coins':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/coins *nombre o id* *AÑADIR cantidad*  |");
					list($userIdOrName,$coins) = $list;
					$targetPlayer = $this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
						}else{
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					if(is_numeric($coins)){
						if($coins > 500){
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El número de monedas a añadir debe de ser MENOR a 501, porque de otro modo se vuelven negativas.  |");
						}
					}else{
						return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>Las monedas deberían de ser un número, ¿no? supongo, tal vez no.  |");
					}
					
					$targetPlayer->database->addCoins($targetPlayer->id,$coins - 1);
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'online':
				$userCount = count($this->pandas);
				
				return $target->send("260;Actualmente hay $userCount conectado(s).  |");
			case 'mute':
			case 'silenciar':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/mute,silenciar *nombre o id*  |");
					list($userIdOrName) = $list;
					$targetPlayer = $this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
						}else{
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					if($targetPlayer->moderator && $target->modLevel < 3) return null;
					
					$this->moderatorMsg("{$target->username} ha silenciado a {$targetPlayer->username}.");
					
					if(!$targetPlayer->muted){
						$targetPlayer->startUserMuted();
						$targetPlayer->muted = true;
						return $this->removePandaManager($targetPlayer,"80;KICK_BLACKLIST_MSG|");
					}else{
						return $target->send("260;Utiliza \"/unmute\" para desilenciar a un usuario.  |");
					}
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'rdo':
				if($target->modLevel > 1) {
					array_shift($list);
					list($transfo,$transfoType) = $list;
					$argumentos = explode(' ',$original);
					array_shift($argumentos);
					array_shift($argumentos);
					$a = $argumentos[0];
					array_shift($argumentos);
					$b = implode(' ',$argumentos);
					
					foreach($this->pandas as $user){
						$target->room->send("113;{$target->id};13;{$user->id};$a;$b|");
					}
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'do':
				if($target->modLevel > 1) {
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/do *id or name* *transfo* *transfoTYPE*  |");
					list($userIdOrName,$transfo,$transfoType) = $list;
					
					$targetPlayer = $userIdOrName === "me"?$target:$this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
						}else{
							return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						}
					}
					
					$argumentos = explode(' ',$original);
					array_shift($argumentos);
					array_shift($argumentos);
					array_shift($argumentos);
					$str = implode(';',$argumentos);
					
					$targetPlayer->room->send("113;{$target->id};13;{$targetPlayer->id};$str|");
				} else {
					$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
				break;
			case 'kick':
			case 'patada':
				array_shift($list);
				if(count($list) < 1) return $target->send("260;/kick,patada *nombre o id*  |");
				list($userIdOrName) = $list;
				$targetPlayer = $this->getPlayerByName($userIdOrName);
						
				if($targetPlayer === NULL){
					if(is_numeric($userIdOrName)){
						$targetPlayer = $this->getPlayerById($userIdOrName);
						if($targetPlayer === NULL){
							$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							return;
						}
					}else{
						$target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
						return;
					}
				}
				if($targetPlayer->moderator && $target->modLevel < 3) return null;
				
				$this->moderatorMsg("{$target->username} ha removido del servidor a {$targetHolder->username}.");
				
				return $this->removePandaManager($targetPlayer,"80;KICK_BLACKLIST_MSG|");
			case 'ban':
			case 'suspender':
				if($target->modLevel > 1) {
					$argumentos = explode(' ',$original);
					array_shift($argumentos);
					array_shift($argumentos);
					array_shift($argumentos);
					array_shift($argumentos);
					array_shift($list);
					if(count($list) < 1) return $target->send("260;/ban,suspender,bann *id or nombre* *duración* *razón*  |");
					if(count($list) < 2) return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>Faltan argumentos.  |");
					list($userIdOrName,$duration,$reason) = $list;
					$reason = implode(' ',$argumentos);
					$targetPlayer = $this->getPlayerByName($userIdOrName);
							
					if($targetPlayer === NULL){
						if(is_numeric($userIdOrName)){
							$targetPlayer = $this->getPlayerById($userIdOrName);
							if($targetPlayer === NULL){
								if($this->tryBanFromDatabase($userIdOrName,$duration,$reason,$target->id,$target) === FALSE) {
									return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
								}
								return;
							}
						} else {
							if($this->tryBanFromDatabase($userIdOrName,$duration,$reason,$target->id,$target) === FALSE) {
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
							$this->moderatorMsg("{$target->username} suspendió a ".((!is_numeric($userIdOrName))?"Nombre":"ID")."[$userIdOrName] por $time hora(s). Razón: $reason");
							return;
						}
					}
					
					if(!is_numeric($duration)){
						return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>Ingresa una duración válida.  |");
					}
					// && $targetPlayer->modLevel < 3
					if($targetPlayer->moderator && $target->modLevel < 3 && $targetPlayer->modLevel == 3) return;
					
					$time = ((strtotime("+$duration hours") - strtotime("now")) / (60 * 60));
					
					$this->moderatorMsg("{$target->username} suspendió a {$targetPlayer->username} por $time hora(s). Razón: $reason");
					
					if($duration === 0) {
						$targetPlayer->database->banUser($targetPlayer->id,0,$targetPlayer->username,$reason,$target->id,time());
						return $this->removePandaManager($targetPlayer,"80;KICK_MODERATOR_MSG;25000000|");
					} else {
						$targetPlayer->database->banUser($targetPlayer->id,strtotime("+".$duration." hours"),$targetPlayer->username,$reason,$target->id,time());
						return $this->removePandaManager($targetPlayer,"260;¡<u><font size=\"15\">Parece que has roto las reglas de Panfu</font></u>!<br>Por el incumplimiento de las normas que crean y mantienen el \"ambiente seguro\" en todo Panfu, teniendo en cuenta que fuiste advertido reiteradas veces, se te ha bloqueado el acceso al juego por <font color=\"#FEDC3D\">$time hora(s)</font>. <br>Al cesar el bloqueo, se te retornará el completo acceso al juego.<br>¡Ten más cuidado la próxima vez!  |");
					}
				} else {
					return $target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
				}
			case 'aparecer':
			case 'summon':
				array_shift($list);
				if(count($list) < 2) return $target->send("260;/summon,aparecer *tipo* *nombre o id*  |");
				list($type,$userIdOrName) = $list;
				
				switch($type){
					case 'panda':
					case 'a':
						$targetPlayer = $this->getPlayerByName($userIdOrName);
								
						if($targetPlayer === NULL){
							if(is_numeric($userIdOrName)) {
								$targetPlayer = $this->getPlayerById($userIdOrName);
								if($targetPlayer === NULL) {
									return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
								}
							} else {
								return $target->send("260;<font color='#FF0000'>¡Ocurrió un eror!</font><br>El nombre o ID que nos brindaste parece estar mal tipeada, por favor, verifica lo que ingresaste: '".$userIdOrName."'.  |");
							}
						}
						
						if(isset($targetPlayer->room)) {
							$targetPlayer->room->remove($targetPlayer,"none","113;-1;14;RoomLeft;Llamado por un mod LUL|");
						}
						
						$this->rooms[$target->room->externalId]->add($targetPlayer,"disp");
						break;
					case 'b':
					case 'all':
						if($target->modLevel > 1) {
							foreach($this->pandas as $player){
								if($player->id != $target->id) {
									if(isset($player->room)) {
										$player->room->remove($player);
									}
									$this->rooms[$player->room->externalId]->add($player,"disp");
								}
							}
						} else {
							$target->send("260;Tu nivel de moderación no es lo suficientemente alto para realizar esta acción.  |");
						}
						break;
				}
			case 'petname':
				array_shift($list);
				list($petName,$newName) = $list;
				
				$this->checkTextIsCommand('!petname '.$petName.' '.$newName, $target);
				break;
			default:
				return false;
				//$target->room->send("78;{$target->id};$original|");
		}
	}
	
	function checkTextIsCommand($message, $target)
	{
		if(strpos($message,' ') !== FALSE){
			$parts = explode(' ',$message);
		}else{
			$message = $message.' ';
			$parts = explode(' ',$message);
		}
		
		$userCount = count($this->pandas);
		
		switch($parts[0]){
			case '/speed':
			case '/ae':
			case '/sf':
			case '/st':
			case '/sg':
			case '/sc':
			case '/nf':
			case '/nc':
			case '/ng':
			case '/alias':
			case '/alpha':
			case '/we':
			case '/she':
			case '/he':
				if($this->canModifyAtt($target)) {
					switch($parts[0]) {
						case '/speed':
							if($target->modLevel > 1 || $target->socialLevel > 10) {
								array_shift($parts);
								list($speedLevel) = $parts;
								$speedLevel = strtolower($speedLevel);
								
								switch($speedLevel) {
									case 'a':
										$speedLevel = 100;
										break;
									case 'b':
										$speedLevel = 300;
										break;
									case 'c':
										$speedLevel = 500;
										break;
									case 'd':
										$speedLevel = 1000;
										break;
									case 'e':
										$speedLevel = 1300;
										break;
									case 'f':
										$speedLevel = 1500;
										break;
									case 'g':
										$speedLevel = 2000;
										break;
									case 'h':
										$speedLevel = 2500;
										break;
									case 'i':
										$speedLevel = 3000;
										break;
									default:
										$target->send("260;Nivel de jump-speed inválido, los niveles actualmente disponibles son desde el <font color='#00FF00'>a</font> hasta la <font color='#00FF00'>i</font>.  |");
										return true;
								}
								
								$target->speed = $speedLevel;
								$target->updateAttributes('pandaJumpSpeed',$speedLevel);
							} else {
								$target->send("260;Necesitas ser nivel 11 para realizar esta acción.  |");
							}
							return true;
						case '/alpha':
							if($target->modLevel > 1 || $target->socialLevel > 18) {
								array_shift($parts);
								list($alphaLevel) = $parts;
								$alphaLevel = strtolower($alphaLevel);
								
								switch($alphaLevel) {
									case 'a':
										$alphaLevel = 1;
										break;
									case 'b':
										$alphaLevel = 0.8;
										break;
									case 'c':
										$alphaLevel = 0.6;
										break;
									case 'd':
										$alphaLevel = 0.4;
										break;
									case 'e':
										$alphaLevel = 0.1;
										break;
									default:
										$target->send("260;Nivel de alpha inválido, los niveles actualmente disponibles son desde el <font color='#00FF00'>a</font> hasta el <font color='#00FF00'>e</font>.  |");
										return true;
								}
								
								$target->attributes['avatarAlpha'] = $alphaLevel;
								$target->updateAttributes('pandaAlpha',$alphaLevel);
								$target->room->restart($target);
							} else {
								$target->send("260;Necesitas ser nivel 19 para realizar esta acción.  |");
							}
							
							return true;
						case '/ae':
							if($target->modLevel > 1 || $target->socialLevel > 23) {
								if($target->attributes['appearEffect'] === 0 || $target->attributes['appearEffect'] === '0') {
									$target->attributes['appearEffect'] = 1;
									$target->updateAttributes('appearEffect',1);
									$target->room->restart($target);
									return true;
								}
								
								$target->attributes['appearEffect'] = 0;
								$target->updateAttributes('appearEffect',0);
								$target->room->restart($target);
							} else {
								$target->send("260;Necesitas ser nivel 24 para realizar esta acción.  |");
							}
							
							return true;
						case '/ng':
							if($target->modLevel > 1 || $target->socialLevel > 27) {
								array_shift($parts);
								list($color) = $parts;
								if(strpos($color,'.') !== false) {
									$color = str_replace('.','',$color);
									$target->attributes['nameGlow'] = '0x'.$color;
									$target->updateAttributes('nameGlow','0x'.$color);
									$target->room->restart($target);
								} else {
									$target->send("260;El color es inválido, inténtalo de nuevo.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 28 para realizar esta acción.  |");
							}
							return true;
						case '/nc':
							if($target->modLevel > 1 || $target->socialLevel > 33) {
								array_shift($parts);
								list($color) = $parts;
								if(strpos($color,'.') !== false) {
									$color = str_replace('.','',$color);
									$target->attributes['nameColor'] = '0x'.$color;
									$target->updateAttributes('nameColor','0x'.$color);
									$target->room->restart($target);
								} else {
									$target->send("260;El color es inválido, inténtalo de nuevo.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 34 para realizar esta acción.  |");
							}
							return true;
						case '/we':
							if($target->modLevel > 1 || $target->socialLevel > 35) {
								if($target->attributes['walkEffect'] === 0 || $target->attributes['walkEffect'] === '0')
								{
									$target->attributes['walkEffect'] = 1;
									$target->updateAttributes('walkEffect',1);
									$target->room->restart($target);
									return true;
								}
								$target->attributes['walkEffect'] = 0;
								$target->updateAttributes('walkEffect',0);
								$target->room->restart($target);
							} else {
								$target->send("260;Necesitas ser nivel 36 para realizar esta acción.  |");
							}
							return true;
						case '/nf':
							return $target->send("260;Esta opción se queda descontinuada por el momento.  |");
							array_shift($parts);
							list($font) = $parts;
							if(strpos('Hiruko',$font) !== false || strpos('HirukoFont',$font) !== false || strpos('Verdana',$font) !== false || strpos('VerdanaFont',$font) !== false) {
								$target->attributes['nameFont'] = $font;
								$target->updateAttributes('nameFont',$font);
								$target->room->restart($target);
							} else {
								$target->send("260;La fuente es inválida, inténtalo de nuevo.  |");
							}
							return true;
						case '/he':
						case '/she':
							if($target->modLevel > 1 || $target->socialLevel > 36) {
								array_shift($parts);
								list($txt) = $parts;
								$headEffect = $this->getHeadEffect($txt,$target);
								
								if($headEffect === null) {
									$target->send("260;El nombre del efecto no está definido.  |");
									return true;
								}
								
								$target->attributes['headEffect'] = $headEffect;
								$target->updateAttributes('headEffect',$headEffect);
								$target->room->restart($target);
							} else {
								$target->send("260;Necesitas ser nivel 37 para realizar esta acción.  |");
							}
							return true;
						case '/st':
							if($target->modLevel > 1 || $target->socialLevel > 40) {
								array_shift($parts);
								$status = implode(' ',$parts);
								if(preg_match("/^[a-zA-Z0-9@_ !¡?¿]+$/",$status) && strlen($status) >= 4 && strlen($status) <= 20) {
									$target->attributes['statusText'] = $status;
									$target->updateAttributes('statusText',$status);
									$target->room->restart($target);
								} else {
									$target->send("260;El estado contiene carácteres inválidos o tiene menos de 4 o más de 20 palabras.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 41 para realizar esta acción.  |");
							}
							return true;
						case '/sc':
							if($target->modLevel > 1 || $target->socialLevel > 42) {
								array_shift($parts);
								list($color) = $parts;
								if(strpos($color,'.') !== false) {
									$color = str_replace('.','',$color);
									$target->attributes['statusColor'] = '0x'.$color;
									$target->updateAttributes('statusColor','0x'.$color);
									$target->room->restart($target);
								} else {
									$target->send("260;El color es inválido, inténtalo de nuevo.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 43 para realizar esta acción.  |");
							}
							return true;
						case '/alias':
							if($target->modLevel > 1 || $target->socialLevel > 59) {
								array_shift($parts);
								$name = implode(' ',$parts);
								if(preg_match("/^[a-zA-Z0-9@_ !¡?¿.,]+$/",$name) && strlen($name) >= 4 && strlen($name) <= 20) {
									$target->attributes['nameAlias'] = ucfirst($name);
									$target->updateAttributes('nameAlias',ucfirst($name));
									$target->room->restart($target);
								} else {
									$target->send("260;Nombre demasiado largo o el mismo contiene carácteres no aceptados.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 60 para realizar esta acción.  |");
							}
							return true;
						case '/sg':
							if($target->modLevel > 1 || $target->socialLevel > 49) {
								array_shift($parts);
								list($color) = $parts;
								$srr = explode(':',$target->attributes);
								if(strpos($color,'.') !== false) {
									$color = str_replace('.','',$color);
									$target->attributes['statusGlow'] = '0x'.$color;
									$target->updateAttributes('statusGlow','0x'.$color);
									$target->room->restart($target);
								} elseif($color === -5 || $color === '-5') {
									$target->attributes['statusGlow'] = -5;
									$target->updateAttributes('statusGlow',-5);
									$target->room->restart($target);
								} else {
									$target->send("260;El color es inválido, inténtalo de nuevo.  |");
								}
							} else {
								$target->send("260;Necesitas ser nivel 50 para realizar esta acción.  |");
							}
							return true;
						case '/sf':
							return $target->send("260;Esta opción se queda descontinuada por el momento.  |");
							array_shift($parts);
							list($font) = $parts;
							if(strpos('Hiruko',$font) !== false || strpos('HirukoFont',$font) !== false || strpos('Verdana',$font) !== false || strpos('VerdanaFont',$font) !== false) {
								$target->attributes['statusFont'] = $font;
								$target->updateAttributes('statusFont',$font);
								$target->room->restart($target);
							} else {
								$target->send("260;El estado contiene carácteres inválidos o tiene menos de 4 o más de 20 palabras.  |");
							}
							return true;
					}
				} else {
					$target->send("260;Vuelve en 0 horas y 1 minuto para utilizar este comando.  |");
				}
				return true;
			case '/kick':
				$this->removePandaManager($target,"80;KICK_BLACKLIST_MSG|");
				return true;
			case '/online':
				$this->checkModeratorCheck(Array('online'),$target);
				return true;
			case '/petname':
				list($id,$name,$newname) = $parts;
				
				$result = $this->verifyName($newname,$target);
				
				if($result === 'SUCCESS') {
					$result2 = $target->database->changePetName($name,$newname,$target->id);
					
					if($result2 === 'FAILED') {
						$panda->send('40;1010;<font color="#FF0000">No podemos darte ese nombre, otra de tus Mascotas ya se llama así.</font>|');
					} else {
						$panda->send('40;1010;<font color="#00FF00">¡Vuelve a iniciar sesión para ver el nuevo nombre de tu mascota!</font>|');
					}
				} else {
					$target->send("260;$result  |");
				}
				
				return true;
			default:
				return false;
		}
	}
	
	function getHeadEffect($id, $panda)
	{
		$top = strtolower($id);
		
		switch($top)
		{
			case 'stars':
				return 'stars$b$80$17';
			case 'thunder':
				return 'thunder$b$80$17';
			case 'hearts':
				return 'heartIcon$b$80$17';
			case 'idea':
				return 'bulb$b$80$17';
			case 'music':
				return 'music$b$80$17';
			case 'interrogacion':
				return 'questionmark$b$80$17';
			case 'exclamacion':
				return 'exclamationmark$b$80$17';
			case 'animalshop':
				if($panda->moderator) {
					return 'cityanimalshop$b$80$17';
				}
				return null;
			default:
				return null;
		}
	}
	
	function verifyName($name, $user)
	{
		if($this->filter($name,$user) === 'none') {
			if(preg_match("/^[a-zA-Z0-9.,]+$/",$name)) {
				if(strlen($name) <= 13) {
					return 'SUCCESS';
				}
				return 'No podemos darte ese nombre, el nombre debe un número MENOR o IGUAL a 13 letras.';
			}
			return 'No podemos darte ese nombre, contiene carácteres que no podemos aceptar.';
		} else {
			return 'No podemos darte ese nombre, contiene palabras inapropiadas.';
		}
	}
	
	function getBanInfo($id) {
		return $this->databaseManager->original->checkBanInfo($id);
	}
	
	function checkExistence($arg) {
		return $this->databaseManager->original->checkExistence(is_numeric($arg),$arg);
	}
	
	function handleECardMessages($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$target = $this->pandasByPlayerId[Packet::$Duo[0]];
		$ecard  = Packet::$Duo[1];
		$name   = $panda->username;
		
		if($target === NULL) {
			return true;
		}
		
		return $target->send("42;{$panda->id};$ecard;$name|");
	}
	
	function canModifyAtt($panda)
	{
		$timestamp = floor(microtime(true) * 1000);
		
		if(time() - $panda->lastAttCommand > 60 || !isset($panda->lastAttCommand)) {
			$panda->lastAttCommand = time();
			return true;
		} else {
			return false;
		}
	}
	
	function deleteWord($user,$id) {
		$r = $this->databaseManager->original->deleteFilterWord($user,$id);
		if($r[0] == 3) {
			return [true,$r[1],$r[2]];
		}
		
		return [false,$r[0]];
	}
	
	function getFilteredWords($user) {
		$r = $this->databaseManager->original->getWords($user);
		if($r[0] == 3) {
			return [true,$r[1],$r[0]];
		}
		
		return [false,$r[0]];
	}
	
	function addGramaticalWord($message, $gramatic_id, $user) {
		$r = $this->databaseManager->original->addWordFilter($message,$gramatic_id,$user);
		if($r[0] == 3) {
			return [true,$r[1],$r[0]];
		}
		
		return [false,$r[0]];
	}
	
	function loadFilters() {
		if(!$this->filtersLoaded) {
			
			$_a = $this->databaseManager->original->getWordFilter(2);
			$_b = $this->databaseManager->original->getWordFilter(1);
			$_c = $this->databaseManager->original->getWordFilter(3);

			$this->blackListedES = [];
			self::$allowPages = [];
			self::$suspiciousWords = [];
			
			foreach($_a as $_word) {
				array_push($this->blackListedES,$_word['badword']);
			}
			
			foreach($_b as $_word) {
				$a = explode('|',$_word['badword']);
				if(!isset($a[1])) {
					$a[1] = "";
				}
				self::$suspiciousWords[$a[0]] = $a[1];
			}
			
			foreach($_c as $_word) {
				array_push(self::$allowPages,$_word['badword']);
			}
			
			$this->filtersLoaded = true;
		}
	}
	
	function filter($worssd, $panda = null)
	{
		$list = $this->blackListedES;
		
		$worssd = $this->wordfy($worssd);
		
		foreach($list as $word => $aka) {
			$_a = false;
			if(strpos($aka,'|') !== false) $_a = true;
			if(strpos($worssd,$aka) !== false) {
				if($_a) return 'kick';
				if($panda !== null) {
					if($panda->msgWarn < 11) {
						return 'warn';
					} else {
						return 'kick';
					}
				}
				return 'warn';
			}
		}
		
		return 'none';
	}
	
	function wordfy($worssd)
	{
		$none = strtolower($worssd);
		$arr1 = Array('7',' ','4','1','5','3','0','v','V','b','B');
		$arr2 = Array('t','','a','i','s','e','o','u','u','p','p');
		$arr3 = Array('a','e','i','o','u');
		$preg = Array('/[á]/'=>'[a]?','/[é]/'=>'[e]?','/[í]/'=>'[i]?','/[ó]/'=>'[o]?','/[ú]/'=>'[u]?','/[ý]/'=>'[y]?');
		
		foreach($arr1 as $in => $volwe) {
			if(strpos($none,$volwe) !== false) {
				$none = str_replace($volwe,$arr2[$in],$none);
			}
		}
		
		foreach($preg as $in => $va) {
			$none = preg_replace($in,$va,$none);
		}
		
		foreach($arr3 as $a => $b) {
			if(strpos($none,$b.$b) !== false) {
				$none = str_replace($b.$b,$b,$none);
				if(strpos($none,$b.$b) !== false) {
					$none = str_replace($b.$b,$b,$none);
				}
			}
			if(strpos($none,$b.$b.$b) !== false) {
				$none = str_replace($b.$b.$b,$b,$none);
				if(strpos($none,$b.$b.$b) !== false) {
					$none = str_replace($b.$b.$b,$b,$none);
				}
			}
			if(strpos($none,$b.$b.$b.$b) !== false) {
				$none = str_replace($b.$b.$b.$b,$b,$none);
				if(strpos($none,$b.$b.$b.$b) !== false) {
					$none = str_replace($b.$b.$b.$b,$b,$none);
				}
			}
		}
		
		$none = preg_replace("/[^a-zA-Z]+/","",$none);
		return $none;
	}
	
}