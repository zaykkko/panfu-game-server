<?php

namespace PComponent\Panfu\Handlers\Commands;
use PComponent\Logging\Logger;
use PComponent\DatabaseManager;
use PComponent\Panfu\Packets\Packet;

trait AvatarCommands {
	private $roomESSnippets = Array('FilterBot','Isla de piratas','Montaña','Nave espacial','Cueva de seguridad','Northpole','Colina panda','cuarto de los niños','Bar Pirata','Interior del avión','Avión','Establo ponis ','Granero','Establo de ponis','Circuito de carreras','Pista de carreras','Sala de máquinas','Santas Workshop','Bosque Encantado','Acuario','Fondo del mar','Restos del naufragio','Cielo','Nave espacial','Campo de deportes','Pabellón deportivo','Terraza','Tool Shed','Torre del castillo','Ciudad ciri siti city','Árbol','Túnel','Escuela submarina','Fondo del mar','Volcán','Cascada','Cueva detrás de la cascada','Boca de Billy','Templo antiguo','Tienda de animales','Ballroom','Granero de Pokopets','Sótano secreto','Playa','Salón de belleza','San Franpanfu','Entrada de Bitterland','Bosque de Bitterland','Playa caribeña','Castillo','Cueva','Probador','Chez Bruno','Clase','Habitación de colores','Patio del castillo','Pista de baile','Cámara oscura','Disco','Ascensor','Evron\'s castle','Exit ','Cueva de juego','Vestíbulo','Tienda de regalos','Viejo puerto','Hospital','Heladería','San Franpanfu','Parte izquierda de la isla','Parte derecha de la isla','Selva','Kiosko','Salón de los Caballeros','Krunchi','Laboratorio','Laberinto','Piscina','El Lago','Biblioteca','El mundo de Lorax','~ Lady Bot ~');
	
	function handleChangeProfileText($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if($this->canChat($this->getCurrentDayName(),date('H')) === true && !$panda->muted) {
			$id = $panda->id;
			$type = "";
			$text = Packet::$Duo[2];
			
			if($this->filter($text) === 'warn') return;
			
			switch(Packet::$Duo[1]){
				case 14:
					$type = "book";
					break;
				case 0:
					$type = "movie";
					break;
				case 1:
					$type = "color";
					break;
				case 2:
					$type = "hobby";
					break;
				case 3:
					$type = "song";
					break;
				case 4:
					$type = "band";
					break;
				case 5:
					$type = "school_subject";
					break;
				case 6:
					$type = "sport";
					break;
				case 7:
					$type = "animal";
					break;
				case 8:
					$type = "rel_status";
					break;
				case 9:
					$type = "motto";
					break;
				case 10:
					$type = "best_char";
					break;
				case 11:
					$type = "worst_char";
					break;
				case 12:
					$type = "like_most";
					break;
				case 13:
					$type = "like_least";
					break;
				default:
					return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
			}
			
			return $panda->database->updateProfileText($id,$type,$text);
		}
	}
	
	function handleAvatarAction($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$timestamp = floor(microtime(true) * 1000);
		
		if(!isset($panda->lastPlayerAction) || $timestamp > $panda->lastPlayerAction) {
			$panda->lastPlayerAction = floor(microtime(true) * 1000) + 500;
			
			switch(sizeof(Packet::$Duo)) {
				case 1:
					$panda->lastAction = "spell";
					$spell = Packet::$Duo[0];
					if($spell === 'rabbittransformationAlone'){
						$spell = "rabbittransformation";
					}
					switch($spell) {
						case 'sendFlyingCup':
						case 'flyingBottle2':
						case 'flyingHeart':
						case 'sendFlyingBottle':
							if($panda->room->externalId != 41 && !$panda->moderator) {
								if(!$panda->firstWarn) {
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
								return true;
							}
							break;
						case  'sendPancake':
							if($panda->room->externalId != 4 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
								return true;
							}
							break;
						case 'flyingPillow':
						case 'kiss':
						case 'giantTransformation':
						case 'littleBabyTransformation':
						case 'cake':
						case 'shock':
						case 'flyingHeart':
						case 'disappear':
						case 'reappear':
						case 'bubblesLong':
						case 'doSlideVolcanoAnimation':
							if(!$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
								return true;
							}
							break;
						case 'endcartransformation':
							if($panda->room->externalId != 43 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
								return true;
							}
							break;
						case 'iceCube':
							if($panda->room->externalId != 46 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
							}
							break;
						case 'teleportation':
							if($panda->socialLevel < 30 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'hole':
							if($panda->socialLevel < 35 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'dance':
							if($panda->socialLevel < 10 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'fogtransformation':
							if($panda->socialLevel < 44 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'invisible':
							if($panda->socialLevel < 37 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'spidertransformation':
							if($panda->socialLevel < 14 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'greenhaze':
							if($panda->socialLevel < 11 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'mousetransformation':
							if($panda->socialLevel < 8 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'masterOfSlime':
							if($panda->socialLevel < 33 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'masterOfIce':
							if($panda->socialLevel < 38 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'slimebomb':
							if($panda->socialLevel < 15 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'fireworks':
							if($panda->socialLevel < 5 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'greenFart':
							if($panda->socialLevel < 25 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'pikachuSpell':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 65 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'rabbitSpell':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 61 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'chickSpell':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 22 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'lightningPlayerSpell':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 18 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'masterOfSlime':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 33 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'heartFireworks':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 23 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'flowerPower':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 55 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'rainbowRay':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 28 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'roboterTransformation':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 60 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'rabbittransformation':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 34 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'tornado':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 26 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'dance2':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 48 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'dance3':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 51 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
						case 'girlsledgetransformation':
						case 'boysledgetransformation':
						case 'hummercartransformation':
						case 'girlcartransformation':
						case 'boycartransformation':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							break;
						case 'monsterFart':
							if(!$panda->isPremium && !$this->premiumDay) {
								return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
							}
							if($panda->socialLevel < 29 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
							break;
						case 'doSlideLakeAnimation':
							if($panda->room->externalId != 39 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
								}
								return true;
							}
							break;
						case 'doSlideAnimation':
						case 'doDivingAnimation':
							if($panda->room->externalId != 3 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
							}
							break;
						case 'doSlideHomeAnimation':
							if($panda->room->externalId < 1011 && !$panda->moderator){
								if(!$panda->firstWarn) { 
									$panda->send("81|");
									$panda->firstWarn = true;
									return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
								} else {
									return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
								}
							}
							break;
					}
					return $panda->room->send("50;{$panda->id};$spell|");
				case 4:
				case 5:
				case 6:
				case 7:
					switch(Packet::$Duo[0]){
						case "gameInviteAccepted":
							//50;extc;md7;gameInviteAccepted;0;1013;41;1017;false
							$_a = $this->getPlayerById((int) Packet::$Duo[2]);
							$_b = $this->getPlayerById((int) Packet::$Duo[4]);
							$_a->_rps["invites"][$_b->id] = "ok";
							$_b->_rps["invites"][$_a->id] = "ok";
							if($_a === null || $_a->room->externalId != $panda->room->externalId || !isset($_a->_rps["invites"][$_b->id]) != null || (!$_a->gaming && !$_b->gaming)) {
								unset($_b->_rps["invites"][$_a->id]);
								unset($_a->_rps["invites"][$_b->id]);
							}
							
							$this->createGameInstance("rps",$_a,$_b);
							
							$_a->send("50;{$_b->id};gameInviteAccepted;0;{$_a->id};41;{$_b->id};false|");
							$_b->send("50;{$_a->id};gameInviteAccepted;0;{$_b->id};41;{$_a->id};false|");
							break;
						case "invitedPlayerLoadGame":
							return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
						case "cancelAcceptedGameInvitation":
						case "gameInviteTargetNotAvailable":
							$_a = $this->getPlayerById((int) Packet::$Duo[2]);
							if($_a === null || $_a->room->externalId != $panda->room->externalId || !isset($_a->_rps["invites"][$panda->id])) return;
							unset($_a->_rps["invites"][$panda->id]);
							$_a->send("50;{$_a->id};gameInviteTargetNotAvailable;0;{$panda->id};41;{$panda->id};false|");
							break;
						case "gameInviteDenied":
							$_a = $this->getPlayerById((int) Packet::$Duo[2]);
							if($_a === null || $_a->room->externalId != $panda->room->externalId || !isset($_a->_rps["invites"][$panda->id])) return;
							unset($panda->_rps["invites"][$_a->id]);
							$_a->send("50;{$panda->id};gameInviteDenied;2;{$_a->id};41;{$panda->id};false|");
							break;
						case "gameInvite":
							if((int) Packet::$Duo[4] === 1010) {
								$panda->botting = true;
								$panda->gaming = true;
								return $panda->send("50;1010;gameInviteAccepted;0;{$panda->id};41;1010;false|");
							}
							
							$_a = $this->getPlayerById((int) Packet::$Duo[4]);
							if($_a === null || $_a->room->externalId != $panda->room->externalId || isset($_a->_rps["invites"][$panda->id]) || $_a->gaming) {
								return $panda->send("50;{$_a->id};gameInviteDenied;2;{$panda->id};41;{$panda->id};false|");
							}
							$_a->_rps["invites"][$panda->id] = "ok";
							
							return $_a->send("50;{$panda->id};".Packet::$Duo[0].";0;0;41;".Packet::$Duo[4].";".Packet::$Duo[5]."|");
						case "sheriffFreeze":
							if($panda->moderator){
								return $panda->room->send("113;{$panda->id};13;{Packet::$Duo[4]};sheriffFreeze;animation|");
							}
							if(!$panda->firstWarn) { 
								$panda->send("81|");
								$panda->firstWarn = true;
								return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
							}
							return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
						case "throw":
							if($panda->lastAction === "spell" || $panda->moderator) {
								$panda->lastAction = "";
								$x = Packet::$Duo[1];
								$y = Packet::$Duo[2];
								$spell = Packet::$Duo[3];
								$user = !isset(Packet::$Duo[4])?-1:Packet::$Duo[4];
								$target = $this->getPlayerById((int)$user);
								if(isset(Packet::$Duo[5])){
									if(Packet::$Duo[5] === true) return;
								}
								
								switch($spell){
									case 'flyingCup':
									case 'flyingBottle2':
									case 'flyingBottle':
										if($panda->room->externalId != 41 && !$panda->moderator){
											if(!$panda->firstWarn) { 
												$panda->send("81|");
												$panda->firstWarn = true;
												return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
											} else {
												return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
											}
										}
										break;
									case 'slimebombSprite':
										if($panda->socialLevel < 15 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
										break;
									case 'pancake':
										if($panda->room->externalId != 4 && !$panda->moderator) {
											if(!$panda->firstWarn) { 
												$panda->send("81|");
												$panda->firstWarn = true;
												return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
											} else {
												return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
											}
											return true;
										}
										break;
									case 'pikachuSpell':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 65 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
									case 'rabbitSpell':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 61 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
									case 'chickSpell':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 22 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
									case 'lightningPlayerSpell':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 18 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
									case 'rainbowRay':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 28 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
									case 'flowerPower':
										if(!$panda->isPremium && !$this->premiumDay) {
											return $panda->send("260;¡Debes hacerte premium para realizar esta acción!  |");
										}
										if($panda->socialLevel < 55 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
										break;
									case 'hole':
										if($panda->socialLevel < 35 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
										break;
									case 'teleportation':
										if($panda->socialLevel < 30 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
										break;
									case 'icecubeSpell':
										if($panda->socialLevel < 20 && $panda->modLevel < 2 && !$this->premiumDay) return $panda->send("260;Tu nivel es insuficiente para realizar esta acción.   |");
										break;
									case 'flyingPillow':
									case 'cake':
									case 'kiss':
									case 'stonedSpell':
									case 'bombedSpell':
									case 'tumbleweedSpell':
									case 'flyingHeart':
									case 'pinkSlimebombSprite':
									case 'blueSlimebombSprite':
										if(!$panda->moderator) {
											if(!$panda->firstWarn) { 
												$panda->send("81|");
												$panda->firstWarn = true;
												return $this->moderatorMsg("[{$panda->username}] -> Intento de manipulación: utilización de un comando '50' **no autorizado**. Resultado: $spell. [1/2] [USUARIO ADVERTIDO]");
											}
											return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
										}
										break;
								}
								
								if((int) $user === 1010 && $panda->modLevel < 3) {
									return $panda->room->send("50;1010;throw;{$panda->x};{$panda->y};$spell;{$panda->id};false|");
								} else if($user != 1010 && $panda->modLevel > 2 && rand(0,5) === 4) {
									return $panda->room->send("50;1010;throw;{$panda->x};{$panda->y};$spell;{$panda->id};false|40;1010;#FF0000 NO.|");
							    } else {
									return $panda->room->send("50;{$panda->id};throw;$x;$y;$spell;$user;false|");
								}
								
							} elseif($panda->firstWarn) {
								return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
							}
								
							$panda->firstWarn = true;
							return $panda->send("81|");
						case "doDivingAnimation":
						case "doSlideAnimation":
						case "doSlideLakeAnimation":
						case "doSlideHomeAnimation":
						case "doSlideVolcanoAnimation":
							switch(sizeof(Packet::$Duo)){
								case 5:
									$p = Packet::$Duo[0];
									$p2 = Packet::$Duo[1];
									$p3 = Packet::$Duo[2];
									$p4 = Packet::$Duo[3];
									$p5 = Packet::$Duo[4];
									
									$panda->room->send("50;{$panda->id};$p;$p2;$p3;$p4;$p5|");
									break;
								case 6:
									$p = Packet::$Duo[0];
									$p2 = Packet::$Duo[1];
									$p3 = Packet::$Duo[2];
									$p4 = Packet::$Duo[3];
									$p5 = Packet::$Duo[4];
									$p6 = Packet::$Duo[5];
									
									$panda->room->send("50;{$panda->id};$p;$p2;$p3;$p4;$p5;$p6|");
									break;
								default:
									if($panda->firstWarn){
										return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
									}else{
										$panda->send("81|");
										$panda->firstWarn = true;
									}
									return true;
							}
							break;
						default:
							if($panda->firstWarn){
								return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
							}else{
								$panda->send("81|");
								$panda->firstWarn = true;
							}
							return true;
					}
					break;
				default:
					if($panda->firstWarn){
						return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					$panda->send("81|");
					$panda->firstWarn = true;
					return true;
			}
		}
	}
	
	function handlePacketTester($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if(!$panda->moderator) {
			return $this->removePandaManager($panda);
		}
		
		return $panda->room->send(implode(";",Packet::$Duo) . "|");
	}
	
	function summonPlayer($args = null) {
		if($args === null) return;
		$_sock = $args[0];
		$_rawd = explode(" ",$args[1]);
		$x = 0;
		$y = 0;
		$id = 0;
		$name2 = null;
		$transfo = "";
		$direction = 1;
		$pokopet = "";
		$sheriff = 0;
		$items = "1001";
		
		if(count(explode(",",$_sock->generatedIds)) >= 100) return $_sock->send("260;Al parecer generaste 100 o más usuarios, por favor cambia de sala para eliminarlos.  |");
		//summon ~ ~ ~ Aweonao {"pokopet":8,"direction":3,"items":"1002,104127,104128"}
		foreach($_rawd as $name => $value) {
			switch($name) {
				case 0:
					$x = $value == '~'?$_sock->x:(!is_numeric($value)?345:$value);
					break;
				case 1:
					$y = $value == '~'?$_sock->y:(!is_numeric($value)?345:$value);
					break;
				case 2:
					$id = $value == '~'?($_sock->id+rand(10000,90000)):(!is_numeric($value)?rand(30000,80000):$value);
					break;
				case 3:
					$name2 = $value == '~'?("Panda$id"):$value;
					break;
				case 4:
					if(strrpos($value,'{') !== false && strrpos($value,'}') !== false && strrpos($value,'\',') === false && strrpos($value,':\'') === false) {
						$_data = json_decode($value,true);
						
						foreach($_data as $type => $def) {
							switch(strtolower($type)) { 
								case "pokopet":
								case "pokopetid":
								case "petid":
									$pokopet = is_numeric($def)?$def:$pokopet;
									break;
								case "direction":
								case "dir":
								case "direc":
									$direction = is_numeric($def)?$def:$direction;
									break;
								case "transformation":
								case "tranfo":
									$transfo = isset($def)?$def:$transfo;
									break;
								case "sheriff":
								case "mod":
									$sheriff = is_bool($def)?1:0;
									break;
								case "items":
								case "itemlist":
									$items = $def;
									break;
							}
						}
					} else return $_sock("260;Comando inválido, cambia los ' por \".  |");
					break;
			}
		}
		//113;-2;10;243;221;;5;false;7;1;Zayko_,100346,104069,100340,102853,102852,102850
		$_sock->generatedIds = "{$_sock->generatedIds},$id";
		return $_sock->room->send("30;$id;{$_sock->room->externalId};$x;$y;$name;$direction|577;$id;{$_sock->room->externalId};none;;0x00FF00;HirukoFont;0xC66700;$name2;HirukoFont;INDEFINIDO;0xFF0000;0;INDEFINIDO;0;1;delimited=false&&moderator=false&&bannedBefore=false&&hash=-1;false|113;$id;10;$x;$y;$transfo;$direction;false;$pokopet;$sheriff;".ucfirst($name2).",$items|");
	}
	
	function getSummon($sock, $arg) {
		if($arg === null) return null;
		$data = explode(",",$sock->generatedIds);
		
		if($arg < 100) {
			
			return isset($data[$arg])?$data[$arg]:null;
		}
		
		foreach($data as $i => $v) {
			if($v == $arg) return $v;
		}
		
		return null;
	}
	
	function moveSummoner($args = null) {
		$_sock = $args[0];
		$_rawd = explode(" ",$args[1]);
		$id = 0;
		$x = 345;
		$y = 345;
		$speed = 1000;
		$type = 3;
		
		foreach($_rawd as $name => $value) {
			switch($name) {
				case 0:
					switch($value) {
						case "~":
							if(strrpos($this->generatedIds,",") !== false) return $_sock->send("260;Al parecer generaste varios jugadores, deberás de introducir la \"posición\" o \"id\" de los mismos.  |");
							$id = $this->generatedIds;
							break;
						default:
							if($this->getSummon($_sock,$value) == null) return $_sock->send("260;No existe...  |");
							$id = $this->getSummon($_sock,$value);
					}
					break;
				case 1:
					$x = $value === '~'?$_sock->x:(!is_numeric($value)?345:$value);
					break;
				case 2:
					$y = $value === '~'?$_sock->y:(!is_numeric($value)?345:$value);
					break;
				case 3:
					$speed = $value === '~'?1000:(!is_numeric($value)?1000:$value);
					break;
				case 4:
					$type = $value === '~'?3:(!is_numeric($value)?0:$value);
					break;
			}
		}
		
		return $_sock->room->send("20;$id;$speed;$x;$y;$type|");
	}
	
	function handlePacketTesterMe($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->moderator) {
			return $panda->send(implode(";",Packet::$Duo) . "|");
		}
		
		return $this->removePandaManager($panda);
	}
	
	function handlePlayerToPlayer($socket) {
		$panda = $this->pandas[(int) $socket];
		$maam = Packet::$Duo;
		array_shift($maam);
		$_string = implode(';',$maam);
		$_sendAll = false;
		
		if(Packet::$Duo[0] === "-2,".$panda->id) {
			$_sendAll = true;
		} 
		
		if((int) Packet::$Duo[1] === 10 && !$panda->appeared) {
			if((Packet::$Duo[2] >= 0 && Packet::$Duo[2] < 772 && Packet::$Duo[3] >= 0 && Packet::$Duo[3] < 480) || ($panda->room->externalId == 99 || $panda->room->externalId == 63) || $panda->moderator) {
				$_name = strpos(Packet::$Duo[9],",") !== FALSE?explode(",",Packet::$Duo[9])[0]:Packet::$Duo[9];
				if(strtolower($_name) !== strtolower($panda->username)) {
					return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
				}
				$_string = "10;{$maam[2]};{$maam[3]};{$maam[4]};{$maam[5]};{$maam[6]};{$maam[7]};{$maam[8]}|";
				$panda->appeared = true;
				$_sendAll = false;
			} else {
				return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
			}
		} elseif((int) Packet::$Duo[1] === 18 && !$panda->moderator) {
			return $this->banHammer(1010,$panda,strtotime("+72 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		} elseif((int) Packet::$Duo[1] === 13 && !$panda->moderator) {
			if(Packet::$Duo[3] === 'disappear' || Packet::$Duo[3] === 'reappear' || Packet::$Duo[3] === 'sheriffFreeze') {
				return $this->removePandaManager($panda,"260;Se ha detectado una actividad inusual, por esta razón decidimos <font color='#FF0000'>expulsarte</font> del servidor.<br>Tranquilo, podrás volver a ingresar.   |");
			}
		} elseif((int) Packet::$Duo[1] === 14 && !$panda->moderator) {
			switch(Packet::$Duo[2]) {
				case 'Writing':
					if($panda->muted) return;
					break;
				case 'Offline':
				case 'Moodup':
					return;
				case 'Levelup':
					if(!is_numeric(Packet::$Duo[3])) {
						return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					if((int) Packet::$Duo[3] <= 66 && (int) Packet::$Duo[3] > 1) {
						if($panda->socialLevel + 1 === (int)Packet::$Duo[3]) {
							$panda->socialLevel = (int)Packet::$Duo[3];
							break;
						}
						return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					return $this->banHammer(1010,$panda,strtotime("+1 hour"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
				case 'Won':
					if(!is_numeric(Packet::$Duo[3])) {
						return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
					}
					if((int) Packet::$Duo[3] < 99999) {
						break;
					}
					return $this->banHammer(1010,$panda,strtotime("+2 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
			}
		}
		
		if($_sendAll) {
			
			return $panda->room->send("113;{$panda->id};$_string|");
		}
		
		return $panda->room->send("113;{$panda->id};$_string|",$panda->id);
	}
	
}

?>