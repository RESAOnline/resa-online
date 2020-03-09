<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_BackendController extends RESA_Controller
{
	public function getSlug()
	{
		return 'resa_appointments';
	}

	public function getPageName()
	{
		return __('bookings_title', 'resa');
	}

	public function isSettings()
	{
		return false;
	}

	public function getClassDir()
	{
		return __DIR__;
	}


	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts()
	{
		return array_merge(self::$GLOBAL_SCRIPTS, array(
			'libs/calendar/moment.min',
			'libs/calendar/interact.min',
			'libs/calendar/angular-bootstrap-calendar-tpls.min',
			'libs/easeljs.min',
			'directive/wpEditorWrapper',
			'manager/StorageManager',
			'manager/PaymentManagerForm',
			'manager/FormManager',
			'manager/RESANewsManager',
			'manager/AppointmentManager',
			'manager/CalendarManager',
			'manager/FunctionsManager',
			'manager/BackendManager',
			'manager/EditBookingManager',
			'manager/CustomerManager',
			'manager/NewAddPaymentManager',
			'manager/NewAskPaymentManager',
			'manager/NotificationManager',
			'manager/NewEditInfoCalendarManager',
			'manager/NewEditServiceConstraintManager',
			'manager/NewAttachBookingManager',
			'manager/NewMergeCustomerManager',
			'manager/NewPayBookingManager',
			'manager/NewReceiptBookingManager',
			'manager/GroupsManager',
			'manager/GroupManager',
			'manager/ParticipantSelectorManager',
			'manager/PaymentStateManager',
			'manager/QuotationsListManager',
			'manager/LogNotificationsCenterManager',
			'manager/DisplayBookingsManager',
			'manager/PlanningServiceManager',
			'factory/ServiceParameters',
			'factory/Basket',
			'factory/Timeslot',
			'controller/BackendController',
			'controller/EditBookingController',
			'controller/CustomerController',
			'controller/NewAddPaymentController',
			'controller/NewAskPaymentController',
			'controller/NotificationController',
			'controller/NewEditInfoCalendarController',
			'controller/NewEditServiceConstraintController',
			'controller/NewAttachBookingController',
			'controller/NewMergeCustomerController',
			'controller/NewPayBookingController',
			'controller/NewReceiptBookingController',
			'controller/GroupsController',
			'controller/GroupController',
			'controller/ParticipantSelectorController',
			'controller/PaymentStateController',
			'controller/QuotationsListController',
			'controller/LogNotificationsCenterController',
			'controller/DisplayBookingsController',
			'controller/PlanningServiceController'
		));
	}



	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		return array(
			'font-awesome.min',
			'angular-bootstrap-calendar.min',
      'resa_admin',
      'resa_admin_responsive',
      'resa_reciept'
		);
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->noDisplayAdminMenu();
		$this->noDisplayAdminBar();
		$this->disabledAnotherCSSPlugin();

		$settings = array(
			'version' => RESA_Variables::getVersion(),
			'staffIsConnected' => RESA_Variables::staffIsConnected(),
			'staffManagement' => RESA_Variables::isStaffManagementActivated(),
			'staff_old_appointments_displayed' => (RESA_Tools::toJSONBoolean(get_option('resa_staff_old_appointments_displayed'))=='true'),
			'staff_display_customer' => get_option('resa_staff_appointment_display_customer'),
			'staff_display_total' => get_option('resa_staff_appointment_display_total'),
			'staff_display_payments' => get_option('resa_staff_appointment_display_payments'),
			'staff_display_numbers' => get_option('resa_staff_appointment_display_numbers'),
			'staff_display_bookings_tab' => get_option('resa_staff_display_bookings_tab'),
			'calendar'=>array(
				'start_time' => get_option('resa_settings_calendar_start_time'),
				'end_time' => get_option('resa_settings_calendar_end_time'),
				'split_time' => get_option('resa_settings_calendar_split_time'),
				'info_calendar_color' => get_option('resa_settings_calendar_info_calendar_color'),
				'service_constraint_color' => get_option('resa_settings_calendar_service_constraint_color')
			)
		);

		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);
		if($currentRESAUser->isRESAStaff()){
			$membersIdCustomerLinked = RESA_Member::getAllData(array('idCustomerLinked' => $currentRESAUser->getId()));
			if(count($membersIdCustomerLinked) == 1 && $membersIdCustomerLinked[0]->isSetPermissions()){
				$associatedMember = $membersIdCustomerLinked[0];
				$associatedMember->applyPermissions($settings);
			}
		}

		$this->renderer('RESA_backend', array(
			'currentUrl'=>RESA_Variables::getCurrentPageURL(),
			'date_format'=> RESA_Tools::wpToJSDateFormat(),
			'time_format'=> RESA_Tools::wpToJSTimeFormat(),
			'months'=>RESA_Variables::getJSONMonths(),
			'countries' => RESA_Variables::getJSONCountries(),
			'calendar'=>array(
				'start_time' => get_option('resa_settings_calendar_start_time'),
				'end_time' => get_option('resa_settings_calendar_end_time'),
				'split_time' => get_option('resa_settings_calendar_split_time'),
				'info_calendar_color' => get_option('resa_settings_calendar_info_calendar_color'),
				'service_constraint_color' => get_option('resa_settings_calendar_service_constraint_color')),
			'settings' => $settings,
			'groupsManagement' => RESA_Variables::isGroupsManagementActivated()
		));
	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods()
	{
		$this->addAjaxMethod('initializationDataAppointments');
		$this->addAjaxMethod('getAppointmentsByDates');
		$this->addAjaxMethod('getQuotations');
		$this->addAjaxMethod('getLogNotifications');
		$this->addAjaxMethod('getLogNotificationsHistory');
		$this->addAjaxMethod('updateBackend');
		$this->addAjaxMethod('updateBackendCustomer');
		$this->addAjaxMethod('updateBackendQuotations');
		$this->addAjaxMethod('sendEmailToCustomer');
		$this->addAjaxMethod('updateNotificationTemplates');
		$this->addAjaxMethod('deleteBooking');
		$this->addAjaxMethod('editInfoCalendar');
		$this->addAjaxMethod('deleteInfoCalendar');
		$this->addAjaxMethod('editServiceConstraint');
		$this->addAjaxMethod('deleteServiceConstraint');
		$this->addAjaxMethod('getGroups');
		$this->addAjaxMethod('editGroups');
		$this->addAjaxMethod('deleteGroup');
		$this->addAjaxMethod('getCustomerById');
		$this->addAjaxMethod('editCustomer');
		$this->addAjaxMethod('deleteCustomer');
		$this->addAjaxMethod('sendCustomEmail');
		$this->addAjaxMethod('sentMessagePasswordReinitialization');
		$this->addAjaxMethod('addPayments');
		$this->addAjaxMethod('askPayment');
		$this->addAjaxMethod('cancelPayment');
		$this->addAjaxMethod('attachBooking');
		$this->addAjaxMethod('mergeCustomer');
		$this->addAjaxMethod('clearSynchronizationCaisseOnline');
		$this->addAjaxMethod('payBookingCaisseOnline');
		$this->addAjaxMethod('payBookingsCaisseOnline');
		$this->addAjaxMethod('calculateBookingPaymentState');
		$this->addAjaxMethod('setPaymentStateBooking');
		$this->addAjaxMethod('acceptQuotationBackend');
		$this->addAjaxMethod('acceptQuotationAndAskPayment');
		$this->addAjaxMethod('seeAllLogNotifications');
		$this->addAjaxMethod('seeAllRESANews');
		$this->addAjaxMethod('clickAllLogNotifications');
		$this->addAjaxMethod('clickOnLogNotifications');
		$this->addAjaxMethod('unreadLogNotification');
		$this->addAjaxMethod('acceptAskAccount');
		$this->addAjaxMethod('forceProcessEmails');
		$this->addAjaxMethod('deleteEmailCustomer');
	}


	/**
	 * Return the initialization data
	 */
	public function initializationDataAppointments(){
		$allServices = RESA_Service::getAllData(array());
		$allReductions = RESA_Reduction::getAllData(array());
		$members = RESA_Member::getAllData(array(), 'position');
		$equipments = RESA_Equipment::getAllData(array(), 'position');
		$infoCalendar = new RESA_InfoCalendar();
		$serviceConstraint = new RESA_ServiceConstraint();
		$memberConstraint = new RESA_MemberConstraint();
		$customer = new RESA_Customer();
		$bookings = array();
		$groups = array();

		$paymentsTypeList = RESA_Variables::paymentsTypeList();
		$statePaymentsList = RESA_Variables::statePaymentsList();
		$statesList = RESA_Variables::statesList();
		$appointmentTags = RESA_Variables::tagsList();

		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);

		$caisseUrl = '';
		$apiCaisseUrl = '';
		if(class_exists('RESA_CaisseOnline')){
			$caisseUrl = RESA_CaisseOnline::getInstance()->getURLCaisse();
			$apiCaisseUrl = RESA_CaisseOnline::getInstance()->getBaseURL();
		}

		$settings = array(
			'version' => RESA_Variables::getVersion(),
			'currentVersion' => RESA_Variables::getVersion(),
			'newUpdate' => RESA_Variables::haveNewVersion(),
			'currency' => get_option('resa_settings_payment_currency'),
			'notification_customer_booking' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_booking')) == 'true',
			'notification_customer_password_reinit' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_password_reinit')) == 'true',
			'notification_quotation_accepted_customer' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer')) == 'true',
			'notification_customer_accepted_account' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_customer_accepted_account')) == 'true',
			'notification_after_appointment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_notifications_email_notification_after_appointment')) == 'true',
			'places' => unserialize(get_option('resa_settings_places')),
			'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
			'calendar'=>array(
				'start_time' => get_option('resa_settings_calendar_start_time'),
				'end_time' => get_option('resa_settings_calendar_end_time'),
				'split_time' => get_option('resa_settings_calendar_split_time'),
				'info_calendar_color' => get_option('resa_settings_calendar_info_calendar_color'),
				'service_constraint_color' => get_option('resa_settings_calendar_service_constraint_color'),
				'drag_and_drop_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_drag_and_drop_activated')) == 'true'
			),
			'company_name' => get_option('resa_settings_company_name'),
			'company_logo' => get_option('resa_settings_company_logo'),
			'company_address' => get_option('resa_settings_company_address'),
			'company_phone' => get_option('resa_settings_company_phone'),
			'company_type' => get_option('resa_settings_company_type'),
			'company_siret' => get_option('resa_settings_company_siret'),
			'tvaActivated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_display_tva_on_bill'))  == 'true',
			'informations_on_receipt'=>unserialize(get_option('resa_settings_informations_on_bill')),
			'informations_on_quotation'=>unserialize(get_option('resa_settings_informations_on_quotation')),
			'senders' => unserialize(get_option('resa_settings_senders')),
			'staffIsConnected' => RESA_Variables::staffIsConnected(),
			'staffManagement' => RESA_Variables::isStaffManagementActivated(),
			'groupsManagement' => RESA_Variables::isGroupsManagementActivated(),
			'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
			'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
			'staff_old_appointments_displayed' => (RESA_Tools::toJSONBoolean(get_option('resa_staff_old_appointments_displayed'))=='true'),
			'staff_display_customer' => (RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_customer'))=='true'),
			'staff_display_total' => get_option('resa_staff_appointment_display_total'),
			'staff_display_payments' => get_option('resa_staff_appointment_display_payments'),
			'staff_display_numbers' => get_option('resa_staff_appointment_display_numbers'),
			'staff_display_bookings_tab' => get_option('resa_staff_display_bookings_tab'),
			'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
			'askAdvancePaymentTypeAccounts' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
			'firstParameterDone' => RESA_Tools::toJSONBoolean(get_option('resa_first_parameter_done')) == 'true',
			'caisse_online_activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true' && class_exists('RESA_CaisseOnline'),
			'caisse_online_activated_but_nocaisseonline'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true' && !class_exists('RESA_CaisseOnline'),
			'caisse_online_site_url'=> $caisseUrl,
			'caisse_online_server_url'=>$apiCaisseUrl,
			'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
			'stripeConnectId' =>get_option('resa_settings_payment_stripe_connect_id'),
			'apiStripeConnectUrl' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage() && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::getURL():'',
			'settings_link' => RESA_Variables::getLinkParameters('activities', $currentRESAUser),
			'daily_link' => RESA_Variables::getLinkParameters('daily', $currentRESAUser),
			'vouchers_link' => RESA_Variables::getLinkParameters('vouchers', $currentRESAUser),
			'support_url' => RESA_Variables::getURLSupport($currentRESAUser->getEmail()),
			'swikly_link' => (class_exists('RESA_Swikly') && get_option('resa_settings_payment_swikly', false) &&  get_option('resa_settings_payment_swikly_display_link',0)!=0)?RESA_Swikly::getSwiklyLink():'',
			'lastRESANewsId' => $currentRESAUser->getLastRESANewsId(),
			'mailbox_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_mailbox_activated'))=='true',
			'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
			'customer_account_url' => get_option('resa_settings_customer_account_url'),
			'payment_ask_advance_payment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_ask_advance_payment'))=='true',
			'payment_ask_text_advance_payment' => unserialize(get_option('resa_settings_payment_ask_text_advance_payment')),
			'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
			'form_category_services' => unserialize(get_option('resa_settings_form_category_services')),
			'statePaymentsList' => $statePaymentsList,
			'statesList' => $statesList,
			'appointmentTags' => $appointmentTags,
			'vatList' => unserialize(get_option('resa_settings_vat_list')),
			'allLanguages' => RESA_Variables::getLanguages(),
			'languages' => unserialize(get_option('resa_settings_languages')),
			'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),
			'no_response_quotation_times' => intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_times')),
			'notifications' => array(
				'notification_customer_booking_subject'=>unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_subject')),
				'notification_customer_booking_text'=>unserialize(get_option('resa_settings_notifications_email_notification_customer_booking_text')),
				'notification_after_appointment_subject'=>unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_subject')),
				'notification_after_appointment_text'=>unserialize(get_option('resa_settings_notifications_email_notification_after_appointment_text')),
				'notification_customer_need_payment_subject'=>unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_subject')),
				'notification_customer_need_payment_text'=>unserialize(get_option('resa_settings_notifications_email_notification_customer_need_payment_text')),
				'notification_quotation_customer_booking_subject'=>unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_subject')),
				'notification_quotation_customer_booking_text'=>unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text')),
				'notification_quotation_accepted_customer_booking_subject'=>unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_subject')),
				'notification_quotation_accepted_customer_booking_text'=>unserialize(get_option('resa_settings_notifications_email_notification_quotation_accepted_customer_text')),
				'notification_quotation_customer_booking_text'=>unserialize(get_option('resa_settings_notifications_email_notification_quotation_customer_booking_text')),
				'notifications_templates' => unserialize(get_option('resa_settings_notifications_email_notifications_templates')),
				'notifications_templates_last_modification_date' => get_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'))
			),
			'needUpdate' => get_option('resa_update_customers', -1) != -1
		);

		$associatedMember = new RESA_Member();
		if($currentRESAUser->isRESAStaff()){
			$membersIdCustomerLinked = RESA_Member::getAllData(array('idCustomerLinked' => $currentRESAUser->getId()));
			if(count($membersIdCustomerLinked) == 1 && $membersIdCustomerLinked[0]->isSetPermissions()){
				$associatedMember = $membersIdCustomerLinked[0];
				$associatedMember->applyPermissions($settings);
			}
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
		$limit = 10;
		$logNotifications = RESA_LogNotification::getAllDataWithLimit(array('criticity' => 0), $places, $limit);
		$logNotifications = array_merge($logNotifications, RESA_LogNotification::getAllDataWithLimit(array('criticity' => 1), $places, $limit));
		usort($logNotifications, array("RESA_LogNotification", "compare"));

		$skeletonPayment = new RESA_Payment();
		$skeletonGroup = new RESA_Group();

		$customersManagers = RESA_Customer::getAllDataManagers();

		echo  '{
			"customersManagers":'.RESA_Tools::formatJSONArray($customersManagers).',
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"services":'.RESA_Tools::formatJSONArray($allServices).',
			"reductions":'.RESA_Tools::formatJSONArray($allReductions).',
			"members":'.RESA_Tools::formatJSONArray($members).',
			"equipments":'.RESA_Tools::formatJSONArray($equipments).',
			"associatedMember":'.$associatedMember->toJSON().',
			"groups":'.RESA_Tools::formatJSONArray($groups).',
			"currentRESAUser":'.$currentRESAUser->toJSON().',
			"token":"'.$currentRESAUser->getToken().'",
			"jsonURL":"'.site_url('wp-json').'",
			"skeletonInfoCalendar":'. $infoCalendar->toJSON().',
			"skeletonServiceConstraint":'. $serviceConstraint->toJSON().',
			"skeletonMemberConstraint":'. $memberConstraint->toJSON().',
			"skeletonCustomer":'. $customer->toJSON().',
			"settings":'.json_encode($settings).',
			"paymentsTypeList":'.json_encode($paymentsTypeList).',
			"idPaymentsTypeToName":'.json_encode(RESA_Variables::idPaymentsTypeToName()).',
			"logNotificationsWaitingNumber":'.RESA_LogNotification::countDataIdCustomer($currentRESAUser->getId(), $places).',
			"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
			"currentPromoCodes":'.json_encode(RESA_Reduction::getAllPromoCodes()).',
			"skeletonPayment":'. $skeletonPayment->toJSON().',
			"skeletonGroup":'. $skeletonGroup->toJSON().'
		}';

		wp_die();
	}


	/**
	 * Return appointments by dates
	 */
	public function getAppointmentsByDates(){
		$startDate = new DateTime();
		$endDate = new DateTime();
		$filters = array();
		$places = array();

		if(isset($_REQUEST['startDate'])) $startDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('d-m-Y H:i:s', sanitize_text_field($_REQUEST['startDate'])));
		if(isset($_REQUEST['endDate'])) $endDate = DateTime::createFromFormat('d-m-Y H:i:s', sanitize_text_field($_REQUEST['endDate']));
		if(isset($_REQUEST['filters'])) $filters = json_decode(stripslashes(sanitize_text_field($_REQUEST['filters'])));
		if(isset($filters) && isset($filters->places) && count((array)$filters->places) > 0){
			$currentRESAUser = new RESA_Customer();
			$currentRESAUser->loadCurrentUser(false);

			$oldFilters = $currentRESAUser->getFilterSettings();
			if(isset($oldFilters->favPage)) $filters->favPage = $oldFilters->favPage;
			$currentRESAUser->setFilterSettings($filters);
			$currentRESAUser->save(false);
			$filterPlaces = (array)$filters->places;
			foreach($filterPlaces as $key => $value){
				if($value) {
					array_push($places, $key);
				}
			}
		}
		$bookings = RESA_Booking::getAllData(array('oldBooking'=>false), $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $places);
		$customers = array();
		$allIdBooking = array();
		$allIdAppointments = array();
		foreach($bookings as $booking){
			$customer = $booking->getCustomer();
			if($customer->isLoaded()){
				array_push($customers, $customer);
			}
			array_push($allIdBooking, $booking->getId());
			$allIdAppointments = array_merge($allIdAppointments, $booking->getAllIdAppointments());
		}
		$groups = RESA_Group::getAllData(
			array('startDate' => array($startDate->format('Y-m-d H:i:s'), '>='),
					'endDate' => array($endDate->format('Y-m-d H:i:s'), '<='),
					'idPlace'=>array($places)));

		$alerts = RESA_Alert::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'), array('idBooking' => array($allIdBooking)), array('idAppointment' => array($allIdAppointments)));
		$infoCalendars = RESA_InfoCalendar::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'), $places);
		$serviceConstraints = RESA_ServiceConstraint::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'));
		$memberConstraints = RESA_MemberConstraint::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'));

		echo '{
			"groups":'.RESA_Tools::formatJSONArray($groups).',
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"customers":'.RESA_Tools::formatJSONArray($customers).',
			"alerts":'.RESA_Tools::formatJSONArray($alerts).',
			"infoCalendars":'.RESA_Tools::formatJSONArray($infoCalendars).',
			"serviceConstraints":'.RESA_Tools::formatJSONArray($serviceConstraints).',
			"memberConstraints":'.RESA_Tools::formatJSONArray($memberConstraints).'
		}';
		wp_die();
	}


	/**
	 * Return quotations
	 */
	public function getQuotations(){
		$offset = 0;
		$limit = 10;
		$filters = array();
		$places = array();
		if(isset($_REQUEST['offset']) && is_numeric($_REQUEST['offset'])) $offset = sanitize_text_field($_REQUEST['offset']);
		if(isset($_REQUEST['limit']) && is_numeric($_REQUEST['limit'])) $limit = sanitize_text_field($_REQUEST['limit']);
		if(isset($_REQUEST['filters'])) $filters = json_decode(stripslashes(sanitize_text_field($_REQUEST['filters'])));
		if(isset($filters) && isset($filters->places) && count((array)$filters->places) > 0){
			$currentRESAUser = new RESA_Customer();
			$currentRESAUser->loadCurrentUser(false);
			$oldFilters = $currentRESAUser->getFilterSettings();
			if(isset($oldFilters->favPage)) $filters->favPage = $oldFilters->favPage;
			$currentRESAUser->setFilterSettings($filters);
			$currentRESAUser->save(false);
			$filterPlaces = (array)$filters->places;
			foreach($filterPlaces as $key => $value){
				if($value) {
					array_push($places, $key);
				}
			}
		}
		$countData = RESA_Booking::countDataWithLimit(array('oldBooking'=>0, 'quotation' => 1), $offset, $limit);
		$bookings = RESA_Booking::getAllDataWithLimit(array('oldBooking'=>0, 'quotation' => 1), $offset, $limit, $places);
		$customers = array();
		foreach($bookings as $booking){
			$customer = $booking->getCustomer();
			if($customer->isLoaded()){
				array_push($customers, $customer);
			}
		}

		echo '{
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"customers":'.RESA_Tools::formatJSONArray($customers).',
			"countData":'.$countData.'
		}';
		wp_die();
	}

	/**
	 * Return log notifications
	 */
	public function getLogNotifications(){
		$limit = 10;
		$criticity = 0;
		$lastIdLogNotifications = -1;
		if(isset($_REQUEST['limit']) && is_numeric($_REQUEST['limit'])) $limit = sanitize_text_field($_REQUEST['limit']);
		if(isset($_REQUEST['criticity']) && is_numeric($_REQUEST['criticity'])) $criticity = sanitize_text_field($_REQUEST['criticity']);
		if(isset($_REQUEST['lastIdLogNotifications']) && is_numeric($_REQUEST['lastIdLogNotifications'])) $lastIdLogNotifications = sanitize_text_field($_REQUEST['lastIdLogNotifications']);
		$data = array('id' => array($lastIdLogNotifications, '<'));
		if($criticity != -1){
			$data['criticity'] = $criticity;
		}
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);
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
		echo '{
			"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).'
		}';
		wp_die();
	}

	/**
	 * Return log notifications of id Booking
	 */
	public function getLogNotificationsHistory(){
		$idBooking = -1;
		if(isset($_REQUEST['idBooking']) && is_numeric($_REQUEST['idBooking'])) $idBooking = sanitize_text_field($_REQUEST['idBooking']);
		$logNotifications = RESA_LogNotification::getAllData(array('idBooking' => $idBooking));
		echo RESA_Tools::formatJSONArray($logNotifications);
		wp_die();
	}

	/**
	 * Return the new bookings
	 */
	public function updateBackend(){
		$startDate = new DateTime();
		$endDate = new DateTime();
		$filters = array();
		$places = array();

		if(isset($_REQUEST['startDate'])) $startDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('d-m-Y H:i:s', sanitize_text_field($_REQUEST['startDate'])));
		if(isset($_REQUEST['endDate'])) $endDate = DateTime::createFromFormat('d-m-Y H:i:s', sanitize_text_field($_REQUEST['endDate']));
		if(isset($_REQUEST['filters'])) $filters = json_decode(stripslashes(sanitize_text_field($_REQUEST['filters'])));
		if(isset($filters) && isset($filters->places) && count((array)$filters->places) > 0){
			$filterPlaces = (array)$filters->places;
			foreach($filterPlaces as $key => $value){
				if($value) {
					array_push($places, $key);
				}
			}
		}
		$lastModificationDateBooking = new DateTime();
		$lastModificationDateCustomer = new DateTime();
		$lastModificationDateGroup = new DateTime();
		$lastModificationDateNotificationsTemplates = new DateTime();
		$lastIdLogNotifications = -1;
		if(isset($_REQUEST['lastModificationDateBooking'])){
			$lastModificationDateBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateBooking'])));
			$lastModificationDateBooking = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateBooking);
		}
		if(isset($_REQUEST['lastModificationDateCustomer'])){
			$lastModificationDateCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateCustomer'])));
			$lastModificationDateCustomer = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateCustomer);
		}
		if(isset($_REQUEST['lastModificationDateGroup'])){
			$lastModificationDateGroup = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateGroup'])));
			$lastModificationDateGroup = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateGroup);
		}
		if(isset($_REQUEST['lastModificationDateNotificationsTemplates'])){
			$lastModificationDateNotificationsTemplates = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateNotificationsTemplates'])));
			$lastModificationDateNotificationsTemplates = DateTime::createFromFormat('Y-m-d H:i:s', $lastModificationDateNotificationsTemplates);
		}
		if(isset($_REQUEST['lastIdLogNotifications']) && is_numeric(json_decode($_REQUEST['lastIdLogNotifications']))){
			$lastIdLogNotifications = json_decode(sanitize_text_field($_REQUEST['lastIdLogNotifications']));
		}

		$lastModificationDateBooking = $lastModificationDateBooking->format('Y-m-d H:i:s');
		$lastModificationDateCustomer = $lastModificationDateCustomer->format('Y-m-d H:i:s');
		$lastModificationDateGroup = $lastModificationDateGroup->format('Y-m-d H:i:s');
		$currentLastModificationDateNotificationsTemplates = DateTime::createFromFormat('Y-m-d H:i:s', get_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s')));

		$bookings = RESA_Booking::getAllData(array('oldBooking'=>false, 'modificationDate'=> array($lastModificationDateBooking, '>')), $startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s'), $places);
		$customers =  RESA_Customer::getAllDataMoreThanModificationDate($lastModificationDateCustomer);

		$groups = RESA_Group::getAllData(array('lastModificationDate' => array($lastModificationDateGroup, '>'), 'startDate' => array($startDate->format('Y-m-d H:i:s'), '>='), 'endDate' => array($endDate->format('Y-m-d H:i:s'), '<='), 'idPlace'=>array($places)));

		$settings = array();
		if($lastModificationDateNotificationsTemplates < $currentLastModificationDateNotificationsTemplates){
			$settings['notifications'] = array(
				'notifications_templates' => unserialize(get_option('resa_settings_notifications_email_notifications_templates')),
				'notifications_templates_last_modification_date' => get_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'))
			);
		}
		$settings['currentVersion'] = RESA_Variables::getVersion();
		$settings['nextVersion'] = RESA_Variables::getVersion();
		$settings['newUpdate'] = RESA_Variables::haveNewVersion();

		$logNotifications = RESA_LogNotification::getAllData(array('id' => array($lastIdLogNotifications, '>')), $places);
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);

		$allIdBooking = array();
		$allIdAppointments = array();
		foreach($bookings as $booking){
			$customer = $booking->getCustomer();
			if($customer->isLoaded()){
				array_push($customers, $customer);
			}
			array_push($allIdBooking, $booking->getId());
			$allIdAppointments = array_merge($allIdAppointments, $booking->getAllIdAppointments());
		}
		$groups = RESA_Group::getAllData(
			array('startDate' => array($startDate->format('Y-m-d H:i:s'), '>='),
					'endDate' => array($endDate->format('Y-m-d H:i:s'), '<='),
					'idPlace'=>array($places)));

		$alerts = RESA_Alert::getAllDataWithDate($startDate->format('Y-m-d H:i:s'),  $endDate->format('Y-m-d H:i:s'), array('idBooking' => array($allIdBooking)), array('idAppointment' => array($allIdAppointments)));
		echo '{
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"alerts":'.RESA_Tools::formatJSONArray($alerts).',
			"groups":'.RESA_Tools::formatJSONArray($groups).',
			"customers":'.RESA_Tools::formatJSONArray($customers).',
			"logNotificationsWaitingNumber":'.RESA_LogNotification::countDataIdCustomer($currentRESAUser->getId(), $places).',
			"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
			"settings":'.json_encode($settings).',
			"lastDateBackend":"'.date('Y-m-d H:i:s', current_time('timestamp')).'"
		}';
		wp_die();
	}


	/**
	 * Return the new bookings for customer
	 */
	public function updateBackendCustomer(){
		$lastModificationDateBooking = new DateTime();
		$idCustomer = -1;
		$lastIdLogNotifications = -1;
		$lastIdEmailCustomer = -1;
		if(isset($_REQUEST['lastModificationDateBooking'])){
			$lastModificationDateBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateBooking'])));
			$lastModificationDateBooking = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateBooking);
		}
		$lastModificationDateBooking = $lastModificationDateBooking->format('Y-m-d H:i:s');
		if(isset($_REQUEST['idCustomer']) && is_numeric(json_decode($_REQUEST['idCustomer']))) $idCustomer = json_decode(stripslashes($_REQUEST['idCustomer']));
		if(isset($_REQUEST['lastIdLogNotifications']) && is_numeric(json_decode($_REQUEST['lastIdLogNotifications']))){
			$lastIdLogNotifications = json_decode(stripslashes($_REQUEST['lastIdLogNotifications']));
		}
		if(isset($_REQUEST['lastIdEmailCustomer']) && is_numeric(json_decode($_REQUEST['lastIdEmailCustomer']))){
			$lastIdEmailCustomer = json_decode(stripslashes($_REQUEST['lastIdEmailCustomer']));
		}

		$bookings = RESA_Booking::getAllData(array('oldBooking'=>false, 'idCustomer'=> $idCustomer,'modificationDate'=> array($lastModificationDateBooking, '>')));
		$logNotifications = RESA_LogNotification::getAllData(array('id' => array($lastIdLogNotifications, '>'), 'idCustomer' => $idCustomer));
		$emailsCustomer = RESA_EmailCustomer::getAllData(array('id' => array($lastIdEmailCustomer, '>'), 'idCustomer' => $idCustomer));

		echo '{
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
			"emailsCustomer":'.RESA_Tools::formatJSONArray($emailsCustomer).'
		}';
		wp_die();
	}

	/**
	 * update backend quotations
	 */
	public function updateBackendQuotations(){
		$lastModificationDateBooking = new DateTime();
		if(isset($_REQUEST['lastModificationDateBooking'])){
			$lastModificationDateBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['lastModificationDateBooking'])));
			$lastModificationDateBooking = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateBooking);
		}
		$lastModificationDateBooking = $lastModificationDateBooking->format('Y-m-d H:i:s');
		$filters = array();
		$places = array();
		if(isset($_REQUEST['filters'])) $filters = json_decode(stripslashes(sanitize_text_field($_REQUEST['filters'])));
		if(isset($filters) && isset($filters->places) && count((array)$filters->places) > 0){
			$filterPlaces = (array)$filters->places;
			foreach($filterPlaces as $key => $value){
				if($value) {
					array_push($places, $key);
				}
			}
		}
		$allIdCreation = '';
		if(isset($_REQUEST['allIdCreation'])) $allIdCreation = json_decode(stripslashes(sanitize_text_field($_REQUEST['allIdCreation'])));
		$bookings = RESA_Booking::getUpdateQuotations($lastModificationDateBooking, $allIdCreation, $places);
		$customers = array();
		foreach($bookings as $booking){
			$customer = $booking->getCustomer();
			if($customer->isLoaded()){
				array_push($customers, $customer);
			}
		}
		echo '{
			"bookings":'.RESA_Tools::formatJSONArray($bookings).',
			"customers":'.RESA_Tools::formatJSONArray($customers).'
		}';
		wp_die();
	}

	/**
	 * Booking send.
	 */
	public function sendEmailToCustomer(){
		Logger::INFO('Begin');
		try {
			$idBooking = -1;
			$subject = '';
			$message = '';
			if(isset($_REQUEST['idBooking']) && is_numeric(json_decode($_REQUEST['idBooking']))){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
			}
			if(isset($_REQUEST['subject'])){
				$subject = json_decode(stripslashes(sanitize_text_field($_REQUEST['subject'])));
			}
			if(isset($_REQUEST['message'])){
				$message = json_decode(stripslashes($_REQUEST['message']));
			}
			if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }
			$booking = new RESA_Booking();
			$booking->loadById($idBooking);
			if(!$booking->isQuotation()){
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				$result = RESA_Mailer::sendMessageBooking($booking, false, true, $subject, $message, $currentUser->getId());
				if(!$result['customer']) throw new Exception('Erreur envoie au client');
				if(!$result['emails']) throw new Exception('Erreur envoie aux emails définies');
				if(!$result['members']) throw new Exception('Erreur envoie aux membres du staff associés');
				$booking->save();
				$customer = $booking->getCustomer();
				$logNotification = RESA_Algorithms::generateLogNotification(14, $booking, $customer, $currentUser);
				if(isset($logNotification))	$logNotification->save();
			}
			else if($booking->isQuotation()){
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				RESA_Mailer::sendMessageQuotation($booking, true, $subject, $message, $currentUser->getId());
				$customer = $booking->getCustomer();
				$logNotification = RESA_Algorithms::generateLogNotification(17, $booking, $customer, $currentUser);
				if(isset($logNotification))	$logNotification->save();
				$booking->setNumberSentEmailQuotation(0);
				$booking->clearModificationDate();
				$booking->save();
			}
			echo '{"result":"ok"}';
		}
		catch(Exception $e){
			Logger::Error($e->getMessage());
			echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		wp_die();
	}

	/**
	 * Booking send.
	 */
	public function updateNotificationTemplates(){
		Logger::INFO('Begin');
		$notificationTemplates = array();
		if(isset($_REQUEST['notificationTemplates'])){
			$notificationTemplates = json_decode(stripslashes($_REQUEST['notificationTemplates']));
		}
		update_option('resa_settings_notifications_email_notifications_templates', serialize($notificationTemplates));
		update_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'));
		echo json_encode($notificationTemplates);
		wp_die();
	}



	/**
	 * Delete booking
	 */
	public function deleteBooking(){
		Logger::INFO('Begin');
		$idBooking = -1;
		if(isset($_REQUEST['idBooking']) && is_numeric(json_decode(stripslashes($_REQUEST['idBooking'])))){
			$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
		}
		$booking = new RESA_Booking();
		$booking->loadById($idBooking);
		if($idBooking > -1){
			$booking->deleteMe();
			$currentUser = new RESA_Customer();
			$currentUser->loadCurrentUser(false);
			$logNotification = RESA_Algorithms::generateLogNotification(23, $booking, $booking->getCustomer(), $currentUser);
			if(isset($logNotification))	$logNotification->save();
		}
		echo $booking->toJSON();
		wp_die();
	}

	/**
	 * Edit info calendar
	 */
	public function editInfoCalendar(){
		Logger::INFO('Begin');
		$infoCalendar = new RESA_InfoCalendar();
		if(isset($_REQUEST['infoCalendar'])){
			$infoCalendarJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['infoCalendar'])));
			$infoCalendar->fromJSON($infoCalendarJSON);
			$customer = new RESA_Customer();
			$customer->loadCurrentUser(false);
			$infoCalendar->setIdUserCreator($customer->getId());
			$date = DateTime::createFromFormat('d-m-Y H:i:s', $infoCalendarJSON->date);
			$infoCalendar->setDate($date->format('Y-m-d H:i:s'));
			$infoCalendar->setStartTime($date->format('H:i'));
			$dateEnd = DateTime::createFromFormat('d-m-Y H:i:s', $infoCalendarJSON->dateEnd);
			$infoCalendar->setDateEnd($dateEnd->format('Y-m-d H:i:s'));
			$infoCalendar->setEndTime($dateEnd->format('H:i'));
			$infoCalendar->save();
		}
		echo $infoCalendar->toJSON();
		wp_die();
	}

	/**
	 *
	 */
	public function deleteInfoCalendar(){
		Logger::INFO('Begin');
		$idInfoCalendar = -1;
		if(isset($_REQUEST['idInfoCalendar']) && is_numeric(json_decode(stripslashes($_REQUEST['idInfoCalendar'])))){
			$idInfoCalendar = json_decode(stripslashes(sanitize_text_field($_REQUEST['idInfoCalendar'])));
		}
		$infoCalendar = new RESA_InfoCalendar();
		$infoCalendar->loadById($idInfoCalendar);
		$success = false;
		if($infoCalendar->isLoaded()){
			$infoCalendar->deleteMe();
			$success = true;
		}
		echo json_encode(array('success' => $success));
		wp_die();
	}

	/**
	 * Edit service constraint
	 */
	public function editServiceConstraint(){
		Logger::INFO('Begin');
		if(isset($_REQUEST['constraint']) && isset($_REQUEST['isServiceConstraint'])){
			$constraintJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['constraint'])));
			$isServiceConstraint = json_decode(stripslashes(sanitize_text_field($_REQUEST['isServiceConstraint'])));
			if($isServiceConstraint){
				$serviceConstraint = new RESA_ServiceConstraint();
				$serviceConstraint->fromJSON($constraintJSON);
				$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $constraintJSON->startDate);
				$serviceConstraint->setStartDate($startDate->format('Y-m-d H:i:s'));
				$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $constraintJSON->endDate);
				$serviceConstraint->setEndDate($endDate->format('Y-m-d H:i:s'));
				$serviceConstraint->save();
				echo $serviceConstraint->toJSON();
			}
			else {
				$memberConstraint = new RESA_MemberConstraint();
				$memberConstraint->fromJSON($constraintJSON);
				$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $constraintJSON->startDate);
				$memberConstraint->setStartDate($startDate->format('Y-m-d H:i:s'));
				$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $constraintJSON->endDate);
				$memberConstraint->setEndDate($endDate->format('Y-m-d H:i:s'));
				$memberConstraint->save();
				echo $memberConstraint->toJSON();
			}
		}
		else {
			echo '';
		}
		wp_die();
	}

	/**
	 *
	 */
	public function deleteServiceConstraint(){
		Logger::INFO('Begin');
		if(isset($_REQUEST['idConstraint']) && isset($_REQUEST['isServiceConstraint']) && is_numeric(json_decode(stripslashes($_REQUEST['idConstraint'])))){
			$idConstraint = json_decode(stripslashes(sanitize_text_field($_REQUEST['idConstraint'])));
			$isServiceConstraint = json_decode(stripslashes(sanitize_text_field($_REQUEST['isServiceConstraint'])));
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
			echo json_encode(array('success' => $success));
		}
		else {
			echo json_encode(array('error' => 'error'));
		}
		wp_die();
	}

	/**
	 * get groups
	 */
	public function getGroups(){
		Logger::INFO('Begin');
		$week = array();
		if(isset($_REQUEST['week'])) $week = json_decode(stripslashes(sanitize_text_field($_REQUEST['week'])));
		try{
			$arrayGroups = array();
			foreach($week as $day){
				$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $day->startDate)->format('Y-m-d H:i:s');
				$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $day->endDate)->format('Y-m-d H:i:s');
				$groups = RESA_Group::getAllDataInterval($startDate, $endDate);
				$arrayGroups = array_merge($arrayGroups, $groups);
			}
			echo RESA_Tools::formatJSONArray($arrayGroups);
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		wp_die();
	}

	/**
	 * Edit or create a group
	 */
	public function editGroups(){
		Logger::INFO('Begin');
		if(isset($_REQUEST['groups'])){
			$editGroupsJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['groups'])));
			$arrayGroups = array();
			foreach($editGroupsJSON as $editGroupJSON){
				$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $editGroupJSON->startDate)->format('Y-m-d H:i:s');
				$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $editGroupJSON->endDate)->format('Y-m-d H:i:s');
				if($editGroupJSON->oneByBooking && $editGroupJSON->id != -1){
					$editGroup = RESA_Group::getAllData(
						array(
						'id' => $editGroupJSON->id,
						'idService'=>$editGroupJSON->idService,
						'idPlace' => $editGroupJSON->idPlace,
						'name' => $editGroupJSON->name,
						'startDate' => $startDate,
						'endDate' => $endDate));
				}
				else {
					$editGroup = RESA_Group::getAllData(
						array(
						'idService'=>$editGroupJSON->idService,
						'idPlace' => $editGroupJSON->idPlace,
						'name' => $editGroupJSON->name,
						'startDate' => $startDate,
						'endDate' => $endDate));
				}
				if(count($editGroup) == 0){
					$editGroup = new RESA_Group();
					$editGroup->fromJSON($editGroupJSON);
					$editGroup->setNew();
				}
				else {
					$editGroup = $editGroup[0];
					$editGroupJSON->id = $editGroup->getId();
					$editGroup->fromJSON($editGroupJSON);
				}
				$editGroup->setStartDate($startDate);
				$editGroup->setEndDate($endDate);
				$editGroup->clearLastModificationDate();
				$editGroup->save();
				array_push($arrayGroups, $editGroup);
			}
			echo RESA_Tools::formatJSONArray($arrayGroups);
		}
		else {
			echo '';
		}
		wp_die();
	}

	/**
	 *
	 */
	public function deleteGroup(){
		Logger::INFO('Begin');
		$arrayGroups = array();
		if(isset($_REQUEST['group']) && isset($_REQUEST['week'])){
			$editGroupJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['group'])));
			$week = json_decode(stripslashes(sanitize_text_field($_REQUEST['week'])));
			if(count($week) == 0){
				$editGroup = new RESA_Group();
				$editGroup->loadById($editGroupJSON->id);
				$success = false;
				if($editGroup->isLoaded()){
					$editGroup->deleteMe();
					$success = true;
					array_push($arrayGroups, $editGroup);
				}
			}
			else {
				foreach($week as $day){
					$startDate = DateTime::createFromFormat('d-m-Y H:i:s', $day->startDate)->format('Y-m-d H:i:s');
					$endDate = DateTime::createFromFormat('d-m-Y H:i:s', $day->endDate)->format('Y-m-d H:i:s');
					$editGroup = RESA_Group::getAllData(
						array(
							'idService'=>$editGroupJSON->idService,
							'idPlace' => $editGroupJSON->idPlace,
							'name' => $editGroupJSON->name,
					 		'startDate' => $startDate,
							'endDate' => $endDate
						));
					if(count($editGroup) == 1) {
						$editGroup = $editGroup[0];
						$editGroup->deleteMe();
						array_push($arrayGroups, $editGroup);
					}
				}
			}
			echo RESA_Tools::formatJSONArray($arrayGroups);
		}
		wp_die();
	}

	/**
	 * Get customer by id
	 */
	public function getCustomerById(){
		Logger::INFO('Begin');
		$idCustomer = -1;
		try{
			if(isset($_REQUEST['idCustomer']) && is_numeric(json_decode(stripslashes($_REQUEST['idCustomer'])))){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
			}
			$customer = new RESA_Customer();
			$customer->loadById($idCustomer);
			if(!$customer->isLoaded()){
				throw new Exception("Client non trouvé");
			}
			else {
				$logNotifications = RESA_LogNotification::getAllData(array('idCustomer' => $customer->getId()));
				$emailsCustomer = RESA_EmailCustomer::getAllData(array('idCustomer' => $customer->getId()));
				echo '{
					"customer" : '.$customer->toJSON().',
					"logNotifications":'.RESA_Tools::formatJSONArray($logNotifications).',
					"emailsCustomer":'.RESA_Tools::formatJSONArray($emailsCustomer).'
				}';
			}
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		wp_die();
	}



	/**
	 * Edit customer or create if not exist
	 */
	public function editCustomer(){
		Logger::INFO('Begin');
		$customer = new RESA_Customer();
		if(isset($_REQUEST['customer'])){
			$customerJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['customer'])));
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
						if((!$customer->isWpUser()) ||
							(username_exists($customerJSON->email) == $customerJSON->ID ||
							email_exists($customerJSON->email) == $customerJSON->ID) ||
							username_exists($customerJSON->email) === false ||
							email_exists($customerJSON->email) === false){
							$customer->fromJSON($customerJSON);
							$customer->save();
						}
						else {
							throw new Exception(__('email_already_exist_error', 'resa'));
						}
					}
					if($isNewUser && isset($notify) && $notify){
						$currentUser = new RESA_Customer();
						$currentUser->loadCurrentUser(false);
						RESA_Mailer::sentMessageAccountCreation($customer, $currentUser->getId());
					}
					$result = array(
						'status' => $status,
						'customer' => $customer->toJSON(),
						'oldID' => $oldID);
					echo json_encode($result);
				}else throw new Exception(__('authentication_or_register_error', 'resa'));
			}catch(Exception $e){
				Logger::Error($e->getMessage());
				echo json_encode(__('Error_word','resa').$e->getMessage());
			}
		}
		else echo json_encode(__('Error_word','resa'));
		wp_die();
	}

	/**
	 * Delete customer
	 */
	public function deleteCustomer(){
		Logger::INFO('Begin');
		$customer = new RESA_Customer();
		$idCustomer = -1;

		Logger::DEBUG(print_r('COucou', true));
		if(isset($_REQUEST['idCustomer']) && is_numeric(json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer']))))){
			$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
			$customer->loadById($idCustomer, true);
			$result = array(
				'status' => true,
				'message' => __('Ok','resa'),
				'oldID' => $idCustomer
			);

			Logger::DEBUG(($customer->isDeletable())?'Yes':'No');
			try {
				if($customer->isLoaded() && $customer->isRESACustomer() && $customer->isDeletable()){
					$success = $customer->deleteMe();
					if($success){
						if($customer->isAskAccount()){
							$currentUser = new RESA_Customer();
							$currentUser->loadCurrentUser(false);
							RESA_Mailer::sendMessageResponseAskAccount($customer, false, $currentUser->getId());
						}
					}
					else {
						$result['status'] = false;
						$result['message'] = __('can_not_delete_customer_error','resa');
					}
				}
				else {
					$result['status'] = false;
					$result['message'] = __('can_not_delete_customer_error','resa');
				}
			}
			catch(Exception $e){
				$result['status'] = false;
				$result['message'] = __('can_not_delete_customer_error','resa');
			}
			echo json_encode($result);
		}
		else {
			echo json_encode(array(
			'status' => false,
			'message' => __('can_not_delete_customer_error','resa')
			));
		}
		wp_die();
	}

	/**
	 * Edit customer or create if not exist
	 */
	public function sendCustomEmail(){
		Logger::INFO('Begin');
		if(isset($_REQUEST['idCustomer']) && isset($_REQUEST['idBooking']) && isset($_REQUEST['email']) && isset($_REQUEST['message']) && isset($_REQUEST['subject']) && isset($_REQUEST['sender'])){
			try{
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
				$customer = new RESA_Customer();
				$customer->loadByIdWithoutBookings($idCustomer);

				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);

				$email = json_decode(stripslashes(sanitize_text_field($_REQUEST['email'])));
				$subject = json_decode(stripslashes(sanitize_text_field($_REQUEST['subject'])));
				$message = json_decode(stripslashes(($_REQUEST['message'])));
				$sender = json_decode(stripslashes(sanitize_text_field($_REQUEST['sender'])));
				if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				$places = array();
				if(is_object($currentUser->getFilterSettings()) && isset($currentUser->getFilterSettings()->places)){
					$filterPlaces = (array)($currentUser->getFilterSettings()->places);
					foreach($filterPlaces as $key => $value){
						if($value) {
							array_push($places, $key);
						}
					}
				}
				$result = RESA_Mailer::sendCustomMessage($booking, $customer, $email, $subject, $message, $currentUser->getId(), $sender);
				if($email == $customer->getEmail() && $result){
					$logNotification = RESA_Algorithms::generateLogNotification(15, $booking, $customer, $currentUser);
					$logNotification->addIdPlaces($places);
					if(isset($logNotification))	$logNotification->save();
				}
				if($result) echo 'Ok';
				else echo 'Nok';
			}
			catch(Exception $e){
				Logger::Error($e);
				echo 'Nok';
			}
		}
		else echo 'Nok';
		wp_die();
	}


	/**
	 * Edit customer or create if not exist
	 */
	public function sentMessagePasswordReinitialization(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idCustomer'])){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
				$customer = new RESA_Customer();
				$customer->loadByIdWithoutBookings($idCustomer);

				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				if(!RESA_Mailer::sentMessagePasswordReinitialization($customer, $currentUser->getId())){
					throw new Exception('Error dans l\'envoie de l\'email');
				}
				$logNotification = RESA_Algorithms::generateLogNotification(13, new RESA_Booking(), $customer, $currentUser);
				if(isset($logNotification))	$logNotification->save();

				echo '{"result":"ok"}';
			}
			else {
				throw new Exception(__('Error_word','resa'));
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/***
	 * @deprecated
	 */
	public function addPayments()
	{
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['payments']) && isset($_REQUEST['force'])){
				$data = RESA_Payment::getAllData();
				$paymentsInPost = json_decode(stripslashes(wp_kses_post($_REQUEST['payments'])));
				$force = json_decode(stripslashes(sanitize_text_field($_REQUEST['force'])));
				$idPayments = array();
				$idCustomer = -1;
				foreach($paymentsInPost as $paymentInPost) {
					$paymentDate = DateTime::createFromFormat('d-m-Y H:i:s', $paymentInPost->paymentDate);
					$payment = new RESA_Payment();
					$payment->fromJSON($paymentInPost);
					$payment->setPaymentDate($paymentDate->format('Y-m-d H:i:s'));
					$booking = new RESA_Booking();
					$booking->loadById($payment->getIdBooking());
					$needToPay = $booking->getNeedToPay();
					$totalPrice = $booking->getTotalPrice();
					if(($needToPay > 0 && $payment->getValue() <= $needToPay) ||
						 ($payment->getValue() <= ($totalPrice - $needToPay) && $payment->isRepayment()) ||
						 ($needToPay < 0 && $payment->getValue() <= ($needToPay * -1) && $payment->isRepayment() && $booking->isCancelled())){
						$bookingPayments = $booking->getPayments();
						array_push($bookingPayments, $payment);
						$booking->setPayments($bookingPayments);
						$booking->save();
						$idCustomer = $booking->getIdCustomer();
					}
					else if(($needToPay<0 && !$payment->isRepayment()) || $needToPay == 0)
						throw new Exception('Not needed payments.');
					else if(($payment->getValue() > $needToPay && !$payment->isRepayment()) ||
						($payment->getValue() > ($needToPay * -1) && $payment->isRepayment()))
						throw new Exception('The value of payment is grantest than needed payment');
				}
				if($idCustomer != -1){
					$customer = new RESA_Customer();
					$customer->loadById($idCustomer);
					echo $customer->toJSON();
				}
				else {
					throw new Exception('Error of customer');
				}
			}
			else {
				throw new Exception('Error');
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}


	public function askPayment(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idBookings']) && isset($_REQUEST['paymentsType']) && isset($_REQUEST['stopAdvancePayment']) &&
				isset($_REQUEST['expiredDays']) && isset($_REQUEST['subject']) && isset($_REQUEST['message']) && isset($_REQUEST['language'])){
				$paymentsType = json_decode(stripslashes(sanitize_text_field($_REQUEST['paymentsType'])));
				$idBookings = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBookings'])));
				$stopAdvancePayment = json_decode(stripslashes(sanitize_text_field($_REQUEST['stopAdvancePayment'])));
				$expiredDays = json_decode(stripslashes(sanitize_text_field($_REQUEST['expiredDays'])));
				$subject = json_decode(stripslashes(sanitize_text_field($_REQUEST['subject'])));
				$message = json_decode(stripslashes(($_REQUEST['message'])));
				$language = stripslashes(sanitize_text_field($_REQUEST['language']));
				if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }

				$bookings = array();
				foreach($idBookings as $idBooking){
					$booking = new RESA_Booking();
					$booking->loadById($idBooking);
					array_push($bookings, $booking);
				}
				if(count($bookings) == 0){
					throw new Exception('Error in askPayment no bookings selected');
				}

				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);

				$success = true;
				if(in_array('swikly', $paymentsType) && class_exists('RESA_Swikly') && RESA_Swikly::isSentSwikWithAskPayment()){
					$data = RESA_Swikly::swikly($bookings[0], $bookings[0]->getCustomer(), '', true);
					if($data['success'] == 'error'){
						throw new \Exception("Error Swikly", 1);
					}
				}

				if(!RESA_Mailer::sendMessageNeedPayment($bookings, $paymentsType, $stopAdvancePayment, $expiredDays, $subject, $message, $currentUser->getId(), $language)){
					throw new Exception('Erreur dans l\'envoie de l\'email');
				}


				$textualPaymentTypes = '';
				foreach($paymentsType as $type){
					if(!empty($type)){
						if($textualPaymentTypes!='')
							$textualPaymentTypes .= ',';
						$textualPaymentTypes .= $type;
					}
				}

				//Save ask payment
				foreach($bookings as $booking){
					$askPayment = new RESA_AskPayment();
					$askPayment->setIdBooking($booking->getId());
					$askPayment->setIdUserCreator($currentUser->getId());
					$askPayment->setTypesPayment($textualPaymentTypes);
					$askPayment->setValue($booking->getNeedToPay());
					$askPayment->setTypeAdvancePayment($stopAdvancePayment);
					$askPayment->calculateExpiredDate($expiredDays);

					$allAskPayments = $booking->getAskPayments();
					array_push($allAskPayments, $askPayment);
					$booking->setAskPayments($allAskPayments);
					$booking->clearModificationDate();
					$booking->save(false);

					$customer = $booking->getCustomer();
					$logNotification = RESA_Algorithms::generateLogNotification(16, $booking, $customer, $currentUser);
					if(isset($logNotification))	$logNotification->save();
				}

				$customer = $bookings[0]->getCustomer();
				$customer->setLocale($language);
				$customer->save(false);
				echo $customer->toJSON();
			}
			else {
				throw new Exception("Error");
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/***
	 * @deprecated
	 */
	public function cancelPayment(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idPayment'])){
				$idPayment = json_decode(stripslashes(sanitize_text_field($_REQUEST['idPayment'])));
				$payment = new RESA_Payment();
				$payment->loadById($idPayment);
				if($payment->isCancellable()){
					$payment->cancel();
					$payment->save();

					$booking = new RESA_Booking();
					$booking->loadById($payment->getIdBooking());

					$customer = new RESA_Customer();
					$customer->loadById($booking->getIdCustomer());
					echo $customer->toJSON();
				}
				else throw new Exception(__('can_not_cancel_payment_error', 'resa'));
			}
			else throw new Exception(__('can_not_cancel_payment_error', 'resa'));
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/***
	 * @deprecated
	 */
	public function attachBooking()
	{
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['payments'])){
				$data = RESA_Payment::getAllData();
				$paymentsInPost = json_decode(stripslashes(wp_kses_post($_REQUEST['payments'])));
				$idPayments = array();
				$idCustomer = -1;
				foreach($paymentsInPost as $paymentInPost) {
					$paymentDate = DateTime::createFromFormat('d-m-Y H:i:s', $paymentInPost->paymentDate);
					$payment = new RESA_Payment();
					$payment->loadById($paymentInPost->id);
					if(!$payment->haveBooking()){
						$payment->setIdBooking($paymentInPost->idBooking);
						$payment->setIdCustomer(-1);
						$payment->setNote($paymentInPost->note);
						$booking = new RESA_Booking();
						$booking->loadById($payment->getIdBooking());
						if(!$booking->haveThisPayment($payment->getId())){
							$needToPay = $booking->getNeedToPay();
							$bookingPayments = $booking->getPayments();
							array_push($bookingPayments, $payment);
							$booking->setPayments($bookingPayments);
							$booking->save();
							$idCustomer = $booking->getIdCustomer();
						}
						else {
							throw new Exception('error attach booking');
						}
					}
					else {
						throw new Exception('error attach booking');
					}
				}
				if($idCustomer != -1){
					$customer = new RESA_Customer();
					$customer->loadById($idCustomer);
					echo $customer->toJSON();
				}
				else {
					throw new Exception('Error of customer');
				}
			}
			else {
				throw new Exception('Error');
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/**
	 * Merge customer
	 * @deprecated
	 */
	public function mergeCustomer(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idCustomer']) && isset($_REQUEST['idMergeCustomer'])){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
				$idMergeCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idMergeCustomer'])));

				$returnCustomer = new RESA_Customer();
				$deleteCustomerID = 0;
				$customer = new RESA_Customer();
				$customer->loadById($idCustomer);
				$mergeCustomer = new RESA_Customer();
				$mergeCustomer->loadById($idMergeCustomer);
				if(!$customer->isWpUser() || !$mergeCustomer->isWpUser()){
					if($customer->isWpUser()){
						$mergeCustomer->migrateID($customer->getId(), true);
						$deleteCustomerID = $mergeCustomer->getId();
						$mergeCustomer->deleteMe();
						$returnCustomer->loadById($customer->getId());
					}
					else {
						$customer->migrateID($mergeCustomer->getId(), true);
						$deleteCustomerID = $customer->getId();
						$customer->deleteMe();
						$returnCustomer->loadById($mergeCustomer->getId());
					}
				}
				else throw new Exception('Impossible to merge');
				$result = array(
					'customer' => $returnCustomer->toJSON(),
					'deleteCustomerID' => $deleteCustomerID
				);
				echo json_encode($result);
			}
			else throw new Exception('Error');
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/*
	 * synchronizationnCaisseOnline
	 */
	public function clearSynchronizationCaisseOnline(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idCustomer'])){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
				$customer = new RESA_Customer();
				$customer->loadById($idCustomer);

				if(class_exists('RESA_CaisseOnline')){
					RESA_CaisseOnline::getInstance()->clearSynchronizationCustomer($customer);
				}

				$customer = new RESA_Customer();
				$customer->loadById($idCustomer);
				echo $customer->toJSON();
			}
			else throw new Exception('Error');
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	/**
	 * pay booking caisse online
	 */
	public function payBookingCaisseOnline(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idBooking'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				if(class_exists('RESA_CaisseOnline')){
					$result = RESA_CaisseOnline::getInstance()->payBooking($booking);
				}
				if($result['result']){
					echo json_encode(array('result' => 'success', 'message' => 'La réservation a été envoyée sur la caisse !'));
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
			wp_die();
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode(array('result' => 'error', 'message' => $e->getMessage()));
			wp_die();
		}
	}

	/**
	 * pay booking caisse online
	 */
	public function payBookingsCaisseOnline(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idCustomer'])){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
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
				if(count($bookings) == 0) echo 'Aucune réservation n\'a été envoyée sur la caisse !';
				else {
					if(class_exists('RESA_CaisseOnline')){
						$result = RESA_CaisseOnline::getInstance()->payBookings($bookings, $idBookings);
						if($result['result']){
							echo 'Les réservations ont été envoyée sur la caisse !';
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
			wp_die();
		}
		catch(Exception $e){
			Logger::Error($e);
			echo $e->getMessage();
			wp_die();
		}
	}

	/**
	 *
	 */
	public function calculateBookingPaymentState(){
		Logger::INFO('begin');
		try
		{
			if(isset($_REQUEST['idBooking'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
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
					echo '{
						"booking":'.$booking->toJSON().'
					}';
				}
				else {
					throw new Exception('error');
				}
			}
			else {
				throw new Exception('error');
			}
			wp_die();
		}
		catch(Exception $e){
			Logger::Error($e);
			//echo json_encode($e->getMessage());
			wp_die(json_encode($e->getMessage()), 401);
		}
	}


	/**
	 *
	 */
	public function setPaymentStateBooking(){
		Logger::INFO('begin');
		try
		{
			if(isset($_REQUEST['idBooking']) && isset($_REQUEST['newPaymentState'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$newPaymentState = json_decode(stripslashes(sanitize_text_field($_REQUEST['newPaymentState'])));
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
					$currentUser = new RESA_Customer();
					$currentUser->loadCurrentUser(false);
					$logNotification = RESA_Algorithms::generateLogNotification(8, $booking, $customer, $currentUser);
					if(isset($logNotification))	$logNotification->save();

					echo '{
						"booking":'.$booking->toJSON().'
					}';
				}
				else {
					throw new Exception('error');
				}
			}
			else {
				throw new Exception('bad_parameters');
			}
			wp_die();
		}
		catch(Exception $e){
			Logger::Error($e);
			//echo json_encode($e->getMessage());
			wp_die(json_encode($e->getMessage()), 401);
		}
	}

	/**
	 * accept quotation backend
	 */
	public function acceptQuotationBackend(){
		try{
			if(isset($_REQUEST['idBooking']) && isset($_REQUEST['subject']) && isset($_REQUEST['message']) && isset($_REQUEST['language'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				if($booking->isLoaded() && $booking->isQuotation() && $booking->isQuotationRequest()){
					$currentUser = new RESA_Customer();
					$currentUser->loadCurrentUser(false);
					$subject = json_decode(stripslashes(sanitize_text_field($_REQUEST['subject'])));
					$message = json_decode(stripslashes($_REQUEST['message']));
					$language = stripslashes(sanitize_text_field($_REQUEST['language']));
					if(empty($subject) || empty($message)){ throw new Exception('Erreur message ou sujet vide'); }


					if(!RESA_Mailer::sendMessageQuotationAccepted($booking, true, $subject, $message, $currentUser->getId())){
						throw new Exception('Erreur dans l\'envoie de l\'email');
					}
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
							$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
							$booking->setTransactionId($oldBooking->getTransactionId());
							$booking->setIdCreation($oldBooking->getIdCreation());
							$booking->setPaymentState($oldBooking->getPaymentState());
							$booking->setQuotation($oldBooking->isQuotation());
							$booking->setNew();
							$oldBooking->save();
						}
						else {
							throw new Exception(__('create_new_version_bookings_error', 'resa'));
						}
					}
					$booking->clearModificationDate();
					$booking->clearLastDateSentEmailQuotation();
					$booking->setAlreadySentEmail(false);
					$booking->setQuotationRequest(false);
					$booking->save();
					$customer = $booking->getCustomer();
					$logNotification = RESA_Algorithms::generateLogNotification(7, $booking, $customer, $currentUser);
					if(isset($logNotification))	$logNotification->save();

					echo '{
						"booking":'.$booking->toJSON().',
						"oldIdBooking":'.$idBooking.'}';
				}
			}
			else {
				throw new Exception('error');
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}


	public function acceptQuotationAndAskPayment(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['paymentsType']) && isset($_REQUEST['stopAdvancePayment']) && isset($_REQUEST['expiredDays']) && isset($_REQUEST['subject']) && isset($_REQUEST['message']) && isset($_REQUEST['idBooking']) && isset($_REQUEST['language'])){
				$paymentsType = json_decode(stripslashes(sanitize_text_field($_REQUEST['paymentsType'])));
				$stopAdvancePayment = json_decode(stripslashes(sanitize_text_field($_REQUEST['stopAdvancePayment'])));
				$expiredDays = json_decode(stripslashes(sanitize_text_field($_REQUEST['expiredDays'])));
				$subject = json_decode(stripslashes(sanitize_text_field($_REQUEST['subject'])));
				$message = json_decode(stripslashes($_REQUEST['message']));
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$language = stripslashes(sanitize_text_field($_REQUEST['language']));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				if($booking->isLoaded() && $booking->isQuotation() && $booking->isQuotationRequest()){
					$currentUser = new RESA_Customer();
					$currentUser->loadCurrentUser(false);
					if(!RESA_Mailer::sendMessageQuotationAccepted($booking, true, $subject, $message, $currentUser->getId(), $paymentsType, $stopAdvancePayment, $expiredDays)){
						throw new Exception('Erreur dans l\'envoie de l\'email');
					}
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
							$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
							$booking->setTransactionId($oldBooking->getTransactionId());
							$booking->setIdCreation($oldBooking->getIdCreation());
							$booking->setPaymentState($oldBooking->getPaymentState());
							$booking->setQuotation($oldBooking->isQuotation());
							$booking->setNew();
							$oldBooking->save();
						}
						else {
							throw new Exception(__('create_new_version_bookings_error', 'resa'));
						}
					}
					$booking->clearModificationDate();
					$booking->clearLastDateSentEmailQuotation();
					$booking->setAlreadySentEmail(false);
					$booking->setQuotationRequest(false);
					$booking->save();
					$customer = $booking->getCustomer();
					$logNotification = RESA_Algorithms::generateLogNotification(7, $booking, $customer, $currentUser);
					if(isset($logNotification))	$logNotification->save();
					$customer->setLocale($language);
					$customer->save(false);


					$bookings = array();
					array_push($bookings, $booking);
					$success = true;
					if(in_array('swikly', $paymentsType) && class_exists('RESA_Swikly') && RESA_Swikly::isSentSwikWithAskPayment()){
						$data = RESA_Swikly::swikly($bookings[0], $bookings[0]->getCustomer(), '', true);
						if($data['success'] == 'error'){
							throw new \Exception("Error Swikly", 1);
						}
					}

					if(!RESA_Mailer::sendMessageNeedPayment($bookings, $paymentsType, $stopAdvancePayment, $expiredDays, $subject, $message, $currentUser->getId(), $language)){
						throw new Exception('Erreur dans l\'envoie de l\'email');
					}

					$textualPaymentTypes = '';
					foreach($paymentsType as $type){
						if(!empty($type)){
							if($textualPaymentTypes!='')
								$textualPaymentTypes .= ',';
							$textualPaymentTypes .= $type;
						}
					}

					//Save ask payment
					foreach($bookings as $booking){
						$askPayment = new RESA_AskPayment();
						$askPayment->setIdBooking($booking->getId());
						$askPayment->setIdUserCreator($currentUser->getId());
						$askPayment->setTypesPayment($textualPaymentTypes);
						$askPayment->setValue($booking->getNeedToPay());
						$askPayment->setTypeAdvancePayment($stopAdvancePayment);
						$askPayment->calculateExpiredDate($expiredDays);

						$allAskPayments = $booking->getAskPayments();
						array_push($allAskPayments, $askPayment);
						$booking->setAskPayments($allAskPayments);
						$booking->clearModificationDate();
						$booking->save(false);

						$customer = $booking->getCustomer();
						$logNotification = RESA_Algorithms::generateLogNotification(16, $booking, $customer, $currentUser);
						if(isset($logNotification))	$logNotification->save();
					}

					$customer = $bookings[0]->getCustomer();
					echo $customer->toJSON();
				}
			}
			else {
				throw new Exception("Error");

			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function seeAllLogNotifications(){
		Logger::INFO('Begin');
		try
		{
			$currentUser = new RESA_Customer();
			$currentUser->loadCurrentUser(false);
			$allLogNotificationsNotSeen = RESA_LogNotification::getAllDataNotSeen($currentUser->getId());
			foreach($allLogNotificationsNotSeen as $logNotification){
				$logNotification->seenIdCustomer($currentUser->getId());
				$logNotification->save();
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function seeAllRESANews(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['lastId']) && is_numeric($_REQUEST['lastId'])){
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				$currentUser->setLastRESANewsID(sanitize_text_field($_REQUEST['lastId']));
				$currentUser->save(false);
			}
			else {
				throw new Exception(__("Error_word", "resa"));
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}



	public function clickAllLogNotifications(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['lastId']) && is_numeric($_REQUEST['lastId'])){
				$limit = sanitize_text_field($_REQUEST['lastId']);
				RESA_LogNotification::clickAllLogNotifications($limit);
			}
			else {
				throw new Exception(__("Error_word", "resa"));
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function clickOnLogNotifications(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idLogNotification'])){
				$idLogNotification = json_decode(stripslashes(sanitize_text_field($_REQUEST['idLogNotification'])));
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

				echo  '{
					"booking":'.$booking->toJSON().',
					"customer":'.$customer->toJSON().'
				}';
			}
			else {
				throw new Exception("Error");
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function unreadLogNotification(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idLogNotification'])){
				$idLogNotification = json_decode(stripslashes(sanitize_text_field($_REQUEST['idLogNotification'])));
				$logNotification = new RESA_LogNotification();
				$logNotification->loadById($idLogNotification);
				if($logNotification->isLoaded()){
					$logNotification->setClicked(false);
					$logNotification->save();
				}
				echo 'OK';
			}
			else {
				throw new Exception("Error");
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function acceptAskAccount(){
		Logger::INFO('Begin');
		try
		{
			if(isset($_REQUEST['idCustomer'])){
				$customer = new RESA_Customer();
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
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
					$status = 'replaced';

					RESA_Mailer::sendMessageResponseAskAccount($customer, true);

					$result = array(
						'status' => $status,
						'customer' => $customer->toJSON(),
						'oldID' => $idCustomer);
					echo json_encode($result);
				}
			}
			else {
				throw new Exception("Error");
			}
		}
		catch(Exception $e){
			Logger::Error($e);
			echo json_encode($e->getMessage());
		}
		wp_die();
	}

	public function forceProcessEmails(){
		Logger::INFO('Begin');
		RESA_Crons::clearRESACheckEmailsEvent();
		wp_die();
	}

	public function deleteEmailCustomer(){
		Logger::INFO('Begin');
		if(isset($idEmailCustomer)){
			$idEmailCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idEmailCustomer'])));
			$emailCustomer = new RESA_EmailCustomer();
			$emailCustomer->loadById($idEmailCustomer);
			$emailCustomer->deleteMe();
		}
		wp_die();
	}

}
