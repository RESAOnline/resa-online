<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIBookingsController
{

	public static function getBookings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			if(isset($data['startDate']) && isset($data['endDate']) && isset($data['nbByPage']) && isset($data['page']) && isset($data['stateList']) && isset($data['paymentsList'])){
				$canReturnAppointments = true;
				if($currentRESAUser->isRESAStaff()){
					$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
					$canReturnAppointments = $associatedMember->getPermissionOldAppointments() || !RESA_Tools::isDatePassed($data['startDate']);
				}
				if($canReturnAppointments){
					$startDate = $data['startDate'];
					$endDate = $data['endDate'];
					$nbByPage = $data['nbByPage'];
					$page = $data['page'];
					$stateList = json_decode($data['stateList']);
					if(count($stateList) == 0) $stateList = array('ok', 'waiting');
					$paymentsList = json_decode($data['paymentsList']);
					if(count($paymentsList) == 0) $paymentsList = array('noPayment','advancePayment','deposit','complete');
					$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
					if($currentRESAUser->isRESAStaff()){
						$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
						if($associatedMember->getPermissionOnlyAppointments()){
							$results = RESA_Appointment::getIdBookingsWithMember($startDate, $endDate, $stateList, $paymentsList, $nbByPage, $page, $associatedMember->getId(), $places);
						}
						else {
							$results = RESA_Appointment::getIdBookings($startDate, $endDate, $stateList, $paymentsList, $nbByPage, $page, $places);
						}
					}
					else {
						$results = RESA_Appointment::getIdBookings($startDate, $endDate, $stateList, $paymentsList, $nbByPage, $page, $places);
					}
					$bookings = [];
					foreach($results['idBookings'] as $idBookingClass){
						$booking = new RESA_Booking();
						$booking->loadByLastIdCreation($idBookingClass->idBooking);
						$result = (array)$booking->getBookingLiteJSON();

						if($currentRESAUser->isRESAStaff()){
							$result['note'] = $result['staffNote'];
							$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
							if(!$associatedMember->getPermissionDisplayCustomer()){ $result['customer'] = null; }
							if(!$associatedMember->getPermissionDisplayTotal()){
								$result['totalPrice'] = null;
								foreach($result['appointments'] as $appointment){
									for($i = 0; $i < count($appointment->appointmentNumberPrices); $i++){
										$appointment->appointmentNumberPrices[$i]->totalPrice = null;
									}
								}
							}
							if(!$associatedMember->getPermissionDisplayPayments()){ $result['paymentState'] = null; }
							if(!$associatedMember->getPermissionDisplayNumbers()){
								$result['numbers'] = 0;
								foreach($result['appointments'] as $appointment){
									for($i = 0; $i < count($appointment->appointmentNumberPrices); $i++){
										$appointment->appointmentNumberPrices[$i]->number = null;
									}
								}
							}
						}
						array_push($bookings, (object)$result);
					}
					$return = array('bookings' => $bookings, 'max' => $results['max']);
					$response->set_data($return);
				}
				else {
					$response->set_status(401);
					$response->set_data(array('error' => 'not_can_access', 'message' => 'Not access'));
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

	public static function getQuotations(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($data['nbByPage']) && isset($data['page'])){
				$nbByPage = $data['nbByPage'];
				$page = $data['page'];
				$data = array('oldBooking'=>false, 'quotation' => true);

				$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
				$max = RESA_Booking::countDataWithLimit($data);
				$result = RESA_Booking::getAllDataWithLimit($data, $page * $nbByPage, $nbByPage, $places);

				$bookings = [];
				foreach($result as $booking){
					array_push($bookings, $booking->getBookingLiteJSON());
				}
				$return = array('bookings' => $bookings, 'max' => $max);
				$response->set_data($return);
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


	public static function getAppointments(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			if(isset($data['startDate']) && isset($data['endDate']) && isset($data['nbByPage']) && isset($data['page']) && isset($data['stateList'])){
				$startDate = $data['startDate'];
				$endDate = $data['endDate'];
				$nbByPage = $data['nbByPage'];
				$page = $data['page'];
				$stateList = json_decode($data['stateList']);
				if(count($stateList) == 0) $stateList = array('ok', 'waiting');
				$appointmentsFormated = array();
				$data = array(
					'startDate' => array($startDate, '>='),
					'endDate' => array($endDate, '<='),
					'state' => array($stateList)
				);
				$limit = ($nbByPage * $page) . ',' . $nbByPage;
				$canReturnAppointments = true;
				$associatedMember = null;
				if($currentRESAUser->isRESAStaff()){
					$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
					$canReturnAppointments = $associatedMember->getPermissionOldAppointments() || !RESA_Tools::isDatePassed($startDate);
				}
				if($canReturnAppointments){
					$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
					$appointments = RESA_Appointment::getAllDataWithLimit($data, $limit, $places);
					if($currentRESAUser->isRESAStaff()){
						if($associatedMember->getPermissionOnlyAppointments()){
							$appointments = RESA_Tools::filterMemberNotInAppointment($appointments, $associatedMember->getId());
						}
					}
					foreach($appointments as $appointment){
						$result = (array)RESA_Booking::getBookingLite($appointment->getIdBooking());
						$customer = new RESA_Customer();
						$customer->loadById($result['idCustomer']);
						$service = RESA_Service::getServiceLite($appointment->getIdService());
						$result = array_merge($result, array(
							'id' => $result['idCreation']!=-1?$result['idCreation']:$appointment->getIdBooking(),
							'idAppointment' => $appointment->getId(),
							'service' => RESA_Tools::getTextByLocale($service->name, get_locale()),
							'color' => $service->color,
							'askParticipants' => $service->askParticipants,
							'positionService' => $service->position,
							'startDate' => $appointment->getStartDate(),
							'endDate' => $appointment->getEndDate(),
							'state' => $appointment->getState(),
							'numberPrices' => array(),
							'members' => array(),
							'customer' => $customer->toSimpleArray(),
							'noEnd' => $appointment->isNoEnd(),
							'numbers' => $appointment->getNumbers(),
							'alerts' => array()
						));
						$result['idService'] = $service->id;
						$result['quotation'] = $result['quotation']?true:false;
						$result['paymentState'] = RESA_Variables::calculateBookingPayment($result['status'], $result['paymentState']);
						$result['prices'] = array();
						foreach($appointment->getAppointmentNumberPrices() as $appointmentNumberPrice){
							$price = json_decode($appointmentNumberPrice->toJSON());
							$price->name = RESA_ServicePrice::getServicePriceName($appointmentNumberPrice->getIdPrice());
							array_push($result['prices'], $price);
						}
						$result['members'] = array();
						foreach ($appointment->getAppointmentMembers() as $appointmentMember) {
							$member = json_decode($appointmentMember->toJSON());
							$member->name = RESA_Member::getMemberName($appointmentMember->getIdMember());
							array_push($result['members'], $member);
						}
						$result['tags'] = $appointment->getTags();
						$alerts = RESA_Alert::getAllDataWithIds(array('idBooking' => array(array($result['id']))), array('idAppointment' => array(array($result['idAppointment']))));
						foreach($alerts as $alert){
							array_push($result['alerts'],json_decode($alert->toJSON()));
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
						array_push($appointmentsFormated, (object)$result);
					}
					$response->set_data($appointmentsFormated);
				}
				else {
					$response->set_status(401);
					$response->set_data(array('error' => 'not_can_access', 'message' => 'Not access'));
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

	public static function getAppointmentsMonths(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			if(isset($data['startDate']) && isset($data['endDate'])){
				$startDate = $data['startDate'];
				$endDate = $data['endDate'];
				$stateList = json_decode($data['stateList']);
				if(count($stateList) == 0) $stateList = array('ok', 'waiting');

				$data = array(
					'startDate' => array($startDate, '>='),
					'endDate' => array($endDate, '<='),
					'state' => array($stateList)
				);

				$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
				$events = RESA_Appointment::getCountAppointmentsAndGroupByIdServices($data, $places);
				$appointments = RESA_Appointment::getAllIdAppointments($data);
				$idAppointments = array();
				$idBookings = array();
				foreach($appointments as $appointment){
					array_push($idAppointments, $appointment->id);
					array_push($idBookings, $appointment->idBooking);
				}
				$alerts = RESA_Alert::getAllDataWithDate($startDate, $endDate, array('idBooking' => array($idBookings)), array('idAppointment' => array($idAppointments)));
				$results = array();
				foreach($alerts as $alert){
					$date = DateTime::createFromFormat('Y-m-d H:i:s', $alert->getStartDate());
					$dateEnd = DateTime::createFromFormat('Y-m-d H:i:s', $alert->getEndDate());
					do {
						if(!isset($results[$date->format('Y-m-d')])){
							$results[$date->format('Y-m-d')] = array('numbers' => 0, 'date' => $date->format('Y-m-d H:i:s'));
						}
						$results[$date->format('Y-m-d')]['numbers']++;
						$date->add(new DateInterval('P1D'));
					}while($date <= $dateEnd);
					$response->set_data($results);
				}
				$response->set_data(array('events' => $events, 'alerts' => $results));
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

	public static function getBooking(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $request['id'];
			$booking = new RESA_Booking();
			$booking->loadByLastIdCreation($idBooking);

			$bookingJSONLite = $booking->getBookingLiteJSON();

			if($currentRESAUser->isRESAStaff()){
				$bookingJSONLite->note = null;
				$bookingJSONLite->publicNote = null;
				$bookingJSONLite->customerNote = null;
				$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
				if(!$associatedMember->getPermissionDisplayCustomer()){ $bookingJSONLite->customer = null; }
				if(!$associatedMember->getPermissionDisplayTotal()){
					$bookingJSONLite->totalPrice = null;
					for($i = 0; $i < count($bookingJSONLite->appointments); $i++){
						for($j = 0; $j < count($bookingJSONLite->appointments[$i]->appointmentNumberPrices); $j++){
							$bookingJSONLite->appointments[$i]->appointmentNumberPrices[$j]->totalPrice = null;
						}
					}
				}
				if(!$associatedMember->getPermissionDisplayPayments()){ $bookingJSONLite->idPaymentState = null; }
				if(!$associatedMember->getPermissionDisplayNumbers()){
					for($i = 0; $i < count($bookingJSONLite->appointments); $i++){
						$bookingJSONLite->appointments[$i]->number = 0;
						for($j = 0; $j < count($bookingJSONLite->appointments[$i]->appointmentNumberPrices); $j++){
							$bookingJSONLite->appointments[$i]->appointmentNumberPrices[$j]->number = null;
						}
					}
				}
			}
			$response->set_data($bookingJSONLite);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function deleteBooking(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $request['id'];
			$booking = new RESA_Booking();
			$booking->loadByLastIdCreation($idBooking);
			$success = false;
			if($idBooking > -1 && $booking->isLoaded()){
				$logNotification = RESA_Algorithms::generateLogNotification(23, $booking, $booking->getCustomer(), $currentRESAUser);
				if(isset($logNotification))	$logNotification->save();
				$booking->deleteMe();
				$success = true;
			}
			$response->set_data(array('result' => $success));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getBookingHistoric(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $request['id'];
			$allIdBookings = RESA_Booking::getAllIdBookingLite($idBooking);
			$logNotifications = RESA_LogNotification::getAllData(array('idBooking' => array($allIdBookings)));
			$customerEmails = RESA_EmailCustomer::getAllData(array('idBooking' => array($allIdBookings)));

			$array = json_decode(RESA_Tools::formatJSONArray($logNotifications));
			$array = array_merge($array, json_decode(RESA_Tools::formatJSONArray($customerEmails)));

			usort($array, function($object1, $object2){
				$creationDate1 = DateTime::createFromFormat('Y-m-d H:i:s', $object1->creationDate);
				$creationDate2 = DateTime::createFromFormat('Y-m-d H:i:s', $object2->creationDate);
				return $creationDate1->getTimestamp() - $creationDate2->getTimestamp();
			});
			$response->set_data($array);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getBookingData(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $request['id'];

			$booking = new RESA_Booking();
			$booking->loadByLastIdCreation($idBooking);

			$customer = $booking->getCustomer();

			$services = array();
			$timeslots = array();
			$members = array();
			$reductions = array();

			$idServices = array();
			$idMembers = array();
			$idReductions = array();

			foreach($booking->getAppointments() as $appointment){
				// OPTIMIZE:
				$service = new RESA_Service();
				$service->loadById($appointment->getIdService());
				if(!in_array($service->getId(), $idServices)){
					array_push($services, $service);
					array_push($idServices, $service->getId());
				}
				if($service->getOldService()){
					$service = new RESA_Service();
					$service->loadByIdLastVersion($appointment->getIdService());
					if(!in_array($service->getId(), $idServices)){
						array_push($services, $service);
						array_push($idServices, $service->getId());
					}
				}
				//TIMESLOT ACTUEL
				$startDate = DateTime::createFromFormat('Y-m-d H:i:s',  $appointment->getStartDate());
				$endDate =  DateTime::createFromFormat('Y-m-d H:i:s',  $appointment->getEndDate());
				$timeslot = RESA_Algorithms::getTimeslot($startDate, $endDate, $service, array(), -1, true, '');
				if(!isset($timeslot)) {
					$timeslot = array(
						'idAppointment' => $appointment->getId(),
						'isCustom' => true
					);
				}
				$timeslot['idAppointment'] = $appointment->getId();
				array_push($timeslots, $timeslot);

				foreach($appointment->getAppointmentMembers() as $appointmentMember){
					if(!in_array($appointmentMember->getIdMember(), $idMembers)){
						$member = new RESA_Member();
						$member->loadById($appointmentMember->getIdMember());
						array_push($members, $member);
						array_push($idMembers, $member->getId());
					}
				}

				foreach($appointment->getAppointmentReductions() as $appointmentReduction){
					if(!in_array($appointmentReduction->getIdReduction(), $idReductions)){
						$reduction = new RESA_Reduction();
						$reduction->loadById($appointmentReduction->getIdReduction());
						array_push($reductions, $reduction);
						array_push($idReductions, $reduction->getId());
					}
				}
			}

			foreach($booking->getBookingReductions() as $bookingReduction){
				if(!in_array($bookingReduction->getIdReduction(), $idReductions)){
					$reduction = new RESA_Reduction();
					$reduction->loadById($bookingReduction->getIdReduction());
					array_push($reductions, $reduction);
					array_push($idReductions, $reduction->getId());
				}
			}

			$history = [];
			$allIdBookings = RESA_Booking::getAllIdBookingLite($idBooking);
			$logNotifications = RESA_LogNotification::getAllData(array('idBooking' => array($allIdBookings)));
			$customerEmails = RESA_EmailCustomer::getAllData(array('idBooking' => array($allIdBookings)));

			$history = json_decode(RESA_Tools::formatJSONArray($logNotifications));
			$history = array_merge($history, json_decode(RESA_Tools::formatJSONArray($customerEmails)));

			usort($history, function($object1, $object2){
				$creationDate1 = DateTime::createFromFormat('Y-m-d H:i:s', $object1->creationDate);
				$creationDate2 = DateTime::createFromFormat('Y-m-d H:i:s', $object2->creationDate);
				return $creationDate1->getTimestamp() - $creationDate2->getTimestamp();
			});

			$data =  array(
				'customer' => json_decode($customer->toJSON()),
				'services' => json_decode(RESA_Tools::formatJSONArray($services)),
				'timeslots' => $timeslots,
				'members' => json_decode(RESA_Tools::formatJSONArray($members)),
				'reductions' => json_decode(RESA_Tools::formatJSONArray($reductions)),
				'history' => $history
			);


			$response->set_data($data);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getBookingsSettings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'allLanguages' => RESA_Variables::getLanguages(),
				'languages' => unserialize(get_option('resa_settings_languages')),
				'countries' => json_decode(RESA_Variables::getJSONCountries()),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'currency' => get_option('resa_settings_payment_currency'),
				'tags' => unserialize(get_option('resa_settings_custom_tags')),
				'places' => unserialize(get_option('resa_settings_places')),
				'caisse_online_activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true' && class_exists('RESA_CaisseOnline'),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
				'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
				'idPaymentsTypeToName' => RESA_Variables::idPaymentsTypeToName(),
				'mailbox_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_mailbox_activated'))=='true',
				'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
				'notification_customer_accepted_account' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_accepted_account')) == 'true',
				'calendar'=>array(
					'start_time' => get_option('resa_settings_calendar_start_time'),
					'end_time' => get_option('resa_settings_calendar_end_time'),
					'split_time' => get_option('resa_settings_calendar_split_time')==30?2:(get_option('resa_settings_calendar_split_time')==15?4:6),
					'info_calendar_color' => get_option('resa_settings_calendar_info_calendar_color'),
					'service_constraint_color' => get_option('resa_settings_calendar_service_constraint_color')
				),
				'caisse_online_activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true' && class_exists('RESA_CaisseOnline'),
				'caisse_online_site_url'=> class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getURLCaisse():'',
				'caisse_online_server_url'=>class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getBaseURL():'',
				'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
			);
			$json = '{
				"settings":'.json_encode($settings).',
				"skeletonInfoCalendar":'. (new RESA_InfoCalendar())->toJSON().',
				"skeletonServiceConstraint":'. (new RESA_ServiceConstraint())->toJSON().',
				"skeletonMemberConstraint":'. (new RESA_MemberConstraint())->toJSON().',
				"services":'.json_encode(RESA_Service::getServicesLite()).',
				"members":'.json_encode(RESA_Member::getMembersLite()).',
				"statesList":'.json_encode(RESA_Variables::statesList()).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function receipt(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $request['id'];
			$booking = new RESA_Booking();
			$booking->loadByLastIdCreation($idBooking);

			$services = array();
			$reductions = array();

			$idServices = array();
			$idReductions = array();

			foreach($booking->getAppointments() as $appointment){
				// OPTIMIZE:
				$service = new RESA_Service();
				$service->loadById($appointment->getIdService());
				if(!in_array($service->getId(), $idServices)){
					array_push($services, $service);
					array_push($idServices, $service->getId());
				}
				if($service->getOldService()){
					$service = new RESA_Service();
					$service->loadByIdLastVersion($appointment->getIdService());
					if(!in_array($service->getId(), $idServices)){
						array_push($services, $service);
						array_push($idServices, $service->getId());
					}
				}

				foreach($appointment->getAppointmentReductions() as $appointmentReduction){
					if(!in_array($appointmentReduction->getIdReduction(), $idReductions)){
						$reduction = new RESA_Reduction();
						$reduction->loadById($appointmentReduction->getIdReduction());
						array_push($reductions, $reduction);
						array_push($idReductions, $reduction->getId());
					}
				}
			}

			foreach($booking->getBookingReductions() as $bookingReduction){
				if(!in_array($bookingReduction->getIdReduction(), $idReductions)){
					$reduction = new RESA_Reduction();
					$reduction->loadById($bookingReduction->getIdReduction());
					array_push($reductions, $reduction);
					array_push($idReductions, $reduction->getId());
				}
			}
			$settings = array(
				'currency' => get_option('resa_settings_payment_currency'),
				'places' => unserialize(get_option('resa_settings_places')),
				'company_name' => get_option('resa_settings_company_name'),
				'company_logo' => get_option('resa_settings_company_logo'),
				'company_address' => get_option('resa_settings_company_address'),
				'company_phone' => get_option('resa_settings_company_phone'),
				'company_type' => get_option('resa_settings_company_type'),
				'company_siret' => get_option('resa_settings_company_siret'),
				'tvaActivated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_display_tva_on_bill'))  == 'true',
				'informations_on_receipt'=>unserialize(get_option('resa_settings_informations_on_bill')),
				'informations_on_quotation'=>unserialize(get_option('resa_settings_informations_on_quotation')),
				'vatList' => unserialize(get_option('resa_settings_vat_list')),
				'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
				'caisse_online_server_url'=>class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getBaseURL():'',
				'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
			);

			$data =  array(
				'services' => json_decode(RESA_Tools::formatJSONArray($services)),
				'reductions' => json_decode(RESA_Tools::formatJSONArray($reductions)),
				'paymentsTypeToName' => RESA_Variables::idPaymentsTypeToName(),
				'settings' => $settings
			);

			$response->set_data($data);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function pushBookingInCaisseOnline(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$idBooking = $data['idBooking'];
			try {
				if(isset($idBooking)){
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);
					if(class_exists('RESA_CaisseOnline')){
						$result = RESA_CaisseOnline::getInstance()->payBooking($booking);
					}
					if($result['result']){
						$response->set_data(array('result' => 'success', 'message' => 'La réservation a été envoyée sur la caisse !'));
					}
					else {
						if($result['type'] == 'booking_already_exist'){
							throw new Exception('Cette réservation est déjà en attente sur la caisse, veuillez l\'encaisser !');
						}
						else {
							throw new Exception('La caisse ne répond pas !');
						}
					}
				}else {
					throw new Exception('Error');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('result' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function pushBookingsInCaisseOnline(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idCustomer'])){
					$idCustomer = $data['idCustomer'];
					$customer = new RESA_Customer();
					$customer->loadById($idCustomer); //need bookings

					$bookings = array();
					$idBookings = array();
					foreach($customer->getBookings() as $booking){
						if(!$booking->isPaymentStateComplete()){
							array_push($bookings, $booking);
							array_push($idBookings, $booking->getIdCreation());
						}
					}
					if(count($bookings) == 0) $response->set_data(array('result' => 'success', 'message' => 'Aucune réservation n\'a été envoyée sur la caisse !'));
					else {
						if(class_exists('RESA_CaisseOnline')){
							$result = RESA_CaisseOnline::getInstance()->payBookings($bookings, $idBookings);
							if($result['result']){
								$response->set_data(array('result' => 'success', 'message' => 'Les réservations ont été envoyée sur la caisse !'));
							}
							else {
								if($result['type'] == 'booking_already_exist'){
									throw new Exception('Cette réservation est déjà en attente sur la caisse, veuillez l\'encaisser !');
								}
								else {
									throw new Exception('La caisse ne répond pas !');
								}
							}
						}
						else {
							throw new Exception(__('caisse_online_not_installed', 'resa'));
						}
					}
				}
				else {
					throw new Exception(__('Error_word', 'resa'));
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('result' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function changePaymentState(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idBooking']) && isset($data['newPaymentState'])){
					$idBooking = $data['idBooking'];
					$newPaymentState = $data['newPaymentState'];
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);
					$tickets = [];
					if(class_exists('RESA_CaisseOnline')){
						$result = RESA_CaisseOnline::getInstance()->getBookingTickets($booking);
						if(is_array($result)){
							$tickets = $result;
						}
					}
					$caisseActivated = class_exists('RESA_CaisseOnline') && RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated', 0)) == 'true';
					if(count($tickets) == 0 && !$caisseActivated &&
						($booking->getPaymentState() != $newPaymentState) &&
						($newPaymentState == 'noPayment' || $newPaymentState == 'advancePayment'  || $newPaymentState == 'deposit' || $newPaymentState == 'complete')){
						$booking->setPaymentState($newPaymentState);
						if($newPaymentState != 'noPayment'){
							$booking->changeStatesAfterPayment(unserialize(get_option('resa_settings_states_parameters')));
						}
						$booking->clearModificationDate();
						$booking->save(false);

						$customer = $booking->getCustomer();
						$logNotification = RESA_Algorithms::generateLogNotification(8, $booking, $customer, $currentRESAUser);
						if(isset($logNotification))	$logNotification->save();
						$response->set_data(array('booking' => $booking->getBookingLiteJSON()));
					}
					else {
						throw new Exception('error');
					}
				}
				else {
					throw new Exception('bad_parameters');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('result' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function calculateBookingPaymentState(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				if(isset($data['idBooking'])){
					$idBooking = $data['idBooking'];
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);

					$tickets = false;
					if(class_exists('RESA_CaisseOnline')){
						$tickets = RESA_CaisseOnline::getInstance()->getBookingTickets($booking);
					}
					if(!is_bool($tickets)){
						//Logger::DEBUG(print_r($tickets, true));
						$totalTickets = 0;
						$totalReceipts = 0;
						foreach($tickets as $ticket){
							foreach($ticket->payments as $payment){
								if($ticket->type == 'ticket'){
									$totalTickets += $payment->amount;
								}
								else if($ticket->type == 'receipt'){
									$totalReceipts += $payment->amount;
								}
							}
						}
						$paymentState = $booking->isPaymentDepositPayment()?'deposit':'noPayment';

						if($totalTickets != 0){
							$paymentState = 'complete';
						}
						else if($totalReceipts != 0){
							$paymentState = 'advancePayment';
						}

						$booking->setPaymentState($paymentState);
						if($paymentState != 'noPayment' && $paymentState != 'deposit'){
							$booking->changeStatesAfterPayment(unserialize(get_option('resa_settings_states_parameters')));
						}
						$booking->clearModificationDate();
						$booking->save();
						$response->set_data(array('booking' => $booking->getBookingLiteJSON()));
					}
					else {
						throw new Exception('error');
					}
				}
				else {
					throw new Exception('bad_parameters');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('result' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


}
