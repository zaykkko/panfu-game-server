<?php

namespace PComponent\Saving;
use PComponent\Logging\Logger;

class Saver {
	private $allowed = Array("frogSpell","snailSpell","chickSpell","pikachuSpell","rabbitSpell");
	private $saved;
	private $pandas;
	public $_server;
	
	function __construct($server, $data = false) {
		if(!$data) {
			$this->saved = Array();
			$this->_server = $server;
		} else {
			$this->pandas = Array();
			$this->_server = $server;
		}
	}
	
	function save() {
		if(func_num_args() < 2) return -1;
		if(isset($this->saved)) {
			$this->saved[func_get_arg(0)] = Array("transform"=>func_get_arg(2),"time"=>floor(microtime(true)*1000)+func_get_arg(1));
		} else {
			if(!isset($this->pandas[func_get_arg(0)])) {
				$this->pandas[func_get_arg(0)] = Array(func_get_arg(2)=>func_get_arg(1));
			} else {
				$this->pandas[func_get_arg(0)][func_get_arg(2)] = func_get_arg(1);
			}
		}
	}
	
	function check() {
		foreach($this->saved as $id => $info) {
			if(floor(microtime(true)*1000) > $info['time']) {
				unset($this->saved[$id]);
				if(isset($this->_server->pandas[$id]))$this->_server->pandas[$id]->room->send("50;".$id.";endcartransformation|");
			}
		}
	}
	
	function transformed() {
		if(func_num_args() == 0) return -1;
		$t = isset($this->saved[func_get_arg(0)])?$this->saved[func_get_arg(0)]['time']:-1;
		
		if($t==-1) return null;
		if(floor(microtime(true)*1000) < $t) {
			return true;
		}
		unset($this->saved[func_get_arg(0)]);
		if(isset($this->_server->pandas[func_get_arg(0)]))$this->_server->pandas[func_get_arg(0)]->room->send("50;".func_get_arg(0).";endcartransformation|");
		return false;
	}
	
	function get() {
		if(func_num_args() < 1) return -1;
		$a = isset($this->pandas[func_get_arg(0)]);if($a==null) return -1;
		return isset($this->pandas[func_get_arg(0)][func_get_arg(1)])?$this->pandas[func_get_arg(0)][func_get_arg(1)]:-1;
	}
	
	function _delete() {
		if(func_num_args() < 1) return -1;
		if(isset($this->saved)) {
			if(isset($this->saved[func_get_arg(0)])) {
				unset($this->saved[func_get_arg(0)]);return 1;
			}
		} else {
			if(isset($this->pandas[func_get_arg(0)][func_get_arg(1)])) {
				unset($this->pandas[func_get_arg(0)][func_get_arg(1)]);return 1;
			}
		}
		return -1;
	}
	
	function flush() {
		$this->saved = Array();
	}
}
?>