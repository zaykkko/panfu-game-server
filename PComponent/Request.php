<?php

namespace PComponent\Request;

class Request {
	private static DiscordURLS = Array(
		1 => 'https://discordapp.com/api/webhooks/419962388259799050/ZWg9aq8cbJ8Fkb3yu1oU-K9ISrxGKimcgCZzzK9cwyZ_RdwN320m3n-YCZ2wTNcJLeNw',
		2 => 'https://discordapp.com/api/webhooks/419962388259799050/ZWg9aq8cbJ8Fkb3yu1oU-K9ISrxGKimcgCZzzK9cwyZ_RdwN320m3n-YCZ2wTNcJLeNw'
	);
	
	private static $response;
	private static $url;
	private static $body;
	public static $callback;
	public static $request;
	
	function __construct($url = NULL, $isDiscord = FALSE) {
		if($url === null && $isDiscord === FALSE) throw new Exception("URL indefinido.");
		if($isDiscord) $url = self::$DiscordURLS[$url];
		
		self::$url = $url;
		self::$request = curl_init(self::$url);
		
		curl_setopt(self::$request,CURLOPT_CUSTOMREQUEST,"POST");
		curl_setopt(self::$request,CURLOPT_RETURNTRANSFER,true);
	}
	
	static function close() {
		if(!isset(self::$request)) throw new Exception("No se abrió ningún request.");
		
		curl_close(self::$request);
	}
	
	static function send($body, $callback = NULL) {
		if(!isset(self::$request)) throw new Exception("No se abrió ningún request.");
		
		curl_setopt(self::$request,CURLOPT_POSTFIELDS,$body);
		curl_setopt(self::$request,CURLOPT_FOLLOWLOCATION,1);
		
		self::$response = curl_exec(self::$request);
		
		if($callback !== NULL) $callback(self::$response);
	}
	
	static function getResponse() {
		return (!isset(self::$response) && self::$response !== NULL)?self::$response:NULL;
	}
}