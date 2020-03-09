<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIPlanningController
{
	public static function planningServices(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			$data = $request->get_json_params();
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 23:59:59');
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			$services = RESA_Service::getAllDataWithOrderByPlaces(array('oldService'=>false, 'activated' => 1), 'position', $places);
			$results = array();

			$minHours = intval(get_option('resa_settings_calendar_start_time'));
			$maxHours = intval(get_option('resa_settings_calendar_end_time'));
			$infoCalendarColor = get_option('resa_settings_calendar_info_calendar_color');
			$serviceConstraintColor = get_option('resa_settings_calendar_service_constraint_color');
			foreach($services as $service){
				$appointments = self::getAllAppointments($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $service);
				$timeslots = $service->getTimeslots($startDate, $endDate, true);
				$formatTimeslots = array();
				foreach($timeslots as $timeslot){
					$timeslotStartDate = RESA_Algorithms::timeToDate($timeslot->getStartTime(), $startDate);
					$timeslotEndDate = RESA_Algorithms::timeToDate($timeslot->getEndTime(), $startDate);
					if($minHours > self::getHours($timeslotStartDate)) $minHours = self::getHours($timeslotStartDate);
					if($maxHours < self::getHours($timeslotEndDate)) $maxHours = self::getHours($timeslotEndDate);
					array_push($formatTimeslots, array(
						'id' => $timeslot->getId(),
						'idService' => $service->getId(),
						'idServiceAvailability' => $timeslot->getIdServiceAvailability(),
						'startDate' => $timeslotStartDate->format('Y-m-d H:i:s'),
						'endDate' => $timeslotEndDate->format('Y-m-d H:i:s'),
						'level' => 0,
						'numbers' => 0,
						'type' => 'timeslot',
						'color' => $service->getColor()
					));
				}
				$constraints = RESA_ServiceConstraint::getAllDataWithDate($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $service->getId());
				foreach($constraints as $constraint){
					$constraintStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getStartDate());
					$constraintEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getEndDate());
					$minDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d').' '.$minHours.':00:00');
					$maxDate = DateTime::createFromFormat('Y-m-d H:i:s', $endDate->format('Y-m-d').' '.($maxHours-1).':00:00');
					if($constraintStartDate < $minDate) $constraintStartDate = $minDate;
					if($constraintEndDate > $maxDate) $constraintEndDate = $maxDate;
					array_push($formatTimeslots, array(
						'id' => $constraint->getId(),
						'idService' => $service->getId(),
						'idServiceAvailability' => -1,
						'startDate' => $constraintStartDate->format('Y-m-d H:i:s'),
						'endDate' => $constraintEndDate->format('Y-m-d H:i:s'),
						'level' => 0,
						'numbers' => 0,
						'constraint' => json_decode($constraint->toJSON()),
						'type' => 'constraint',
						'color' => $serviceConstraintColor
					));
				}
				$formatTimeslots = self::fillTimeslots($appointments, $formatTimeslots, $service);
				$formatTimeslots = self::ordinateTimeslots($formatTimeslots);
				$serviceJSON = (object)array(
					'id' => $service->getId(),
					'name' => $service->getName(),
					'places' => $service->getPlaces(),
					'color' => $service->getColor(),
					'timeslots' => $formatTimeslots
				);
				array_push($results, $serviceJSON);
			}
			$infoCalendars = RESA_InfoCalendar::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'), $places);
			$formatInfoCalendars = [];
			foreach($infoCalendars as $infoCalendar){
				$infoCalendarStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d').' '.$infoCalendar->getStartTime().':00');
				$infoCalendarEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d').' '.$infoCalendar->getEndTime().':00');
				if($minHours > $infoCalendarStartDate->format('H')) $minHours = $infoCalendarStartDate->format('H');
				if($maxHours <= $infoCalendarEndDate->format('H')) $maxHours = $infoCalendarEndDate->format('H') + 1;

				$formatInfoCalendar = (array)json_decode($infoCalendar->toJSON());
				$formatInfoCalendar['level'] = 0;
				$formatInfoCalendar['startDate'] = $infoCalendarStartDate->format('Y-m-d H:i:s');
				$formatInfoCalendar['endDate'] =  $infoCalendarEndDate->format('Y-m-d H:i:s');
				$formatInfoCalendar['color'] =  $infoCalendarColor;
				array_push($formatInfoCalendars, $formatInfoCalendar);
			}
			$formatInfoCalendars = self::ordinateTimeslots($formatInfoCalendars);
			$result = array(
				'minHours' => $minHours,
				'maxHours' => $maxHours,
				'services' => $results,
				'infoCalendars' => $formatInfoCalendars
			);
			$response->set_data($result);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	private static function getHours($datetime){
		$hours = intval($datetime->format('H'));
		$minutes = intval($datetime->format('i')) > 0;
		$hours++;
		if($hours > 24) $hours = 24;
		return $hours;
	}

	public static function planningMembers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 23:59:59');
			$members = array();
			if($currentRESAUser->isRESAStaff()){
				$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
				$canReturnAppointments = $associatedMember->getPermissionOldAppointments() || !RESA_Tools::isDatePassed($data['date'].' 00:00:00');
				if(!$canReturnAppointments) $members = array();
				else {
					if($associatedMember->getPermissionOnlyAppointments()){
						$members = RESA_Member::getAllData(array('id' => $associatedMember->getId()), 'position');
					}
					else {
						$members = RESA_Member::getAllData(array('activated' => 1), 'position');
					}
				}
			}
			else {
				$members = RESA_Member::getAllData(array('activated' => 1), 'position');
			}
			$results = array();

			$minHours = intval(get_option('resa_settings_calendar_start_time'));
			$maxHours = intval(get_option('resa_settings_calendar_end_time'));

			$serviceConstraintColor = get_option('resa_settings_calendar_service_constraint_color');
			$memberConstraints = RESA_MemberConstraint::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'));
			foreach($members as $member){
				$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
				$appointments = self::getAllMembers($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $member, $places);
				$formatAppointments = array();
				foreach($appointments as $appointment){
					$appointmentStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->startDate);
					$appointmentEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->endDate);
					if($minHours > $appointmentStartDate->format('H')) $minHours = $appointmentStartDate->format('H');
					if($maxHours <= $appointmentEndDate->format('H')) $maxHours = $appointmentEndDate->format('H') + 1;

					$service = new RESA_Service();
					$service->loadByIdLastVersion($appointment->idService); //TODO can uptimizated

					array_push($formatAppointments, array(
						'id' => $appointment->id,
						'idService' => $service->getId(),
						'service' => $service->getName(),
						'color' => $service->getColor(),
						'idMember' => $appointment->idMember,
						'startDate' => $appointment->startDate,
						'endDate' => $appointment->endDate,
						'level' => 0,
						'numbers' => $appointment->numbers,
						'type' => 'timeslot',
						'attribuated' => $appointment->attribuated,
					));
				}
				foreach($memberConstraints as $constraint){
					if($constraint->getIdMember() == $member->getId()){
						$constraintStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getStartDate());
						$constraintEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getEndDate());
						//if($minHours > $constraintStartDate->format('H')) $minHours = $constraintStartDate->format('H');
						//if($maxHours <= $constraintEndDate->format('H')) $maxHours = $constraintEndDate->format('H') + 1;

						if($constraintStartDate < $startDate){
							$constraintStartDate = clone $startDate;
						}
						if($constraintEndDate > $endDate){
							$constraintEndDate = clone $endDate;
						}

						array_push($formatAppointments, array(
							'id' => $constraint->getId(),
							'idMember' => $member->getId(),
							'startDate' => $constraintStartDate->format('Y-m-d H:i:s'),
							'endDate' => $constraintEndDate->format('Y-m-d H:i:s'),
							'level' => 0,
							'numbers' => 0,
							'constraint' => json_decode($constraint->toJSON()),
							'type' => 'constraint',
							'name' => 'Contrainte',
							'color' => $serviceConstraintColor
						));
					}
				}
				$formatAppointments = self::ordinateTimeslots($formatAppointments);
				$memberJSON = (object)array(
					'id' => $member->getId(),
					'nickname' => $member->getNickname(),
					'appointments' => $formatAppointments
				);
				array_push($results, $memberJSON);
			}
			$json = '{
				"minHours":' . $minHours . ',
				"maxHours":' . $maxHours . ',
				"members":' . json_encode($results) . '
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * planning groups
	 */
	public static function planningGroups(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 23:59:59');
			$members = array();
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			if($currentRESAUser->isRESAStaff()){
				$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
				if($associatedMember->getPermissionOnlyAppointments()){
					$members = RESA_Member::getAllData(array('id' => $associatedMember->getId()), 'position');
					if(count($members) > 0){
						$places =	$members[0]->getPlaces();
					}
				}
				else {
					$members = RESA_Member::getAllData(array('activated' => 1), 'position');
				}

			}
			else {
				$members = RESA_Member::getAllData(array('activated' => 1), 'position');
			}
			$results = array();

			$minHours = intval(get_option('resa_settings_calendar_start_time'));
			$maxHours = intval(get_option('resa_settings_calendar_end_time'));
			$idGroups = self::getAllIdGroups($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $places);
			$groups = array();
			$formatGroupsWithoutMembers = array();
			$allIdServices = array();
			$services = array();
			foreach($idGroups as $idGroup){
				$group = new RESA_Group();
				$group->loadById($idGroup->id);
				$groupStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $group->getStartDate());
				$groupEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $group->getEndDate());
				if($minHours > $groupStartDate->format('H')) $minHours = $groupStartDate->format('H');
				if($maxHours <= $groupEndDate->format('H')) $maxHours = $groupEndDate->format('H') + 1;
				array_push($groups, $group);
				if(!in_array($group->getIdService(), $allIdServices)){
					$service = new RESA_Service();
					$service->loadByIdLastVersion($group->getIdService());
					array_push($services, $service);
					$allIdServices = array_merge($allIdServices, $service->getAllIds());
				}
				if($group->haveNoMemberAttribuated() && !$currentRESAUser->isRESAStaff()){
					$formatGroup = (array)json_decode($group->toJSON(true));
					$formatGroup['level'] = 0;
					array_push($formatGroupsWithoutMembers, $formatGroup);
				}
			}
			$formatGroupsWithoutMembers = self::ordinateTimeslots($formatGroupsWithoutMembers);
			$participantsGroupByDates = self::getAllParticipants($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $places);
			foreach($participantsGroupByDates as $participantsGroupByDate){
				foreach($participantsGroupByDate->participants as $participant){
					if(!in_array($participant->idService, $allIdServices)){
						$service = new RESA_Service();
						$service->loadByIdLastVersion($participant->idService);
						$participant->idService = $service->getId();
						array_push($services, $service);
						$allIdServices = array_merge($allIdServices, $service->getAllIds());
					}
					else {
						foreach ($services as $service) {
							if($service->isSameService($participant->idService)){
								$participant->idService = $service->getId();
							}
						}
					}
				}
			}
			$infoCalendarColor = get_option('resa_settings_calendar_info_calendar_color');
			$serviceConstraintColor = get_option('resa_settings_calendar_service_constraint_color');
			$memberConstraints = RESA_MemberConstraint::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'));
			$infoCalendars = RESA_InfoCalendar::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'), $places);
			$formatInfoCalendars = [];
			foreach($infoCalendars as $infoCalendar){
				$infoCalendarStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d').' '.$infoCalendar->getStartTime().':00');
				$infoCalendarEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d').' '.$infoCalendar->getEndTime().':00');
				//if($minHours > $infoCalendarStartDate->format('H')) $minHours = $infoCalendarStartDate->format('H');
				//if($maxHours <= $infoCalendarEndDate->format('H')) $maxHours = $infoCalendarEndDate->format('H') + 1;

				$formatInfoCalendar = (array)json_decode($infoCalendar->toJSON());
				$formatInfoCalendar['level'] = 0;
				$formatInfoCalendar['startDate'] = $infoCalendarStartDate->format('Y-m-d H:i:s');
				$formatInfoCalendar['endDate'] =  $infoCalendarEndDate->format('Y-m-d H:i:s');
				$formatInfoCalendar['color'] =  $infoCalendarColor;
				array_push($formatInfoCalendars, $formatInfoCalendar);
			}
			$formatInfoCalendars = self::ordinateTimeslots($formatInfoCalendars);
			foreach($members as $member){
				if(($member->isInPlaces($places) || $member->isNoPlace() || (count($places) == 0)) && $member->isAvailable($startDate)){
					$formatGroups = array();
					foreach($groups as $group){
						if(in_array($member->getId(), $group->getIdMembers())){
							$formatGroup = (array)json_decode($group->toJSON(true));
							$formatGroup['level'] = 0;
							$formatGroup['type'] = 'group';
							array_push($formatGroups, $formatGroup);
						}
					}
					$formatConstraints = array();
					foreach($memberConstraints as $constraint){
						if($constraint->getIdMember() == $member->getId()){
							$constraintStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getStartDate());
							$constraintEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $constraint->getEndDate());
							//if($minHours > $constraintStartDate->format('H')) $minHours = $constraintStartDate->format('H');
							//if($maxHours <= $constraintEndDate->format('H')) $maxHours = $constraintEndDate->format('H') + 1;

							if($constraintStartDate < $startDate){
								$constraintStartDate = clone $startDate;
							}
							if($constraintEndDate > $endDate){
								$constraintEndDate = clone $endDate;
							}

							array_push($formatGroups, array(
								'id' => $constraint->getId(),
								'idMember' => $member->getId(),
								'startDate' => $constraintStartDate->format('Y-m-d H:i:s'),
								'endDate' =>  $constraintEndDate->format('Y-m-d H:i:s'),
								'level' => 0,
								'numbers' => 0,
								'constraint' => json_decode($constraint->toJSON()),
								'type' => 'constraint',
								'name' => 'Contrainte',
								'color' => $serviceConstraintColor
							));
							$formatConstraint = (array)json_decode($constraint->toJSON());
							$formatConstraint['name'] = 'Contrainte';
							$formatConstraint['level'] = 0;
							$formatConstraint['type'] = 'constraint';
							$formatConstraint['color'] = $serviceConstraintColor;
							array_push($formatConstraints, $formatConstraint);
						}
					}
					$formatGroups = self::ordinateTimeslots($formatGroups, true);
					$formatConstraints = self::ordinateTimeslots($formatConstraints);
					$memberJSON = (object)array(
						'id' => $member->getId(),
						'places' => $member->getPlaces(),
						'nickname' => $member->getNickname(),
						'groups' => $formatGroups,
						'constraints' => $formatConstraints
					);
					array_push($results, $memberJSON);
				}
			}

			$result = array(
				'minHours' => $minHours,
				'maxHours' => $maxHours,
				'services' => json_decode(RESA_Tools::formatJSONArray($services)),
				'groups' => json_decode(RESA_Tools::formatJSONArray($groups)),
				'members' => $results,
				'groupsWithoutMembers' => $formatGroupsWithoutMembers,
				'participants' => $participantsGroupByDates,
				'infoCalendars' =>  $formatInfoCalendars
			);

			$response->set_data((object)$result);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * fill timeslot with number of appointment and create custom timeslot if not found
	 */
	private static function fillTimeslots($appointments, $timeslots, $service){
		foreach($appointments as $appointment){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->startDate);
			$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->endDate);
			$found = false;
			for($i = 0; $i < count($timeslots); $i++){
				$timeslot = $timeslots[$i];
				$startDateT = DateTime::createFromFormat('Y-m-d H:i:s', $timeslot['startDate']);
				$endDateT = DateTime::createFromFormat('Y-m-d H:i:s', $timeslot['endDate']);
				if($startDate == $startDateT && $endDate == $endDateT){
					$timeslots[$i]['numbers'] += $appointment->numbers;
					$found = true;
				}
			}
			if(!$found){
				array_push($timeslots, array(
					'id' => -1,
					'idService' => $service->getId(),
					'startDate' => $appointment->startDate,
					'endDate' => $appointment->endDate,
					'level' => 0,
					'numbers' => $appointment->numbers,
					'type' => 'timeslot',
					'color' => $service->getColor()
				));
			}

		}
		return $timeslots;
	}

	/**
	 * ordinate timeslots with startdate and endDate
	 */
	private static function ordinateTimeslots($timeslots, $forceConstraint = false){
		$listTimeslots = [];
		for($i = 0; $i < count($timeslots); $i++){
			$listLevelCuts = [];
			$timeslot = $timeslots[$i];
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $timeslot['startDate']);
			$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $timeslot['endDate']);
			if(!isset($timeslot['type']) || ($timeslot['type'] != 'constraint' || $forceConstraint)){
				for($j = 0; $j < count($listTimeslots); $j++){
					$timeslotB = $listTimeslots[$j];
					$startDateB = DateTime::createFromFormat('Y-m-d H:i:s', $timeslotB['startDate']);
					$endDateB = DateTime::createFromFormat('Y-m-d H:i:s', $timeslotB['endDate']);
					if(($startDate < $startDateB && $startDateB < $endDate) ||
						($startDate < $endDateB && $endDateB < $endDate) ||
						($startDateB <= $startDate && $endDate <= $endDateB) ||
						($startDateB < $startDate && $startDate < $endDateB)){
						array_push($listLevelCuts, $timeslotB['level']);
					}
				}
				for($level = 0; $level < 100; $level++){
					if(!in_array($level, $listLevelCuts)){
						$timeslot['level'] = $level;
						break;
					}
				}
			}
			else {
				$timeslot['level'] = 100;
			}
			array_push($listTimeslots, $timeslot);
		}

		usort($listTimeslots, function($timeslotA, $timeslotB){
			$res = $timeslotA['level'] - $timeslotB['level'];
			if($res == 0){
				$startDateA = DateTime::createFromFormat('Y-m-d H:i:s', $timeslotA['startDate']);
				$startDateB = DateTime::createFromFormat('Y-m-d H:i:s', $timeslotB['startDate']);
				$res = $startDateA->getTimestamp() - $startDateB->getTimestamp();
			}
			return $res;
		});


		return $listTimeslots;
	}

	/**
	 * get all appointments
	 */
	private static function getAllAppointments($startDate, $endDate, $service){
		global $wpdb;
		$allIds = $service->getLinkOldServices();
		if(!empty($allIds)) $allIds .= ',';
		$allIds .= $service->getId();
		$results = $wpdb->get_results('SELECT id,idService,startDate,endDate,numbers FROM ' . $wpdb->prefix. 'resa_appointment WHERE startDate >= \''.$startDate.'\' AND startDate <= \''.$endDate.'\' AND state<>\'updated\' AND state<>\'cancelled\' AND state<>\'abandonned\' AND idService IN ('.$allIds.') ORDER BY startDate');
		return $results;
	}

	private static function getAllMembers($startDate, $endDate, $member, $idPlaces){
		global $wpdb;
		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$results = $wpdb->get_results('SELECT id,idService,startDate,endDate,appointment.numbers, member.idMember, member.number as attribuated
				FROM ' . $wpdb->prefix. 'resa_appointment as appointment
				INNER JOIN ' . $wpdb->prefix. 'resa_appointment_member as member ON appointment.startDate >= \''.$startDate.'\' AND appointment.startDate <= \''.$endDate.'\' AND appointment.state<>\'updated\' AND appointment.state<>\'cancelled\' AND appointment.state<>\'abandonned\' AND member.idMember='.$member->getId().' AND member.idAppointment = appointment.id '. $filterPlace .'
				ORDER BY appointment.startDate');
		return $results;
	}

	private static function getAllIdGroups($startDate, $endDate, $idPlaces){
		global $wpdb;
		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$results = $wpdb->get_results('SELECT id FROM ' . $wpdb->prefix. 'resa_group WHERE startDate >= \''.$startDate.'\' AND startDate <= \''.$endDate.'\' '. $filterPlace.' ORDER BY startDate');
		return $results;
	}

	private static function findParticipantsLine($allParticipants, $startDate, $endDate){
		foreach($allParticipants as $participant){
			if($participant->startDate === $startDate && $participant->endDate === $endDate){
				return $participant;
			}
		}
		return null;
	}

	private static function getAllParticipants($startDate, $endDate, $idPlaces){
		global $wpdb;
		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$results = $wpdb->get_results('SELECT appointment.id, appointment.idService, startDate, endDate, number_price.participants, servicePrice.participantsParameter FROM ' . $wpdb->prefix. 'resa_appointment as appointment
				INNER JOIN ' . $wpdb->prefix. 'resa_appointment_number_price as number_price ON appointment.startDate >= \''.$startDate.'\' AND appointment.startDate <= \''.$endDate.'\' AND (appointment.state<>\'updated\' AND appointment.state<>\'cancelled\' AND appointment.state<>\'abandonned\') AND (appointment.id = number_price.idAppointment) '. $filterPlace .'
				INNER JOIN ' . $wpdb->prefix. 'resa_service_price as servicePrice ON servicePrice.id = number_price.idPrice ORDER BY appointment.startDate');
		$allParticipants = array();
		foreach($results as $result){
			$result->participants = unserialize($result->participants);
			$participants = self::findParticipantsLine($allParticipants, $result->startDate, $result->endDate);
			if(!isset($participants)){
				$participants = (object)array('startDate' => $result->startDate, 'endDate' => $result->endDate, 'participants' => array());
				array_push($allParticipants, $participants);
			}
			foreach($result->participants as $participant){
				$participant->idAppointment = $result->id;
				$participant->idService = $result->idService;
				array_push($participants->participants, $participant);
			}
		}
		return $allParticipants;
	}


	public static function settings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'places' => unserialize(get_option('resa_settings_places')),
				'custom_tags' => unserialize(get_option('resa_settings_custom_tags')),
				'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),
				'currency' => get_option('resa_settings_payment_currency'),
				'calendar_start_time' => intval(get_option('resa_settings_calendar_start_time')),
				'calendar_end_time' => intval(get_option('resa_settings_calendar_end_time')),
				'calendar_split_time' => intval(get_option('resa_settings_calendar_split_time')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staffManagementActivated' => RESA_Variables::isStaffManagementActivated(),
				'groupManagementActivated' => RESA_Variables::isGroupsManagementActivated(),
				'tabsActivities' => unserialize(get_option('resa_planning_tabs_activities', serialize(array()))),
				'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
				'idPaymentsTypeToName' => RESA_Variables::idPaymentsTypeToName(),
				'statesList' => RESA_Variables::statesList(),
				'caisse_online_activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true' && class_exists('RESA_CaisseOnline'),
				'caisse_online_site_url'=> class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getURLCaisse():'',
				'caisse_online_server_url'=>class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getBaseURL():'',
				'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters'))
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

	public static function timeslots(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idActivity = $request['idActivity'];
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $request['date'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $request['date'].' 23:59:59');

			$activity = new RESA_Service();
			$activity->loadById($idActivity);
			if($activity->isLoaded() && $activity->isActivated() && !$activity->getOldService()){
				$timeslots = RESA_Algorithms::getTimeslots($startDate, $endDate, $activity, array(), -1, true, '');
				for($i = 0; $i < count($timeslots); $i++){
					$timeslot = $timeslots[$i];
          unset($timeslots[$i]['members']);
					$timeslotStartDate = RESA_Algorithms::timeToDate($timeslot['startTime'], $startDate);
					$timeslotEndDate = RESA_Algorithms::timeToDate($timeslot['endTime'], $startDate);
					$timeslots[$i]['startDate'] = $timeslotStartDate->format('Y-m-d H:i:s');
					$timeslots[$i]['endDate'] = $timeslotEndDate->format('Y-m-d H:i:s');
				}
				$response->set_data($timeslots);
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'bad_activity', 'message' => 'Activity not found'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function timeslot(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idActivity = $request['idActivity'];
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $request['startDate']);
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $request['endDate']);

			$activity = new RESA_Service();
			$activity->loadByIdLastVersion($idActivity);
			$timeslot = RESA_Algorithms::getTimeslot($startDate, $endDate, $activity, array(), -1, true, '');
			$response->set_data($timeslot);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function appointments(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idActivity = $data['idActivity'];
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['dateStart']);
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['dateEnd']);
			$nbByPage = $data['nbByPage'];
			$page = $data['page'];

			$activity = new RESA_Service();
			$activity->loadById($idActivity);
			$appointmentsFormated = array();
			if($activity->isLoaded() && $activity->isActivated() && !$activity->getOldService()){
				$allIds = $activity->getLinkOldServices();
				if(!empty($allIds)) $allIds .= ',';
				$allIds .= $activity->getId();
				$data = array(
					'startDate' => array($startDate->format('Y-m-d H:i:s'), '>='),
					'endDate' => array($endDate->format('Y-m-d H:i:s'), '<='),
					'idService' => array(explode(',', $allIds)),
					'state' => array('updated', '<>')
				);
				$appointments = RESA_Appointment::getAllDataWithLimit($data, ($page*$nbByPage).','.$nbByPage);
				$max = RESA_Appointment::countAllData($data);
				$groupActivated = RESA_Variables::isGroupsManagementActivated();

				foreach($appointments as $appointment){
					$result = (array)RESA_Booking::getBookingLite($appointment->getIdBooking());
					$customer = new RESA_Customer();
					$customer->loadById($result['idCustomer']);
					$result = array_merge($result, array(
						'id' => $result['idCreation']!=-1?$result['idCreation']:$appointment->getIdBooking(),
						'idAppointment' => $appointment->getId(),
						'askParticipants' => $activity->isAskParticipants(),
						'state' => $appointment->getState(),
						'prices' => array(),
						'customer' => $customer->toSimpleArray()
					));
					$result['quotation'] = $result['quotation']?true:false;
					$result['paymentState'] = RESA_Variables::calculateBookingPayment($result['status'], $result['paymentState']);
					$result['tags'] = $appointment->getTags();
					$result['prices'] = array();
					foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
						$price = json_decode($appointmentNumberPrice->toJSON());
						$servicePrice = new RESA_ServicePrice();
						$servicePrice->loadById($appointmentNumberPrice->getIdPrice());
						$price->name = $servicePrice->getName();
						$price->participantsParameter = $servicePrice->getParticipantsParameter();
						if($groupActivated){
							for($i = 0; $i < count($price->participants); $i++){
								$groupName = RESA_Group::getGroupNameParticipantURI($appointment->getIdPlace(), $idActivity,
									$appointment->getStartDate(), $appointment->getEndDate(), $price->participants[$i]->uri);
								$price->participants[$i]->meta_group = $groupName;
							}
						}
						array_push($result['prices'], $price);
					}
					$result['members'] = array();
					foreach ($appointment->getAppointmentMembers() as $appointmentMember) {
						$member = json_decode($appointmentMember->toJSON());
						$member->name = RESA_Member::getMemberName($appointmentMember->getIdMember());
						array_push($result['members'], $member);
					}
					array_push($appointmentsFormated, (object)$result);
				}
				$json = $appointmentsFormated;
				//Ajouter count
				$result = array(
					'appointments' => $appointmentsFormated,
					'max' => $max
				);

				$response->set_data($result);
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'bad_activity', 'message' => 'Activity not found'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getAppointment(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idAppointment = $request['id'];
			$appointment = new RESA_Appointment();
			$appointment->loadById($idAppointment);
			$groupActivated = RESA_Variables::isGroupsManagementActivated();
			//SEE appointments
			$activity = RESA_Service::getServiceLite($appointment->getIdService());
			$result = (array)RESA_Booking::getBookingLite($appointment->getIdBooking());
			$customer = new RESA_Customer();
			$customer->loadById($result['idCustomer']);
			$result = array_merge($result, array(
				'id' => $result['idCreation']!=-1?$result['idCreation']:$appointment->getIdBooking(),
				'idAppointment' => $appointment->getId(),
				'askParticipants' => $activity->askParticipants,
				'state' => $appointment->getState(),
				'prices' => array(),
				'customer' => $customer->toSimpleArray()
			));
			$result['quotation'] = $result['quotation']?true:false;
			$result['paymentState'] = RESA_Variables::calculateBookingPayment($result['status'], $result['paymentState']);
			$result['tags'] = $appointment->getTags();
			$result['prices'] = array();
			foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
				$price = json_decode($appointmentNumberPrice->toJSON());
				$servicePrice = new RESA_ServicePrice();
				$servicePrice->loadById($appointmentNumberPrice->getIdPrice());
				$price->name = $servicePrice->getName();
				$price->participantsParameter = $servicePrice->getParticipantsParameter();
				if($groupActivated){
					for($i = 0; $i < count($price->participants); $i++){
						$groupName = RESA_Group::getGroupNameParticipantURI($appointment->getIdPlace(), $activity->id,
							$appointment->getStartDate(), $appointment->getEndDate(), $price->participants[$i]->uri);
						$price->participants[$i]->meta_group = $groupName;
					}
				}
				array_push($result['prices'], $price);
			}
			$result['members'] = array();
			foreach ($appointment->getAppointmentMembers() as $appointmentMember) {
				$member = json_decode($appointmentMember->toJSON());
				$member->name = RESA_Member::getMemberName($appointmentMember->getIdMember());
				array_push($result['members'], $member);
			}
			if($currentRESAUser->isRESAStaff()){
				$result['note'] = $result['staffNote'];
				$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
				if(!$associatedMember->getPermissionDisplayCustomer()){ $result['customer'] = null; }
				if(!$associatedMember->getPermissionDisplayTotal()){
					$result['totalPrice'] = null;
					for($i = 0; $i < count($result['numberPrices']); $i++){
						$result['numberPrices'][$i]->totalPrice = null;
					}
				}
				if(!$associatedMember->getPermissionDisplayPayments()){ $result['paymentState'] = null; }
				if(!$associatedMember->getPermissionDisplayNumbers()){
					$result['numbers'] = 0;
					for($i = 0; $i < count($result['numberPrices']); $i++){
						$result['numberPrices'][$i]->number = null;
					}
				}
			}
			$response->set_data($result);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * return all values needed to generate the booking editor
	 */
	public static function bookingEditor(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();

			$modelCustomer = new RESA_Customer();
			$settings = array(
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'countries' => json_decode(RESA_Variables::getJSONCountries()),
				'languages' => unserialize(get_option('resa_settings_languages')),
				'allLanguages' => RESA_Variables::getLanguages(),
				'equipments_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_equipments_management_actived'))=='true',
				'groups_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_groups_management_actived'))=='true',
				'staff_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_staff_management_actived'))=='true',
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'currency' => get_option('resa_settings_payment_currency'),
				'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),
				'custom_tags' => unserialize(get_option('resa_settings_custom_tags')),
				'places' => unserialize(get_option('resa_settings_places')),
				'vat_list'=>unserialize(get_option('resa_settings_vat_list')),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters'))
			);

			$json = '{
				"settings":' . json_encode($settings) . ',
				"modelCustomer":' . $modelCustomer->toJSON() . '
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * return all values needed to generate the booking editor
	 */
	public static function calculateReductions(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();
			$allReductions = array();
			$mapIdClientObjectReduction = array();
			if(isset($data['idCustomer']) && isset($data['servicesParameters']) && isset($data['idBooking']) && isset($data['allowInconsistencies']) && isset($data['frontForm'])){
				$servicesParameters = json_decode(stripslashes(wp_kses_post($data['servicesParameters'])));
				$idCustomer = sanitize_text_field($data['idCustomer']);
				$idBooking = sanitize_text_field($data['idBooking']);
				$allowInconsistencies = sanitize_text_field($data['allowInconsistencies']);
				$frontForm = sanitize_text_field($data['frontForm']);
				$appointments = array();

				$customer = new RESA_Customer();
				$customer->loadById($idCustomer);
				try{
					$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters, $customer, true, $idBooking, (true && !$frontForm), $customer->getTypeAccount());
				}
				catch(Exception $e){
					Logger::DEBUG($e);
				}
				$couponsList = array();
				if(isset($data['couponsList'])) $couponsList = json_decode(stripslashes(wp_kses_post($data['couponsList'])));
				$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer,'', (true && !$frontForm));
				$allReductions = $result['reductions'];
				$mapIdClientObjectReduction = $result['mapIdClientObjectReduction'];

				foreach($couponsList as $coupon){
					if(!empty($coupon)){
						$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, $coupon, (true && !$frontForm));
						$allReductions = array_merge($allReductions, $result['reductions']);
						foreach($result['mapIdClientObjectReduction'] as $id => $params){
							if(!isset($mapIdClientObjectReduction[$id])){
								$mapIdClientObjectReduction[$id] = [];
							}
							$mapIdClientObjectReduction[$id] = array_merge($mapIdClientObjectReduction[$id], $params);
						}
					}
				}
			}

			$json = '{
				"reductions":'.RESA_Tools::formatJSONArray($allReductions).',
				"mapIdClientObjectReduction":'.json_encode($mapIdClientObjectReduction).'}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}



	/**
	 * return all values needed to generate the add booking editor
	 */
	public static function addBookingEditor(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();
			try {
				if(isset($data['customer']) && isset($data['frontForm']) && isset($data['idForm']) && isset($data['typePayment']) && isset($data['servicesParameters'])){
					$admin = true;
					$customerJSON = json_decode($data['customer']);
					//Logger::DEBUG(print_r($customerJSON, true));
					$frontForm = $data['frontForm'];
					$idForm = $data['idForm'];
					$typePayment = $data['typePayment'];
					$customer = new RESA_Customer();
					//If Caisse online not response
					if((($frontForm || !$admin) && RESA_Variables::isTypePaymentOnline($typePayment) && ($typePayment!='swikly') && (!class_exists('RESA_CaisseOnline') || !RESA_CaisseOnline::getInstance()->isCaisseOnlineStatusOk()))){
						throw new Exception(__('payment_online_error', 'resa'));
					}
					if(($admin && !$frontForm) && isset($customerJSON->ID)){
						$customer->loadById($customerJSON->ID);
					}
					else if($frontForm && $currentRESAUser->isLoaded()){
						$customer = $currentRESAUser;
					}
					$typeAccount = $customerJSON->typeAccount;
					if($customer->isLoaded()){
						$typeAccount = $customer->getTypeAccount();
					}
					if(!RESA_Customer::isPaymentTypeAuthorized($typeAccount, $typePayment, unserialize(get_option('resa_settings_types_accounts')))){
							throw new Exception(__('type_payment_unauthorized_error', 'resa'));
					}

					if((!$frontForm && $customer->getId() <= 0) || ($customerJSON->ID == -1 && !$currentRESAUser->isLoaded())){
						if(isset($customerJSON) && isset($customerJSON->password) && !empty($customerJSON->password) &&
							isset($customerJSON->lastName) && !empty($customerJSON->lastName) &&
							isset($customerJSON->email) && !empty($customerJSON->email)){
							//Create new user.
							$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
							if(!username_exists($customerJSON->login) && !email_exists($customerJSON->email) && RESA_Customer::isOkPasswordFormat($customerJSON->password)){
								$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email);
								if($customer->isLoaded()){
									$customerJSON->ID = $customer->getId();
									$customer->fromJSON($customerJSON);
									$customer->setRole(RESA_Variables::getCustomerRole());
									$customer->save();
									RESA_Mailer::sentMessageAccountCreation($customer);
									RESA_Customer::authenticate($customerJSON->login, $customerJSON->password);
								} else throw new Exception(__('authentication_or_register_error', 'resa'));
							}
							else {
								//Connection
								$customer = RESA_Customer::authenticate($customerJSON->login, $customerJSON->password);
								if(!$customer->isLoaded()){
									if(is_numeric(username_exists($customerJSON->login)) || is_numeric(email_exists($customerJSON->email))){
										if(is_numeric(email_exists($customerJSON->email))) throw new Exception(__('email_already_exist_error', 'resa'));
										else if(is_numeric(username_exists($customerJSON->login))) throw new Exception(__('login_already_exist_error', 'resa'));
										else if(!RESA_Customer::isOkPasswordFormat($customerJSON->password)) throw new Exception(__('bad_password_error', 'resa'));
									}
									else throw new Exception(__('authentication_error', 'resa'));
								}
							}
						}
						else if(isset($customerJSON) &&
							isset($customerJSON->email) && !empty($customerJSON->email) &&
							isset($customerJSON->idFacebook) && !empty($customerJSON->idFacebook) &&
							class_exists('RESA_FacebookLogin') && get_option('resa_facebook_activated', false) && RESA_FacebookLogin::isOkCustomer($customerJSON)){
							$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
							$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email);
							if($customer->isLoaded()){
								$customerJSON->ID = $customer->getId();
								$customer->fromJSON($customerJSON);
								$customer->setRole(RESA_Variables::getCustomerRole());
								$customer->save();
								RESA_Mailer::sentMessageAccountCreation($customer);
								RESA_Customer::authenticate($customer->getLogin(), $customer->getPassword());
							}else throw new Exception(__('authentication_or_register_error', 'resa'));
						}
						else throw new Exception(__('authentication_or_register_error', 'resa'));
					}
					if($customer->isNew()){
						throw new Exception(__('authentication_or_register_reload_error', 'resa'));
					}
					if($customer->isAskAccount()){
						throw new Exception(__('not_validate_account_error', 'resa'));
					}

					$idBooking = -1;
					if(isset($data['idBooking'])) $idBooking = $data['idBooking'];
					$isNewBooking = ($idBooking == -1);
					$servicesParameters = json_decode($data['servicesParameters']);
					//Logger::DEBUG(print_r($servicesParameters, true));
					$quotation = false;
					if(isset($data['quotation'])) $quotation = $data['quotation'];
					if($admin && !$frontForm && isset($data['allowInconsistencies'])){
						$allowInconsistencies = $data['allowInconsistencies'];
					}
					else {
						$allowInconsistencies = false;
					}
					$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters, $customer, $allowInconsistencies, $idBooking, ($admin && !$frontForm), $customer->getTypeAccount());

					//reductions.
					$couponsList = array();
					if(isset($data['couponsList'])) $couponsList = json_decode(stripslashes(wp_kses_post($data['couponsList'])));
					$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, '', ($admin && !$frontForm));
					$allReductions = $result['reductions'];
					$mapIdClientObjectReduction = $result['mapIdClientObjectReduction'];
					foreach($couponsList as $coupon){
						if(!empty($coupon)){
							$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, $coupon, ($admin && !$frontForm));
							$allReductions = array_merge($allReductions, $result['reductions']);
							foreach($result['mapIdClientObjectReduction'] as $id => $params){
								if(!isset($mapIdClientObjectReduction[$id])){
									$mapIdClientObjectReduction[$id] = [];
								}
								$mapIdClientObjectReduction[$id] = array_merge($mapIdClientObjectReduction[$id], $params);
							}
						}
					}
					//$reductions = RESA_Algorithms::returnReductionsNotCombinables($appointments, $allReductions, $mapIdClientObjectReduction);
					$booking = RESA_Algorithms::fillBookingReductions($appointments, $allReductions, $mapIdClientObjectReduction);
					if($customer->getId() == -1){ throw new Exception(__('authentication_or_register_reload_error', 'resa')); }
					$booking->setIdCustomer($customer->getId());
					$booking->setIdUserCreator($customer->getId());
					if($currentRESAUser->getId()!=-1) {
						$booking->setIdUserCreator($currentRESAUser->getId());
					}
					if(!empty(get_option('resa_settings_tva'))){
						$booking->setTVA(get_option('resa_settings_tva'));
					}
					$bookingCustomReductions = array();
					$customReductions = json_decode($data['customReductions']);
					foreach($customReductions as $customReduction){
						$bookingCustomReduction = new RESA_BookingCustomReduction();
						$bookingCustomReduction->fromJSON($customReduction);
						array_push($bookingCustomReductions, $bookingCustomReduction);
					}
					$booking->setBookingCustomReductions($bookingCustomReductions);
					$booking->savePrices();
					$sendMessage = $data['sendEmailToCustomer'];
					if(!$isNewBooking){
						$oldBooking = new RESA_Booking();
						$oldBooking->loadById($idBooking);
						if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
							if(!$oldBooking->isOldBooking()){
								$oldBooking->setOldBooking(true);
								foreach($oldBooking->getAppointments() as $appointment){
									$appointment->setState('updated');
								}
								$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
								$booking->addLinkOldBookings($oldBooking->getId());
								$booking->setAdvancePayment($oldBooking->getAdvancePayment());
								$booking->setCreationDate($oldBooking->getCreationDate());
								$booking->setIdUserCreator($oldBooking->getIdUserCreator());
								$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
								$booking->setTransactionId($oldBooking->getTransactionId());
								$booking->setIdCreation($oldBooking->getIdCreation());
								$booking->setPaymentState($oldBooking->getPaymentState());
								$booking->setQuotationRequest($oldBooking->isQuotationRequest());
								$booking->setAlreadySentEmail($oldBooking->isAlreadySentEmail());
								$oldBooking->save(false);
							}
							else {
								throw new Exception(__('create_new_version_bookings_error', 'resa'));
							}
						}
					}

					$currentUrl = $data['currentUrl'];
					$bookingNote = '';
					if(isset($data['bookingNote'])){
						$bookingNote = RESA_Tools::formatTextaeraHTML($data['bookingNote']);
					}
					$bookingPublicNote = '';
					if(isset($data['bookingPublicNote'])){
						$bookingPublicNote = RESA_Tools::formatTextaeraHTML($data['bookingPublicNote']);
					}
					$bookingStaffNote = '';
					if(isset($data['bookingStaffNote'])){
						$bookingStaffNote = RESA_Tools::formatTextaeraHTML($data['bookingStaffNote']);
					}
					$bookingCustomerNote = '';
					if(isset($data['bookingCustomerNote'])){
						$bookingCustomerNote = RESA_Tools::formatTextaeraHTML($data['bookingCustomerNote']);
					}

					//Logger::DEBUG($booking->toJSON());
					$statesParameters = unserialize(get_option('resa_settings_states_parameters'));
					if($booking->isQuotation()){ $typePayment = ''; }
					$advancePaymentSelected = false;
					if(isset($data['advancePayment'])){
						$advancePaymentSelected = $data['advancePayment'];
					}
					if($isNewBooking) {
						if($advancePaymentSelected){
							$booking->setAdvancePayment($booking->calculateTotalPriceForm($statesParameters, true));
						}
						$booking->setTypePaymentChosen($typePayment);
					}
					$booking->setQuotation($quotation);
					if($quotation && $frontForm){
						$booking->setQuotationRequest(true);
					}
					$booking->setAllowInconsistencies($allowInconsistencies);
					$booking->setNote($bookingNote);
					$booking->setPublicNote($bookingPublicNote);
					$booking->setStaffNote($bookingStaffNote);
					$booking->setCustomerNote($bookingCustomerNote);
					$booking->setNew();
					$booking->save(!$frontForm);
					$booking->reload();

					if(($sendMessage || $frontForm) && ($typePayment != 'systempay' && $typePayment != 'paypal' && $typePayment != 'monetico' && $typePayment != 'stripe' && $typePayment != 'stripeConnect'  && $typePayment != 'paybox')){
						if(!$booking->isQuotation()){
							RESA_Mailer::sendMessageBooking($booking, true);
							$booking->setAlreadySentEmail(true);
						}
						else {
							RESA_Mailer::sendMessageQuotationCustomer($booking);
						}
					}
					else if($sendMessage && $booking->isQuotation()){
						RESA_Mailer::sendMessageQuotation($booking);
					}


					if($isNewBooking){
						if($frontForm && $booking->isNotQuotation())
							$logNotification = RESA_Algorithms::generateLogNotification(0, $booking, $customer, $currentRESAUser);
						else if($frontForm && $booking->isQuotation())
							$logNotification = RESA_Algorithms::generateLogNotification(1, $booking, $customer, $currentRESAUser);
						else if(!$frontForm && $booking->isNotQuotation())
							$logNotification = RESA_Algorithms::generateLogNotification(2, $booking, $customer, $currentRESAUser);
						else
							$logNotification = RESA_Algorithms::generateLogNotification(3, $booking, $customer, $currentRESAUser);
					}
					else {
						if(!$frontForm && $booking->isNotQuotation())
							$logNotification = RESA_Algorithms::generateLogNotification(4, $booking, $customer, $currentRESAUser);
						else
							$logNotification = RESA_Algorithms::generateLogNotification(5, $booking, $customer, $currentRESAUser);
					}
					if(isset($logNotification))	$logNotification->save();

					//Save participants
					$nbParticipants = 0;
					$booking->generateParticipantsUri($customer->getId());
					$participants = $booking->getParticipants();
					$nbParticipants += count($participants);
					$customer->mergeParticipants($participants);
					if($nbParticipants > 0){
						$booking->clearModificationDate();
						$booking->save(false);
						$booking->reload();
						if(RESA_Variables::isGroupsManagementActivated()){
							foreach($booking->getAppointments() as $appointment){
								RESA_Algorithms::createGroupsIfNecessary($appointment, $booking);
								RESA_Algorithms::addParticipantsInGroups($appointment);
							}
						}
						$customer->save(false);
					}
					$successUrl = get_option('resa_settings_payment_return_url_success');
					if(!get_option('resa_settings_payment_activate')){
						$successUrl = '';
					}
					if($booking->isQuotation()){
						$successUrl = get_option('resa_settings_quotation_return_url_success');
					}

					$informationConfirmationText = unserialize(get_option('resa_settings_form_informations_confirmation_text'));
					if($booking->isQuotation()){
						$informationConfirmationText = unserialize(get_option('resa_settings_form_quotation_informations_confirmation_text'));
					}
					if(!empty($idForm)){
						$form = new RESA_Form();
						$form->loadById(substr($idForm, strlen('form')));
						if($form->isLoaded()){
							$informationConfirmationText = $form->getInformationsConfirmationText();
						}
					}
					$confirmationText = RESA_Mailer::formatMessage($booking, $customer, RESA_Tools::getTextByLocale($informationConfirmationText, get_locale()), true);
					RESA_Session::store('RESA_booking', $booking->getId());

					$payment = '';
					if($typePayment == 'systempay' && class_exists('RESA_Systempay')){
						$payment = RESA_Systempay::systempay($booking, $currentUrl, $advancePaymentSelected, true);
					} else if($typePayment == 'paypal' && class_exists('RESA_Paypal')){
						$payment = RESA_Paypal::paypal($booking, $currentUrl, $advancePaymentSelected, true);
					} else if($typePayment == 'monetico' && class_exists('RESA_MoneticoPayment')){
						$payment = RESA_MoneticoPayment::monetico($booking, $customer, $currentUrl, $advancePaymentSelected, true);
					}else if($typePayment == 'stripe' && class_exists('RESA_Stripe')){
						$payment = RESA_Stripe::stripe($booking, $customer, $currentUrl, $advancePaymentSelected, true);
					}else if($typePayment == 'stripeConnect' && class_exists('RESA_StripeConnect')){
						$payment = RESA_StripeConnect::stripeConnect($booking, $customer, $currentUrl, $advancePaymentSelected, true);
					}else if($typePayment == 'paybox' && class_exists('RESA_Paybox')){
						$payment = RESA_Paybox::paybox($booking, $customer, $currentUrl, $advancePaymentSelected, true);
					}else if($typePayment == 'swikly' && class_exists('RESA_Swikly')){
						$payment = RESA_Swikly::swikly($booking, $customer, $currentUrl);
					}

					//If no payment
					if(get_option('resa_settings_payment_activate') == true && $typePayment != 'onTheSpot' && $typePayment != 'later' && $booking->isNotQuotation() && $booking->stateAskPayment($statesParameters)){
						if(($frontForm || !$admin) && ($payment == '' || !isset($payment) || count($payment) == 0)){
							$booking->changeStatesAfterPayment(unserialize(get_option('resa_settings_states_parameters')));
							$booking->clearModificationDate();
							$booking->save(false);
							RESA_Mailer::sendMessageBooking($booking, true);
						}
					}

					$alerts = RESA_Alert::getAllDataWithDate(
						$booking->getAppointmentFirstDate(),
						$booking->getAppointmentEndDate(),
						array('idBooking' => array(array($booking->getId()))),
						array('idAppointment' => array($booking->getAllIdAppointments()))
					);
					$result = array(
						'booking' => $booking->getBookingLiteJSON(),
						'alerts' => json_decode(RESA_Tools::formatJSONArray($alerts)),
						'payment' => $payment,
						'successUrl' => $successUrl,
						'confirmationText' => $confirmationText);
				}
				else {
					throw new Exception("Error", 1);
				}
				$response->set_data($result);
			}
			catch(RESA_Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_token', 'message' => $e->getMessage()));
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_token', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


}
