<?php

namespace PComponent;
use PComponent\Database;
use PComponent\Logging\Logger;

class DatabaseManager {

	public $databaseConnections = array();
	public $pandasByDatabases = array();
	public $original;
	
	function __construct($a) {
		if($a) $this->original = new Database();
	}
	
	// Returns database index
	function add($panda) {
		if(empty($this->databaseConnections)) {
			$databaseIndex = $this->createDatabase();
			$this->pandasByDatabases[$databaseIndex][] = $panda;
		} else {
			$databaseIndex = $this->getOpenDatabase();
			$this->pandasByDatabases[$databaseIndex][] = $panda;
		}

		$panda->database = $this->databaseConnections[$databaseIndex];

		return $databaseIndex;
	}

	function remove($panda) {

		foreach($this->pandasByDatabases as $databaseIndex => $pandaArray) {
			if(($pandaIndex = array_search($panda, $pandaArray)) !== false) {

				unset($this->pandasByDatabases[$databaseIndex][$pandaIndex]);

				if(count($this->pandasByDatabases[$databaseIndex]) == 0) {

					unset($this->pandasByDatabases[$databaseIndex]);
					unset($this->databaseConnections[$databaseIndex]);
				}

				return true;
			}
		}
	}

	function getOpenDatabase() {
		foreach($this->pandasByDatabases as $databaseIndex => $pandaArray) {
			if(count($this->pandasByDatabases[$databaseIndex]) < 20) {
				return $databaseIndex;
			} else {
				return $this->createDatabase();
			}
		}
	}

	function createDatabase() {
		$newDatabase = new Database();
		$this->databaseConnections[] = $newDatabase;
		$databaseIndex = array_search($newDatabase, $this->databaseConnections);

		return $databaseIndex;
	}
	

}

?>