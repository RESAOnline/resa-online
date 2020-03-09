<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APICalendarsController
{
	public static function init(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){

			$calendars = get_option('resa_settings_calendars', null);
			if(isset($calendars)) $calendars = unserialize($calendars);
			else $calendars = array();

			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'calendars' => $calendars,
			);
			$json = '{
				"settings":'.json_encode($settings).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function save(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['calendars'])){
				$calendars = json_decode($data['calendars']);
				self::replaceSynchronizedCalendar($calendars);
				$calendars = update_option('resa_settings_calendars', serialize($calendars));
			}
			$response->set_data(json_decode('{}'));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function saveCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$calendar = null;
			if(isset($data['calendar'])){
				$calendar = json_decode($data['calendar']);
				$calendars = get_option('resa_settings_calendars', null);
				if(isset($calendars)) $calendars = unserialize($calendars);
				else $calendars = array();
				$found = false;
				for($i = 0; $i < count($calendars); $i++){
					if($calendars[$i]->id == $calendar->id){
						$found = true;
						$calendars[$i]->color = $calendar->color;
						$calendars[$i]->type = $calendar->type;
						$calendars[$i]->dates = $calendar->dates;
						$calendars[$i]->groupDates = $calendar->groupDates;
						$calendars[$i]->manyDates = $calendar->manyDates;
					}
				}
				if(!$found){
					array_push($calendars, $calendar);
				}
				else {
					self::replaceSynchronizedCalendar([$calendar]);
				}
				$calendars = update_option('resa_settings_calendars', serialize($calendars));
				$response->set_data($calendar);
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad parameters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	private static function replaceSynchronizedCalendar($calendars){
		$servicesAvailabilities = RESA_ServiceAvailability::getAllCalendarSynchronized();
		$memberAvailabilities = RESA_MemberAvailability::getAllCalendarSynchronized();
		//Logger::DEBUG(RESA_Tools::formatJSONArray($servicesAvailabilities));
		$alreadySynchronized = array();
		$alreadySynchronizedMA = array();
		foreach($calendars as $calendar){
			foreach($servicesAvailabilities as $servicesAvailability){
				if($servicesAvailability->getIdCalendar() == $calendar->id){
					Logger::DEBUG('update service synchronisaiton : ' . $calendar->id);
					array_push($alreadySynchronized, $servicesAvailability->getId());
					if(isset($calendar->color)){ $servicesAvailability->setColor($calendar->color); }
					$servicesAvailability->setDates($calendar->dates);
					$servicesAvailability->setGroupDates($calendar->groupDates);
					$servicesAvailability->setManyDates($calendar->manyDates);
					$servicesAvailability->save();
				}
			}
			foreach($memberAvailabilities as $memberAvailability){
				if($memberAvailability->getIdCalendar() == $calendar->id){
					Logger::DEBUG('update member synchronisaiton : ' . $calendar->id);
					array_push($alreadySynchronizedMA, $memberAvailability->getId());
					if(isset($calendar->color)){ $memberAvailability->setColor($calendar->color); }
					$memberAvailability->setDates($calendar->dates);
					$memberAvailability->save();
				}
			}
		}
		//Delete synchronisation
		foreach($servicesAvailabilities as $servicesAvailability){
			if(!in_array($servicesAvailability->getId(), $alreadySynchronized)){
				array_push($alreadySynchronized, $servicesAvailability->getId());
				Logger::DEBUG('Delete service synchronisaiton');
				$servicesAvailability->setIdCalendar('');
				$servicesAvailability->save();
			}
		}
		foreach($memberAvailabilities as $memberAvailability){
			if(!in_array($memberAvailability->getId(), $alreadySynchronizedMA)){
				Logger::DEBUG('Delete member synchronisaiton');
				array_push($alreadySynchronizedMA, $memberAvailability->getId());
				$memberAvailability->setIdCalendar('');
				$memberAvailability->save();
			}
		}
	}

}
