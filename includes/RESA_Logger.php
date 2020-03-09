<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Need to define WP_RESA_DEBUG.
 */
class Logger
{
	const ERROR = 0;
	const WARNING = 1;
	const INFO = 2;
	const DEBUG = 3;

	public static function INFO($message, $whereCalled = false) {
		self::PrintLog(self::INFO, $message, $whereCalled);
	}
	public static function DEBUG($message, $whereCalled = false) {
		self::PrintLog(self::DEBUG, $message, $whereCalled);
	}
	public static function WARNING($message, $whereCalled = false) {
		self::PrintLog(self::WARNING, $message, $whereCalled);
	}
	public static function ERROR($message, $whereCalled = false) {
		self::PrintLog(self::ERROR, $message, $whereCalled);
	}

	private static function PrintLog($level, $message, $whereCalled)
	{
		if($level <= self::actualLevel())
		{
			$e = new Exception();
			$trace = $e->getTrace();
			$header = '['.self::getDateTime(). ' - ' .self::toStringLevel($level);
			if(count($trace) >= 5 && $whereCalled){
				for($i = 4; $i >= 3; $i--){
					$lastCall = $trace[$i];
					if(isset($lastCall['class'])){
						$className = $lastCall['class'].'::'.$lastCall['function'];
						if(isset($trace[$i-1]['line'])) $className .= '::line ' . $trace[$i-1]['line'];
						$header .= ' - ' .$className;
					}
				}
			}
			$lastCall = $trace[2];
			$className = ' in main.php ?';
			if(isset($lastCall['class'])){
				$className = $lastCall['class'].'::'.$lastCall['function'];
				if(isset($trace[1]['line'])) $className .= '::line ' . $trace[1]['line'];
			}
			$header .= ' - ' .$className . ']';
			error_log($header. ' ' . $message);
		}
	}



	private static function getDateTime()
	{
		return date("m-d H:i:s");
	}

	private static function toStringLevel($level)
	{
		$string = '';
		switch($level)
		{
			case self::ERROR: $string = 'Error'; break;
			case self::WARNING: $string = 'Warning'; break;
			case self::INFO: $string = 'Info'; break;
			case self::DEBUG: $string = 'Debug'; break;
			default: $string = 'Undefined'; break;
		}
		return $string;
	}


	private static function actualLevel()
	{
		if(!defined('WP_RESA_DEBUG') || !WP_RESA_DEBUG) return -1;
		else if(defined( 'WP_DEBUG' ) && WP_DEBUG) return 3; //it is useless
		else return 2;
	}

}
