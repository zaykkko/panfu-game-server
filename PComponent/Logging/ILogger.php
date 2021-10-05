<?php

namespace PComponent\Logging;

interface ILogger {

	const Info = "Info";
	const Fine = "Oc";
	const Notice = "Noti";
	const Debug = "Debugger";
	const Warn = "Warn";
	const Error = "Error";
	const Fatal = "Fatal";
	
	const DateFormat = "H:i:s";
	
	static function Info($message);
	static function Fine($message);
	static function Notice($message);
	static function Debug($message);
	static function Warn($message);
	static function Error($message);
	static function Fatal($message);
	static function GS($message);
	static function Discord($message, $groupId, $panda, $sn);
	
}

?>