<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_FormPublicController extends RESA_Controller
{
	private $form;
	private $serviceSlugs;
	private $quotation;
	private $typesAccounts;

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
			'factory/Basket',
			'factory/ServiceParameters',
			'factory/Timeslot',
			'manager/FormManager',
			'manager/DateComputation',
			'manager/PaymentManagerForm',
			'manager/StorageManager',
			'manager/FunctionsManager',
			'manager/ParticipantSelectorManager',
			'controller/NewFormController',
			'controller/ParticipantSelectorController',
			'directive/datetimepicker'
		));
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		$css = array(
			'sweetalert',
			'angular-css',
		);
		$cssFile = 'design-form';
		if(!empty($this->form)){
			$form = new RESA_Form();
			$form->loadById(substr($this->form, strlen('form')));
			if($form->isLoaded() && $form->getTypeForm() == 'RESA_newform'){
				$cssFile = 'design-newform';
			}
		}
		array_push($css, $cssFile);

		$customCSS = get_option('resa_settings_global_custom_css');
		if(!empty($this->form)){
			$form = new RESA_Form();
			$form->loadById(substr($this->form, strlen('form')));
			if($form->isLoaded()){
				$customCSS = $customCSS.','.$form->getCustomCSS();
				$customCSS .= ','. $form->getFileCSSGenerated();
			}
		}
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

	public function setForm($form){ $this->form = $form; }
	public function setServiceSlugs($serviceSlugs){ $this->serviceSlugs = $serviceSlugs; }

	/**
	 *
	 */
	public function setQuotation($quotation){
		$this->quotation = $quotation;
	}

	/**
	 *
	 */
	public function setTypesAccounts($typesAccounts){
		$this->typesAccounts = $typesAccounts;
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->useScriptsAndStyles();
		$this->registerAjaxMethods();
		$quotation = (isset($this->quotation))?($this->quotation == 'true'):false;
		$typesAccounts = $this->typesAccounts;

		$formFile = 'RESA_form';
		if(!empty($this->form)){
			$form = new RESA_Form();
			$form->loadById(substr($this->form, strlen('form')));
			if($form->isLoaded()){
				$typesAccounts = $form->getTypeAccounts();
				$quotation = $form->isQuotation();
				$formFile = $form->getTypeForm();
			}
		}
		if($form->isLoaded() && $form->getTypeForm() == 'RESA_form'){
			echo RESA_Algorithms::getCSSVarColors($form);
		}

		$this->renderer($formFile,
			array(
				'currentUrl'=>RESA_Variables::getCurrentPageURL(),
				'months'=>RESA_Variables::getJSONMonths(),
				'countries' => RESA_Variables::getJSONCountries(),
				'date_format'=> RESA_Tools::wpToJSDateFormat(),
				'time_format'=> RESA_Tools::wpToJSTimeFormat(),
				'serviceSlugs' => json_encode($this->serviceSlugs),
				'form' => $this->form,
				'quotation' => RESA_Tools::toJSONBoolean($quotation),
				'typesAccounts' => json_encode($typesAccounts)
			));
	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods() {
		$this->addAjaxMethod('initializationDataForm', true);
	}

	/**
	 * Return the initialization data
	 */
	public function initializationDataForm(){
		$idForm = json_decode(stripslashes($_REQUEST['form']));
		$serviceSlugs = json_decode(stripslashes($_REQUEST['serviceSlugs']));
		$quotation = json_decode(stripslashes($_REQUEST['quotation']));
		$typesAccounts = json_decode(stripslashes($_REQUEST['typesAccounts']));

		$servicesDB = RESA_Service::getAllData(array('activated'=>true, 'oldService'=>0));
		$services = array();
		$form = new RESA_Form();
		if(!empty($idForm)){
			$form->loadById(substr($idForm, strlen('form')));
			if($form->isLoaded()){
				$serviceSlugs = $form->getServices();
			}
		}
		if(count($serviceSlugs) > 0){
			foreach($servicesDB as $service){
				if(in_array($service->getSlug(), $serviceSlugs)){
					array_push($services, $service);
				}
			}
		}
		else {
			$services = $servicesDB;
		}

		$members = RESA_Member::getAllData(array('activated'=>true)); //TODO needed ?
		$customer = new RESA_Customer();
		$customer->loadCurrentUser();
		$paymentsTypeList = RESA_Variables::paymentsTypeList();


		$settings = array(
			'isQuotation' => $quotation,
			'places' => unserialize(get_option('resa_settings_places')),
			'timeslots_mentions' => unserialize(get_option('resa_settings_timeslots_mentions')),
			'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
			'checkbox_payment' => $form->isCheckboxPayment(),
			'checkbox_title_payment' => $form->getCheckboxTitlePayment(),
			'currency' => get_option('resa_settings_payment_currency'),
			'formActivated'=> $form->isActivated() && count($services) > 0,
			'form_deactivated_text'=> $form->getDeactivatedText(),
			'browser_not_compatible_sentence' => unserialize(get_option('resa_settings_browser_not_compatible_sentence')),
			'payment_activate'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_activate'))=='true',
			'payment_not_activated_text'=>unserialize(get_option('resa_settings_payment_not_activated_text')),
			'colors' => $form->getColors(),
			'form_display_image_service' => RESA_Tools::toJSONBoolean($form->isDisplayImageService())=='true',
			'form_display_image_category' => RESA_Tools::toJSONBoolean($form->isDisplayImageCategory())=='true',
			'form_display_image_place' => RESA_Tools::toJSONBoolean($form->isDisplayImagePlace())=='true',
			'form_selected_place_sentence' => $form->getSelectedPlaceSentence(),
			'form_selected_service_sentence' => $form->getSelectedServiceSentence(),
			'form_choose_a_date' => $form->getChooseADateTitle(),
			'form_choose_a_timeslot' => $form->getChooseATimeslotTitle(),
			'form_selected_date_sentence' => $form->getSelectedDateSentence(),
			'form_selected_timeslot_sentence' => $form->getSelectedTimeslotSentence(),
			'form_choose_prices' => $form->getChoosePricesTitle(),
			'form_choose_quantity' => $form->getChooseQuantityTitle(),
			'form_remaining_equipments' => $form->getRemainingEquipments(),
			'form_prices_suffix_by_persons' => $form->getPricesSuffixByPersons(),
			'form_add_new_date_text'=> $form->getAddNewDateTextButton(),
			'form_add_new_activity_text'=> $form->getAddNewActivityTextButton(),
			'informations_customer_text' => $form->getInformationsCustomerText(),
			'informations_participants_text' => $form->getInformationsParticipantsText(),
			'informations_payment_text' => $form->getInformationsPaymentText(),
			'form_recap_booking_title' => $form->getRecapBookingTitle(),
			'customer_note_text' => $form->getCustomerNotePlaceholder(),
			'payment_ask_advance_payment' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_ask_advance_payment'))=='true',
			'payment_ask_advance_payment_type_accounts' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
			'payment_ask_text_advance_payment' => unserialize(get_option('resa_settings_payment_ask_text_advance_payment')),
			'account_url' => get_option('resa_settings_customer_account_url'),
			'form_steps_title' => $form->getStepsTitle(),
			'form_category_services' => unserialize(get_option('resa_settings_form_category_services')),
			'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
			'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),
			'enabledColor' => (RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_enabled_enabled_color')) == 'true')?get_option('resa_settings_calendar_enabled_days_front_color'):'',
			'disabledColor' => (RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_enabled_disabled_color')) == 'true')?get_option('resa_settings_calendar_disabled_days_front_color'):'',
			'facebook_activated' => get_option('resa_facebook_activated', false) && class_exists('RESA_FacebookLogin') && !empty(get_option('resa_facebook_api_id', '')),
			'facebook_api_id' => get_option('resa_facebook_api_id', ''),
			'facebook_api_version' => get_option('resa_facebook_api_version', ''),
		);

		$booking = new RESA_Booking();
		if(RESA_Session::isDefined('RESA_booking')){
			$booking->loadById(RESA_Session::load('RESA_booking'));
		}
		echo  '{
			"allServices":'.RESA_Tools::formatJSONArray($servicesDB).',
			"services":'.RESA_Tools::formatJSONArray($services).',
			"members":'.RESA_Tools::formatJSONArray($members).',
			"customer":'.$customer->toJSON().',
			"settings":'.json_encode($settings).',
			"paymentsTypeList":'.json_encode($paymentsTypeList).',
			"booking":'.json_encode($booking->getBookingLiteJSON()).'
		}';

		wp_die();
	}



}
