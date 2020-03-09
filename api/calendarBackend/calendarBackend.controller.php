<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APICalendarBackendController
{
	public static function getInformationsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];

			$data = array(
				'startDate' => array($startDate, '>='),
				'endDate' => array($endDate, '<=')
			);
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			$infoCalendars = RESA_InfoCalendar::getAllDataWithDate($startDate,  $endDate, $places);
			$response->set_data(json_decode(RESA_Tools::formatJSONArray($infoCalendars)));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getInformationsCalendarMonth(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];
			$data = array(
				'startDate' => array($startDate, '>='),
				'endDate' => array($endDate, '<=')
			);
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			$infoCalendars = RESA_InfoCalendar::getAllDataWithDate($startDate,  $endDate, array('idPlaces' => array($places)));
			$results = array();
			foreach($infoCalendars as $infoCalendar){
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $infoCalendar->getDate());
				$dateEnd = DateTime::createFromFormat('Y-m-d H:i:s', $infoCalendar->getDateEnd());
				do {
					if(!isset($results[$date->format('Y-m-d')])){
						$results[$date->format('Y-m-d')] = array('numbers' => 0, 'date' => $date->format('Y-m-d H:i:s'), 'note' => '');
					}
					$results[$date->format('Y-m-d')]['numbers']++;
					$results[$date->format('Y-m-d')]['note'] .= $infoCalendar->getNote().htmlspecialchars('<br />');
					$date->add(new DateInterval('P1D'));
				}while($date <= $dateEnd);
			}
			$response->set_data($results);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function informationsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($data['infoCalendar'])){
				$infoCalendarJSON = json_decode(stripslashes(wp_kses_post($data['infoCalendar'])));
				$infoCalendar = new RESA_InfoCalendar();
				$infoCalendar->fromJSON($infoCalendarJSON);
				$customer = new RESA_Customer();
				$customer->loadCurrentUser(false);
				$infoCalendar->setIdUserCreator($customer->getId());
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $infoCalendarJSON->date);
				$infoCalendar->setDate($date->format('Y-m-d H:i:s'));
				$infoCalendar->setStartTime($date->format('H:i'));
				$dateEnd = DateTime::createFromFormat('Y-m-d H:i:s', $infoCalendarJSON->dateEnd);
				$infoCalendar->setDateEnd($dateEnd->format('Y-m-d H:i:s'));
				$infoCalendar->setEndTime($dateEnd->format('H:i'));
				$infoCalendar->save();
				$response->set_data(json_decode($infoCalendar->toJSON()));
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

	public static function deleteInformationsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($request['id']) && is_numeric($request['id'])){
				$infoCalendar = new RESA_InfoCalendar();
				$infoCalendar->loadById($request['id']);
				$success = false;
				if($infoCalendar->isLoaded()){
					$infoCalendar->deleteMe();
					$success = true;
				}
				$response->set_data(array('result' => $success));
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


	public static function getConstraintsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];

			$places = array();
			$data = array(
				'startDate' => array($startDate, '>='),
				'endDate' => array($endDate, '<=')
			);

			$serviceConstraints = RESA_ServiceConstraint::getAllDataWithDate($startDate,  $endDate);
			$memberConstraints = RESA_MemberConstraint::getAllDataWithDate($startDate,  $endDate);
			$formatSC = array();
			foreach($serviceConstraints as $serviceConstraint){
				array_push($formatSC, $serviceConstraint->getServiceConstraintLiteJSON());
			}
			$formatMC = array();
			foreach($memberConstraints as $memberConstraint){
				array_push($formatMC, $memberConstraint->getMemberConstraintLiteJSON());
			}


			$results = array(
				'serviceConstraints' => $formatSC,
				'memberConstraints' => $formatMC
			);

			$response->set_data($results);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getConstraintsCalendarMonth(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];
			$places = array();
			$serviceConstraints = RESA_ServiceConstraint::getAllDataWithDate($startDate,  $endDate);
			$memberConstraints = RESA_MemberConstraint::getAllDataWithDate($startDate,  $endDate);
			$results = array();
			foreach($serviceConstraints as $serviceConstraint){
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $serviceConstraint->getStartDate());
				$dateEnd = DateTime::createFromFormat('Y-m-d H:i:s', $serviceConstraint->getEndDate());
				do {
					if(!isset($results[$date->format('Y-m-d')])){
						$results[$date->format('Y-m-d')] = array('numbers' => 0, 'date' => $date->format('Y-m-d H:i:s'));
					}
					$results[$date->format('Y-m-d')]['numbers']++;
					$date->add(new DateInterval('P1D'));
				}while($date <= $dateEnd);
			}
			foreach($memberConstraints as $memberConstraint){
				$date = DateTime::createFromFormat('Y-m-d H:i:s', $memberConstraint->getStartDate());
				$dateEnd = DateTime::createFromFormat('Y-m-d H:i:s', $memberConstraint->getEndDate());
				do {
					if(!isset($results[$date->format('Y-m-d')])){
						$results[$date->format('Y-m-d')] = array('numbers' => 0, 'date' => $date->format('Y-m-d H:i:s'));
					}
					$results[$date->format('Y-m-d')]['numbers']++;
					$date->add(new DateInterval('P1D'));
				}while($date <= $dateEnd);
			}
			$response->set_data($results);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function constraintsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($data['constraint']) && isset($data['isServiceConstraint'])){
				$constraintJSON = json_decode(stripslashes(wp_kses_post($data['constraint'])));
				$isServiceConstraint = sanitize_text_field($data['isServiceConstraint']);
				if($isServiceConstraint){
					$serviceConstraint = new RESA_ServiceConstraint();
					$serviceConstraint->fromJSON($constraintJSON);
					$serviceConstraint->setStartDate($constraintJSON->startDate);
					$serviceConstraint->setEndDate($constraintJSON->endDate);
					$serviceConstraint->save();
					$response->set_data($serviceConstraint->getServiceConstraintLiteJSON());
				}
				else {
					$memberConstraint = new RESA_MemberConstraint();
					$memberConstraint->fromJSON($constraintJSON);
					$memberConstraint->setStartDate($constraintJSON->startDate);
					$memberConstraint->setEndDate($constraintJSON->endDate);
					$memberConstraint->save();
					$response->set_data($memberConstraint->getMemberConstraintLiteJSON());
				}
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

	public static function deleteConstraintsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($request['id']) && isset($request['isServiceConstraint']) && is_numeric($request['id'])){
				$idConstraint = sanitize_text_field($request['id']);
				$isServiceConstraint = sanitize_text_field($request['isServiceConstraint']);
				if($isServiceConstraint){
					$serviceConstraint = new RESA_ServiceConstraint();
					$serviceConstraint->loadById($idConstraint);
					$success = false;
					if($serviceConstraint->isLoaded()){
						$serviceConstraint->deleteMe();
						$success = true;
					}
				}
				else {
					$memberConstraint = new RESA_MemberConstraint();
					$memberConstraint->loadById($idConstraint);
					$success = false;
					if($memberConstraint->isLoaded()){
						$memberConstraint->deleteMe();
						$success = true;
					}
				}
				$response->set_data(array('result' => $success));
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function getAlertsCalendar(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];
			$places = array();
			$alerts = RESA_Alert::getAllDataWithDate($startDate,  $endDate);
			$response->set_data(json_decode(RESA_Tools::formatJSONArray($alerts)));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}




}
