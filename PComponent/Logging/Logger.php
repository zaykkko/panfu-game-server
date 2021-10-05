<?php

namespace PComponent\Logging;
use PComponent\Request;
use PComponent\Panfu\Discord;

class Logger implements ILogger {

	public static $logOnly = array();
	public static $noOutput = array();
	private static $discordUrls = Array(
		'att' => 'https://discordapp.com/api/webhooks/420798396094218241/7IovCHaC9ZVbFjmexl2OkSTZULX8JFxLt4W1tFR9VVOUTYUowm4fDLdu8r7GSk9L06fP'
	);
	private static $gsName = false;
	private static $first = false;
	private static $asdas = Array(
		' **========================================================**',
		' ||         _        _ _ _ _   _         _ _ _ _           ||',
		' ||       |  |      |  _ _  | |  |      |  _ _   |         ||',
		' ||       |  |      |  _ _  | |  |      |  _ _   |         ||',
		' ||       |  |      | |   | | |  |      |_|   |  |         ||',
		' ||       |  |	    | |   | | |  |	   _ _|  |         ||',
		' ||       |  |      | |   | | |  |      |  _ _ _ |         ||',
		' ||       |  | _ _  | | _ | | |  | _ _  | |_ _ _           ||',
		' ||       |_ _ _ _| |_ _ _ _| |_ _ _ _| |_ _ _ _ |         ||',
		' ||       |_ _ _ _| |_ _ _ _| |_ _ _ _| |_ _ _ _ |         ||',
		' ||                                                        ||',
		' **========================================================**',
		'',
		'   ~$$name$$ : ',
		'   ---------------------- ',
		' ~ Zona horaria que utiliza actualmente Panfu: GMT-6 ~~ (hora oficial de la Ciudad de Mexico)',
		' ~ Estan TODOS los paquetes de Panfu definidos, el socket que envie un paquete INVALIDO sera REMOVIDO automaticamente del servidor.',
		' ~ Cada servidor tiene su capacidad indicada en el archivo "worlds.json", la cual al alcanzar su tope sera NEGADA la conexion de sockets a dicho servidor.'
	);
	
	static function Log($message, $logLevel, $color, $f = false) {
		if(!self::$first) self::First();
		if(!empty(self::$logOnly) && !in_array($logLevel, self::$logOnly)) return;
		
		if($f) {
			$writeData = sprintf("%c# [CDMX] %s {%s} %c%c %s%c", 10, date(self::DateFormat),$logLevel,62,62,$message,10);
			echo $writeData;
		} elseif(!in_array($logLevel, self::$noOutput)) {
			$writeData = self::$gsName === false?sprintf("# [CDMX] %s {%s} %c%c %s%c", date(self::DateFormat),$logLevel,62,62,$message,10):sprintf("# [CDMX] %s {%s -> %s} %c%c %s%c", date(self::DateFormat),$logLevel,self::$gsName,62,62,$message,10);
			echo $writeData;
		}
	}
	
	static function First() {
		self::$first = true;
		echo sprintf("%c%s%c",10,str_replace('$$name$$',self::$gsName,implode(sprintf('%c',10),self::$asdas)),10);
		echo sprintf("%c",10);
	}
	
	static function makeIt($_str) {
		$_txt="";for($i=0;$i<strlen($_str);$i++){$_txt=$_txt.' '.$_str{$i};}
		return $_txt;
	}
	
	static function Info($message, $first = false) {
		self::Log($message, self::Info, '', $first);
	}
	
	static function Fine($message, $color = "green") {
		self::Log($message, self::Fine, $color);
	}
	
	static function Notice($message, $color = "purple") {
		self::Log($message, self::Notice, $color);
	}
	
	static function Debug($message, $color = "cyan") {
		self::Log($message, self::Debug, $color);
	}
	
	static function Warn($message, $color = "red") {
		self::Log($message, self::Warn, $color);
	}
	
	static function Error($message, $color = "red") {
		self::Log($message, self::Error, $color);
	}
	
	static function Fatal($message, $color = "red") {
		self::Log($message, self::Fatal, $color);
	}
	
	static function GS($message) {
		self::$gsName = $message;
	}
	
	static function Discord($message, $groupId, $panda, $sn) {
		if(!isset($groupId)) return false;
		if(!isset($panda)) return false;
		
		
		/**
		try {
			$dic = new Discord(self::$discordUrls[$groupId]);
			$dic->message("**[ES][** *{$panda->id}* **|** *{$panda->username}* **|** *$sn* **]** **->**
$message");
			
			$dic->send();
			
			return true;
		} catch(Exception $e) {
			return false;
		}
		**/
	}

}

?>