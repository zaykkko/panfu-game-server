<?php

namespace PComponent\Panfu\Handlers\Commands;

use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;

trait Moderation {

	function handlePlayerUbication($socket) {
		$panda = $this->pandas[(int) $socket];
		
		if($panda->moderator){
			$targetPlayer = $this->getPlayerById(Packet::$Duo[0]);
			
			if($targetPlayer === null){
				$panda->send("211;0|");
			}else{
				$panda->send("211;{$panda->room->externalId}|");
			}
		} else {
			return $this->banHammer(1010,$panda,strtotime("+24 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function handleLockPlayer($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		if($panda->moderator) return $panda->send("260;Utiliza el comando /kick para expulsar o /warn para advertir a un jugador.  |");
		
		return $this->banHammer(1010,$panda,strtotime("+5 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
	}
	
	function handleSendReport($socket)
	{
		$panda = $this->pandas[(int) $socket];
		
		$user = $this->pandasById[Packet::$Duo[0]];
		$reason = Packet::$Duo[1];
		
		if($user === NULL || Packet::$Duo[0] === $panda->id) {
			return true;
		}
		
		if(!isset($panda->lastReport) || $panda->lastReport > floor(microtime(true) * 1000)) {
			$panda->lastReport = floor(microtime(true) * 1000) + 180000;
			foreach($this->pandasByIdModerators as $user) {
				$user->send("260;{$panda->username} ha reportado a {$user->username} por: $reason.  |");
			}
		} else {
			$panda->send("Vuelve en 3 minutos para reportar de nuevo a más jugadores.");
		}
	}
	
	function kickPlayer($panda, $name) {
		if(!$panda->moderator) return $this->banHammer(1010,$panda,strtotime("+24 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
	}
	
	function handleSendBlock($socket)
	{
		$panda = $this->pandas[(int) $socket];
		$id = Packet::$Duo[0];
		
		if($panda->moderator){
			$targetPlayer = $this->getPlayerById($id);
			if($targetPlayer === null){
				return null;
			}else{
				$targetPlayer->send("81|");
			}
		}else{
			return $this->banHammer(1010,$panda,strtotime("+24 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:" . Packet::$RawData);
		}
	}
	
	function moderatorMsg($arguments) {
		foreach($this->pandasByIdModerators as $a) {
			$a->send("40;1010;$arguments|");
		}
		
		try {
			return $this->trackData("discordhook",$arguments,"Manipulation bot");
		} catch(DataException $e) {
			Logger::Warn($e);
		}
	}
	
	function unbanPan($sender, $user) {
		return $sender->database->unbanUser($sender,$user);
	}
	
	function banHammer($sender, $target, $time, $reason = "No fue definida", $message = "default", $ad = true) {
		$sender->database->banUser($target->id,$time,$target->username,$reason,$sender,time());
		$time = (($time - strtotime("now")) / (60 * 60));
		
		if($ad) {
			foreach($this->pandasByIdModerators as $a) {
				$a->send("40;1010;El bot suspendió a {$target->username} por $time. Razón: utilización de programas o comportamiento extraño respecto al envío o recibo de paquetes (manipulación de poderes, carteles, etc)|");
			}
		}
		
		try {
			$this->trackData("discordhook","El bot suspendió a {$target->username} por $time horas. Razón: $reason.","Manipulation bot");
		} catch(DataException $e) {
			Logger::Warn($e);
		}
		
		switch($message) {
			case "default":
				$message = "¡<u><font size=\"15\">Parece que has roto las reglas de Panfu</font></u>!<br>Por el incumplimiento de las normas que crean y mantienen el \"ambiente seguro\" en todo Panfu, teniendo en cuenta que fuiste advertido reiteradas veces, se te ha bloqueado el acceso al juego por <font color=\"#FEDC3D\">$time hora(s)</font>. <br>Al cesar el bloqueo, se te retornará el completo acceso al juego.<br>¡Ten más cuidado la próxima vez!";
				break;
		}
		
		return $this->removePandaManager($target,"260;$message  |");
	}
	
	function onReportUser($obj) {
		$panda = $this->pandasByPlayerId[$obj->t];
		
		if($panda != NULL) {
			
			$panda->send('260;Detectamos una actividad inusual en tu cuenta, se les ha enviado un reporte a los moderadores activos para que revisen tu situación. Como resultado del incidente: fuiste <font color="#FF0000">expulsado</font> temporalmente del servidor.  |');
			
			$this->moderatorMsg("{$panda->username} fue reportado por nuestro sistema debido: {$obj->r} ~ Según los registros, los argumentos dados fueron: {$obj->a}. ~ Servicio: {$obj->s}");
				
			return $this->banHammer(1010,$panda,strtotime("+24 hours"),"ATTEMPTED_GAME_MANIPULATION|#|ARGS:{$panda->username} fue reportado por nuestro sistema debido: {$obj->r} ~ Según los registros, los argumentos dados fueron: {$obj->a}. ~ Servicio: {$obj->s}","default",false);
		}
	}
	
}

?>