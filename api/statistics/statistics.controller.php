<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIStatictisController
{
	public static function daily(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$data = $request->get_json_params();
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 00:00:00')->format('Y-m-d H:i:s');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['date'].' 23:59:59')->format('Y-m-d H:i:s');
			$groupNumber = get_option('resa_daily_group_number', 10);
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);

			global $wpdb;
			$filterPlace = (count($places)>0)?RESA_Tools::generateWhereWithPlace($places, 'idPlace', '', ''):'';
			$results = $wpdb->get_results('SELECT id,idService,startDate,endDate,numbers FROM ' . $wpdb->prefix. 'resa_appointment WHERE startDate >= \''.$startDate.'\' AND startDate <= \''.$endDate.'\' AND state=\'ok\' '.$filterPlace.' ORDER BY startDate');
			$alreadyLoaded = array();
			$services = array();
			$data = array();
			$global = array('minDate' => $endDate, 'minStartDate' => $endDate, 'maxDate' => $startDate, 'maxStartDate' => $startDate, 'bookings' => 0, 'numbers' => 0);
			foreach($results as $result){
				$service = RESA_Service::getServiceLite($result->idService);
				if(isset($service)){
					if(!in_array($service->id, $alreadyLoaded)){
						array_push($services, $service);
						array_push($alreadyLoaded, $service->id);
					}
				}
				$key = $service->id.''.$result->startDate.''.$result->endDate;
				if(!isset($data[$key])){
					$data[$key] = array(
						'idService'=>$service->id,
						'startDate' => $result->startDate,
						'endDate' => $result->endDate,
						'bookings' => 0,
						'numbers' => 0,
						'groups' => 0
					);
				}
				$data[$key]['bookings']++;
				$data[$key]['numbers'] += $result->numbers;
				if($result->numbers >= $groupNumber) $data[$key]['groups']++;

				$global['bookings']++;
				$global['numbers'] += $result->numbers;

				$localStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $result->startDate);
				$locaEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $result->endDate);
				$globalMinDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['minDate']);
				$globalMinStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['minStartDate']);
				$globalMaxStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['maxStartDate']);
				$globalMaxDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['maxDate']);

				if($localStartDate < $globalMinDate) $global['minDate'] = $result->startDate;
				if($localStartDate < $globalMinStartDate) $global['minStartDate'] = $result->startDate;
				if($localStartDate > $globalMaxStartDate) $global['maxStartDate'] = $result->startDate;
				if($locaEndDate > $globalMaxDate) $global['maxDate'] = $result->endDate;


				$globalMinDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['minDate']);
				$globalMaxDate = DateTime::createFromFormat('Y-m-d H:i:s', $global['maxDate']);
				$calendarStartTime = DateTime::createFromFormat('Y-m-d H', $localStartDate->format('Y-m-d').' '.get_option('resa_settings_calendar_start_time'));
				$calendarEndTime = DateTime::createFromFormat('Y-m-d H', $localStartDate->format('Y-m-d').' '.get_option('resa_settings_calendar_end_time'));

				if($calendarStartTime < $globalMinDate) $global['minDate'] = $calendarStartTime->format('Y-m-d H:i:s');
				if($calendarEndTime > $globalMaxDate) $global['maxDate'] = $calendarEndTime->format('Y-m-d H:i:s');
			}
			$result = array();
			foreach($data as $value){
				array_push($result, (object) $value);
			}

			usort($services, function($service1, $service2){
				return $service1->position - $service2->position;
			});


			$json = '{
				"services":'.json_encode($services).',
				"statistics":'.json_encode($result).',
				"global":'.json_encode((object)$global).'
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
	 *
	 */
	public static function dailySettings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'displayWeather' => get_option('resa_daily_display_weather', 1),
				'urlWeather' => get_option('resa_daily_url_weather', ''),
				'places' => unserialize(get_option('resa_settings_places')),
				'currency' => get_option('resa_settings_payment_currency')
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

	/**
	 *
	 */
	public static function promoCodes(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$promoCodes = array();
			global $wpdb;
			$results = $wpdb->get_results('(SELECT '.RESA_BookingReduction::getTableName().'.promoCode as promoCode FROM '.RESA_BookingReduction::getTableName().' WHERE '.RESA_BookingReduction::getTableName().'.promoCode<>\'\')
			UNION (SELECT '.RESA_AppointmentReduction::getTableName().'.promoCode as promoCode FROM '.RESA_AppointmentReduction::getTableName().' WHERE '.RESA_AppointmentReduction::getTableName().'.promoCode<>\'\')');
			foreach($results as $result){
				array_push($promoCodes, $result->promoCode);
			}
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
			$response->set_data(array(
				'promoCodes' => $promoCodes,
				'settings' => $settings,
				'startDate' => get_option('resa_settings_start_date_statistics', ''),
				'endDate' => get_option('resa_settings_end_date_statistics', ''),
			));
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
	public static function promoCodesBookings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && isset($data['promoCode']) && isset($data['startDate']) && isset($data['endDate'])){
			$promoCode = $data['promoCode'];
			$bookingsPage = $data['page'] - 1;
			if($bookingsPage < -1) $bookingsPage = 0;

			$bookingsByPage = $data['limit'];
			$promoCodeWHERE = '<>\'\'';
			if(!empty($promoCode) && $promoCode!='all'){
				$promoCodeWHERE = '=\''.$promoCode.'\'';
			}

			$startDate = $data['startDate'].' 00:00:00';
			$endDate = $data['endDate'].' 23:59:59';


			global $wpdb;
			if($bookingsPage == 0){
				$countMax = $wpdb->get_results('(SELECT '.RESA_Booking::getTableName().'.id as id FROM '.RESA_BookingReduction::getTableName().'
				INNER JOIN '.RESA_Booking::getTableName().' ON '.RESA_BookingReduction::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND '.RESA_Booking::getTableName().'.oldBooking=\'0\' AND '.RESA_BookingReduction::getTableName().'.promoCode '.$promoCodeWHERE.'
				INNER JOIN '.RESA_Appointment::getTableName().' ON '.RESA_Appointment::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id  AND '.RESA_Appointment::getTableName().'.startDate >= \''.$startDate.'\' AND '.RESA_Appointment::getTableName().'.startDate <= \''.$endDate.'\' GROUP BY id)
				UNION ALL
				(SELECT '.RESA_Booking::getTableName().'.id as id FROM '.RESA_AppointmentReduction::getTableName().'
				INNER JOIN '.RESA_Appointment::getTableName().' ON '.RESA_AppointmentReduction::getTableName().'.idAppointment = '.RESA_Appointment::getTableName().'.id AND '.RESA_AppointmentReduction::getTableName().'.promoCode '.$promoCodeWHERE.' AND '.RESA_Appointment::getTableName().'.startDate >= \''.$startDate.'\' AND '.RESA_Appointment::getTableName().'.startDate <= \''.$endDate.'\'
				INNER JOIN '.RESA_Booking::getTableName().' ON '.RESA_Appointment::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND '.RESA_Booking::getTableName().'.oldBooking=\'0\' GROUP BY id)
				ORDER BY id DESC');
			}
			if(!isset($countMax)) $countMax = array();
			$countMax = count($countMax);

			$results = $wpdb->get_results('(SELECT '.RESA_Booking::getTableName().'.id as id FROM '.RESA_BookingReduction::getTableName().'
			INNER JOIN '.RESA_Booking::getTableName().' ON '.RESA_BookingReduction::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND '.RESA_Booking::getTableName().'.oldBooking=\'0\' AND '.RESA_BookingReduction::getTableName().'.promoCode '.$promoCodeWHERE.'
			INNER JOIN '.RESA_Appointment::getTableName().' ON '.RESA_Appointment::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id  AND '.RESA_Appointment::getTableName().'.startDate >= \''.$startDate.'\' AND '.RESA_Appointment::getTableName().'.startDate <= \''.$endDate.'\' GROUP BY id)
			UNION ALL
			(SELECT '.RESA_Booking::getTableName().'.id as id FROM '.RESA_AppointmentReduction::getTableName().'
			INNER JOIN '.RESA_Appointment::getTableName().' ON '.RESA_AppointmentReduction::getTableName().'.idAppointment = '.RESA_Appointment::getTableName().'.id AND '.RESA_AppointmentReduction::getTableName().'.promoCode '.$promoCodeWHERE.' AND '.RESA_Appointment::getTableName().'.startDate >= \''.$startDate.'\' AND '.RESA_Appointment::getTableName().'.startDate <= \''.$endDate.'\'
			INNER JOIN '.RESA_Booking::getTableName().' ON '.RESA_Appointment::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND '.RESA_Booking::getTableName().'.oldBooking=\'0\' GROUP BY id)
			ORDER BY id DESC LIMIT '. ($bookingsPage * $bookingsByPage) .',' . $bookingsByPage);

			$bookings = array();
			foreach($results as $result){
				$idBooking = $result->id;
				$booking = new RESA_Booking();
				$booking->loadByLastIdCreation($idBooking);
				$json = $booking->getBookingLiteJSON();
				$json->promoCodes = $booking->getPromoCodes();
				array_push($bookings, $json);
			}

			update_option('resa_settings_start_date_statistics', $data['startDate']);
			update_option('resa_settings_end_date_statistics', $data['endDate']);

			$response->set_data(array(
				'bookings' => $bookings,
				'nbTotal' => $countMax
			));
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
	public static function initMembersStats(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'tags' => unserialize(get_option('resa_settings_custom_tags')),
				'places' => unserialize(get_option('resa_settings_places')),
				'currency' => get_option('resa_settings_payment_currency')
			);
			$response->set_data(array(
				'settings' => $settings,
				'startDate' => get_option('resa_settings_start_date_statistics', ''),
				'endDate' => get_option('resa_settings_end_date_statistics', ''),
			));
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
	private static function getAllIdGroups($startDate, $endDate, $idPlaces){
		global $wpdb;
		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$results = $wpdb->get_results('SELECT id FROM ' . $wpdb->prefix. 'resa_group WHERE startDate >= \''.$startDate.'\' AND startDate <= \''.$endDate.'\' '. $filterPlace.' ORDER BY startDate');
		return $results;
	}


	/**
	 *
	 */
	public static function countHoursMembers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && isset($data['startDate']) && isset($data['endDate'])){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['startDate'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['endDate'].' 23:59:59');
			$places = RESA_Variables::getFilterSettingsPlaceWithPlaces($data['places']);

			update_option('resa_settings_start_date_statistics', $data['startDate']);
			update_option('resa_settings_end_date_statistics', $data['endDate']);
			$categories = unserialize(get_option('resa_settings_form_category_services'));

			$members = array();
			$allMembers = RESA_Member::getAllData(array('activated' => 1), 'position');
			foreach($allMembers as $member){
				if(($member->isInPlaces($places) || $member->isNoPlace() || (count($places) == 0))){
					$members['' . $member->getId()] = array(
						'id' => $member->getId(),
						'places' => $member->getPlaces(),
						'lastname' =>$member->getLastName(),
						'firstname' =>$member->getFirstName(),
						'nickname' => $member->getNickname(),
						'position' => $member->getPosition(),
						'dates' => array(),
						'sums' => array('total' => 0)
					);
					foreach($categories as $category){
						$members['' . $member->getId()]['sums'][$category->id] = 0;
					}
				}
			}

			$allIdServices = array();
			$services = array();
			$cptDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d H:i:s'));
			do{
				foreach($members as $key => $member){
					$default = array(
						'date' => $cptDate->format('Y-m-d H:i:s'),
						'groups' => [],
						'total' => 0,
						'noCategory' => 0
					);
					foreach($categories as $category){
						$default[$category->id] = 0;
					}
					$members[$key]['dates'][$cptDate->format('Y-m-d')] = $default;
				}
				$idGroups = self::getAllIdGroups($cptDate->format('Y-m-d').' 00:00:00', $cptDate->format('Y-m-d').' 23:59:59', $places);
				foreach($idGroups as $idGroup){
					$group = new RESA_Group();
					$group->loadById($idGroup->id);
					if(!$group->haveNoMemberAttribuated()){
						if(!in_array($group->getIdService(), $allIdServices)){
							$service = new RESA_Service();
							$service->loadByIdLastVersion($group->getIdService());
							array_push($services, $service);
							$allIdServices = array_merge($allIdServices, $service->getAllIds());
						}
						$categoryServices = 'noCategory';
						foreach($services as $service){
							if($service->isSameService($group->getIdService())){
								$categoryServices = $service->getCategory();
							}
						}
						foreach ($group->getIdMembers() as $idMember) {
							array_push($members['' . $idMember]['dates'][$cptDate->format('Y-m-d')]['groups'], json_decode($group->toJSON(true)));
							$members['' . $idMember]['dates'][$cptDate->format('Y-m-d')]['total'] += $group->getDuration();
							$members['' . $idMember]['dates'][$cptDate->format('Y-m-d')][$categoryServices] += $group->getDuration();
							$members['' . $idMember]['sums']['total'] += $group->getDuration();
							$members['' . $idMember]['sums'][$categoryServices] += $group->getDuration();
						}
					}
				}
				$cptDate->add(new DateInterval('P1D'));
			}
			while($cptDate <= $endDate);
			$members = array_values($members);
			for($i = 0; $i < count($members); $i++){
				$members[$i]['dates'] =  array_values($members[$i]['dates']);
			}
			$response->set_data(array(
				'categories' => $categories,
				'members' => $members
			));
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
	public static function initStatisticsActivities(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'tags' => unserialize(get_option('resa_settings_custom_tags')),
				'places' => unserialize(get_option('resa_settings_places')),
				'currency' => get_option('resa_settings_payment_currency')
			);
			$response->set_data(array(
				'settings' => $settings,
				'startDate' => get_option('resa_settings_start_date_statistics', ''),
				'endDate' => get_option('resa_settings_end_date_statistics', ''),
			));
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
	public static function statisticsActivities(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && isset($data['startDate']) && isset($data['endDate'])){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['startDate'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['endDate'].' 23:59:59');
			$places = RESA_Variables::getFilterSettingsPlaceWithPlaces($data['places']);

			update_option('resa_settings_start_date_statistics', $data['startDate']);
			update_option('resa_settings_end_date_statistics', $data['endDate']);

			$totals = array(
				'dates' => array(),
				'total' => 0,
				'numbers' => 0,
				'price' => 0
			);
			$services = [];
			$allServices = RESA_Service::getAllDataWithOrderByPlaces(array('oldService'=>false, 'activated' => 1), 'position', $places);
			foreach($allServices as $service){
				$services[$service->getId()] = array(
					'id' => $service->getId(),
					'name' => $service->getName(),
					'places' => $service->getPlaces(),
					'allIds' => $service->getAllIds(),
					'dates' => array(),
					'total' => 0,
					'numbers' => 0,
					'price' => 0
				);
			}
			$cptDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d H:i:s'));
			do{
				$totals['dates'][$cptDate->format('Y-m-d')] = array(
					'date' => $cptDate->format('Y-m-d H:i:s'),
					'total' => 0,
					'numbers' => 0,
					'price' => 0
				);

				foreach($services as $key => $service){
					$data = array(
						'startDate' => array($cptDate->format('Y-m-d').' 00:00:00', '>='),
						'endDate' => array($cptDate->format('Y-m-d').' 23:59:59', '<='),
						'idService' => array($service['allIds']),
						'state' => array('AND', array(array('updated', '<>'), array('abandonned', '<>'), array('cancelled', '<>')))
					);

					$count = RESA_Appointment::countAllData($data);
					$numbers = RESA_Appointment::numbersAllData($data);
					$totalPrice = RESA_Appointment::totalPricesAllData($data);

					$default = array(
						'date' => $cptDate->format('Y-m-d H:i:s'),
						'total' => $count,
						'numbers' => $numbers,
						'price' => $totalPrice
					);
					$services[$key]['dates'][$cptDate->format('Y-m-d')] = $default;
					$services[$key]['total'] += $count;
					$services[$key]['numbers'] += $numbers;
					$services[$key]['price'] += $totalPrice;

					$totals['dates'][$cptDate->format('Y-m-d')]['total'] += $count;
					$totals['dates'][$cptDate->format('Y-m-d')]['numbers'] += $numbers;
					$totals['dates'][$cptDate->format('Y-m-d')]['price'] += $totalPrice;

					$totals['total'] += $count;
					$totals['numbers'] += $numbers;
					$totals['price'] += $totalPrice;
				}
				$cptDate->add(new DateInterval('P1D'));
			}
			while($cptDate <= $endDate);

			$services = array_values($services);
			$response->set_data(array(
				'services' => $services,
				'totals' => $totals
			));
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
	public static function initStatisticsBookings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$settings = array(
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'tags' => unserialize(get_option('resa_settings_custom_tags')),
				'places' => unserialize(get_option('resa_settings_places')),
				'currency' => get_option('resa_settings_payment_currency')
			);
			$response->set_data(array(
				'settings' => $settings,
				'startDate' => get_option('resa_settings_start_date_statistics', ''),
				'endDate' => get_option('resa_settings_end_date_statistics', ''),
			));
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
	private static function getBookingsLite($startDate, $endDate, $places) {
		global $wpdb;
		$filterPlace = (count($places)>0)?RESA_Tools::generateWhereWithPlace($places, RESA_Appointment::getTableName().'.idPlace', '', ''):'';
		$result = $wpdb->get_results('SELECT '.RESA_Booking::getTableName().'.id, '.RESA_Booking::getTableName().'.totalPrice, '.RESA_Booking::getTableName().'.idCustomer, '.RESA_Booking::getTableName().'.idUserCreator  FROM '. RESA_Booking::getTableName() . '
			INNER JOIN '.RESA_Appointment::getTableName().' ON '.RESA_Appointment::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND '.RESA_Appointment::getTableName().'.startDate >= \''.$startDate.'\' AND  '.RESA_Appointment::getTableName().'.startDate <= \''.$endDate.'\' AND '.RESA_Appointment::getTableName().'.state<>\'updated\' AND '.RESA_Appointment::getTableName().'.state<>\'cancelled\' AND '.RESA_Appointment::getTableName().'.state<>\'abandonned\' '. $filterPlace . ' GROUP BY '.RESA_Booking::getTableName().'.id');
		return $result;
	}

	/**
	 *
	 */
	public static function statisticsBookings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && isset($data['startDate']) && isset($data['endDate'])){
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $data['startDate'].' 00:00:00');
			$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $data['endDate'].' 23:59:59');
			$places = RESA_Variables::getFilterSettingsPlaceWithPlaces($data['places']);

			update_option('resa_settings_start_date_statistics', $data['startDate']);
			update_option('resa_settings_end_date_statistics', $data['endDate']);

			$results = self::getBookingsLite($startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $places);
			$nbBookings = count($results);
			$totalPrice = 0;
			$nbBookingsOnline = 0;
			foreach($results as $result){
				$totalPrice += $result->totalPrice;
				$nbBookingsOnline += ($result->idCustomer == $result->idUserCreator)?1:0;
			}
			$purcentOnline = ($nbBookings > 0)?round(($nbBookingsOnline/$nbBookings) * 100):0;
			$response->set_data(array(
				'nbBookings' => $nbBookings,
				'totalPrice' => $totalPrice,
				'nbBookingsOnline' => $nbBookingsOnline,
				'purcentOnline' => $purcentOnline
			));
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
	public static function search(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$value = $data['value'];
			$result = array();
			if((is_numeric($value) && !is_string($value)) || (is_string($value) && is_numeric($value) && substr($value, 0, 1) != '0')){
				$result['bookings'] = array();
				$results = RESA_Booking::getAllIdBookingsWithIdBoooking($value, ' LIMIT 5');
				foreach($results as $idBooking){
					$bookingLite = RESA_Booking::getBookingLite($idBooking->id);
					$customer = new RESA_Customer();
					$customer->loadByIdWithoutBookings($bookingLite->idCustomer);
					$bookingLite->customer = json_decode($customer->toJSON());
					$bookingLite->paymentState = RESA_Variables::calculateBookingPayment($bookingLite->status, $bookingLite->paymentState);
					array_push($result['bookings'], $bookingLite);
				}
			}
			if((is_string($value) && !is_numeric($value)) || (is_numeric($value) && (strlen($value.'') >= 4) && is_string($value) && $value[0] == '0')){
				$result['customers'] = json_decode(RESA_Tools::formatJSONArray(RESA_Customer::searchAllCustomersWithLimit($value, 0, 5)));
			}
			$response->set_data((object)$result);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
