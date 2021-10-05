<?php
namespace PComponent\Panfu\Plugins\BotGames;

use PComponent\Panfu\Logger;
use PComponent\Panfu\Packets\Packet;
use PComponent\Panfu\Plugins\Base\Plugin;

final class BotGames extends Plugin {
	
	public $dependencies = array("AntiAd" => "loadAntiAd");
	
	public $gameCommands = array(
		"s" => array(
			"m#sm" => array("handlePlayerMessage", self::Before)
		)
	);
	
	public $_pName = "BotGames";
	
	public $xmlHandlers = array(null);
	
	public $commandPrefixes = array("!");
	
	public $commands = array(
		"test" => "handleTestMessages"
	);
	
	public $lyrics = Array(
		"tests-help" => "<font color=\"#FF0000\"><font color=\"#0000FF\">        TESTS          </font><br><font color=\"#FF0000\">==============</font><br>Lo único que debes hacer es responder la preguntas. Te daré una serie de opciones, para responderlas debes poner <font color=\"#0F0F0F\">!test r *número de opción*</font>.         <br><font color=\"#0FCFF0\">Para comenzar escribe <u>!test s <font color=\"#000\">*nombre de test*</font></u></font>. <br> Escribe <font color=\"#0CC6FF0\"><u>!test lista</u></font> para obtener una lista con <u>TODOS</u> los tests disponibles.",
		"tests-list" => "<font color=\"#0000FF\">        LISTA:         </font><br><font color=\"#0CC6FF0\">-Psicópata<br>           -Locura<br>              -Asesino"
	);
	
	private $mutedPenguins = array();
	
	private $patchedItems;
	private $antiAd;
	
	function __construct($server) {
		$this->server = $server;
	}

	function onReady() {
		parent::__construct(__CLASS__);
	}
	
	function onDisconnect($panda) {
	}
	
	function handleTestMessages(array $args, $panda) {
		if(!isset($args[0])) $panda->send("40;1010;¡Vaya!|");
	}
	
	function handlePlayerMessage($panda) {
		return -1;
		$message = Packet::$Duo[0];
		
		$firstCharacter = substr($message, 0, 1);
		if(in_array($firstCharacter, $this->commandPrefixes)) {
			$messageParts = explode(" ", $message);
			$title = $messageParts[0];
			array_shift($messageParts);
			switch($title) {
				case 'tests':
					if(!isset($messageParts[0])) {
						Packet::$Handler = 'NONE';
						return $panda->send("40;1010;|");
					}
			}
		}
	}
	
}

?>
