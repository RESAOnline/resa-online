<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AskAccountController extends RESA_Controller
{
	private $typesAccount;

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
		if(class_exists('RESA_FacebookLogin')){
			array_push(self::$GLOBAL_SCRIPTS, '../../../resa-online-facebook/js/RESAFacebookLogin');
		}
		else {
			array_push(self::$GLOBAL_SCRIPTS, 'extension/RESAFacebookLogin');
		}
		return array_merge(self::$GLOBAL_SCRIPTS, array(
			'libs/is.min',
			'manager/AskAccountManager',
			'manager/DateComputation',
			'manager/FunctionsManager',
			'controller/AskAccountController',
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
			'design-form'
		);
		$customCSS = get_option('resa_settings_form_custom_css');
		if(!empty($customCSS) && $customCSS != false){
			$array = explode(',', $customCSS);
			if(count($array) > 0){
				foreach($array as $cssFile){
					array_push($css, 'css_custom/' . $cssFile);
				}
			}
		}
		return $css;
	}

	/**
	 *
	 */
	public function setTypeAccount($typeAccount){
		$this->typeAccount = $typeAccount;
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->useScriptsAndStyles();
		$this->registerAjaxMethods();

		$customer = new RESA_Customer();
		$customer->loadCurrentUser();
		$settings = array(
			'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
			'browser_not_compatible_sentence' => unserialize(get_option('resa_settings_browser_not_compatible_sentence')),
			'facebook_activated' => get_option('resa_facebook_activated', false) && class_exists('RESA_FacebookLogin'),
			'facebook_api_id' => get_option('resa_facebook_api_id', ''),
			'facebook_api_version' => get_option('resa_facebook_api_version', '')
		);
		echo RESA_Algorithms::getCSSVarColors();
		$this->renderer('RESA_askAccount',
			array(
				'customer'=>$customer->toJSON(),
				'settings'=>json_encode($settings),
				'typeAccount' => json_encode($this->typeAccount),
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

	}


}
