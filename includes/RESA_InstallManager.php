<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class RESA_InstallManager
{
	public $options;
	public static $allEntities = array(
		'RESA_Customer', 'RESA_InfoCalendar', 'RESA_Alert', 'RESA_LogNotification', 'RESA_EmailCustomer', 'RESA_Equipment', 'RESA_Member',
		'RESA_MemberAvailability', 'RESA_Service', 'RESA_MemberLink', 'RESA_MemberLinkService', 'RESA_MemberAvailabilityService',
		'RESA_ServiceAvailability', 'RESA_ServiceTimeslot', 'RESA_ServicePrice', 'RESA_ServiceMemberPriority', 'RESA_ServiceConstraint',
		'RESA_MemberConstraint', 'RESA_Booking', 'RESA_Appointment', 'RESA_Payment', 'RESA_AskPayment', 'RESA_AppointmentNumberPrice',
		'RESA_AppointmentReduction', 'RESA_AppointmentMember', 'RESA_Group', 'RESA_Reduction', 'RESA_ReductionConditions', 'RESA_ReductionCondition',
		'RESA_ReductionConditionService', 'RESA_ReductionApplication', 'RESA_ReductionConditionsApplication', 'RESA_ReductionConditionApplication',
		'RESA_BookingReduction', 'RESA_BookingCustomReduction', 'RESA_StaticGroup', 'RESA_Form'
	);

	public function __construct()
	{
		$this->options = array(
			'resa_plugin_version' => RESA_Variables::getVersion(),
			'resa_settings_calendar_client_id' => '',
			'resa_settings_calendar_client_secret' => '',
			'resa_settings_calendar_token' => '',
			'resa_settings_caisse_online_activated' => 0,
			'resa_settings_caisse_online_license_id' => '',
			'resa_settings_caisse_online_site_url' => '',
			'resa_settings_caisse_online_mode' => 0,
			'resa_settings_mailbox_activated' => 0,
			'resa_settings_mailbox' => serialize(array()),
			'resa_settings_checkbox_payment' => 1,
			'resa_settings_checkbox_title_payment' => serialize(json_decode(json_encode(array(get_locale() => __('accept_the_general_condition_sentence','resa'))))),
			'resa_settings_company_name' => '',
			'resa_settings_company_logo' => '',
			'resa_settings_company_address' => '',
			'resa_settings_company_phone' => '',
			'resa_settings_company_type' => '',
			'resa_settings_company_siret' => '',
			'resa_settings_customer_account_url' => '',
			'resa_settings_customer_booking_url' => '',
			'resa_settings_calendars' => serialize(array()),
			'resa_settings_calendar_start_time' => 8,
			'resa_settings_calendar_end_time' => 19,
			'resa_settings_calendar_split_time' => 30,
			'resa_settings_calendar_drag_and_drop_activated' => 1,
			'resa_settings_calendar_enabled_enabled_color' => 1,
			'resa_settings_calendar_enabled_disabled_color' => 1,
			'resa_settings_calendar_enabled_days_front_color' => '#23c46c',
			'resa_settings_calendar_disabled_days_front_color' => '#c9c9c9',
			'resa_settings_calendar_info_calendar_color' => '#848484',
			'resa_settings_calendar_service_constraint_color' => '#848484',
			'resa_settings_colors' => serialize((object)array('primaryColor' => '#23c46c', 'secondaryColor' => '#20B363')),
			'resa_settings_places' => serialize(array()),
			'resa_settings_timeslots_mentions' => serialize(array(json_decode(json_encode(array('id' => 'timeslots_mentions_0', 'name' => json_decode(json_encode(array(get_locale() => __('Promo_word','resa')))), 'backgroundColor' => '#fe6d4c'))))),
			'resa_staff_management_actived' => 0,
			'resa_equipments_management_actived' => 0,
			'resa_groups_management_actived' => 0,
			'resa_staff_word_many' => serialize(json_decode(json_encode(array(get_locale() => __('Members_word','resa'))))),
			'resa_staff_word_single' => serialize(json_decode(json_encode(array(get_locale() => __('Member_word','resa'))))),
			'resa_staff_old_appointments_displayed' => 0,
			'resa_staff_appointment_display_customer' => 0,
			'resa_staff_appointment_display_total' => 0,
			'resa_staff_appointment_display_payments' => 0,
			'resa_staff_appointment_display_numbers' => 1,
			'resa_staff_display_bookings_tab' => 1,
			'resa_settings_languages' => serialize(array(get_locale())),
			'resa_settings_tva' => '',
			'resa_settings_display_tva_on_bill' => 1,
			'resa_settings_informations_on_bill' => serialize(new stdClass()),
			'resa_settings_informations_on_quotation' => serialize(new stdClass()),
			'resa_settings_payment_activate' => 1,
			'resa_settings_payment_not_activated_text' => serialize(new stdClass()),
			'resa_settings_payment_currency' => '€',
			'resa_settings_payment_ask_currency' => 0,
			'resa_settings_payment_ask_text_currency' => serialize(new stdClass()),
			'resa_settings_payment_ask_advance_payment' => 0,
			'resa_settings_payment_ask_advance_payment_type_accounts' =>  serialize(array()),
			'resa_settings_payment_ask_text_advance_payment' => serialize(new stdClass()),
			'resa_settings_payment_return_url_success' => '',
			'resa_settings_payment_return_url_error' => '',
			'resa_settings_quotation_return_url_success' => '',
			'resa_settings_payment_on_the_spot' => 1,
			'resa_settings_payment_later' => 0,
			'resa_settings_payment_transfer' => 0,
			'resa_settings_payment_transfer_text' => serialize(new stdClass()),
			'resa_settings_payment_cheque' => 0,
			'resa_settings_payment_cheque_text' => serialize(new stdClass()),
			'resa_settings_payment_paypal' => 0,
			'resa_settings_payment_paypal_title' => serialize(new stdClass()),
			'resa_settings_payment_paypal_idShop' => '',
			'resa_settings_payment_paypal_mode' => 1,
			'resa_settings_payment_systempay' => 0,
			'resa_settings_payment_systempay_title' => serialize(new stdClass()),
			'resa_settings_payment_systempay_id_shop' => '',
			'resa_settings_payment_systempay_certificate' => '',
			'resa_settings_payment_systempay_mode' => 1,
			'resa_settings_payment_monetico' => 0,
			'resa_settings_payment_monetico_title' => serialize(new stdClass()),
			'resa_settings_payment_monetico_tpe' => '',
			'resa_settings_payment_monetico_id_shop' => '',
			'resa_settings_payment_monetico_key' => '',
			'resa_settings_payment_monetico_mode' => 1,
			'resa_settings_payment_stripe' => 0,
			'resa_settings_payment_stripe_title' => serialize(new stdClass()),
			'resa_settings_payment_stripe_public_key' => '',
			'resa_settings_payment_stripe_private_key' => '',
			'resa_settings_payment_stripe_connect_conditions_validated' => 0,
			'resa_settings_payment_stripe_connect' => 0,
			'resa_settings_payment_stripe_connect_id' => '',
			'resa_settings_payment_paybox' => 0,
			'resa_settings_payment_paybox_title' => serialize(new stdClass()),
			'resa_settings_payment_paybox_site' => '',
			'resa_settings_payment_paybox_rank' => '',
			'resa_settings_payment_paybox_id' => '',
			'resa_settings_payment_paybox_secret' => '',
			'resa_settings_payment_paybox_mode' => 1,
			'resa_settings_payment_swikly' => 0,
			'resa_settings_payment_swikly_title' => serialize(new stdClass()),
			'resa_settings_payment_swikly_user_secret' => '',
			'resa_settings_payment_swikly_user_api' => '',
			'resa_settings_payment_swikly_api_key' => '',
			'resa_settings_payment_swikly_api_secret' => '',
			'resa_settings_payment_swikly_deposit_type' => 'reservation',
			'resa_settings_payment_swikly_process' => 0,
			'resa_settings_payment_swikly_process_type' => 0,
			'resa_settings_payment_swikly_process_value' => 0,
			'resa_settings_payment_swikly_sent_swik_with_ask_payment' => 0,
			'resa_settings_payment_swikly_display_link' => 0,
			'resa_settings_payment_swikly_mode' => 0,
			'resa_settings_payment_custom_payment_types' => '',
			'resa_settings_vat_list' => serialize(array(
				((object)array('id' => 'vat_1', 'label'=>(object)array(get_locale() => 'TVA 0'), 'name'=>(object)array(get_locale() => 'TVA 0%'), 'value' => 0, 'reference' => '')),
				((object)array('id' => 'vat_2', 'label'=>(object)array(get_locale() => 'TVA 10'), 'name'=> (object)array(get_locale() => 'TVA 10%'), 'value' => 10, 'reference' => '')),
				((object)array('id' => 'vat_3', 'label'=>(object)array(get_locale() => 'TVA 20'), 'name'=>(object)array(get_locale() => 'TVA 20%'), 'value' => 20, 'reference' => ''))
			)),
			'resa_systempay_trans_id' => 1,
			'resa_settings_log_notifications' => serialize(self::getStaticLogNotifications()),
			'resa_settings_senders' => serialize(array()),
			'resa_settings_notifications_email_notification_customer_booking' => 1,
			'resa_settings_notifications_email_notification_customer_booking_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_booking_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_staff_booking' => 0,
			'resa_settings_notifications_email_notification_staff_booking_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_staff_booking_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_booking' => 0,
			'resa_settings_notifications_email_notification_emails_booking_emails' => '',
			'resa_settings_notifications_email_notification_emails_booking_places_emails' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_booking_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_booking_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_payment_booking' => 0,
			'resa_settings_notifications_email_notification_emails_payment_booking_emails' => '',
			'resa_settings_notifications_email_notification_emails_payment_booking_places_emails' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_payment_booking_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_payment_booking_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_need_payment_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_need_payment_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_account_creation' => 0,
			'resa_settings_notifications_email_notification_customer_account_creation_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_account_creation_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_password_reinit' => 0,
			'resa_settings_notifications_email_notification_customer_password_reinit_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_password_reinit_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_payment' => 0,
			'resa_settings_notifications_email_notification_customer_payment_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_payment_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_before_appointment' => 0,
			'resa_settings_notifications_email_notification_before_appointment_days' => 2,
			'resa_settings_notifications_email_notification_before_appointment_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_before_appointment_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_after_appointment' => 0,
			'resa_settings_notifications_email_notification_after_appointment_days' => 5,
			'resa_settings_notifications_email_notification_after_appointment_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_after_appointment_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_customer' => 0,
			'resa_settings_notifications_email_notification_quotation_customer_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_customer_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_requests' => 0,
			'resa_settings_notifications_email_notification_emails_quotation_requests_emails' => '',
			'resa_settings_notifications_email_notification_emails_quotation_requests_places_emails' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_requests_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_requests_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_accepted_customer' => 0,
			'resa_settings_notifications_email_notification_quotation_accepted_customer_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_accepted_customer_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_customer_booking' => 0,
			'resa_settings_notifications_email_notification_quotation_customer_booking_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_quotation_customer_booking_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_answered' => 0,
			'resa_settings_notifications_email_notification_emails_quotation_answered_emails' => '',
			'resa_settings_notifications_email_notification_emails_quotation_answered_places_emails' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_answered_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_quotation_answered_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_no_response_quotation' => 0,
			'resa_settings_notifications_email_notification_no_response_quotation_days' => 5,
			'resa_settings_notifications_email_notification_no_response_quotation_times' => 1,
			'resa_settings_notifications_email_notification_no_response_quotation_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_no_response_quotation_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_ask_account' => 0,
			'resa_settings_notifications_email_notification_customer_ask_account_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_ask_account_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_ask_account' => 0,
			'resa_settings_notifications_email_notification_emails_ask_account_emails' => '',
			'resa_settings_notifications_email_notification_emails_ask_account_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_emails_ask_account_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_accepted_account' => 0,
			'resa_settings_notifications_email_notification_customer_accepted_account_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_accepted_account_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_refused_account' => 0,
			'resa_settings_notifications_email_notification_customer_refused_account_subject' => serialize(new stdClass()),
			'resa_settings_notifications_email_notification_customer_refused_account_text' => serialize(new stdClass()),
			'resa_settings_notifications_email_notifications_templates' => serialize(array()),
			'resa_settings_notifications_email_notifications_templates_last_modification_date' => (new DateTime())->format('Y-m-d H:i:s'),
			'resa_settings_notifications_custom_shortcodes' => serialize(array(((object)array('shortcode' => '[RESA_logo]', 'params' => array((object)array('value' => plugins_url('ui/assets/image/logo-resa-online.png', dirname(__FILE__)), 'type' => 'image')), 'block' => '<img src="[0]" style="width: 50%;">')))),
			'resa_settings_global_custom_css' => '',
			'resa_settings_browser_not_compatible_sentence' => serialize(json_decode(json_encode(array(get_locale() => __('browser_not_compatible_sentence','resa'))))),
			'resa_settings_form_display_remaining_position_overcapacity' => 0,
			'resa_settings_form_add_new_date_text' => serialize(json_decode(json_encode(array(get_locale() => __('add_an_another_date_link_title','resa'))))),
			'resa_settings_form_add_new_activity_text' => serialize(json_decode(json_encode(array(get_locale() => __('add_another_activity_link_title','resa'))))),
			'resa_settings_form_informations_customer_text' => serialize(new stdClass()),
			'resa_settings_form_informations_participants_text' => serialize(new stdClass()),
			'resa_settings_form_informations_payment_text' => serialize(new stdClass()),
			'resa_settings_form_informations_confirmation_text' => serialize(new stdClass()),
			'resa_settings_form_quotation_informations_confirmation_text' => serialize(new stdClass()),
			'resa_settings_form_customer_note_text' => serialize(new stdClass()),
			'resa_settings_form_category_services' => serialize(array()),
			'resa_settings_form_participants_parameters' => serialize(array(json_decode(json_encode((object)array('id' => 'lastname', 'label'=>array(get_locale() => __('Default_word','resa')), 'fields'=>array(json_decode(json_encode( (object)array('varname' => 'lastname', 'name'=>array(get_locale() => __('last_name_field_title','resa')), 'type' => 'text', 'mandatory'=> true))), json_decode(json_encode(array('varname' => 'firstname', 'name'=>array(get_locale() => __('first_name_field_title','resa')), 'type' => 'text', 'mandatory'=> true))))))))),
			'resa_systempay_trans_id' => 1,
			'resa_notification_after_appointment_cron' => '',
			'resa_notification_no_response_quotation_cron' => '',
			'resa_settings_states_parameters' => serialize(array()),
			'resa_settings_custom_tags' => serialize(array(json_decode(json_encode( (object)array('id' => 'absent', 'title'=>array(get_locale() => __('Absent_word','resa')), 'color' => 'tag_vert'))))),
			'resa_settings_types_accounts' => serialize(array(json_decode(json_encode((object)array('id' => 'type_account_0', 'name'=>array(get_locale() => __('Type_account_customer_word','resa')), 'fields' => array('lastName' => 1, 'firstName' => 2, 'company' => 2, 'phone' => 1, 'phone2' => 0, 'address' => 2,
				 'postalCode' => 2, 'town' => 2, 'country' => 2, 'siret' => 0, 'legalForm' => 0, 'newsletters' => 0),
			 	 'paymentsTypeList' => array()))),json_decode(json_encode((object)array('id' => 'type_account_1', 'name'=>array(get_locale() => __('Type_account_company_word','resa')),
				 'fields' => array('lastName' => 1, 'firstName' => 2, 'company' => 1, 'phone' => 1, 'phone2' => 0, 'address' => 2,
				 'postalCode' => 2, 'town' => 2, 'country' => 2, 'siret' => 1, 'legalForm' => 1, 'newsletters' => 0),
			 	 'paymentsTypeList' => array()))))),
			'resa_daily_display_weather' => 1,
	 		'resa_daily_url_weather' => '',
			'resa_daily_group_number' => 10,
			'resa_installation_done' => 0,
			'resa_redirect_setup' => 1,
			'resa_planning_tabs_activities' => serialize(array()),
			'resa_first_parameter_done' => 0,
			'resa_backend_v2_activated' => true
			);
	}

	public function getOptionNames(){
		$wpRESAOptions = array();
		foreach ($this->options as $key => $value) {
			array_push($wpRESAOptions, $key);
		}
		return $wpRESAOptions;
	}

	public function createForm(){
		include_once(plugin_dir_path(__FILE__) . 'entities/RESA_Form.php');
		global $wpdb;
		$wpdb->query(RESA_Form::getCreateQuery());
		$firstForm = new RESA_Form();
		$firstForm->setId(1);
		$firstForm->setName('Formulaire par defaut');
		$firstForm->setActivated(true);
		$firstForm->generateCSS();
		$firstForm->save();
	}

	public function install()
	{
		if (!get_option('resa_installation_done')) {
      $this->createTables();
			$this->addOptions();

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

			$this->fillNotificationTemplates();
			add_role(RESA_Variables::getCustomerRole(), __(RESA_Variables::getCustomerRole(), 'resa'), array());
			add_role(RESA_Variables::getStaffRole(), __(RESA_Variables::getStaffRole(), 'resa'), array('read' => true, RESA_Variables::getRoleCap() => true));
			add_role(RESA_Variables::getManagerRole(), __(RESA_Variables::getManagerRole(), 'resa'), array('read' => true, RESA_Variables::getRoleCap() => true));
			get_role( 'administrator' )->add_cap( RESA_Variables::getRoleCap() );
			$this->createForm();
			$this->defaultStateParameters();
			$arrayUploadDir = wp_get_upload_dir();
			if(!is_dir($arrayUploadDir['basedir'].'/resa_css')){
				mkdir($arrayUploadDir['basedir'].'/resa_css');
			}
			update_option('resa_installation_done', '1');
    }
		flush_rewrite_rules();
	}

	/**
	 * In activation plugins
	 */
	public static function loadRESAOnlineBeforePlugins() {
		$pluginRESAOnline = RESA_Variables::getPluginSlugName() . '/main.php';
		$plugins = get_option( 'active_plugins' );
		$index = -1;
		for($i = 0; $i < count($plugins); $i++){
			$plugin = $plugins[$i];
			if(strpos($plugin, 'resa') === 0 && $index == -1){
				$index = $i;
			}
		}
		if($index != -1 ){
			$indexRESAOnline = array_search($pluginRESAOnline, $plugins);
			if($indexRESAOnline != $index){
	      $tmp = $plugins[$indexRESAOnline];
	      $plugins[$indexRESAOnline] = $plugins[$index];
	      $plugins[$index] = $tmp;
	      update_option( 'active_plugins', $plugins);
			}
		}
	}

	public function uninstall()
	{
		$this->dropTables();
		$this->removeOptions();
		remove_role(RESA_Variables::getCustomerRole());
		remove_role(RESA_Variables::getStaffRole());
		remove_role(RESA_Variables::getManagerRole());
		get_role( 'administrator' )->remove_cap( RESA_Variables::getRoleCap() );
	}

	private function addOptions()
	{
		foreach ( $this->options as $name => $value ) {
			add_option( $name, $value );
		}
	}

	public function removeOptions()
	{
		foreach ( $this->options as $name => $value ) {
        delete_option( $name );
    }
	}

	private function createTables() {
		global $wpdb;
		foreach (self::$allEntities as $entity) {
			if(!empty($entity::getCreateQuery())){
				$wpdb->query($entity::getCreateQuery());
			}
		}
		foreach (self::$allEntities as $entity) {
			if(!empty($entity::getConstraints())){
				$wpdb->query($entity::getConstraints());
			}
		}
		RESA_Customer::initializeAutoIncrement();
	}

	private function dropTables() {
		global $wpdb;
		for($i = (count(self::$allEntities) - 1); $i >= 0; $i--){
			$entity = self::$allEntities[$i];
			$wpdb->query($entity::getDeleteQuery());
		}
	}

	public static function getStaticLogNotifications(){
		$list = array();
		$list[0] = json_decode(json_encode((object)array('type' => 0, 'criticity' => 0, 'text' => 'Nouvelle réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> prévue le <b>[dateBooking]</b>')));
		$list[1] = json_decode(json_encode((object)array('type' => 1, 'criticity' => 0, 'text' => 'Nouveau devis <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> prévue le <b>[dateBooking]</b>')));
		$list[2] = json_decode(json_encode((object)array('type' => 2, 'criticity' => 1, 'text' => 'Nouvelle réservation <b>n°[idBooking]</b> créée par <b>[manager]</b> pour le client <b>[company] [lastName] [firstName]</b>')));
		$list[3] = json_decode(json_encode((object)array('type' => 3, 'criticity' => 1, 'text' => 'Nouveau devis <b>n°[idBooking]</b> créé par <b>[manager]</b> pour le client <b>[company] [lastName] [firstName]</b>')));
		$list[4] = json_decode(json_encode((object)array('type' => 4, 'criticity' => 1, 'text' => 'Edition de la réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[5] = json_decode(json_encode((object)array('type' => 5, 'criticity' => 1, 'text' => 'Edition du devis <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[6] = json_decode(json_encode((object)array('type' => 6, 'criticity' => 0, 'text' => 'Le devis <b>n°[idBooking]</b> a été accepté par le client <b>[company] [lastName] [firstName]</b>')));
		$list[7] = json_decode(json_encode((object)array('type' => 7, 'criticity' => 1, 'text' => 'Le devis <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a été accepté par <b>[manager]</b>')));
		$list[8] = json_decode(json_encode((object)array('type' => 8, 'criticity' => 1, 'text' => 'L\'état de paiement de la réservation <b>n°[idBooking]</b> a changé par <b>[manager]</b> en <b>[paymentState]</b>')));
		$list[9] = json_decode(json_encode((object)array('type' => 9, 'criticity' => 0, 'text' => 'Nouveau paiement (<b>[paymentState]</b>) pour la réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> par <b>[vendor]</b>')));
		$list[10] = json_decode(json_encode((object)array('type' => 10, 'criticity' => 1, 'text' => 'Nouveau paiement (<b>[paymentState]</b>) pour la réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> par <b>[vendor]</b>')));
		$list[11] = json_decode(json_encode((object)array('type' => 11, 'criticity' => 0, 'text' => 'Le devis <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a expiré')));
		$list[12] = json_decode(json_encode((object)array('type' => 12, 'criticity' => 0, 'text' => 'Le devis <b>n°[idBooking]</b> a été réfusé par le client <b>[company] [lastName] [firstName]</b>')));
		$list[13] = json_decode(json_encode((object)array('type' => 13, 'criticity' => 1, 'text' => 'Envoie de l\'email de connexion au compte client au client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[14] = json_decode(json_encode((object)array('type' => 14, 'criticity' => 1, 'text' => 'Envoie de l\'email de réservation pour la réservation <b>n°[idBooking]</b> au client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[15] = json_decode(json_encode((object)array('type' => 15, 'criticity' => 1, 'text' => 'Envoie d\'un email personnalisé au client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[16] = json_decode(json_encode((object)array('type' => 16, 'criticity' => 1, 'text' => 'Envoie de l\'email de paiement de la réservation <b>n°[idBooking]</b> au client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[17] = json_decode(json_encode((object)array('type' => 17, 'criticity' => 1, 'text' => 'Envoie de l\'email de devis pour le devis <b>n°[idBooking]</b> au client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		$list[18] = json_decode(json_encode((object)array('type' => 18, 'criticity' => 1, 'text' => 'Le devis <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a été relancé')));
		$list[19] = json_decode(json_encode((object)array('type' => 19, 'criticity' => 0, 'text' => 'Nouvelle demande de création compte par le client <b>[company] [lastName] [firstName]</b>')));
		$list[20] = json_decode(json_encode((object)array('type' => 20, 'criticity' => 0, 'text' => 'Le client <b>[company] [lastName] [firstName]</b> vous a envoyé un email')));
		$list[21] = json_decode(json_encode((object)array('type' => 21, 'criticity' => 0, 'text' => 'La demande de paiement pour la réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a expirée')));
		$list[22] = json_decode(json_encode((object)array('type' => 22, 'criticity' => 0, 'text' => 'La réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a été abandonnée')));
		$list[23] = json_decode(json_encode((object)array('type' => 23, 'criticity' => 1, 'text' => 'La réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> a été supprimé par <b>[manager]</b>')));
		$list[24] = json_decode(json_encode((object)array('type' => 24, 'criticity' => 1, 'text' => 'Nouveau remboursement pour la réservation <b>n°[idBooking]</b> du client <b>[company] [lastName] [firstName]</b> par <b>[manager]</b>')));
		return $list;
	}

	public static function generateNotificationTemplate($title, $content){
		$body = '<div><h2 style="">'.$title.'</h2><div class="article-content" style="text-align: left;">'.$content.'</div></div>';
		$logo = plugins_url('ui/assets/image/logo-resa-online.png', dirname(__FILE__));
		$emptyTemplate = '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="white" style="text-align: center;"><tbody><tr><td><center><table width="550" cellspacing="0" cellpadding="0" bgcolor="white" style="text-align: center;"><tbody><tr><td style="text-align: left;"><div><table id="header" style="font-size: 12px;color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff"><tbody><tr><td style="color: #ffffff;" colspan="2" height="30"><br></td></tr><tr><td style="color: #ffffff;" colspan="2" height="30">.</td></tr></tbody></table><div style="text-align: center;">[RESA_logo]</div><table id="header" style="font-size: 12px;color: #444;" border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff"><tbody><tr></tr><tr><td> <span style="font-size: 32px;"></span> </td></tr></tbody></table><table id="content" style="color: #444;font-size: 12px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff"><tbody><tr><td colspan="2">'. $body .'</td></tr></tbody></table><table id="footer" style="font-size: 12px;" border="0" width="490" cellspacing="0" cellpadding="0" bgcolor="#ffffff"><tbody><tr style="font-size: 11px;color: #999999;"><td colspan="2"><div> <a style="color: black;" href="https://resa-online.fr/">Propulsé par RESA-Online</a> </div></td></tr><tr><td style="color: #ffffff;" colspan="2" height="15">.</td></tr></tbody></table></div></td></tr></tbody></table></center></td></tr></tbody></table>';

		/* [RESA_logo] => <img src="'.$logo.'" style="width: 50%;"> */
		return $emptyTemplate;
	}

	public static function fillNotificationTemplate($varname, $title, $content, $subject = false){
		if($subject){
			update_option($varname, serialize(((object)array(get_locale() => $title))));
		}
		else {
			update_option($varname, serialize(((object)array(get_locale() => self::generateNotificationTemplate($title, $content)))));
		}
	}

	public static function fillNotificationTemplates(){
		if(get_locale() == 'fr_FR'){
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_booking_subject', '[RESA_company_name] : Votre réservation en ligne', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_booking_text', 'Votre réservation en ligne', '<p>Merci [RESA_customer_lastname] [RESA_customer_firstname] !</p><p>Nous avons bien enregistré votre demande de réservation.</p><p>Vous pouvez dès à présent vous rendre sur notre site pour consulter vos réservations en cliquant directement sur <a href="[RESA_link_account]" style="text-decoration-line: underline;">votre compte client</a><br></p><p><br></p><p>Note éventuelle :&nbsp;</p><p>[RESA_booking_note]<br></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_staff_booking_subject', '[RESA_company_name] : Nouvelle réservation en ligne n°[RESA_booking_id]', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_staff_booking_text', 'Nouvelle réservation en ligne n°[RESA_booking_id]', '<p>Une réservation vous a été attribuée au planning.</p><p>Détails :</p><p>[RESA_booking_details]</p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_booking_subject', '[RESA_company_name] : Nouvelle réservation en ligne n°[RESA_booking_id]', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_booking_text', 'Nouvelle réservation en ligne n°[RESA_booking_id]', '<p style="font-size: 13px;">Client : [RESA_customer_lastname] [RESA_customer_firstname].</p><p style="font-size: 13px;">Entreprise :&nbsp;[RESA_customer_company].</p><p style="font-size: 13px;"><a href="[RESA_customer_display]">Voir la réservation dans RESA-Online.</a></p><p style="font-size: 13px;">&nbsp;</p><p style="font-size: 13px;">Détails de la réservation :&nbsp;</p><p style="font-size: 13px;">[RESA_booking_details]</p><p style="font-size: 13px;">&nbsp;</p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_before_appointment_subject', '[RESA_company_name] : Rappel de votre réservation !', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_before_appointment_text', 'Nous attendons votre visite !', '<p>Merci [RESA_customer_lastname] [RESA_customer_firstname] pour votre réservation ! Nous attendons votre visite le [RESA_booking_first_date] !</p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_after_appointment_subject', '[RESA_company_name] : Nous avons apprécié votre visite !', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_after_appointment_text', 'Nous avons appréciez votre visite !', '<p>Merci [RESA_customer_lastname] [RESA_customer_firstname] ! Nous avons passé un très bon moment en votre compagnie !</p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_payment_subject', '[RESA_company_name] : Paiement de votre réservation reçu', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_payment_text', 'Paiement de votre réservation reçu.', '<p style="font-size: 13px;">Merci [RESA_customer_lastname] [RESA_customer_firstname],</p><p style="font-size: 13px;">Nous avons bien reçu le paiement pour la réservation n°[RESA_booking_id].</p><p style="font-size: 13px;"><a href="[RESA_link_account]"> Rendez-vous sur votre compte client pour visualiser votre réservation.</a></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_payment_booking_subject', '[RESA_company_name] : Paiement de la réservation n°[RESA_booking_id] reçu', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_payment_booking_text', 'Paiement de la réservation n°[RESA_booking_id] reçu', '<p style="font-size: 13px;">Client : [RESA_customer_lastname] [RESA_customer_firstname].</p><p style="font-size: 13px;">Entreprise :&nbsp;[RESA_customer_company].</p><p style="font-size: 13px;">Paiement reçu pour la réservation n°[RESA_booking_id].</p><p style="font-size: 13px;"><a href="[RESA_customer_display]">Voir la réservation dans RESA-Online.</a></p><p style="font-size: 13px;">&nbsp;</p><p style="font-size: 13px;">&nbsp;</p><p style="font-size: 13px;">Détails de la réservation :&nbsp;</p><p style="font-size: 13px;">[RESA_booking_details]</p><p style="font-size: 13px;">&nbsp;</p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_need_payment_subject', '[RESA_company_name] : Confirmer votre réservation', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_need_payment_text', 'Confirmer votre réservation', '<p style="font-size: 13px;">Bonjour [RESA_customer_lastname] [RESA_customer_firstname],</p><p style="font-size: 13px;">Afin de valider votre réservation, veuillez régler la totalité en ligne avant votre venue.</p><p style="font-size: 13px;"><a href="[RESA_link_payment_booking]"> Vous pouvez dès maintenant en cliquant ici</a></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_customer_subject', '[RESA_company_name] : Votre demande de devis', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_customer_text', 'Votre demande de devis', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname] !</p><p> Nous avons bien enregistré votre demande de devis.</p><p>Nous revenons vers vous très rapidement.</p><p><a href="[RESA_link_account]">Accédez à votre compte client</a></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_quotation_requests_subject', '[RESA_company_name] : Nouvelle demande de devis', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_quotation_requests_text', 'Nouvelle demande de devis', '<p>Le client [RESA_customer_lastname] [RESA_customer_firstname] à fait une demande de devis en ligne.</p><p> <a href="[RESA_customer_display]" target="">Veuillez vous rendre ici&nbsp;</a>pour voir la demande de devis.</p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_customer_booking_subject', '[RESA_company_name] : Votre devis en ligne', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_customer_booking_text', 'Votre devis en ligne', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname].</p><p> Nous avons enregistré un devis pour votre réservation.</p><p>Vous pouvez dès à présent vous rendre sur notre site pour le consulter pour cela suivez ce lien :&nbsp;&nbsp;<a href="[RESA_link_account]">votre compte client</a><br></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_accepted_customer_subject', '[RESA_company_name] : Votre devis en ligne', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_quotation_accepted_customer_text', 'Votre demande de devis', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname] !</p><p> Nous avons répondu à votre demande de devis.</p><p>Vous pouvez dès à présent consulter le devis et l\'accepter directement depuis <a href="[RESA_link_account]">votre compte client</a></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_quotation_answered_subject', '[RESA_company_name] : Acceptation du devis en ligne', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_quotation_answered_text', 'Acceptation du devis en ligne', '<p>[RESA_customer_lastname] [RESA_customer_firstname] a <b>accepté le devis en ligne.</b></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_no_response_quotation_subject', '[RESA_company_name] : Votre devis en ligne est en attente', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_no_response_quotation_text', 'Votre devis en ligne est en attente', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname].</p><p> Nous avons enregistré un devis pour votre réservation qui est toujours en attente de votre validation</p><p>Vous pouvez dès à présent vous rendre sur notre site pour le consulter et le valider.</p><p>Pour cela suivez ce lien :&nbsp;&nbsp;<a href="[RESA_link_account]">votre compte client</a><br></p><p><br></p><p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_account_creation_subject', '[RESA_company_name] : Votre compte client', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_account_creation_text', 'Votre compte client', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname].</p><p>Nous vous avons créer un compte client sur notre site internet.</p><p>Vous pouvez dès à présent y accéder en suivant simplement ce lien :&nbsp;<a href="[RESA_link_account]">votre compte client</a><br></p><p><br></p><p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_password_reinit_subject', '[RESA_company_name] : Votre compte client', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_password_reinit_text', 'Votre compte client', '<p style="font-size: 13px;">Bonjour [RESA_customer_lastname] [RESA_customer_firstname].</p><p style="font-size: 13px;">Vous pouvez dès à présent y accéder en suivant simplement ce lien :&nbsp;<a href="[RESA_link_account]" style="background-color: transparent;">votre compte client</a><br></p><p><br></p><p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_ask_account_subject', '[RESA_company_name] : Votre demande de compte', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_ask_account_text', 'Votre demande de compte', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname].</p><p>Nous avons bien enregistré votre demande de création de compte</p><p>Nous revenons vers vous très rapidement.</p><p><br></p><p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_ask_account_subject', '[RESA_company_name] : Nouvelle demande de compte', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_emails_ask_account_text', 'Nouvelle demande de compte', '<p style="font-size: 13px;">Client : [RESA_customer_lastname] [RESA_customer_firstname].</p><p style="font-size: 13px;">Entreprise :&nbsp;[RESA_customer_company].</p><p style="font-size: 13px;"><a href="[RESA_customer_display]">Voir le compte client.</a></p><p style="font-size: 13px;">&nbsp;</p><p style="font-size: 13px;">&nbsp;</p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_accepted_account_subject', '[RESA_company_name] : Demande de compte accepté', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_accepted_account_text', 'Demande de compte accepté', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname] !</p><p> Nous avons accepté votre demande de compte.</p><p>Vous pouvez dès à présent vous connecter à <a href="[RESA_link_account]">votre compte client</a></p><p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');

			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_refused_account_subject', '[RESA_company_name] : Demande de compte refusé', '', true);
			self::fillNotificationTemplate('resa_settings_notifications_email_notification_customer_refused_account_text', 'Demande de compte refusé', '<p>Bonjour [RESA_customer_lastname] [RESA_customer_firstname] !</p><p> Nous avons refusé votre demande de compte.</p><p>Veuillez nous contacter pour plus de détails<p><br></p><p>Toute l\'équipe de [RESA_company_name] vous remercie<br></p><p><br></p>');
		}
	}

	public static function defaultStateParameters(){
		$states_parameters = array((object) array(
			'id' => 1,
			'name' => __('automatic_validation_parameter_words', 'resa'),
			'description' => __('automatic_validation_parameter_description_words', 'resa'),
			'state' => 'ok',
			'paiement' => true,
			'paiementState' => 'ok',
			'capacity' => true,
			'expired' => true,
			'form' => (object) array(),
			'stateBackend' => 'ok',
			'paiementBackend' => true,
			'paiementStateBackend' => 'ok',
			'capacityBackend' => true,
			'expiredBackend' => true
		),
		(object)array(
			'id' => 2,
			'name' => __('payment_validation_parameter_words', 'resa'),
			'description' => __('payment_validation_parameter_description_words', 'resa'),
			'state' => 'waiting',
			'paiement' => true,
			'paiementState' => 'ok',
			'capacity' => false,
			'expired' => false,
			'form' => (object) array(),
			'stateBackend' => 'waiting',
			'paiementBackend' => true,
			'paiementStateBackend' => 'ok',
			'capacityBackend' => false,
			'expiredBackend' => false
		),
		(object)array(
			'id' => 3,
			'name' => __('payment_booking_validation_parameter_words', 'resa'),
			'description' => __('payment_booking_validation_parameter_description_words', 'resa'),
			'state' => 'waiting',
			'paiement' => true,
			'paiementState' => 'ok',
			'capacity' => true,
			'expired' => true,
			'form' => (object) array(),
			'stateBackend' => 'waiting',
			'paiementBackend' => true,
			'paiementStateBackend' => 'ok',
			'capacityBackend' => true,
			'expiredBackend' => true
		));
		update_option('resa_settings_states_parameters', serialize($states_parameters));
	}


}
