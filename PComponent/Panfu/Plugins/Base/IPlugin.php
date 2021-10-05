<?php

namespace PComponent\Panfu\Plugins\Base;

interface IPlugin {

	const Before = 0;
	const After = 1;
	const Both = 3;
	const Override = 4;

	function handleXmlPacket($panda, $beforeCall = true);
	function handleWorldPacket($panda, $beforeCall = true);
	
}

?>