<?php

namespace PComponent\Panfu\Handlers\Commands;
use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;

trait Connection {
	
	function handleHeartBeat($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$panda->lastPing = time();
		
		return $panda->send("1050;pongy|");
	}
	
	function handleUserSalt($socket)
	{
		$panda = $this->pandas[(int) $socket];
		if($panda->salted) return $this->banHammer(1010,$panda,strtotime("+24 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		$salt = uniqid(mt_rand(), true);	
		$seot = crypt("{$panda->id}am{$salt}k9s".rand(0,597),md5(floor(microtime(true) * 1000)));
		$userSalt = $seot.$salt;
		$panda->salt = $userSalt;
		$panda->salted = true;
		$panda->saveSalt();
		$this->updateServerCount($panda);
		$panda->loginTime = time();
		$panda->send("301;".$userSalt.";{$panda->SWID}|");
		$panda->x = rand(200,500);
		$panda->y = rand(200,370);
		if(!$panda->canFirstLog) {
			$this->rooms[$this->getOpenRoom()]->add($panda,'none');
		} else {
			$this->joinHome($panda->id,$panda);
		}
		if($this->maintenanceMode && !$panda->moderator) {
			return $this->removePandaManager($panda,"260;Error en el servidor.  |");
		}
	}
	
	function handleUserLogged($socket)
	{
		$id = Packet::$Duo[0];
		$token = Packet::$Duo[1];
		$room = Packet::$Duo[2];
		$panda = $this->pandas[(int) $socket];
		
		if(!is_numeric($id) || !is_numeric($token) || !is_numeric($room)) {
			return $this->removePandaManager($panda);
		}
		
		$this->databaseManager->add($panda);
		
		$info = $panda->database->getColumnsByToken($token,$id,Array("auth_token","verified","activated","tour_finished","display_name","loginIP","SWID","pandaJumpSpeed","pandaAlpha","nameGlow","nameColor","nameFont","walkEffect","appearEffect","headEffect","statusFont","nameAlias","statusGlow","statusColor","statusText","muted_timer","last_login","attributes","usertemplate","muted","social_level","sheriff","premium","id","username","home_locked"));
		
		if($info !== NULL && $info['auth_token'] !== NULL) {
			unset($info['auth_token']);
			$data = $info;
			if(isset($this->pandasByPlayerId[$data['id']])) { // multiple login check
				return $this->removePandaManager($this->pandasByPlayerId[$data['id']]);
			}
			$ip = $data['loginIP'];
			$panda->id = $data['id'];
			if(strpos($data['loginIP'],$panda->ipAddress) === FALSE) {
				$panda->database->updateIP($panda->id,$data['loginIP'] . ",{$panda->ipAddress}");
			}
			$this->pandasByPlayerId[$data['id']] = $panda;
			$panda->timeouted = $this->_save->get($panda->id,"timeout") === -1?false:true;
			$this->pandasByUsername[strtolower($data['username'])] = $panda;
			$duration = $panda->isBanned();
			if($duration === false) {
				if((int)$data['activated']==0) {
					return $this->removePandaManager($panda,"990;NOT_ACTIVATED|");
				}
				
				$panda->moderator = (int)$data['sheriff'] > 0?true:false;
				$panda->modLevel = (int)$data['sheriff'];
				if($panda->moderator) {
					$this->pandasByPlayerIdModerators[$panda->id] = $panda;
				}
				$panda->activeHouse = (int) $data['home_locked'] === 0?false:true;
				$panda->username = $data['display_name'];
				if($this->_save->get($panda->id,"reverseban") != -1) {
					$timing = $this->_save->get($panda->id,"reverseban");
					if(strtotime("now") < $timing) {
						$panda->reverseBan = true;
					} else {
						$this->_save->_delete($panda->id,"reverseban");
					}
				}
				if((int)$data['verified'] === 0) {
					if($this->filter($data['display_name'],null) === 'warn') {
						$panda->database->updateDisplayName("Panda{$panda->id}",$panda->id);
						$panda->verificar();
						return $this->removePandaManager($panda);
					}
					$panda->verificar();
				}
				$block = (int)$data['muted'] === 1?true:false;
				$panda->muted = $block;
				$panda->identified = true;
				$panda->session = $token;
				$panda->btiming = $data['muted_timer'];
				$panda->SWID = $data['SWID'];
				$panda->speed = $data['pandaJumpSpeed'];
				$panda->socialLevel = (int)$data['social_level'];
				$panda->isPremium = $data['premium'] > 0?true:false;
				$days = $this->getCurrentDayName();
				
				if($days === 'Sábado' || $days === 'Viernes') {
					$panda->isPremium = true;
					$this->premiumDay = true;
				} else {
					$this->premiumDay = false;
				}
				
				$panda->premiumNumber = $data['premium'];
				$panda->loginHash = $data['usertemplate'];
				$panda->attributes = Array(
					'statusText' => $data['statusText'],
					'statusColor' => $data['statusColor'],
					'statusFont' => $data['statusFont'],
					'statusGlow' => $data['statusGlow'],
					'nameAlias' => ucfirst($data['nameAlias']),
					'nameFont' => $data['nameFont'],
					'nameColor' => $data['nameColor'],
					'nameGlow' => $data['nameGlow'],
					'appearEffect' => $data['appearEffect'],
					'headEffect' => $data['headEffect'],
					'walkEffect' => $data['walkEffect'],
					'avatarAlpha' => $data['pandaAlpha']
				);
				$panda->attributes['statusGlow'] = $data['statusGlow'] === NULL?'INDEFINIDO':$data['statusGlow'];
				$panda->attributes['nameAlias'] = $data['nameAlias'] === NULL?$panda->username:ucfirst($data['nameAlias']);
				$panda->attributes['nameGlow'] = $data['nameGlow'] === NULL?'INDEFINIDO':$data['nameGlow'];
				$panda->attributes['headEffect'] = $data['headEffect'] === NULL?'INDEFINIDO':$data['headEffect'];
				
				$panda->randKey = strtoupper($this->getRandKey(9));
				$panda->delimitions = rand(0,10);
				$panda->getBuddies();
				
				$panda->send("0;OK;ES|45;1;{$panda->randKey}|");
				
				$panda->verifiedTimes = 2;
				if($block === true) {
					if($data['muted_timer'] < strtotime('now') && (int)$data['muted_timer'] != 0) {
						$panda->startUserMuted();
						$panda->muted = false;
						$panda->updateMute(false);
					}					
				}
				
				$this->updateServerCount($panda);
				
				if((int)$data['tour_finished'] === 0) {
					$panda->canFirstLog = true;
				}
				
				$panda->updateLocalInfo($this->serverId,time(),$panda->id);
				$panda->database->changeLoginInfoAA($panda->id,$this->getNewSession(round(rand(9,99) * rand(8,20)) + round(strlen($panda->username) - 1 * rand(5,78))),null,$panda->loginHash);
			} else {
				return $this->removePandaManager($panda,"990;USER_BANNED|");
			}
		} else {
			return $this->removePandaManager($panda);
		}
	}
	
	function getRandKey($max = 5)
	{
		$keys = Array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $result = Array();
		
        for($i=0;$i<$max;$i++) {
            $num = rand(0,count($keys)-1);
			array_push($result,$keys[$num]);
        }
		
		return implode('',$result);
	}
	
	function updateServerCount($target)
	{
		$users = floor(count($this->pandas) * 2);
		
		$target->database->setServerCount($users,$target->id,$this->serverId);
	}
	
	function handleLogout($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		return $this->removePandaManager($panda,"3|");
	}
	
}

?>