<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class RESA_UpdatesManager
{
	private static $versions = array('1.6.2', '1.6.3', '1.6.4', '1.7.0', '1.7.1', '1.7.2', '1.7.3', '1.7.4', '1.8.0', '1.8.1', '1.8.2', '1.8.3', '1.8.4', '1.8.5', '1.9.0', '1.9.1', '1.9.2', '1.9.3', '1.9.4', '1.9.5', '1.9.6', '1.9.7', '1.9.8', '1.9.9', '1.10.0', '1.11.0', '1.11.1', '1.11.2', '1.11.3', '1.11.4', '1.11.5', '1.11.6', '2.0.0', '2.0.1', '2.0.2', '2.0.3', '2.0.4', '2.0.5', '2.0.6', '2.1.0', '2.1.1', '2.1.2', '2.1.3', '2.1.4', '2.2.0', '2.2.1', '2.2.2', '2.2.3', '2.2.4', '2.2.5', '2.2.6');
	private static $mapFunctionsToVersions =  array ('update_1_6_2', 'update_1_6_3', 'update_1_6_4', 'update_1_7_0', 'update_1_7_1', 'update_1_7_2', 'update_1_7_3', 'update_1_7_4', 'update_1_8_0', 'update_1_8_1', 'update_1_8_2', 'update_1_8_3', 'update_1_8_4', 'update_1_8_5', 'update_1_9_0', 'update_1_9_1', 'update_1_9_2', 'update_1_9_3', 'update_1_9_4', 'update_1_9_5', 'update_1_9_6', 'update_1_9_7', 'update_1_9_8', 'update_1_9_9', 'update_1_10_0', 'update_1_11_0', 'update_1_11_1', 'update_1_11_2', 'update_1_11_3', 'update_1_11_4', 'update_1_11_5', 'update_1_11_6', 'update_2_0_0', 'update_2_0_1', 'update_2_0_2', 'update_2_0_3', 'update_2_0_4', 'update_2_0_5', 'update_2_0_6', 'update_2_1_0', 'update_2_1_1', 'update_2_1_2', 'update_2_1_3', 'update_2_1_4', 'update_2_2_0', 'update_2_2_1', 'update_2_2_2', 'update_2_2_3', 'update_2_2_4', 'update_2_2_5', 'update_2_2_6');

	public static function updatePlugin()
	{
		$version = RESA_Variables::getVersion();
		$installedVersion = get_option('resa_plugin_version');
		$currentUpdateLaunched = get_option('resa_plugin_update_launched', false);

		if($version !== $installedVersion && get_option('resa_installation_done'))
		{
			$indexOfVersion = array_search($version, self::$versions);
			$indexOfInstalledVersion = array_search($installedVersion, self::$versions);
			if($indexOfInstalledVersion < $indexOfVersion && $indexOfVersion !== FALSE && $indexOfInstalledVersion !== FALSE)
			{
				while($indexOfInstalledVersion != $indexOfVersion)
				{
					update_option('resa_plugin_update_launched', true);
					call_user_func('self::' . self::$mapFunctionsToVersions[$indexOfInstalledVersion + 1]);
					$indexOfInstalledVersion++;
					update_option('resa_plugin_update_launched', false);
					update_option('resa_settings_license_next_version', '');
				}
			}
			//else throw new Exception("Bdd index of installed version");
		}
	}

	/**
	 *
	 */
	public static function currentVersionIsMoreRecentlyThanNextVersion($version, $nextVersion){
		$versionIndex = array_search($version, self::$versions);
		$nextVersionIndex = array_search($nextVersion, self::$versions);
		if(!is_bool($versionIndex) && !is_bool($nextVersionIndex)){
			return $versionIndex >= $nextVersionIndex;
		}
		return $nextVersionIndex;
	}

	public static function update_1_6_2(){
		global $wpdb;
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_info_calendar` ADD `idPlaces` TEXT NOT NULL AFTER `idUserCreator`;');
		update_option('resa_plugin_version', '1.6.2');
	}

	public static function update_1_6_3(){
		global $wpdb;
		update_option('resa_plugin_version', '1.6.3');
	}

	public static function update_1_6_4(){
		global $wpdb;
		update_option('resa_plugin_version', '1.6.4');
	}

	public static function update_1_7_0(){
		global $wpdb;

		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service` ADD `advancePaymentByAccountTypes` TEXT NOT NULL AFTER `advancePayment`;');
		$statesParameters = unserialize(get_option('resa_settings_states_parameters'));
		for($i = 0; $i < count($statesParameters); $i++){
			$statesParameters[$i]->stateBackend = $statesParameters[$i]->state;
			$statesParameters[$i]->paiementBackend = $statesParameters[$i]->paiement;
			$statesParameters[$i]->paiementStateBackend = $statesParameters[$i]->paiementState;
			$statesParameters[$i]->capacityBackend = $statesParameters[$i]->capacity;
			$statesParameters[$i]->expiredBackend = $statesParameters[$i]->expired;
		}
		update_option('resa_settings_states_parameters', serialize($statesParameters));

		update_option('resa_redirect_setup', 0);
		update_option('resa_first_parameter_done', 1);
		if(!get_option('resa_settings_notifications_custom_shortcodes')){
			update_option('resa_settings_notifications_custom_shortcodes', serialize(array(
				((object)array('shortcode' => '[RESA_logo]', 'params' => array(plugins_url('ui/assets/image/logo-resa-online.png', dirname(__FILE__))), 'block' => '<img src="[0]" style="width: 50%;">'))
			)));
		}
		if(!get_option('resa_settings_notifications_custom_shortcodes')){
			update_option('resa_settings_global_custom_css', get_option('resa_settings_form_custom_css', ''));
		}
		update_option('resa_daily_display_weather', 1);
		update_option('resa_settings_payment_later', 0);
		update_option('resa_settings_payment_stripe_private_key', '');

		$paymentsTypeList = RESA_Variables::paymentsTypeList();
		$paymentsTypeListTrue = new stdClass();
		foreach($paymentsTypeList as $payment){
			$paymentsTypeListTrue->{$payment['id']} = true;
		}
		$typesAccounts = unserialize(get_option('resa_settings_types_accounts'));
		for($i = 0; $i < count($typesAccounts); $i++){
			$typesAccounts[$i]->paymentsTypeList = $paymentsTypeListTrue;
		}
		update_option('resa_settings_types_accounts', serialize($typesAccounts));

		update_option('resa_plugin_version', '1.7.0');
	}

	public static function update_1_7_1(){
		global $wpdb;
		update_option('resa_plugin_version', '1.7.1');
	}

	public static function update_1_7_2(){
		global $wpdb;
		update_option('resa_plugin_version', '1.7.2');
	}


	public static function update_1_7_3(){
		global $wpdb;

		$paymentsTypeList = RESA_Variables::paymentsTypeList();
		$paymentsTypeListTrue = new stdClass();
		foreach($paymentsTypeList as $payment){
			$paymentsTypeListTrue->{$payment['id']} = true;
		}
		$typesAccounts = unserialize(get_option('resa_settings_types_accounts'));
		for($i = 0; $i < count($typesAccounts); $i++){
			$typesAccounts[$i]->paymentsTypeList = $paymentsTypeListTrue;
		}
		update_option('resa_settings_types_accounts', serialize($typesAccounts));

		update_option('resa_plugin_version', '1.7.3');
	}

	public static function update_1_7_4(){
		global $wpdb;
		update_option('resa_plugin_version', '1.7.4');
	}

	public static function update_1_8_0(){
		global $wpdb;
		$wpdb->query(RESA_Equipment::getCreateQuery());
		update_option('resa_equipments_management_actived', 0);
		update_option('resa_settings_caisse_online_mode', '0');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_price` ADD `equipments` TEXT NOT NULL AFTER `typesAccounts`');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_timeslot` CHANGE `active` `typeCapacity` INT(11) NOT NULL;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_timeslot` ADD `equipmentsActivated` BOOLEAN NOT NULL AFTER `typeCapacity`;');
		$wpdb->query('UPDATE `'.$wpdb->prefix.'resa_service_timeslot` SET `equipmentsActivated`=1');
		update_option('resa_plugin_version', '1.8.0');
	}

	public static function update_1_8_1(){
		global $wpdb;
		update_option('resa_plugin_version', '1.8.1');
	}

	public static function update_1_8_2(){
		global $wpdb;
		update_option('resa_plugin_version', '1.8.2');
	}

	public static function update_1_8_3(){
		global $wpdb;
		update_option('resa_plugin_version', '1.8.3');
	}

	public static function update_1_8_4(){
		global $wpdb;
		update_option('resa_plugin_version', '1.8.4');
	}


	public static function update_1_8_5(){
		global $wpdb;
		RESA_Crons::clearRESACheckEmailsEvent();
		delete_option('resa_settings_form_steps_title');
		update_option('resa_settings_browser_not_compatible_sentence', serialize(json_decode(json_encode(array(get_locale() => __('browser_not_compatible_sentence','resa'))))));
		update_option('resa_plugin_version', '1.8.5');
	}


	public static function update_1_9_0(){
		global $wpdb;
		update_option('resa_settings_payment_swikly', 0);
		update_option('resa_settings_payment_swikly_title', serialize(new stdClass()));
		update_option('resa_settings_payment_swikly_user_secret', '');
		update_option('resa_settings_payment_swikly_user_api', '');
		update_option('resa_settings_payment_swikly_api_key', '');
		update_option('resa_settings_payment_swikly_api_secret', '');
		update_option('resa_settings_payment_swikly_mode', 0);

		$typesAccounts = unserialize(get_option('resa_settings_types_accounts'));
		for($i = 0; $i < count($typesAccounts); $i++){
			$typesAccounts[$i]->paymentsTypeList->swikly = true;
		}
		update_option('resa_settings_types_accounts', serialize($typesAccounts));

		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_booking` CHANGE `paymentState` `paymentState` ENUM(\'noPayment\',\'advancePayment\',\'deposit\',\'complete\',\'over\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');

		update_option('resa_settings_notifications_email_notification_before_appointment', 0);
		update_option('resa_settings_notifications_email_notification_before_appointment_days', 2);
		update_option('resa_settings_notifications_email_notification_before_appointment_subject', serialize(new stdClass()));
		update_option('resa_settings_notifications_email_notification_before_appointment_text', serialize(new stdClass()));

		RESA_InstallManager::fillNotificationTemplate('resa_settings_notifications_email_notification_before_appointment_subject', '[RESA_company_name] : Rappel de votre réservation !', '', true);
		RESA_InstallManager::fillNotificationTemplate('resa_settings_notifications_email_notification_before_appointment_text', 'Nous attendons votre visite !', '<p>Merci [RESA_customer_lastname] [RESA_customer_firstname] pour votre réservation ! Nous attendons votre visite le [RESA_booking_first_date] !</p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_appointment` ADD `beforeAppointmentNotified` BOOLEAN NOT NULL AFTER `calendarId`;');
		wp_clear_scheduled_hook('resa_notification_after_booking_event');

		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_customer` ADD `idFacebook` TEXT NOT NULL AFTER `idCaisseOnline`;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_reduction` ADD `position` INT NOT NULL AFTER `id`;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_reduction_application` ADD `vatAmount` INT NOT NULL AFTER `value`;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_appointment_reduction` ADD `vatAmount` INT NOT NULL AFTER `value`;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_booking_reduction` ADD `vatAmount` INT NOT NULL AFTER `value`;');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_ask_payment` ADD `typeAdvancePayment` INT NOT NULL AFTER `value`;');

		update_option('resa_update_customers', 0);
		update_option('resa_update_customers_wp_users', 1);
		update_option('resa_plugin_version', '1.9.0');
	}

	public static function update_1_9_1(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.1');
	}

	public static function update_1_9_2(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.2');
	}

	public static function update_1_9_3(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.3');
	}

	public static function update_1_9_4(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.4');
	}

	public static function update_1_9_5(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.5');
	}

	public static function update_1_9_6(){
		global $wpdb;
		update_option('resa_settings_payment_swikly_deposit_type', 'reservation');
		update_option('resa_plugin_version', '1.9.6');
	}

	public static function update_1_9_7(){
		global $wpdb;
		$wpdb->query('UPDATE `'.$wpdb->prefix.'resa_appointment` SET `presentation`=\'\'');
		update_option('resa_settings_payment_swikly_process', 0);
		update_option('resa_settings_payment_swikly_process_type', 0);
		update_option('resa_settings_payment_swikly_process_value', 0);
		update_option('resa_settings_payment_swikly_sent_swik_with_ask_payment', 0);
		update_option('resa_settings_payment_swikly_display_link', 0);
		update_option('resa_plugin_version', '1.9.7');
	}

	public static function update_1_9_8(){
		global $wpdb;
		update_option('resa_settings_calendar_drag_and_drop_activated', 1);
		update_option('resa_plugin_version', '1.9.8');
	}

	public static function update_1_9_9(){
		global $wpdb;
		update_option('resa_plugin_version', '1.9.9');
	}

	public static function update_1_10_0(){
		global $wpdb;
		update_option('resa_settings_company_logo', '');
		update_option('resa_settings_informations_on_quotation', get_option('resa_settings_informations_on_bill', serialize(new stdClass())));

		$customShortcodes = unserialize(get_option('resa_settings_notifications_custom_shortcodes'));
		foreach($customShortcodes as $shortcode){
			$params = $shortcode->params;
			for($i = 0; $i < count($params); $i++){
				$param = (array) $shortcode->params[$i];
				if(!isset($param['type'])){
					$shortcode->params[$i] = (object) array(
						'value' => $params[$i],
						'type' => ($shortcode->shortcode=='[RESA_logo]')?'image':'text'
					);
				}
			}
		}
		update_option('resa_settings_notifications_custom_shortcodes', serialize($customShortcodes));
		update_option('resa_plugin_version', '1.10.0');
	}

	public static function update_1_11_0(){
		global $wpdb;
		update_option('resa_settings_colors', serialize((object)array('primaryColor' => '#23c46c', 'secondaryColor' => '#20B363')));
		update_option('resa_settings_timeslots_mentions', serialize(array(json_decode(json_encode(array('id' => 'timeslots_mentions_0', 'name' => json_decode(json_encode(array(get_locale() => 'Promo'))), 'backgroundColor' => '#fe6d4c'))))));

		$categories = unserialize(get_option('resa_settings_form_category_services'));
		for($i = 0; $i < count($categories); $i++){
			if(is_array($categories[$i])){
				if(!isset($categories[$i]['image'])) $categories[$i]['image'] = ((object)array());
				if(!isset($categories[$i]['label'])) $categories[$i]['label'] = ((object)array());
			}
			else {
				if(!isset($categories[$i]->image)) $categories[$i]->image = ((object)array());
				if(!isset($categories[$i]->label)) $categories[$i]->label = ((object)array());
			}
		}
		update_option('resa_settings_form_category_services', serialize($categories));
		$places = unserialize(get_option('resa_settings_places'));
		for($i = 0; $i < count($places); $i++){
			$places[$i]->image = (object)array();
		}
		update_option('resa_settings_places', serialize($places));

		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_timeslot` ADD `idMention` TEXT NOT NULL AFTER `idsServicePrices`');

		update_option('resa_plugin_version', '1.11.0');
	}

	public static function update_1_11_1(){
		global $wpdb;
		update_option('resa_plugin_version', '1.11.1');
	}

	public static function update_1_11_2(){
		global $wpdb;
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_price` CHANGE `price` `price` DOUBLE NOT NULL');
		update_option('resa_plugin_version', '1.11.2');
	}

	public static function update_1_11_3(){
		global $wpdb;
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_service_availability` ADD `manyDates` TEXT NOT NULL AFTER `groupDates`');

		$calendars = unserialize(get_option('resa_settings_calendars'));
		for($i = 0; $i < count($calendars); $i++){
			$calendars[$i]->manyDates = array();
		}
		update_option('resa_settings_calendars', serialize($calendars));
		update_option('resa_plugin_version', '1.11.3');
	}

	public static function update_1_11_4(){
		update_option('resa_plugin_version', '1.11.4');
	}

	public static function update_1_11_5(){
		update_option('resa_plugin_version', '1.11.5');
	}

	public static function update_1_11_6(){
		update_option('resa_plugin_version', '1.11.6');
	}

	public static function update_2_0_0(){
		update_option('resa_planning_tabs_activities', serialize(array()));
		update_option('resa_backend_v2_activated', false);
		$forms = RESA_Form::getAllData();
		foreach($forms as $form){
			$form->setTypeForm('RESA_form');
			$form->save();
		}
		update_option('resa_plugin_version', '2.0.0');
	}

	public static function update_2_0_1(){
		update_option('resa_plugin_version', '2.0.1');
	}

	public static function update_2_0_2(){
		update_option('resa_plugin_version', '2.0.2');
	}

	public static function update_2_0_3(){
		update_option('resa_plugin_version', '2.0.3');
	}

	public static function update_2_0_4(){
		update_option('resa_plugin_version', '2.0.4');
	}

	public static function update_2_0_5(){
		update_option('resa_plugin_version', '2.0.5');
	}

	public static function update_2_0_6(){
		update_option('resa_plugin_version', '2.0.6');
	}

	public static function update_2_1_0(){
		update_option('resa_plugin_version', '2.1.0');
	}

	public static function update_2_1_1(){
		update_option('resa_plugin_version', '2.1.1');
	}

	public static function update_2_1_2(){
		update_option('resa_plugin_version', '2.1.2');
	}

	public static function update_2_1_3(){
		update_option('resa_plugin_version', '2.1.3');
	}

	public static function update_2_1_4(){
		update_option('resa_settings_log_notifications', serialize(RESA_InstallManager::getStaticLogNotifications()));
		update_option('resa_plugin_version', '2.1.4');
	}


	public static function update_2_2_0(){
		update_option('resa_settings_payment_stripe_connect_id', '');
		update_option('resa_settings_payment_stripe_connect', 0);
		update_option('resa_settings_payment_stripe_connect_conditions_validated', 0);

		update_option('resa_settings_payment_stripe_mode_payment_state', 0);
		update_option('resa_settings_log_notifications', serialize(RESA_InstallManager::getStaticLogNotifications()));

		global $wpdb;
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_customer` ADD `idStripe` TEXT NOT NULL AFTER `idCaisseOnline`');
		$wpdb->query('ALTER TABLE `'.$wpdb->prefix.'resa_customer` ADD `registerNewsletters` BOOLEAN NOT NULL AFTER `deactivateEmail`;');
		$typesAccounts = unserialize(get_option('resa_settings_types_accounts'));
		for($i = 0; $i < count($typesAccounts); $i++){
			$typesAccounts[$i]->fields->newsletters = 0;
			$typesAccounts[$i]->paymentsTypeList->stripeConnect = true;
		}
		update_option('resa_settings_types_accounts', serialize($typesAccounts));
		update_option('resa_plugin_version', '2.2.0');
	}

	public static function update_2_2_1(){
		update_option('resa_plugin_version', '2.2.1');
	}

	public static function update_2_2_2(){
		update_option('resa_plugin_version', '2.2.2');
	}

	public static function update_2_2_3(){
		update_option('resa_plugin_version', '2.2.3');
	}

	public static function update_2_2_4(){
		update_option('resa_plugin_version', '2.2.4');
	}

	public static function update_2_2_5(){
		update_option('resa_plugin_version', '2.2.5');
	}

	public static function update_2_2_6(){
		update_option('resa_plugin_version', '2.2.6');
	}

	public static function updateCustomers(){
		Logger::DEBUG('begin');
		global $wpdb;
		$resaCustomerUpdated = get_option('resa_update_customers', 0);
		$resaCustomerWpUsersUpdated = get_option('resa_update_customers_wp_users', 1);
		if($resaCustomerUpdated != -1){
			$number = 10;
			$customers = RESA_Customer::getAllDataWithLimit(array(), true, $resaCustomerWpUsersUpdated, $resaCustomerUpdated, $number);
			for($i = 0; $i < count($customers); $i++){
				$customer = $customers[$i];
				if(isset($customer) && $customer->isLoaded()){
					Logger::DEBUG($resaCustomerUpdated . ' - ' . $customer->getID() . ' - ' . $customer->getLastName() . ' => ' . (($resaCustomerWpUsersUpdated == 1)?'Wp users':'Resa customer'));
					$customer->save(false);
				}
			}
			if(count($customers) != 0){
				update_option('resa_update_customers', $resaCustomerUpdated + $number);
			}
			else {
				if($resaCustomerWpUsersUpdated == 1){
					update_option('resa_update_customers', 0);
					update_option('resa_update_customers_wp_users', 0);
				}
				else {
					update_option('resa_update_customers', -1);
				}
			}
			Logger::DEBUG('end');
			return false;
		}
		else {
			Logger::DEBUG('Ok c\'est fini !');
			return true;
		}
	}


	public static function forceLastUpdateDatabase(){
		call_user_func('self::' . end(self::$mapFunctionsToVersions));
	}
}
