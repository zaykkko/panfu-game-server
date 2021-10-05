<?php

namespace PComponent\Panfu\Handlers\Commands;

class ChatFilter {
	private static $word;
	private static $send;
	private static $blackListedES = "puto,maraca,maricon,conchudo,conchatumadre,conchadetumadre,hijode,hijodeput,chingon,chinga,puta,trolo,trola,escoria,pendejo,pendeja,mierda,mierder,fuck,wank,frikki,polla,miconcha,vagina,pene,vagine,vaginu,chupala,chupamela,bitch,nigg,dosser,weon,aweonao,weonao,pendeji,kys,kill,stfu,shutup,sutup,hell,shit,cock,penis,dick,connard,satan,joto,boludo,boluda,pelotudo,pelotuda,subnormal,autista,huevon,wevon,webon,cagar,chingada,palancaaltecho,forro,jilipolla,pete,cajeta,cajetud,sexo,sexual";
	static $result = false;
	static $restpt = 'none';
	private static $totalWarns;
	private static $lang;
	
	function __construct($_word, $_target, $_warns)
	{
		self::$word = strtolower($_word);
		self::$send = $_target;
		self::$totalWarns = $_warns;
	}
	
	static function filter()
	{
		$list = explode(',',self::$blackListedES);
		
		foreach($list as $word => $aka) {
			$ooooop = self::transformWord();
			if(strpos($ooooop,$aka) !== false) {
				if(self::$totalWarns < 10) {
					self::$result = true;
					self::$restpt = 'warn';
					return;
				} else {
					self::$result = true;
					self::$restpt = 'kick';
					return;
				}
			}
		}
	}
	
	static function transformWord()
	{
		$txt = self::$word;
		$none = self::$word;
		if(strrpos('a',$txt) !== false)
		{
			$none = str_replace('a','4',$none);
		}
		if(strrpos(' ',$txt) !== false)
		{
			$none = str_replace(' ','',$none);
		}
		if(strrpos('A',$txt) !== false)
		{
			$none = str_replace('A','4',$none);
		}
		if(strrpos('E',$txt) !== false)
		{
			$none = str_replace('E','3',$none);
		}
		if(strrpos('e',$txt) !== false)
		{
			$none = str_replace('e','3',$none);
		}
		if(strrpos('i',$txt) !== false)
		{
			$none = str_replace('i','1',$none);
		}
		if(strrpos('I',$txt) !== false)
		{
			$none = str_replace('I','1',$none);
		}
		if(strrpos('O',$txt) !== false)
		{
			$none = str_replace('O','0',$none);
		}
		if(strrpos('o',$txt) !== false)
		{
			$none = str_replace('o','0',$none);
		}
		if(strrpos('u',$txt) !== false)
		{
			$none = str_replace('u','v',$none);
		}
		if(strrpos('U',$txt) !== false)
		{
			$none = str_replace('U','V',$none);
		}
		if(strrpos('á',$txt) !== false)
		{
			$none = str_replace('á','a',$none);
		}
		if(strrpos('é',$txt) !== false)
		{
			$none = str_replace('é','e',$none);
		}
		if(strrpos('í',$txt) !== false)
		{
			$none = str_replace('í','i',$none);
		}
		if(strrpos('ó',$txt) !== false)
		{
			$none = str_replace('ó','o',$none);
		}
		if(strrpos('ú',$txt) !== false)
		{
			$none = str_replace('ú','u',$none);
		}
		
		$none = preg_replace("/[^a-zA-Z0-9]+/","",$none);
		
		return strtolower($none);
	}
	
}