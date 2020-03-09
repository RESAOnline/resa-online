<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AccountController extends RESA_Controller
{
	public function getSlug()
	{
		return '';
	}

	public function getPageName()
	{
		return '';
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
		if(strpos(get_locale(), 'en_') !== false) {
			array_push(self::$GLOBAL_SCRIPTS, 'libs/locales/angular-locale_en-iso');
		}
		if(class_exists('RESA_FacebookLogin')){
			array_push(self::$GLOBAL_SCRIPTS, '../../../resa-online-facebook/js/RESAFacebookLogin');
		}
		else {
			array_push(self::$GLOBAL_SCRIPTS, 'extension/RESAFacebookLogin');
		}
		return array_merge(self::$GLOBAL_SCRIPTS, array(
			'libs/is.min',
			'manager/AppointmentManager',
			'manager/AccountManager',
			'manager/FunctionsManager',
			'manager/PaymentManagerForm',
			'manager/NewReceiptBookingManager',
			'controller/AccountController',
			'controller/NewReceiptBookingController',
		));
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		$css =  array(
			'angular-css',
			'sweetalert',
			'design-form',
			'resa_reciept'
		);
		$customCSS = get_option('resa_settings_global_custom_css');
		if(!empty($customCSS) && $customCSS != false){
			$arrayUploadDir = wp_get_upload_dir();
			$array = explode(',', $customCSS);
			if(count($array) > 0){
				foreach($array as $cssFile){
					if(!empty($cssFile) && is_dir($arrayUploadDir['basedir'].'/resa_css')){
						array_push($css, $arrayUploadDir['baseurl'].'/resa_css/' . $cssFile);
					}
				}
			}
		}
		return $css;
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->useScriptsAndStyles();
		echo RESA_Algorithms::getCSSVarColors();
		$this->renderer('RESA_account',
			array(
				'currentUrl'=>RESA_Variables::getCurrentPageURL(),
				'date_format'=> RESA_Tools::wpToJSDateFormat(),
				'time_format'=> RESA_Tools::wpToJSTimeFormat(),
				'countries' => RESA_Variables::getJSONCountries()
			));
	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods() {
		$this->addAjaxMethod('initializationDataAccounts', true);
		$this->addAjaxMethod('updateDataAccounts', true);
	}


	/**
	 * Return the initialization data
	 */
	public function initializationDataAccounts(){
		$allServices = RESA_Service::getAllData(array());
		$allReductions = RESA_Reduction::getAllData(array());
		$customer = new RESA_Customer();
		$customer->loadCurrentUser();
		$customer->setPrivateNotes('It is private');

		$bookings = RESA_Booking::getAllData(array('oldBooking'=>false, 'idCustomer' => $customer->getId()));
		$displayBookings = array();
		foreach($bookings as $booking){
			if($booking->isOk() || $booking->isWaiting()){
				array_push($displayBookings, $booking);
			}
		}
		$customer->setBookings($displayBookings);
		$paymentsTypeList = RESA_Variables::paymentsTypeList();


		$apiCaisseUrl = '';
		if(class_exists('RESA_CaisseOnline')){
			$apiCaisseUrl = RESA_CaisseOnline::getInstance()->getBaseURL();
		}


		$settings = array(
			'places' => unserialize(get_option('resa_settings_places')),
			'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
			'caisse_online_activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true',
			'caisse_online_server_url'=> $apiCaisseUrl,
			'caisse_online_license_id'=>get_option('resa_settings_caisse_online_license_id'),
			'stripeConnectId' =>get_option('resa_settings_payment_stripe_connect_id'),
			'apiStripeConnectUrl' => (class_exists('RESA_StripeConnect') && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::getURL():'',
			'statesList' => RESA_Variables::statesList(),
			'checkbox_payment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_checkbox_payment'))=='true',
			'checkbox_title_payment' => unserialize(get_option('resa_settings_checkbox_title_payment')),
			'custom_payment_types' => unserialize(get_option('resa_settings_payment_custom_payment_types')),
			'currency' => get_option('resa_settings_payment_currency'),
			'payment_ask_advance_payment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_ask_advance_payment'))=='true',
			'payment_ask_advance_payment_type_accounts' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
			'payment_ask_text_advance_payment' =>unserialize(get_option('resa_settings_payment_ask_text_advance_payment')),
			'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
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
			'browser_not_compatible_sentence' => unserialize(get_option('resa_settings_browser_not_compatible_sentence')),
			'facebook_activated' => get_option('resa_facebook_activated', false) && class_exists('RESA_FacebookLogin'),
			'facebook_api_id' => get_option('resa_facebook_api_id', ''),
			'facebook_api_version' => get_option('resa_facebook_api_version', ''),
		);

		echo  '{
			"customer":'.$customer->toJSON(false).',
			"services":'.RESA_Tools::formatJSONArray($allServices).',
			"reductions":'.RESA_Tools::formatJSONArray($allReductions).',
			"paymentsTypeList":'.json_encode($paymentsTypeList).',
			"idPaymentsTypeToName":'.json_encode(RESA_Variables::idPaymentsTypeToName()).',
			"settings":'.json_encode($settings).'
		}';
		wp_die();
	}


	public function updateDataAccounts(){
		$customer = new RESA_Customer();
		$customer->loadCurrentUser();
		$lastModificationDateBooking = json_decode(stripslashes($_REQUEST['lastModificationDateBooking']));
		$lastModificationDateBooking = DateTime::createFromFormat('d-m-Y H:i:s', $lastModificationDateBooking);
		$lastModificationDateBooking = $lastModificationDateBooking->format('Y-m-d H:i:s');
		$bookings = RESA_Booking::getAllData(array('oldBooking'=>false, 'idCustomer'=> $customer->getId(), 'modificationDate'=> array($lastModificationDateBooking, '>')));
		echo '{
			"bookings":'.RESA_Tools::formatJSONArray($bookings).'
		}';
		wp_die();
	}




}
