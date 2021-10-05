<?php

namespace PComponent\Panfu\Plugins\Login;

use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class LoginNotice extends Plugin {

	public $gameCommands = Array(
		'0' => array("loginAttempt", self::Before)
	);
	
	public $_pName = "LoginNotice";
	
	public $xmlHandlers = Array();
	
	public $loginServer = true;
	
	function __construct($server) {
		$this->server = $server;
	}
	
	function onDisconnect($panda) {
	}
	
	function onReady() {
		parent::__construct(__CLASS__);
	}
	
	function loginAttempt($panda) {
		Logger::Notice("[SUPUESTA ID => '".Packet::$Duo[0]."'] intenta iniciar sesion.");
	}
	
}

?>