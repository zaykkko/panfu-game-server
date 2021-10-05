<?php

namespace PComponent;

use PComponent\Logging\Logger;

class Database extends \PDO {

	private $configFile = "Database.xml";
	
	function __construct() {
		try {
			parent::__construct("mysql:dbname=panfu;host=localhost;charset=utf8","PepitoPito","conchatuvieja");
			parent::setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			parent::setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci' ");
		} catch(\PDOException $pdoException) {
			Logger::Fatal($pdoException->getMessage());
		}
	}
	
	function updateIP($a, $b) {
		try{
			$object = $this->prepare("UPDATE `users` SET `loginIP` = :am WHERE `id` = :id");
			$object->bindParam(":am",$b,\PDO::PARAM_INT);
			$object->bindParam(":id",$a,\PDO::PARAM_INT);
			$object->execute();
			
			$object->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function verificado($a) {
		try{
			$object = $this->prepare("UPDATE `users` SET `verified` = 1 WHERE `id` = ?");
			$object->bindParam(1,$a,\PDO::PARAM_INT);
			$object->execute();
			
			$object->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateServer($a, $b) {
		try{
			$object = $this->prepare("UPDATE `servers` SET `playercount` = :am WHERE `id` = :id");
			$object->bindParam(":am",$a,\PDO::PARAM_INT);
			$object->bindParam(":id",$b,\PDO::PARAM_INT);
			$object->execute();
			
			$object->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function saveMessage($id, $rid, $sid, $name, $msg, $date, $timestamp) {
		try{
			$object = $this->prepare("INSERT INTO `chatlog` (message,id,name,timestamps,datetimes,room,server) VALUES (:a,:b,:c,:d,:e,:f,:g)");
			$object->bindValue(":a",$msg);
			$object->bindValue(":b",$id);
			$object->bindValue(":c",$name);
			$object->bindValue(":d",$timestamp);
			$object->bindValue(":e",$date);
			$object->bindValue(":f",$rid);
			$object->bindValue(":g",$sid);
			$object->execute();
			
			$object->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updatePremium($a, $b) {
		try{
			$object = $this->prepare("UPDATE `users` SET `premium` = ? WHERE `id` = ?");
			$object->bindParam(1,$b);
			$object->bindParam(2,$a);
			$object->execute();
			
			$object->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function checkBanInfo($id) {
		try{
			$object = $this->prepare("SELECT `moderator`,`notes`,`duration` FROM `bans` WHERE `player` = ? LIMIT 1");
			$object->bindParam(1,$id,\PDO::PARAM_INT);
			$object->execute();
			
			if($object->rowCount() > 0) {
				$info = $object->fetch(\PDO::FETCH_ASSOC);
				$time = (((int)$info['duration'] - strtotime("now")) / (60 * 60));
				$_catch = "Razón: {$info['notes']}, Moderador: ".(((int)$info['moderator']==1010)?"1010 (bot)":$info['moderator'])." [para más información del mod escribe: 'open {$info['moderator']}'], Expiración: $time horas.";
			} else {
				$_catch = "El usuario no está suspendido o su respectiva suspensión ya ha expirado.";
			}
			
			$object->closeCursor();
			
			return $_catch;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function checkExistence($number, $arg) {
		try{
			$exist = null;
			
			if($number) {
				$object = $this->prepare("SELECT `premium`,`id` FROM `users` WHERE `id` = ? LIMIT 1");
				$object->bindParam(1,$arg,\PDO::PARAM_INT);
				$object->execute();
				
				if($object->rowCount() > 0) $exist = (int) $object->fetch(\PDO::FETCH_ASSOC)['id'];
			} else {
				$object = $this->prepare("SELECT `premium`,`id` FROM `users` WHERE LOWER(`username`) = LOWER(?) LIMIT 1");
				$object->bindParam(1,$arg,\PDO::PARAM_STR);
				$object->execute();
				
				if($object->rowCount() > 0) $exist = (int) $object->fetch(\PDO::FETCH_ASSOC)['id'];
			}
			
			$object->closeCursor();
			
			return $exist;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getUserByName($name) {
		try{
			$object = $this->prepare("SELECT `username`,`id`,`premium`,`sheriff`,`coins`,`SWID` FROM `users` WHERE `username` = :str LIMIT 1");
			$object->bindParam(":str",$name,\PDO::PARAM_STR);
			$object->execute();
			if($object->rowCount() > 0) {
				$info = $object->fetch(\PDO::FETCH_ASSOC);
				$object->closeCursor();
				return Array((int)$info['id'],$info['username'],((int)$info['premium']>0?"Sí":"No"),0,$info['SWID'],((int)$info['sheriff']>0?"Sí":"No"),-1,-1,time(),(int)$info['sheriff']);
			}
			
			$object->closeCursor();
			return null;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function leaderboard($type, $id, $score, $points, $winner, $name) {
		try {
			$obj = $this->prepare("SELECT `id`, `points`, `wins`, `username` FROM `leaderboard_rps` WHERE `id` = ? LIMIT 1");
			$table = 'leaderboard_' . $type;
			$obj->bindParam(1,$id,\PDO::PARAM_INT);
			$obj->execute();
			
			if($winner) {
				if($obj->rowCount() > 0) {
					$toj = $this->prepare("UPDATE `leaderboard_rps` SET `points` = `points` + ?, `wins` = `wins` + 1 WHERE `id` = ?");
					$toj->bindParam(1,$points,\PDO::PARAM_INT);
					$toj->bindParam(2,$id,\PDO::PARAM_INT);
					$toj->execute();
					
					return true;
				} else {
					$toj = $this->prepare("INSERT INTO `leaderboard_rps` (`id`,`points`,`wins`,`username`) VALUES (?,?,1,?)");
					$toj->bindParam(1,$id,\PDO::PARAM_INT);
					$toj->bindParam(2,$points,\PDO::PARAM_INT);
					$toj->bindParam(3,$name,\PDO::PARAM_STR);
					$toj->execute();
					
					return true;
				}
			} else {
				if($obj->rowCount() > 0) {
					$toj = $this->prepare("UPDATE `leaderboard_rps` SET `points` = `points` + ? WHERE `id` = ?");
					$toj->bindParam(1,$points,\PDO::PARAM_INT);
					$toj->bindParam(2,$id,\PDO::PARAM_INT);
					$toj->execute();
					
					return true;
				} else {
					$toj = $this->prepare("INSERT INTO `leaderboard_rps` (`id`,`points`,`wins`,`username`) VALUES (?,?,0,?)");
					$toj->bindParam(1,$id,\PDO::PARAM_INT);
					$toj->bindParam(2,$points,\PDO::PARAM_INT);
					$toj->bindParam(3,$name,\PDO::PARAM_STR);
					$toj->execute();
					
					return true;
				}
			}
			
			return false;
			
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function is_blocked($ip) {
		try{
			$object = $this->prepare("SELECT * FROM `blocked_ips` WHERE `user_ip` = ? LIMIT 1");
			$object->bindParam(1,$ip,\PDO::PARAM_STR);
			$object->execute();
			$_rowing = $object->rowCount();
			
			$object->closeCursor();
			
			return ($_rowing>0)?true:false;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function ip_block($ip) {
		try{
			$object = $this->prepare("INSERT INTO `blocked_ips` (`user_ip`) VALUES (?)");
			$object->bindParam(1,$ip,\PDO::PARAM_STR);
			if($object->execute()) {
				
				$object->closeCursor();
				return true;
			}
			
			$object->closeCursor();
			return false;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateSheriff($a, $b) {
		try{
			if(is_numeric($a)) {
				$object = $this->prepare("UPDATE `users` SET `sheriff` = ? WHERE `id` = ?");
				$object->bindParam(1,$b,\PDO::PARAM_INT);
				$object->bindParam(2,$a,\PDO::PARAM_INT);
			} else {
				$object = $this->prepare("UPDATE `users` SET `sheriff` = ? WHERE LOWER(`username`) = LOWER(?)");
				$object->bindParam(1,$b,\PDO::PARAM_INT);
				$object->bindParam(2,$a,\PDO::PARAM_STR);
			}
			$object->execute();
			
			$object->closeCursor();
			return null;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getUserById($pid) {
		try{
			$object = $this->prepare("SELECT `username`,`id`,`premium`,`sheriff`,`SWID` FROM `users` WHERE `id` = :int LIMIT 1");
			$object->bindParam(":int",$name,\PDO::PARAM_INT);
			$object->execute();
			if($object->rowCount() > 0) {
				$info = $object->fetch(\PDO::FETCH_ASSOC);
				$object->closeCursor();
				return Array((int)$info['id'],$info['username'],((int)$info['premium']>0?"Sí":"No"),0,$info['SWID'],((int)$info['sheriff']>0?"Sí":"No"),-1,-1,time(),(int)$info['sheriff']);
			}
			
			$object->closeCursor();
			return null;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function unbanUser($user) {
		Logger::Info("{$target->username} DESBLOQUEÓ A $user.");
		try{
			$content = $this->prepare("DELETE FROM `bans` WHERE LOWER(`player`) = LOWER(?)");
			$content->bindParam(1,$target,\PDO::PARAM_INT);
			if($content->execute()) {
				return true;
			}
			
			return false;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
		
		return false;
	}
	
	function ip_unblock($ip) {
		Logger::Info("{$target->username} DESBLOQUEÓ A $user.");
		try{
			$content = $this->prepare("DELETE FROM `blocked_ips` WHERE `user_ip` = ?");
			$content->bindParam(1,$ip,\PDO::PARAM_STR);
			if($content->execute()) {
				return true;
			}
			
			return false;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
		
		return false;
	}
	
	function banUser($target, $duration, $name, $note, $moderator, $timestamp)
	{
		Logger::Info("CUENTA SUSPENDIDA POR $duration HORA(S). RAZON: $note.");
		try{
			$content = $this->prepare("
				INSERT INTO bans (id,player,moderator,notes,duration,timestamp) VALUES (?,?,?,?,?,?);
				UPDATE users SET bans = bans + 1 WHERE id = ?;
			");
			$content->bindParam(1,$target,\PDO::PARAM_INT);
			$content->bindParam(2,$name,\PDO::PARAM_STR);
			$content->bindParam(3,$moderator,\PDO::PARAM_INT);
			$content->bindParam(4,$note,\PDO::PARAM_STR);
			$content->bindParam(5,$duration,\PDO::PARAM_INT);
			$content->bindParam(6,$timestamp,\PDO::PARAM_INT);
			$content->bindParam(7,$target,\PDO::PARAM_INT);
			$content->execute();
			
			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function addCoins($target, $amount)
	{
		try{
			$content = $this->prepare("UPDATE `users` SET `coins` = coins + ? WHERE `id` = ?");
			$content->bindParam(1,$amount,\PDO::PARAM_INT);
			$content->bindParam(2,$target,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getBuddies($id)
	{
		try{
			$content = $this->prepare("SELECT * FROM `buddies` WHERE `player_id` = :id");
			$content->bindParam(':id',$id,\PDO::PARAM_INT);
			$content->execute();
			
			$arr = $content->fetchAll();
			
			$content->closeCursor();
			
			return $arr;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateSocialLevel($target, $amount)
	{
		try{
			$content = $this->prepare("UPDATE `users` SET `social_score` = ? WHERE `id` = ?");
			$content->bindParam(1,$amount,\PDO::PARAM_INT);
			$content->bindParam(2,$target,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateUserLevel($target, $level) {
		try{
			$content = $this->prepare("UPDATE `users` SET `social_level` = ? WHERE `id` = ?");
			$content->bindParam(1,$level,\PDO::PARAM_INT);
			$content->bindParam(2,$target,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}

	function updateLevel($target)
	{
		try{
			$content = $this->prepare("UPDATE `users` SET `social_level` = social_level + 1 WHERE `id` = ?");
			$content->bindParam(1,$target,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateLocalInfo($server, $login, $id)
	{
		try {
			$content = $this->prepare("UPDATE `users`, `buddies` SET users.current_gameserver = ?, users.last_login = ?, buddies.currentgs = ? WHERE users.id = ? AND buddies.buddy_id = ?");
			$content->bindParam(1,$server,\PDO::PARAM_INT);
			$content->bindParam(2,$login,\PDO::PARAM_INT);
			$content->bindParam(3,$server,\PDO::PARAM_INT);
			$content->bindParam(4,$id,\PDO::PARAM_INT);
			$content->bindParam(5,$id,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateDisplayName($name, $id) {
		try {
			
			$content = $this->prepare("UPDATE `users` SET `display_name` = ? WHERE `id` = ?");
			
			$content->bindParam(1,$name,\PDO::PARAM_STR);
			$content->bindParam(2,$id,\PDO::PARAM_INT);
			
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}

	function addAsBuddy($t1, $t2) {
		try {
			$timestamp = time();
			
			$content = $this->prepare("INSERT INTO `buddies` (player_id,buddy_id,timestamp) VALUES (:id,:bi,:ti), (:ida,:bia,:tia)");
			
			$content->bindParam(':id',$t1,\PDO::PARAM_INT);
			$content->bindParam(':bi',$t2,\PDO::PARAM_INT);
			$content->bindParam(':ti',$timestamp,\PDO::PARAM_INT);
			
			$content->bindParam(':ida',$t2,\PDO::PARAM_INT);
			$content->bindParam(':bia',$t1,\PDO::PARAM_INT);
			$content->bindParam(':tia',$timestamp,\PDO::PARAM_INT);
			
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function changeLoginInfoAA($id, $sessionKey, $salt = "no", $hash)
	{
		try {
			$content = $this->prepare("UPDATE `users` SET `auth_token` = ?, `salt` = NULL WHERE `id` = ?");
			$content->bindParam(1,$sessionKey,\PDO::PARAM_INT);
			$content->bindParam(2,$id,\PDO::PARAM_INT);
			$content->execute();

			$content->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateProfileText($user, $column, $value) {
		try {
			switch($column){
				case 'book':
					$profileInfo = $this->prepare("UPDATE `users` SET `book` = ? WHERE `id` = ?");
					break;
				case 'movie':
					$profileInfo = $this->prepare("UPDATE `users` SET `movie` = ? WHERE `id` = ?");
					break;
				case 'color':
					$profileInfo = $this->prepare("UPDATE `users` SET `color` = ? WHERE `id` = ?");
					break;
				case 'hobby':
					$profileInfo = $this->prepare("UPDATE `users` SET `hobby` = ? WHERE `id` = ?");
					break;
				case 'song':
					$profileInfo = $this->prepare("UPDATE `users` SET `song` = ? WHERE `id` = ?");
					break;
				case 'band':
					$profileInfo = $this->prepare("UPDATE `users` SET `band` = ? WHERE `id` = ?");
					break;
				case 'school_subject':
					$profileInfo = $this->prepare("UPDATE `users` SET `school_subject` = ? WHERE `id` = ?");
					break;
				case 'sport':
					$profileInfo = $this->prepare("UPDATE `users` SET `sport` = ? WHERE `id` = ?");
					break;
				case 'animal':
					$profileInfo = $this->prepare("UPDATE `users` SET `animal` = ? WHERE `id` = ?");
					break;
				case 'rel_status':
					$profileInfo = $this->prepare("UPDATE `users` SET `rel_status` = ? WHERE `id` = ?");
					break;
				case 'motto':
					$profileInfo = $this->prepare("UPDATE `users` SET `motto` = ? WHERE `id` = ?");
					break;
				case 'best_char':
					$profileInfo = $this->prepare("UPDATE `users` SET `best_char` = ? WHERE `id` = ?");
					break;
				case 'worst_char':
					$profileInfo = $this->prepare("UPDATE `users` SET `worst_char` = ? WHERE `id` = ?");
					break;
				case 'like_most':
					$profileInfo = $this->prepare("UPDATE `users` SET `like_most` = ? WHERE `id` = ?");
					break;
				case 'like_least':
					$profileInfo = $this->prepare("UPDATE `users` SET `like_least` = ? WHERE `id` = ?");
					break;
				default:
					return;
			}
			
			$profileInfo->bindParam(1,$value,\PDO::PARAM_STR);
			$profileInfo->bindParam(2,$user,\PDO::PARAM_INT);

			$profileInfo->execute();

			$profileInfo->closeCursor();

		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}

	function setServerCount($amount, $userId = null, $serverId) {
		try {
			$ok = $this->prepare("UPDATE `servers` SET `playercount` = ? WHERE `id` = ?");
			
			$ok->bindParam(1,$amount,\PDO::PARAM_INT);
			$ok->bindParam(2,$serverId,\PDO::PARAM_INT);
			$ok->execute();
			
			$ok->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}

	function userMuted($id, $result)
	{
		try{
			
			$mute = $this->prepare("UPDATE `users` SET `muted` = :block WHERE `id` = :ID");
			
			$mute->bindValue(":block",$result);
			$mute->bindValue(":ID",$id);
			$mute->execute();
			
			$mute->closeCursor();
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function saveSalt($id, $salt)
	{
		try{
			
			$mute = $this->prepare("UPDATE `users` SET salt = :sal WHERE `id` = :ID");
			
			$mute->bindValue(":sal",$salt);
			$mute->bindValue(":ID",$id);
			$mute->execute();
			
			$mute->closeCursor();
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function changeLoginInfo($id, $session, $salt)
	{
		try{
			
			$mom = $this->prepare("UPDATE SET `auth_token` = ?, `salt` = ? WHERE `id` = ?");
			$mom->bindParam(1,$session,\PDO::PARAM_INT);
			$mom->bindValue(2,$salt,\PDO::PARAM_STR);
			$mom->bindValue(3,$id,\PDO::PARAM_INT);
			$mom->execute();
			
			$mom->closeCursor();
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function userBanned($id)
	{
		try{
			
			$mom = $this->prepare("SELECT * FROM `bans` WHERE `id` = :ID LIMIT 1");
			$mom->bindValue(":ID",$id);
			$mom->execute();
			
			if($mom->rowCount() > 0){
				$a = $mom->fetch(\PDO::FETCH_ASSOC);
				$mom->closeCursor();
				return $a['duration'];
			}
			
			$mom->closeCursor();
			return false;
			
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function updateMuteTime($time, $id)
	{
		try{
			$mom = $this->prepare("UPDATE `users` SET `muted_timer` = :time AND `totalmute` = totalmute + 1 WHERE `id` = :id");
			$mom->bindParam(":time",$time,\PDO::PARAM_INT);
			$mom->bindParam(":id",$id,\PDO::PARAM_INT);
			$mom->execute();
			$mom->closeCursor();
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function removeMuteTime($id)
	{
		try{
			$mom = $this->prepare("UPDATE `users` SET `muted_timer` = 0 WHERE `id` = :id");
			$mom->bindParam(":id",$id,\PDO::PARAM_INT);
			$mom->execute();
			
			$mom->closeCursor();
		}catch(\PDOException $pdoException){
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function changePetName($name, $newname, $owner) {
		try {
			$poko = $this->prepare("SELECT * FROM `pets` WHERE `pet_name` = :name AND `owner_id` = :id");
			$poko->bindParam(':name',$name,\PDO::PARAM_STR);
			$poko->bindParam(':id',$owner,\PDO::PARAM_INT);
			$poko->execute();
			
			if($poko->rowCount() < 2) {
				
				$pete = $this->prepare('UPDATE `pets` SET `pet_name` = :name WHERE `owner_id` = :id AND `pet_name` = :pid');
				$pete->bindParam(':name',$newname,\PDO::PARAM_STR);
				$pete->bindParam(':id',$owner,\PDO::PARAM_INT);
				$pete->bindParam(':pid',$name,\PDO::PARAM_STR);
				$pete->execute();
				
				$pete->closeCursor();
				return 'SUCCESS';
			}
			
			$pete->closeCursor();
			return 'FAILED';
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getColumnsByToken($session, $id, array $columns) {
		try {
			$columnsString = implode(', ', $columns);
			$columnsStatement = $this->prepare("SELECT $columnsString FROM `users` WHERE `auth_token` = :key AND `id` = :id");
			$columnsStatement->bindValue(":key", $session, \PDO::PARAM_INT);
			$columnsStatement->bindParam(":id", $id, \PDO::PARAM_INT);
			$columnsStatement->execute();
			
			if($columnsStatement->rowCount() > 0) {
				$pandaColumns = $columnsStatement->fetch(\PDO::FETCH_ASSOC);
			} else {
				$pandaColumns = null;
			}
			
			$columnsStatement->closeCursor();
			
			return $pandaColumns;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function deleteFilterWord($user,$id) {
		try {
			if($user == 1013) {
				$checkStmt = $this->prepare("SELECT * FROM `chatfilter` WHERE LOWER(`badword`) = LOWER(?) LIMIT 1");
				$checkStmt->bindParam(1,$id,\PDO::PARAM_STR);
				$checkStmt->execute();
				
				if($checkStmt->rowCount() > 0) {
					$info = $checkStmt->fetch(\PDO::FETCH_ASSOC);
					$checkStmt->closeCursor();
					
					$delt = $this->prepare("DELETE FROM `chatfilter` WHERE `word_id` = ?");
					$delt->bindParam(1,$info['word_id'],\PDO::PARAM_INT);
					if($delt->execute()) {
						$delt->closeCursor();
						
						return [3,$info['badword'],(int)$info['gramatic_level']];
					}
					
					return [2];
				}
				
				$checkStmt->closeCursor();
				
				return [1];
			} else {
				$checkStmt = $this->prepare("SELECT * FROM `chatfilter` WHERE `word_id` = ? AND `added_by` = ? LIMIT 1");
				$checkStmt->bindParam(1,$id,\PDO::PARAM_INT);
				$checkStmt->bindParam(2,$user,\PDO::PARAM_INT);
				$checkStmt->execute();
				
				if($checkStmt->rowCount() > 0) {
					$info = $checkStmt->fetch(PDO::FETCH_ASSOC);
					$checkStmt->closeCursor();
					
					$delt = $this->prepare("DELETE FROM `chatfilter` WHERE `word_id` = ?");
					$delt->bindParam(1,$id,\PDO::PARAM_INT);
					if($delt->execute()) {
						$delt->closeCursor();
						
						return [3,$info['badword'],(int)$info['gramatic_level']];
					}
					
					return [2];
				}
				
				$checkStmt->closeCursor();
				
				return [1];
			}
		} catch(\PDOException $pdoException) {
			return [2];
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getWords($id) {
		try {
			$checkStmt = $this->prepare("SELECT `badword`,`word_id` FROM `chatfilter` WHERE `added_by` = :user");
			$checkStmt->bindParam(":user",$id,\PDO::PARAM_INT);
			$checkStmt->execute();
			
			if($checkStmt->rowCount() > 0) {
				$words = $checkStmt->fetchAll();
				$_w = [];
				
				foreach($words as $item) {
					array_push($_w,"[id:" . $item['word_id'] . ",txt:\"" . $item['badword'] . "\"]");
				}
				
				$checkStmt->closeCursor();
				
				return [3,implode(',',$_w)];
			}
			
			$checkStmt->closeCursor();
			
			return [1];
		} catch(\PDOException $pdoException) {
			return [2];
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function addWordFilter($word, $lvl, $id) {
		try {
			$checkStmt = $this->prepare("SELECT DISTINCT `badword` FROM `chatfilter` WHERE LOWER(`badword`) = LOWER(:word) LIMIT 1");
			$checkStmt->bindParam(":word",$word,\PDO::PARAM_STR);
			$checkStmt->execute();
			
			if($checkStmt->rowCount() == 0) {
				$addStmt = $this->prepare("INSERT INTO `chatfilter` (`badword`,`gramatic_level`,`added_by`) VALUES (:w,:l,:u)");
				$addStmt->bindParam(":w",$word,\PDO::PARAM_STR);
				$addStmt->bindParam(":l",$lvl,\PDO::PARAM_INT);
				$addStmt->bindParam(":u",$id,\PDO::PARAM_INT);
				
				if($addStmt->execute()) {
					$addStmt->closeCursor();
					
					return [3,$this->lastInsertId()];
				}
				
				$addStmt->closeCursor();
				
				return [2];
			}
			
			$checkStmt->closeCursor();
			
			return [1];
		} catch(\PDOException $pdoException) {
			return [3];
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getWordFilter($gramaticLevel) {
		try {
			$getStatement = $this->prepare("SELECT DISTINCT `badword` FROM `chatfilter` WHERE `gramatic_level` = :lvl");
			$getStatement->bindParam(":lvl",$gramaticLevel,\PDO::PARAM_INT);
			$getStatement->execute();
			
			if($getStatement->rowCount() > 0) {
				$a = $getStatement->fetchAll();
				
				return $a;
			}
			
			$getStatement->closeCursor();
			
			return null;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function attributing($cat, $val, $id) {
		try {
			$query = "UPDATE `users` SET $cat = :att WHERE `ID` = :ID";
			$getStatement = $this->prepare($query);
			$getStatement->bindParam(":att", $val, \PDO::PARAM_STR);
			$getStatement->bindParam(":ID", $id, \PDO::PARAM_INT);
			$getStatement->execute();
			
			$getStatement->closeCursor();
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getColumnByName($id, $column) {
		try {
			$getStatement = $this->prepare("SELECT $column FROM `users` WHERE `username` = :ID");
			$getStatement->bindValue(":ID", $id);
			$getStatement->execute();
			$getStatement->bindColumn($column, $value);
			$getStatement->fetch(\PDO::FETCH_BOUND);
			$getStatement->closeCursor();
			
			return $value;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
	function getColumnById($id, $column) {
		try {
			$getStatement = $this->prepare("SELECT $column FROM `users` WHERE ID = :ID");
			$getStatement->bindValue(":ID", $id);
			$getStatement->execute();
			$getStatement->bindColumn($column, $value);
			$getStatement->fetch(\PDO::FETCH_BOUND);
			$getStatement->closeCursor();
			
			return $value;
		} catch(\PDOException $pdoException) {
			Logger::Warn($pdoException->getMessage());
		}
	}
	
}

?>