<?php

namespace PComponent\Panfu\Packets;
use PComponent\Panfu\Packets\Parsers;
use PComponent\Logging\Logger;

class Packet {

	public static $IsXML;
	public static $Extension;
	public static $Handler;
	public static $Duo;
	public static $isDot;
	public static $packHash;
	public static $count;
	public static $RawData;
	
	private static $Instance;
	
	static function __callStatic($a, $b)
    {
        if(isset($b)) return @$this[$a]($b);
		return @$this[$a]();
    }
	
	function GetInstance() {
		$_ = func_num_args();
		if(self::$Instance == null) {
			self::$Instance = new Packet();
			@Packet::Parse(self::$RawData);
		}
		
		return self::$Instance;
	}
	
	function Parse($rawData) {
		$_ = func_num_args();
		$firstCharacter = substr($rawData, 0, 1);
		self::$IsXML = $firstCharacter == '<';
		self::$isDot = $firstCharacter == ';';
		
		if(self::$IsXML) {
			$xmlArray = Parsers\XMLParser::Parse($rawData);
			if(!$xmlArray) {
				self::$Handler = "policy";
			} else {
				self::$Handler = $xmlArray["body"]["@attributes"]["action"];
				self::$Duo = $xmlArray;
			}
			self::$RawData = $rawData;
		} else {
			$pfArray = Parsers\DOTParser::Parse($rawData);
			self::$Handler = $pfArray[0];
			self::$count = !isset($pfArray[1])?null:$pfArray[1];
			self::$packHash = !isset($pfArray[2])?null:$pfArray[2];
			array_shift($pfArray);
			array_shift($pfArray);
			array_shift($pfArray);
			
			self::$Duo = $pfArray;
			self::$RawData = $rawData;
		}
	}
	
}

?>