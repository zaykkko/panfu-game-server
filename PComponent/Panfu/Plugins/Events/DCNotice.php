<?php

namespace PComponent\Panfu\Plugins\Events;

use PComponent\Events;
use PComponent\Logging\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class DCNotice extends Plugin {

	public $gameCommands = array(null);

	public $xmlHandlers = array(null);

	public $_pName = "DCNotice";
	
	public $eventBinder = true;

	function __construct($server) {
	}

	function onReady() {
		parent::__construct(__CLASS__);
		Events::Append('leave', array($this, 'logPenguinLeave'));
	}
	
	function onDisconnect($panda) {
	}

	function logPenguinLeave($panda) {

		Logger::Notice("{$panda->username} se fue del servidor.");
	}

}

?>