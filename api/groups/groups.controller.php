<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIGroupsController
{
	public static function init(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$groups = RESA_StaticGroup::getAllData();
			$skeletonStaticGroup = new RESA_StaticGroup();
			$settings = array(
				'places' => unserialize(get_option('resa_settings_places')),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
			);
			$json = '{
				"groups":'.RESA_Tools::formatJSONArray($groups).',
				"services":'.json_encode(RESA_Service::getAllServicesAndThisPrices()).',
				"skeletonStaticGroup":'. $skeletonStaticGroup->toJSON().',
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
			if(isset($data['groups'])){
				$groupsInPost = json_decode($data['groups']);
				$data = RESA_StaticGroup::getAllData();
				$idGroups = array();
				for($i = 0; $i < count($groupsInPost); $i++) {
					if(isset($groupsInPost[$i]->isUpdated) && $groupsInPost[$i]->isUpdated){
						$group = new RESA_StaticGroup();
						$group->fromJSON($groupsInPost[$i]);
						$group->save();
						if(!$group->isNew()){
							array_push($idGroups, $group->getId());
						}
					} else array_push($idGroups, $groupsInPost[$i]->id);
				}
				for($i = 0; $i < count($data); $i++) {
					if(!in_array($data[$i]->getId(), $idGroups)){
						$data[$i]->deleteMe();
					}
				}
			}
			$response->set_data(json_decode(RESA_Tools::formatJSONArray(RESA_StaticGroup::getAllData())));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}



	public static function openGroup(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			$startDate = $data['startDate'];
			$endDate = $data['endDate'];
			$idService = $data['idService'];
			$dates = $data['dates'];
			$service = new RESA_Service();
			$service->loadById($idService);

			global $wpdb;
			$filterPlace = (count($places)>0)?RESA_Tools::generateWhereWithPlace($places, 'idPlace', '', ''):'';
			$results = $wpdb->get_results('SELECT appointment.id FROM '. RESA_Appointment::getTableName().' as appointment
			INNER JOIN ' . $wpdb->prefix. 'resa_service as service ON appointment.startDate = \''.$startDate.'\' AND appointment.endDate = \''.$endDate.'\' AND (appointment.state<>\'updated\' AND appointment.state<>\'cancelled\' AND appointment.state<>\'abandonned\') AND appointment.idService IN ('.implode(',', $service->getAllIds()).') '. $filterPlace. ' GROUP BY appointment.id');

			$allAppointments = array();
			foreach($results as $result){
				$appointment = new RESA_Appointment();
				$appointment->loadById($result->id);
				$result = (array)RESA_Booking::getBookingLite($appointment->getIdBooking());
				$customer = new RESA_Customer();
				$customer->loadById($result['idCustomer']);
				$result = array_merge($result, array(
					'id' => $result['idCreation']!=-1?$result['idCreation']:$appointment->getIdBooking(),
					'idAppointment' => $appointment->getId(),
					'idPlace' => $appointment->getIdPlace(),
					'state' => $appointment->getState(),
					'prices' => array(),
					'customer' => $customer->toSimpleArray()
				));
				$result['quotation'] = $result['quotation']?true:false;
				$result['paymentState'] = RESA_Variables::calculateBookingPayment($result['status'], $result['paymentState']);
				$result['tags'] = $appointment->getTags();
				foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
					array_push($result['prices'], json_decode($appointmentNumberPrice->toJSON()));
				}
				array_push($allAppointments, (object)$result);
			}
			$allGroups = array();
			foreach($dates as $date){
				$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
				$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
				$groups = RESA_Group::getAllDataInterval($startDate, $endDate);
				$allGroups = array_merge($allGroups, $groups);
				$constraints = RESA_MemberConstraint::getAllDataWithDate($startDate, $endDate);
				$allGroups = array_merge($allGroups, $constraints);
			}
			$response->set_data(array(
				'appointments' => $allAppointments,
				'groups' => json_decode(RESA_Tools::formatJSONArray($allGroups))
			));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function switchParticipant(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$participant = $data['participant'];
			$idService = $data['idService'];
			$idPlace = $data['idPlace'];
			$dates = $data['dates'];
			$idGroup = $data['idGroup'];

			$groupsUpdated = array();
			foreach($dates as $date){
				$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
				$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
				$allGroups = RESA_Group::getAllData(
					array('startDate' => $startDate,
						'endDate' => $endDate,
						'idPlace' => $idPlace,
						'idService' => $idService)
				);
				for($i = 0; $i < count($allGroups); $i++){
					$group = $allGroups[$i];
					$updated = false;
					if($group->haveParticipant($participant['uri'])){
						$group->removeIdParticipant($participant['uri']);
						$group->save();
						$updated = true;
						array_unshift($groupsUpdated, $group);
					}
					if(!$group->haveParticipant($participant['uri']) && (($idGroup == $group->getId() && $participant['newGroupName'] != '') || ($idGroup == -1 && $participant['newGroupName'] == $group->getName()))){
						$group->addIdParticipant($participant['uri']);
						$group->save();
						$updated = true;
						array_push($groupsUpdated, $group);
					}
				}
			}
			if(count($groupsUpdated) > 0){
				$results = json_decode(RESA_Tools::formatJSONArray($groupsUpdated));
				$response->set_data($results);
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'unknow_group', 'message' => 'Unknow group'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 *
	 */
	public static function switchMember(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$member = $data['member'];
			$idService = $data['idService'];
			$idPlace = $data['idPlace'];
			$dates = $data['dates'];
			$idGroup = $data['idGroup'];
			$groupsUpdated = array();
			foreach($dates as $date){
				$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
				$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
				$allGroups = RESA_Group::getAllData(
					array('startDate' => $startDate,
						'endDate' => $endDate,
						'idPlace' => $idPlace,
						'idService' => $idService)
				);
				for($i = 0; $i < count($allGroups); $i++){
					$group = $allGroups[$i];
					$updated = false;
					if($group->haveIdMember($member['id'])){
						$group->removeIdMember(intval($member['id']));
						$group->save();
						$updated = true;
						array_unshift($groupsUpdated, $group);
					}
					if(!$group->haveIdMember($member['id']) && (($idGroup == $group->getId() && $member['newGroupName'] != '') || $idGroup == -1 && $member['newGroupName'] == $group->getName())){
						$group->addIdMember(intval($member['id']));
						$group->save();
						$updated = true;
						array_push($groupsUpdated, $group);
					}
				}
			}
			if(count($groupsUpdated) > 0){
				$results = json_decode(RESA_Tools::formatJSONArray($groupsUpdated));
				$response->set_data($results);
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'unknow_group', 'message' => 'Unknow group'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 *
	 */
	public static function updateGroup(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$oldGroup = (object)$data['oldGroup'];
			$newGroup = (object)$data['newGroup'];
			$idService = $data['idService'];
			$idPlace = $data['idPlace'];
			$dates = $data['dates'];
			$idGroup = $data['idGroup'];

			$result = '';
			foreach($dates as $date){
				$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
				$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
				if($idGroup > -1 && count($dates) == 1){
					$allGroups = RESA_Group::getAllData(array('id' => $idGroup));
				}
				else {
					$allGroups = RESA_Group::getAllData(
						array('startDate' => $startDate,
							'endDate' => $endDate,
							'idService' => $idService,
							'idPlace' => $idPlace,
							'name' => $oldGroup->name)
					);
				}
				for($i = 0; $i < count($allGroups); $i++){
					$group = $allGroups[$i];
					$updated = false;
					$group->setName($newGroup->name);
					$group->setPresentation($newGroup->presentation);
					$group->setColor($newGroup->color);
					$group->setMax($newGroup->max>0?$newGroup->max:10);
					$group->setOptions($newGroup->options);
					$group->save();
					if($startDate == $data['startDate'] && $endDate == $data['endDate']){
						$result = $group->toJSON(true);
					}
				}
			}
			if($result != ''){
				$response->set_data(json_decode($result));
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'unknow_group', 'message' => 'Unknow group'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 *
	 */
	public static function addGroup(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$newGroup = (object)$data['newGroup'];
			$idService = $data['idService'];
			$idPlace = $data['idPlace'];
			$dates = $data['dates'];
			$result = '';
			try {
				$allGroups = array();
				foreach($dates as $date){
					$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
					$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
					$allGroups = RESA_Group::getAllData(
						array('startDate' => $startDate,
							'endDate' => $endDate,
							'idService' => $idService,
							'idPlace' => $idPlace,
							'name' => $newGroup->name)
					);
					if(count($allGroups) > 0){
						throw new Exception('alreadyExist');
					}
					$group = new RESA_Group();
					$group->setIdService($idService);
					$group->setIdPlace($idPlace);
					$group->setStartDate($startDate);
					$group->setEndDate($endDate);
					$group->setName($newGroup->name);
					$group->setPresentation($newGroup->presentation);
					$group->setColor($newGroup->color);
					$group->setMax($newGroup->max>0?$newGroup->max:10);
					$group->save();
					array_push($allGroups, $group);
					if($startDate == $data['startDate'] && $endDate == $data['endDate']){
						$result = $group->toJSON(true);
					}
				}
				if($result != ''){
					$response->set_data(json_decode($result));
				}
				else {
					$response->set_status(404);
					$response->set_data(array('error' => 'unknow_group', 'message' => 'Unknow group'));
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'already_exist_group', 'message' => 'Group already exist'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 *
	 */
	public static function deleteGroup(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$newGroup = (object)$data['group'];
			$idService = $data['idService'];
			$idPlace = $data['idPlace'];
			$dates = $data['dates'];
			$idGroup = $data['idGroup'];
			$result = '';
			try {
				$allGroups = array();
				foreach($dates as $date){
					$startDate = (new DateTime($date['startDate']))->format('Y-m-d H:i:s');
					$endDate = (new DateTime($date['endDate']))->format('Y-m-d H:i:s');
					if($idGroup > -1 && count($dates) == 1){
						$allGroups = RESA_Group::getAllData(array('id' => $idGroup));
					}
					else {
						$allGroups = RESA_Group::getAllData(
							array('startDate' => $startDate,
								'endDate' => $endDate,
								'idService' => $idService,
								'idPlace' => $idPlace,
								'name' => $newGroup->name)
						);
					}
					for($i = 0; $i < count($allGroups); $i++){
						$group = $allGroups[$i];
						$group->deleteMe();
						if($startDate == $data['startDate'] && $endDate == $data['endDate']){
							$result = $group->toJSON(true);
						}
					}
				}
				if($result != ''){
					$response->set_data(json_decode($result));
				}
				else {
					$response->set_status(404);
					$response->set_data(array('error' => 'unknow_group', 'message' => 'Unknow group'));
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'already_exist_group', 'message' => 'Group already exist'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 *
	 */
	public static function sendPlanningToMember(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$date = $data['date'];
			$content = $data['content'];
			$idMember = $data['idMember'];
			$content = preg_replace('/\r\n|\r|\n|\t/', ' ', $content);
			$member = new RESA_Member();
			$member->loadById($idMember);

			$customer = new RESA_Customer();
		 	$customer->setLoaded(true);
			$result = RESA_Mailer::sendCustomMessage(
				new RESA_Booking(),
				$customer,
				$member->getEmail(),
				'Planning du ' . $date,
				$content,
				$currentRESAUser->getId()
			);
			if(!$result){
				$response->set_status(401);
				$response->set_data(array('error' => 'error_send', 'message' => 'Error sending notification'));
			}
			else {
				$response->set_data(array());
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
