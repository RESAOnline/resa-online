<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class RESA_Session
{
	public static $variables = array('RESA_booking');

	public static function start() {
		if ( ! session_id() ) {
			@session_start();
		}
	}

	public static function delete() {
		@session_destroy();
	}


	public static function removeVariables() {
		if(self::isOpen()){
			for($i = 0; $i < count(self::$variables); $i++){
				$variable = self::$variables[$i];
				self::remove($variable);
			}
		}
	}

	public static function store($variable, $value){
		self::start();
		if(in_array($variable, self::$variables)){
			$_SESSION[$variable] = $value;
		}
	}

	public static function isDefined($variable){
		self::start();
		return isset($_SESSION[$variable]);
	}

	public static function load($variable){
		self::start();
		if(isset($_SESSION[$variable])) {
			return $_SESSION[$variable];
		}
		return NULL;
	}

	public static function remove($variable){
		self::start();
		if(isset($_SESSION[$variable])) {
			unset($_SESSION[$variable]);
		}
	}
}
