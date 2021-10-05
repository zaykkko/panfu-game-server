<?php

namespace PComponent\Panfu;


final class Hashing {
	
	static function regenerate($a, $b) {
		$holders = "x1x2x3x4x5x6x7x8x9";
		
		$rand = rand(0,rand(0,7));
		$holders = str_replace("x1",$rand,$holders);
		$rand = rand(rand(3,9),rand(0,2));
		$holders = str_replace("x2",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x3",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x4",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x5",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x6",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x7",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x8",$rand,$holders);
		$rand = rand(0,9);
		$holders = str_replace("x9",$rand,$holders);
		
		$holders = $holders * $a * rand(2,1500) + (rand(1,89) * $b) * $a;
		$holders .= rand(1,9);
		
		return floor(substr($holders,0,9));
	}
	
	private function _1A($a) {
		return base64_encode("|%&".base64_encode($a)."|%&");
	}
	
	private function _1B($a) {
		return base64_decode(explode("|%&",base64_decode($a))[(0+1)]);
	}
	
	private function _2A($a, $d, $c = '', $y = 1337, $u = false) {
		if(strlen($a)>strlen($d))return-1;
		
		for($r=0,$e=0;$e<strlen($a);$r++,$e++) {
			$f = substr($a,$e,1);
			$g = substr($d,$r,1);
			$c .= chr(floor((ord($f)*$r)*(ord($g)+abs($y+1.3e-5)))+(abs($y)+$e));
			$c .= chr(ord($f)+floor(ord($g)*$y));
			$c .= chr((ord($f)+($y*50))+ord($r+ord($g))+$y+($e*strlen($d.$a)));
		}
		
		return !$u?base64_encode($c):$this->_2C($d).chr(93842281)."L M A O ~ Y O U R  F I L E S  G O T  E N C R Y P T E D!!!!!".chr(93842281).$c.chr(93842281).chr($y);
	}
	
	private function _2C($a) {
		$c = "";
		for($b=0;$b<strlen($a);$b++) {
			$c .= chr(ord($a{$b})+(strlen($a)*$b));
		}
		
		return $c;
	}
	
	private function _2D($a) {
		$c = "";
		for($b=0;$b<strlen($a);$b++) {
			$c .= chr(ord($a{$b})-(strlen($a)*$b));
		}
		
		return $c;
	}
	
	private function _2B($a, $d = null, $c = '', $y = 1337, $u = false) {
		if(!$u) {
			$a = base64_decode($a);
			
			for($r=0,$e=0;$e<strlen($a);$r++,$e+=3) {
				$f = substr($a,$e,3);
				$g = substr($d,$r,1);
				$c .= chr(ord($f{1})-floor(ord($g)*$y));
			}
			
			return $c;
		}
		
		$n = explode(chr(93842281),$a);
		if(strrpos($a,chr(93842281)) === false) DIE("NOOO :0");
		$d = _2D($n[0]);
		echo sprintf("ALAVERGA1: %s%c",$d,10);
		$a = $n[2];
		echo sprintf("ALAVERGA2: %s%c",$a,10);
		$y = ord($n[3]);
		echo sprintf("ALAVERGA3: %s%c",$y,10);
		
		for($r=0,$e=0;$e<strlen($a);$r++,$e+=3) {
			$f = substr($a,$e,3);
			$g = substr($d,$r,1);
			$c .= chr(ord($f{1})-floor(ord($g)*$y));
		}
		
		return $c;
	}
	
	static function gamingdecrypt($a, $z = 7, $x = [2,5,3]) {
		$result = '';
		$a = base64_decode($a);
		for($i=0,$k=strlen($a); $i< $k ; $i++) {
			$char = substr($a, $i, 1);
			$keychar = substr($z, ($i % strlen($z))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}
		$result = explode("\r\n\r",$result)[1];
		return $result;
	}
	
	static function gamingcrypt($a, $z = 7, $x = [2,5,3]) {
		$result = '';
		$random = rand(0,650) + rand(25,800);
		$param1 = md5(($random+rand(2,250))."·".uniqid(mt_rand(),true)."·".$random);
		$param2 = crypt(uniqid(mt_rand(),true),md5($param1)).bin2hex(openssl_random_pseudo_bytes(rand(10,16)));
		$a = ("{$param1}\r\n\r{$a}\r\n\r{$param2}").rand(0,10);
		for($i=0, $k= strlen($a); $i<$k; $i++) {
			$char = substr($a, $i, 1);
			$keychar = substr($z, ($i % strlen($z))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result .= $char;
		}
		return base64_encode($result);
	}
	
}
?>
