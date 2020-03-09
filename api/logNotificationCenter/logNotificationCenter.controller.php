<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APILogNotificationCenterController
{
	public static function initLogNotificationCenter(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
			$limit = 10;
			$logNotifications = RESA_LogNotification::getAllDataWithLimit(array('criticity' => 0), $places, $limit);
			$logNotifications = array_merge($logNotifications, RESA_LogNotification::getAllDataWithLimit(array('criticity' => 1), $places, $limit));
			usort($logNotifications, array("RESA_LogNotification", "compare"));

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
				'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id')
			);

			$json = '{
				"settings":'.json_encode($settings).',
				"logNotificationsWaitingNumber":'.RESA_LogNotification::countDataIdCustomer($currentRESAUser->getId(), $places).',
				"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).'
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getLogNotifications(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$limit = 10;
			$criticity = 0;
			$lastIdLogNotifications = -1;
			if(isset($data['limit']) && is_numeric($data['limit'])) $limit = sanitize_text_field($data['limit']);
			if(isset($data['criticity']) && is_numeric($data['criticity'])) $criticity = sanitize_text_field($data['criticity']);
			if(isset($data['lastIdLogNotifications']) && is_numeric($data['lastIdLogNotifications'])) $lastIdLogNotifications = sanitize_text_field($data['lastIdLogNotifications']);

			$data = array('id' => array($lastIdLogNotifications, '<'));
			if($criticity != -1){
				$data['criticity'] = $criticity;
			}
			$places = array();
			if(is_object($currentRESAUser->getFilterSettings()) && isset($currentRESAUser->getFilterSettings()->places)){
				$filterPlaces = (array)($currentRESAUser->getFilterSettings()->places);
				foreach($filterPlaces as $key => $value){
					if($value) {
						array_push($places, $key);
					}
				}
			}
			$logNotifications = RESA_LogNotification::getAllDataWithLimit($data, $places, $limit);
			$json = '{
				"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function clickOnLogNotifications(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try
			{
				if(isset($data['idLogNotification'])){
					$idLogNotification = sanitize_text_field($data['idLogNotification']);
					$logNotification = new RESA_LogNotification();
					$logNotification->loadById($idLogNotification);
					if($logNotification->isLoaded()){
						$logNotification->setClicked(true);
						$logNotification->save();
					}
					$booking = new RESA_Booking();
					if($logNotification->getIdBooking() > -1){
						$booking->loadByLastIdCreation($logNotification->getIdBooking());
					}
					$customer = $booking->getCustomer();
					if(!isset($customer)){
						$customer = new RESA_Customer();
					}

					$json = '{
						"booking":'.json_encode($booking->getBookingLiteJSON()).',
						"customer":'.$customer->toJSON().'
					}';

					$response->set_data(json_decode($json));
				}
				else {
					throw new Exception("Error");
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function unreadLogNotification(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try
			{
				if(isset($data['idLogNotification'])){
					$idLogNotification = sanitize_text_field($data['idLogNotification']);
					$logNotification = new RESA_LogNotification();
					$logNotification->loadById($idLogNotification);
					if($logNotification->isLoaded()){
						$logNotification->setClicked(false);
						$logNotification->save();
					}
					$response->set_data(array('result' => 'success'));
				}
				else {
					throw new Exception("Bad parameters");
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function seeAllLogNotifications(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try {
				$allLogNotificationsNotSeen = RESA_LogNotification::getAllDataNotSeen($currentRESAUser->getId());
				foreach($allLogNotificationsNotSeen as $logNotification){
					$logNotification->seenIdCustomer($currentRESAUser->getId());
					$logNotification->save();
				}
				$response->set_data(array('result' => 'success'));
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function clickAllLogNotifications(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try
			{
				if(isset($data['lastId']) && is_numeric($data['lastId'])){
					$limit = sanitize_text_field($data['lastId']);
					RESA_LogNotification::clickAllLogNotifications($limit);
				}
				else {
					throw new Exception('Bad parameters');
				}
				$response->set_data(array('result' => 'success'));
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function checkUpdateLogNotifications(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			try
			{
				if(isset($data['lastIdLogNotifications']) && is_numeric($data['lastIdLogNotifications'])){
					$lastIdLogNotifications = sanitize_text_field($data['lastIdLogNotifications']);
					$places = RESA_Variables::getFilterSettingsPlace($currentRESAUser);
					$logNotifications = RESA_LogNotification::getAllData(array('id' => array($lastIdLogNotifications, '>')), $places);

					$json = '{
						"logNotificationsWaitingNumber":'.RESA_LogNotification::countDataIdCustomer($currentRESAUser->getId(), $places).',
						"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).'
					}';
					$response->set_data(json_decode($json));
				}
				else {
					throw new Exception('Bad parameters');
				}
			}
			catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'error', 'message' => $e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
