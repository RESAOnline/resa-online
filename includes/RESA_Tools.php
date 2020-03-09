<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Tools
{

	public static function getParameterById($statesParameters, $id){
		$parameter = null;
		foreach($statesParameters as $parameterAux){
			if($parameterAux->id == $id){
				$parameter = $parameterAux;
			}
		}
		return $parameter;
	}

	public static function formatJSONArray($array){
		$JSON = '';
		for($i = 0; $i < count($array); $i++) {
			if($i  != 0) $JSON .= ',';
			$JSON .= $array[$i]->toJSON();
		}
		return '['.$JSON.']';
	}

	public static function formatJSONArrayCustomer($array){
		$JSON = '';
		for($i = 0; $i < count($array); $i++) {
			if($i  != 0) $JSON .= ',';
			$JSON .= $array[$i]->toJSON(true);
		}
		return '['.$JSON.']';
	}

	public static function toJSONBoolean($value){
		$return = 'false';
		if($value)
			$return = 'true';
		return $return;
	}

	public static function formatTextaeraHTML($value){
		$value = sanitize_textarea_field($value);
		$value = str_replace(array("\n", "\r"), '', nl2br(esc_html($value)));
		$value = str_replace("\t", ' ', $value);
		return $value;
	}

	public static function HTMLTextareaToNl($value){
		return str_replace(array("<br />","<br/>"), "\n", $value);
	}

	public static function echapEolTab($value){
		return preg_replace('/\r\n|\r|\n|\t/', ' ', $value);
	}

	public static function canUseForeach($value){
		return is_object($value) || is_array($value);
	}

	/**
	 * \fn generateLogin
	 * \brief generate the login with firstname, lastname et email
	 */
	public static function generateLogin($firstName, $lastName, $email){
		return $email;
	}

	public static function generateWhereClause($array, $withWHERE = true){
		$result = '';
		if(count($array) > 0) {
			$result .= '';
			if($withWHERE) $result .= 'WHERE ';
			$index = 0;
			foreach($array as $key => $value){
				if(is_array($value)){
					//array(1,2,3) => IN (1,2,3)
					if(is_array($value[0]) && count($value[0]) > 0){
						if($index != 0) $result .= ' AND ';
						$result .= $key . ' IN (\''.implode("','",$value[0]).'\')';
					}
					//array('value', '<=') => key <= value
					else if(!is_array($value[0]) && strcmp($value[0], 'AND')!==0 && strcmp($value[0], 'OR')!==0) {
						if($index != 0) $result .= ' AND ';
						$result .= $key.' '.$value[1].' \''.$value[0].'\' ';
					}
					//array('AND', array(array('value', '<='), array('value', '>='))) => key <= value AND key >= value
					else if(!is_array($value[0]) && is_array($value[1]) && (strcmp($value[0], 'AND') === 0 || strcmp($value[0], 'OR') === 0)) {
						if($index != 0) $result .= ' AND ';
						$subResult = '';
						foreach($value[1] as $subValue){
							if(!empty($subResult)) $subResult .= $value[0] . ' ';
							$subResult .= $key.' '.$subValue[1].' \''.$subValue[0].'\' ';
						}
						$result .= '('.$subResult.')';
					}
				}
				else {
					if($index != 0) $result .= ' AND ';
					$result .= $key.'=\''.$value.'\' ';
				}
				$index++;
			}
		}
		return $result;
	}


	public static function generateWpUserWhereClause($array){
		$result = array('meta_query' => array());
		foreach($array as $key => $value){
			array_push($result['meta_query'], array(
				'key' => $key,
				'value' => $value,
				'compare' => '=='
			));
		}
		return $result;
	}

	/**
	 *
	 */
	 public static function generateWhereWithPlace($idPlaces = array(), $field='idPlaces', $none = '', $delimit=','){
		 $whereIdPlace = '';
 			if(count($idPlaces) > 0){
	 			foreach($idPlaces as $place){
	 				if(!empty($whereIdPlace)) $whereIdPlace .= ' OR ';
	 				$whereIdPlace .= ' '.$field.' LIKE \'%'.$delimit.''.$place.''.$delimit.'%\'';
	 			}
	 			$whereIdPlace = ' AND (( ' . $whereIdPlace . ' ) OR '.$field.'="' . $none .'")';
 		 }
		 return $whereIdPlace;
	 }

	 public static function generateOneParticipantUri($idCustomer, $participant){
 		$uri = $idCustomer;
 		foreach($participant as $key => $value){
 			if($key != 'uri' && $key != 'meta_group'){
 				$uri .= '_' . $value;
 			}
 		}
 		return str_replace(" ", "_", $uri);
 	}

	public static function getAppointmentByIdClientObject($idClientObject, $appointments){
		foreach($appointments as $appointment){
			if($appointment->getIdClientObject() == $idClientObject){
				return $appointment;
			}
		}
		return null;
	}

	public static function getParticipantsParameter($idParameter){
		$parameters = unserialize(get_option('resa_settings_form_participants_parameters'));
		foreach($parameters as $parameter){
			if($parameter->id == $idParameter){
				return $parameter;
			}
		}
		return null;
	}

	public static function filterMemberNotInAppointment($appointments, $idMember){
		$newAppointments = [];
		foreach($appointments as $appointment){
			if($appointment->haveMember($idMember)){
				array_push($newAppointments, $appointment);
			}
		}
		return $newAppointments;
	}

	public static function changeDateButNotTime(DateTime $date, DateTime $newDate){
		$returnDate = clone $date;
		if(isset($date) && $date!=false){
			$returnDate = DateTime::createFromFormat('Y-m-d H:i:s', $newDate->format('Y-m-d') . ' '. $date->format('H:i:s'));
		}
		return $returnDate;
	}

	public static function convertStringToDate($stringDate){
		return DateTime::createFromFormat('Y-m-d H:i:s', $stringDate);
	}

	public static function resetTimeToDate($date){
		$returnDate = null;
		if(isset($date) && $date!=false){
			$returnDate = DateTime::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . ' 00:00:00');
		}
		return $returnDate;
	}

	public static function isDatePassed($oldDate){
		if (!($oldDate instanceof DateTime) && is_string($oldDate)) {
			$oldDate = self::convertStringToDate($oldDate);
			$date = self::resetTimeToDate(new DateTime());
			return $oldDate < $date;
		}
		else if ($oldDate instanceof DateTime) {
			$date = self::resetTimeToDate(new DateTime());
			return $oldDate < $date;
		}
		return false;
	}

	public static function getTextByLocale($object, $locale){
		$array = null;
		if(is_object($object)){
			$array = get_object_vars($object);
		}
		else if(is_array($object)){
			$array = $object;
		}
		else if(is_string($object)){
			$array = unserialize($object);
		}
		$result = '';
		if($array != null){
			if(!isset($array[$locale]) || empty($array[$locale])){
				if(count($array) > 0){
						foreach($array as $language => $value){
							$result = $value;
							if(isset($result) && !empty($result))
								break;
						}
				}
				else $result = '';
			}
			else {
				$result = $array[$locale];
			}
		}
		return $result;
	}
/* @deprecated
	public static function htmlspecialcharsOnTraductionObject($object){
		foreach($object as $key => $value){
			$object->$key = htmlspecialchars($object->$key, ENT_QUOTES);
		}
		return $object;
	}
*/
	public static function wpToJSDateFormat(){
		$SYMBOLS_MATCHING = array('d' => 'dd','D' => 'D','j' => 'd','l' => 'DD','N' => '','S' => '','w' => '','z' => 'o','W' => '','F' => 'MMM','m' => 'mm','M' => 'M','n' => 'm','t' => '','L' => '', 'o' => '', 'Y' => 'yyyy', 'y' => 'y');
		$php_format = get_option('date_format');
		$jqueryui_format = '';
		$escaping = false;
		for($i = 0; $i < strlen($php_format); $i++)
		{
			$char = $php_format[$i];
			if($char === '\\') // PHP date format escaping character
			{
				$i++;
				if($escaping) $jqueryui_format .= $php_format[$i];
				else $jqueryui_format .= '\'' . $php_format[$i];
				$escaping = true;
			}
			else
			{
				if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
				if(isset($SYMBOLS_MATCHING[$char]))
					$jqueryui_format .= $SYMBOLS_MATCHING[$char];
				else
					$jqueryui_format .= $char;
			}
		}
		if($escaping){
			$jqueryui_format .='\'';
		}
		$jqueryui_format = preg_replace(array('/\'/'), array('\\\''), $jqueryui_format);
		return $jqueryui_format;
	}


	public static function wpToJSTimeFormat(){
		$SYMBOLS_MATCHING = array('a' => '','A' => '','B' => '','g' => 'h','G' => 'H','h' => '','H' => '','i' => 'mm','s' => '','u' => '');
		$php_format = get_option('time_format');
		$jqueryui_format = '';
		$escaping = false;
		for($i = 0; $i < strlen($php_format); $i++)
		{
			$char = $php_format[$i];
			if($char === '\\') // PHP date format escaping character
			{
				$i++;
				if($escaping) $jqueryui_format .= $php_format[$i];
				else $jqueryui_format .= '\'' . $php_format[$i];
				$escaping = true;
			}
			else
			{
				if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
				if(isset($SYMBOLS_MATCHING[$char]))
					$jqueryui_format .= $SYMBOLS_MATCHING[$char];
				else
					$jqueryui_format .= $char;
			}
		}
		if($escaping){
			$jqueryui_format .='\'';
		}
		$jqueryui_format = preg_replace(array('/\'/'), array('\\\''), $jqueryui_format);
		return $jqueryui_format;
	}
}
