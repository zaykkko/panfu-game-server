<?php

namespace PComponent;

final class Events {

	private static $events = array();
	private static $timedEvents = array();

	static function GetTimedEvents() {
		return self::$timedEvents;
	}

	static function GetEvents() {
		return self::$events;
	}

	static function GetEvent($event) {
		if(array_key_exists($event, self::$events)) {
			return self::$events[$event];
		}
	}

	static function ResetInterval($eventIndex) {
		if(array_key_exists($eventIndex, self::$timedEvents)) {
			self::$timedEvents[$eventIndex][2] = time();
		}
	}

	static function RemoveInterval($eventIndex) {
		if(array_key_exists($eventIndex, self::$timedEvents)) {
			unset(self::$timedEvents[$eventIndex]);

			return true;
		} else {
			return false;
		}
	}

	static function AppendInterval($interval, $callable) { 
		$eventArray = [$callable, $interval, null, "interval"];
		array_push(self::$timedEvents, $eventArray);

		$eventIndex = array_search($eventArray, self::$timedEvents);
		return $eventIndex;
	}

	static function AppendTimeout($interval, $callable) { 
		$eventArray = [$callable, $interval, null, "timeout"];
		array_push(self::$timedEvents, $eventArray);

		$eventIndex = array_search($eventArray, self::$timedEvents);
		return $eventIndex;
	}

	static function Append($event, $callable) {
		if(array_key_exists($event, self::$events)) {
			array_push(self::$events[$event], $callable);
		} else {
			self::$events[$event] = array($callable);
		}

		$callableIndex = array_search($callable, self::$events[$event]);

		return $callableIndex;
	}

	static function Remove($event, $callableIndex) {
		if(array_key_exists($event, self::$events)) {

			if(array_key_exists($callableIndex, self::$events[$event])) {
				unset(self::$events[$event][$callableIndex]);
			} else {
				return false;
			}

			return true;
		} else {
			return false;
		}
	}

	static function Emit($event, $data = null) {
		if(is_array($event)) {
			
		}
		if(array_key_exists($event, self::$events)) {
			foreach(self::$events[$event] as $eventCallable) {
				$canContinue = call_user_func($eventCallable, $data);
				if(!$canContinue) {
					return false;
				}
			}
		}
		return true;
	}

	static function Flush($event) {
		if(array_key_exists($event, self::$events)) {
			self::$events[$event] = array();
		}
	}

}

?>