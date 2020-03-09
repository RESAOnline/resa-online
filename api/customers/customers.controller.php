<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APICustomersController{
	public static function getCustomer(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$customer = new RESA_Customer();
			$customer->loadByIdWithoutBookings($request['id']);
			if(!$customer->isLoaded()){
				$response->set_status(404);
				$response->set_data(array('error' => 'not_found', 'message' => 'Not found'));
			}
			else {
				$response->set_data(json_decode($customer->toJSON()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getCustomers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$search = '';
			$limit = 10;
			$page = 1;
			if(isset($data['search'])) $search = ($data['search']);
			if(isset($data['limit'])) $limit = ($data['limit']);
			if(isset($data['page'])) $page = ($data['page'] - 1);

			$customers = RESA_Customer::searchAllCustomersWithLimit($search, $page, $limit);
			$nbTotalCustomers = RESA_Customer::countAllCustomers($search);

			$json = '{
				"customers":'.RESA_Tools::formatJSONArray($customers).',
				"nbTotalCustomers":'.$nbTotalCustomers.'
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
	 * Add or edit customer
	 */
	public static function editCustomer(WP_REST_Request $request){
		require_once(ABSPATH.'wp-admin/includes/user.php');

		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			$customer = new RESA_Customer();
			$customerJSON = json_decode($data['customer']);
			try{
				$oldID = $customerJSON->ID;
				$status = 'updated';
				$isNewUser = false;
				if(isset($customerJSON) && $customerJSON->ID == -1){
					$isNewUser = true;
					$status = 'added';
					$phoneAlreadyExist = RESA_Customer::phoneAlreadyExist($customerJSON->phone);
					if(isset($customerJSON->createWpAccount) && $customerJSON->createWpAccount){
						$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
						if(!username_exists($customerJSON->login) && !email_exists($customerJSON->email) && !$phoneAlreadyExist){
							if(!isset($customerJSON->password) || empty($customerJSON->password)){
								$customerJSON->password = wp_generate_password();
							}
							$notify = isset($customerJSON->notify) && $customerJSON->notify;
							$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email, $notify);
							$customer->setRole(RESA_Variables::getCustomerRole());
						}
						else if($phoneAlreadyExist) throw new Exception(__('phone_already_exist_error', 'resa'));
						else if(email_exists($customerJSON->email)) throw new Exception(__('email_already_exist_error', 'resa'));
						else throw new Exception(__('login_already_exist_error', 'resa'));
					}
					else {
						if(!$phoneAlreadyExist){
							$customer->fromJSON($customerJSON);
							$customer->save();
						}
						else throw new Exception(__('phone_already_exist_error', 'resa'));
					}
				}
				else {
					$customer->loadById($customerJSON->ID);
				}
				if($customer->isLoaded()){
					if(!$isNewUser && !$customer->isWpUser() && isset($customerJSON->createWpAccount) && $customerJSON->createWpAccount){
						$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
						if(!username_exists($customerJSON->login) && !email_exists($customerJSON->email)){
							if(!isset($customerJSON->password) || empty($customerJSON->password)){
								$customerJSON->password = wp_generate_password();
							}
							$notify = isset($customerJSON->notify) && $customerJSON->notify;
							$customer->createWpUserWithCustomer($customerJSON->login, $customerJSON->password, $customerJSON->email, $notify);
							$customer->setRole(RESA_Variables::getCustomerRole());
							$customerJSON->ID = $customer->getId();
							$customer->fromJSON($customerJSON);
							$customer->save();
							$customer->loadById($customer->getId());
							$status = 'replaced';
						}
						else if(email_exists($customerJSON->email)) throw new Exception(__('email_already_exist_error', 'resa'));
						else throw new Exception(__('login_already_exist_error', 'resa'));
					}
					else {
						$customerJSON->ID = $customer->getId();
						$customer->fromJSON($customerJSON);
						$customer->save();
					}
					if($isNewUser && isset($notify) && $notify){
						RESA_Mailer::sentMessageAccountCreation($customer, $currentRESAUser->getId());
					}
					$result = array('status' => $status, 'customer' => json_decode($customer->toJSON()));
					$response->set_data($result);
				} else throw new Exception(__('authentication_or_register_error', 'resa'));
			}catch(Exception $e){
				Logger::Error($e->getMessage());
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_token', 'message' => __('Error_word','resa').$e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function deleteCustomer(WP_REST_Request $request){
		require_once(ABSPATH.'wp-admin/includes/user.php');

		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($request['id'])){
				$idCustomer = $request['id'];
				$customer = new RESA_Customer();
				$customer->loadById($idCustomer, true);
				if($customer->isLoaded() && $customer->isRESACustomer() && $customer->isDeletable()){
					$success = $customer->deleteMe();
					if($success){
						if($customer->isAskAccount()){
							RESA_Mailer::sendMessageResponseAskAccount($customer, false, $currentRESAUser->getId());
						}
					}
					else {
						$response->set_status(401);
						$response->set_data(array('error' => 'error_delete', 'message' => 'Error '));
					}
				}
				else {
					$response->set_status(401);
					$response->set_data(array('error' => 'not_deletable', 'message' => 'Not deletable'));
				}
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad Parameters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getCustomersSettings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$customer = new RESA_Customer();
			$settings = array(
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'allLanguages' => RESA_Variables::getLanguages(),
				'languages' => unserialize(get_option('resa_settings_languages')),
				'countries' => json_decode(RESA_Variables::getJSONCountries()),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single'))
			);
			$json = '{
				"settings":'.json_encode($settings).',
				"skeletonCustomer":'. $customer->toJSON().'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function getCustomerData(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$customer = new RESA_Customer();
			$customer->loadById($request['id']);
			if(!$customer->isLoaded()){
				$response->set_status(404);
				$response->set_data(array('error' => 'not_found', 'message' => 'Not found'));
			}
			else {
				$bookings = [];
				foreach($customer->getBookings() as $booking){
					array_push($bookings, $booking->getBookingLiteJSON());
				}
				$logNotifications = RESA_LogNotification::getAllData(array('idCustomer' => $customer->getId()));
				$emailsCustomer = RESA_EmailCustomer::getAllData(array('idCustomer' => $customer->getId()));
				$customerJSON = json_decode($customer->toJSON());
				$customerJSON->bookings = $bookings;
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
					'caisse_online_site_url'=> class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getURLCaisse():'',
					'caisse_online_server_url'=>class_exists('RESA_CaisseOnline')?RESA_CaisseOnline::getInstance()->getBaseURL():'',
					'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
					'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
					'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
					'idPaymentsTypeToName' => RESA_Variables::idPaymentsTypeToName(),
					'mailbox_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_mailbox_activated'))=='true',
					'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
					'notification_customer_accepted_account' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_accepted_account')) == 'true'
				);
				$json = '{
					"settings":'.json_encode($settings).',
					"customer":'.json_encode($customerJSON).',
					"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
					"emailsCustomer":'.RESA_Tools::formatJSONArray($emailsCustomer).'
				}';
				$response->set_data(json_decode($json));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 * Accept ask account
	 */
	public static function acceptAskAccount(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			try{
				if(isset($data['idCustomer'])){
					$customer = new RESA_Customer();
					$idCustomer = $data['idCustomer'];
					$customer->loadById($idCustomer);
					if($customer->isLoaded() && $customer->isAskAccount()){
						$customerJSON = json_decode($customer->toJSON());
						$customer->createWpUserWithCustomer($customerJSON->login, $customer->getPassword(), $customerJSON->email, false);
						$customer->setRole(RESA_Variables::getCustomerRole());
						$customerJSON->ID = $customer->getId();
						$customer->fromJSON($customerJSON);
						$customer->setAskAccount(false);
						$customer->save();
						$customer->loadById($customer->getId());
						RESA_Mailer::sendMessageResponseAskAccount($customer, true);
						$response->set_data(json_decode($customer->toJSON()));
					}
				}
				else {
					throw new Exception("bad_parameters");
				}
			}catch(Exception $e){
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_token', 'message' => __('Error_word','resa').$e->getMessage()));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function deleteEmailCustomer(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			if(isset($request['id'])){
				$idEmailCustomer = $request['id'];
				$emailCustomer = new RESA_EmailCustomer();
				$emailCustomer->loadById($idEmailCustomer);
				$emailCustomer->deleteMe();
				$response->set_data(array('result' => true));
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad Parameters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function forceProcessEmails(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			RESA_Crons::clearRESACheckEmailsEvent();
			$response->set_data(array('result' => true));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function updateCustomer(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$lastModificationDateBooking = new DateTime();
			$idCustomer = -1;
			$lastIdLogNotifications = -1;
			$lastIdEmailCustomer = -1;
			if(isset($data['lastModificationDateBooking'])){
				$lastModificationDateBooking = sanitize_text_field($data['lastModificationDateBooking']);
				$lastModificationDateBooking = DateTime::createFromFormat('Y-m-d H:i:s', $lastModificationDateBooking);
			}
			$lastModificationDateBooking = $lastModificationDateBooking->format('Y-m-d H:i:s');
			if(isset($data['idCustomer']) && is_numeric($data['idCustomer'])) $idCustomer = $data['idCustomer'];
			if(isset($data['lastIdLogNotifications']) && is_numeric($data['lastIdLogNotifications'])){
				$lastIdLogNotifications = $data['lastIdLogNotifications'];
			}
			if(isset($data['lastIdEmailCustomer']) && is_numeric($data['lastIdEmailCustomer'])){
				$lastIdEmailCustomer = $data['lastIdEmailCustomer'];
			}

			$bookings = RESA_Booking::getAllData(array('oldBooking'=>false, 'idCustomer'=> $idCustomer,'modificationDate'=> array($lastModificationDateBooking, '>')));
			$logNotifications = RESA_LogNotification::getAllData(array('id' => array($lastIdLogNotifications, '>'), 'idCustomer' => $idCustomer));
			$emailsCustomer = RESA_EmailCustomer::getAllData(array('id' => array($lastIdEmailCustomer, '>'), 'idCustomer' => $idCustomer));

			$arrayBookings = array();
			foreach($bookings as $booking){
				array_push($arrayBookings, $booking->getBookingLiteJSON());
			}

			$json =  '{
				"bookings":'.json_encode($arrayBookings).',
				"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
				"emailsCustomer":'.RESA_Tools::formatJSONArray($emailsCustomer).'
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function exportCustomers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canManage()){
			$filename ='exportClient_'.date('Y_m_d_H_i', current_time('timestamp')).'.csv';
			if(isset($data['limit'])) $limit = ($data['limit']);
			if(isset($data['page'])) $page = ($data['page'] - 1);
			if(isset($data['filename']) && !empty($data['filename'])) $filename = $data['filename'];

			$customers = RESA_Customer::searchAllCustomersWithLimit('', $page, $limit);
			$dir = wp_get_upload_dir()['basedir'] . '/resa_exports/';
			if (!is_dir($dir)) {
				mkdir($dir);
			}
			$file = fopen($dir . $filename, 'a');
			if($page == 0){
				$header = array(
					'Nom',
					'Prénom',
					'Entreprise',
					'Email',
					'Type de compte',
					'Téléphone',
					'Téléphone 2',
					'Adresse',
					'Ville',
					'Pays',
					'Langue',
					'Siret',
					'Forme légale',
					'Date d\'inscription',
					'Abonné newsletters'
				);
				fwrite($file, implode(';', $header) . PHP_EOL);
			}
			foreach($customers as $customer){
				if($customer->isRESACustomer() && $customer->getEmail() != '******' && !$customer->isAskAccount()){
      		fwrite($file, $customer->toCSV() . PHP_EOL);
				}
			}
			fclose($file);
			$result = array(
				'page' => $page,
				'limit' => $limit,
				'allNbTotalCustomers' => RESA_Customer::countAllCustomers(''),
				'filename' => $filename,
				'fileURL' =>  wp_get_upload_dir()['baseurl'] . '/resa_exports/' . $filename,
				'end' => (count($customers) == 0)
			);

			$response->set_data($result);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
