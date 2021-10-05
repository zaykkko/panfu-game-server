<?php

namespace PComponent\Panfu\Packets\Parsers;

class DOTParser {

	static function Parse($xtData) {
		$xtArray = explode(';', $xtData);
		
		return $xtArray;
	}
	
}

?>