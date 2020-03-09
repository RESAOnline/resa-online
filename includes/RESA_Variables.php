<?php if ( ! defined( 'ABSPATH' ) ) exit;

abstract class RESA_Variables
{
	private static $version = null;

	public static function getPluginSlugName(){
		$plugin_slug = explode('/',dirname(plugin_basename(__FILE__)));
		return $plugin_slug[0];
	}

	public static function getCurrentPageURL() {
    if ( ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443 ) {
        $url = 'https://';
    } else {
        $url = 'http://';
    }
    $url .= isset( $_SERVER['HTTP_X_FORWARDED_HOST'] ) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST'];

    return $url . $_SERVER['REQUEST_URI'];
  }

	public static function haveNewVersion(){
		$pluginRESAOnline = self::getPluginSlugName() . '/main.php';
		$transient = get_site_transient( 'update_plugins' );
		if($transient !== false && isset($transient->response[$pluginRESAOnline])){
			return true;
		}
		return false;
	}


	public static function getVersion(){
		if ( self::$version == null ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( plugin_dir_path(__FILE__) . '../main.php' );
			self::$version = $plugin_data['Version'];
		}
    return self::$version;
	}

	public static function getURLSupport($email){
		return 'https://resa-online.fr/documentations/';
		/*
    return 'https://resa-online.fr/support-resa-online/?company=' . get_option('resa_settings_company_name'). '&address=' . get_option('resa_settings_company_address'). '&phone=' . get_option('resa_settings_company_phone'). '&siteUrl=' . urlencode(site_url()). '&email=' . $email;
		*/
	}

	public static function getTimezoneString()
  {
      // if site timezone string exists, return it
      if ( $timezone = get_option( 'timezone_string' ) ) {
          return $timezone;
      }

      // get UTC offset, if it isn't set then return UTC
      if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
          return 'UTC';
      }

      // adjust UTC offset from hours to seconds
      $utc_offset *= 3600;

      // attempt to guess the timezone string from the UTC offset
      if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
          return $timezone;
      }

      // last try, guess timezone string manually
      $is_dst = date( 'I' );

      foreach ( timezone_abbreviations_list() as $abbr ) {
          foreach ( $abbr as $city ) {
              if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                  return $city['timezone_id'];
          }
      }

      // fallback to UTC
      return 'UTC';
  }

	/**
	 * Not see RESA
	 */
	public static function getCustomerRole(){
		return 'RESA_Customer';
	}

	/**
	 * Just see RESA calendar
	 */
	public static function getStaffRole(){
		return 'RESA_Staff';
	}

	/**
	 * Just see RESA (not admin)
	 */
	public static function getManagerRole(){
		return 'RESA_Manager';
	}

	public static function getRoleCap(){
		return 'resa_view';
	}

	public static function getLinkParameters($subRoute, $currentRESAUser = false){
		if(is_bool($currentRESAUser)){
			$currentRESAUser = new RESA_Customer();
			$currentRESAUser->loadCurrentUser();
			if($currentRESAUser->canEditParameters()){
				if($currentRESAUser->generateNewToken(7)){
					$currentRESAUser->save(false);
				}
			}
		}
		return admin_url('admin.php?page=resa_parameters#/' . $subRoute . '?token='.$currentRESAUser->getToken(), dirname(__FILE__));
	}

	public static function getRESACSSDir(){
		return wp_get_upload_dir()['basedir'].'/resa_css';
	}

	public static function staffIsConnected(){
		$currentUser = new RESA_Customer();
		$currentUser->loadCurrentUser();
		if($currentUser->isLoaded()) return $currentUser->isRESAStaff();
		return false;
	}

	public static function customerIsConnected(){
		$currentUser = new RESA_Customer();
		$currentUser->loadCurrentUser();
		if($currentUser->isLoaded()) return $currentUser->isRESACustomer();
		return false;
	}

	public static function ifUserConnectedSeeSettings(){
		$currentUser = new RESA_Customer();
		$currentUser->loadCurrentUser();
		return $currentUser->canEditParameters();
	}

	public static function isStaffManagementActivated(){
		return RESA_Tools::toJSONBoolean(get_option('resa_staff_management_actived')) =='true';
	}

	public static function isEquipmentsManagementActivated(){
		return RESA_Tools::toJSONBoolean(get_option('resa_equipments_management_actived')) =='true';
	}

	public static function isGroupsManagementActivated(){
		return RESA_Tools::toJSONBoolean(get_option('resa_groups_management_actived')) =='true';
	}

	public static function isTypePaymentOnline($typePayment){
		$list = self::paymentsTypeList();
		for($i = 0; $i < count($list); $i++){
			$payment = $list[$i];
			if($typePayment == $payment['id'] && isset($payment['online']) && $payment['online']){
				return true;
			}
		}
		return false;
	}

	public static function idPaymentsTypeToName(){
		$list = array();
		$list['cash'] = __('cash_payment_type_radio_title','resa');
		$list['cheque'] = __('cheque_payment_type_radio_title','resa');
		$list['card'] = __('card_payment_type_radio_title','resa');
		$locale = get_locale();
		$paypalTitles = unserialize(get_option('resa_settings_payment_paypal_title', 'resa'));
		if(isset($paypalTitles->$locale) && !empty($paypalTitles->$locale)){
			$list['paypal'] = $paypalTitles->$locale;
		}
		else $list['paypal'] = __('paypal_payment_type_radio_title', 'resa');
		$systemPayTitles = unserialize(get_option('resa_settings_payment_systempay_title', 'resa'));
		if(isset($systemPayTitles->$locale) && !empty($systemPayTitles->$locale)){
			$list['systempay'] = $systemPayTitles->$locale;
		}
		else $list['systempay'] = __('systempay_payment_type_radio_title', 'resa');
		$moneticoTitles = unserialize(get_option('resa_settings_payment_monetico_title', 'resa'));
		if(isset($moneticoTitles->$locale) && !empty($moneticoTitles->$locale)){
			$list['monetico'] = $moneticoTitles->$locale;
		}
		else $list['monetico'] = __('monetico_payment_type_radio_title', 'resa');
		$stripeTitles = unserialize(get_option('resa_settings_payment_stripe_title', 'resa'));
		if(isset($stripeTitles->$locale) && !empty($stripeTitles->$locale)){
			$list['stripe'] = $stripeTitles->$locale;
		}
		else $list['stripe'] = __('stripe_payment_type_radio_title', 'resa');
		$stripeTitles = unserialize(get_option('resa_settings_payment_stripe_title', 'resa'));
		if(isset($stripeTitles->$locale) && !empty($stripeTitles->$locale)){
			$list['stripeConnect'] = $stripeTitles->$locale;
		}
		else $list['stripeConnect'] = __('stripe_payment_type_radio_title', 'resa');
		$payboxTitles = unserialize(get_option('resa_settings_payment_paybox_title', 'resa'));
		if(isset($payboxTitles->$locale) && !empty($payboxTitles->$locale)){
			$list['paybox'] = $payboxTitles->$locale;
		}
		else $list['paybox'] = __('paybox_payment_type_radio_title', 'resa');
		$swiklyTitles = unserialize(get_option('resa_settings_payment_swikly_title', 'resa'));
		if(isset($swiklyTitles->$locale) && !empty($swiklyTitles->$locale)){
			$list['swikly'] = $swiklyTitles->$locale;
		}
		else $list['swikly'] = __('swikly_payment_type_radio_title', 'resa');

		$list['customer'] = __('customer_payment_title', 'resa');
		$list['defer'] = __('defer_payment_title', 'resa');
		$list['check'] = __('cheque_payment_type_radio_title', 'resa');
		return $list;
	}

	public static function statePaymentsList(){
		$list = array();
		$list[0] = array('id' => 'none', 'title' => __('none_payments_title', 'resa'), 'displayFilter' => true);
		$list[1] = array('id' => 'advancePayment', 'title' => __('incomplete_payments_title', 'resa'), 'displayFilter' => true);
		$list[2] = array('id' => 'deposit', 'title' => __('deposit_payments_title', 'resa'), 'displayFilter' => false);
		$list[3] = array('id' => 'completed', 'title' => __('complete_payments_title', 'resa'), 'displayFilter' => true);
		$list[4] = array('id' => 'repayment_incompleted', 'title' => __('repayment_incompleted_payments_title', 'resa'), 'displayFilter' => true);
		$list[5] = array('id' => 'repayment_completed', 'title' => __('repayment_completed_payments_title', 'resa'), 'displayFilter' => true);
		return $list;
	}

	public static function paymentsTypeList(){
		$list = array();
		$list[0] = array('id'=>'onTheSpot', 'title' => __('on_the_spot_payment_title', 'resa'), 'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_on_the_spot')) == 'true', 'text'=>'', 'online' => false,
		'class' => 'image_onspot', 'advancePayment' => false);

		$list[1] = array('id'=>'later', 'title' => __('later_payment_title', 'resa'), 'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_later')) == 'true', 'text'=>'', 'online' => false,
		'class' => 'image_onspot', 'advancePayment' => false);

		$list[2] = array('id'=>'transfer', 'title' => __('transfer_payment_title', 'resa'), 'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_transfer')) == 'true', 'online' => false, 'text'=>unserialize(get_option('resa_settings_payment_transfer_text')), 'class' => '', 'advancePayment' => true);

		$list[3] = array('id'=>'cheque', 'title' => __('cheque_payment_title', 'resa'), 'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_cheque')) == 'true','online' => false, 'text'=>unserialize(get_option('resa_settings_payment_cheque_text')), 'class' => '', 'advancePayment' => true);

		$list[4] = array('id'=>'paypal', 'title' => __('paypal_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_paypal_title', 'resa')), 'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_paypal')) == 'true', 'online' => true, 'text'=>'',
		'class' => 'image_card', 'advancePayment' => true);

		$list[5] = array('id'=>'systempay', 'title' =>  __('systempay_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_systempay_title', 'resa')),
		'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_systempay')) == 'true', 'text'=>'', 'online' => true,
		'class' => 'image_card', 'advancePayment' => true);

		$list[6] = array('id'=>'monetico', 'title' =>  __('monetico_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_monetico_title', 'resa')),
		'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_monetico')) == 'true', 'text'=>'', 'online' => true,
		'class' => 'image_card', 'advancePayment' => true);

		$list[7] = array('id'=>'stripe', 'title' =>  __('stripe_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_stripe_title', 'resa')),
		'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_stripe')) == 'true', 'text'=>'', 'online' => true,
		'class' => 'image_card', 'advancePayment' => true);

		$list[8] = array('id'=>'stripeConnect', 'title' =>  __('stripe_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_stripe_title', 'resa')),
		'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_stripe_connect', 0)) == 'true', 'text'=>'', 'online' => true,
		'class' => 'image_card', 'advancePayment' => true);

		$list[9] = array('id'=>'paybox', 'title' =>  __('paybox_payment_title', 'resa'),
		'title_public' => unserialize(get_option('resa_settings_payment_paybox_title', 'resa')),
		'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_paybox')) == 'true', 'text'=>'', 'online' => true,
		'class' => 'image_card', 'advancePayment' => true);

		$list[10] = array('id'=>'swikly', 'title' => __('swikly_payment_title', 'resa'), 'title_public' => unserialize(get_option('resa_settings_payment_swikly_title', '')), 'activated'=> class_exists('RESA_SwiklyConnect') && RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_swikly_connect')) == 'true', 'text'=>'', 'online' => true, 'class' => 'image_card', 'advancePayment' => false);

		$list[11] = array('id'=>'customer', 'title' =>  __('customer_payment_title', 'resa'), 'activated'=>false, 'text'=>'', 'online' => false,
		'class' => '', 'advancePayment' => false);

		$list[12] = array('id'=>'defer', 'title' =>  __('defer_payment_title', 'resa'), 'activated'=>false, 'text'=>'', 'online' => false,
		'class' => '', 'advancePayment' => false);

		$list[13] = array('id'=>'check', 'title' => __('cheque_payment_title', 'resa'), 'activated'=>false,'online' => false, 'text'=>'', 'class' => '', 'advancePayment' => false);
		return $list;
	}

	public static function statesList(){
		$list = array();
		$list[0] = array('id' => 'ok', 'title' => __('ok_word', 'resa'), 'isFilter'=>true, 'selected'=>true, 'filterName' => __('filter_ok_word', 'resa'));
		$list[1] = array('id' => 'waiting', 'title' => __('waiting_word', 'resa'), 'isFilter'=>true, 'selected'=>true, 'filterName' => __('filter_waiting_word', 'resa'));
		$list[2] = array('id' => 'cancelled', 'title' => __('cancelled_word', 'resa'), 'isFilter'=>true, 'selected'=>false, 'filterName' => __('filter_cancelled_word', 'resa'));
		$list[3] = array('id' => 'abandonned', 'title' => __('abandonned_word', 'resa'), 'isFilter'=>true, 'selected'=>false , 'filterName' => __('filter_abandonned_word', 'resa'));
		$list[4] = array('id' => 'deleted', 'title' => __('deleted_word', 'resa'), 'isFilter'=>false, 'selected'=>false, 'filterName' => '');
		$list[5] = array('id' => 'updated', 'title' => __('updated_word', 'resa'), 'isFilter'=>false, 'selected'=>false, 'filterName' => '');
		$list[6] = array('id' => 'pending', 'title' => __('pending_word', 'resa'), 'isFilter'=>false, 'selected'=>false, 'filterName' => '');
		return $list;
	}

	public static function tagsList(){
		$customTags = unserialize(get_option('resa_settings_custom_tags'));
		$list = array();
		foreach($customTags as $tag){
			array_push($list, $tag);
		}
		return $list;
	}

	public static function typeCheckReductionConditions(){
		$list = array();
		$list[0] = array('id'=>0, 'title' => __('all_booking_option', 'resa'), 'idsType' => array('code', 'services', 'amount', 'registerDate', 'customer'));
		$list[1] = array('id'=>1, 'title' => __('same_date_option', 'resa'), 'idsType' => array('services', 'amount',  'registerDate', 'customer'));
		$list[2] = array('id'=>2, 'title' => __('same_appointment_option', 'resa'), 'idsType' => array('services', 'amount',  'registerDate','customer'));
		$list[3] = array('id'=>3, 'title' => __('all_bookings_of_customer_option', 'resa'), 'idsType' => array('code', 'services', 'amount','customer'));
		return $list;
	}

	public static function typeReductionConditions(){
		$list = array();
		array_push($list, array('id'=>'code', 'title' => __('promo_code_title', 'resa')));
		array_push($list, array('id'=>'services', 'title' => __('services_title', 'resa')));
		array_push($list, array('id'=>'amount', 'title' => __('booking_amount_title', 'resa')));
		array_push($list, array('id'=>'registerDate', 'title' => __('booking_register_date_title', 'resa')));
		array_push($list, array('id'=>'customer', 'title' => __('customer_title', 'resa')));
		return $list;
	}

	public static function typeReductionApplications(){
		$list = array();
		$list[0] = array('id'=>0, 'title' => __('fixed_amount_on_sub_total_title', 'resa'), 'applyOn'=>array(0, 1, 2));
		$list[1] = array('id'=>1, 'title' => __('purcent_on_sub_total_title', 'resa'), 'applyOn'=>array(0, 1, 2));
		$list[2] = array('id'=>2, 'title' => __('fixed_amount_on_price_title', 'resa'), 'applyOn'=>array(0, 1, 2));
		$list[3] = array('id'=>3, 'title' => __('change_price_list_value_title', 'resa'), 'applyOn'=>array(1,2));
		$list[4] = array('id'=>4, 'title' => __('quatity_given_title', 'resa'), 'applyOn'=>array(1,2));
		$list[5] = array('id'=>5, 'title' => __('custom_text_title', 'resa'), 'applyOn'=>array(0, 1, 2));
		return $list;
	}

	public static function typeApplicationsTypeReduction(){
		$list = array();
		$list[0] = array('id'=>0, 'title' => __('sub_total_of_booking_title', 'resa'));
		$list[1] = array('id'=>1, 'title' => __('on_the_conditions_reduction_title', 'resa'));
		$list[2] = array('id'=>2, 'title' => __('anothers_cases_title', 'resa'));
		return $list;
	}

	public static function typeApplicationConditionsOn(){
		$list = array();
		$list[0] = array('id'=>0, 'title' => __('on_same_date_title', 'resa'));
		$list[1] = array('id'=>1, 'title' => __('on_same_appointment_title', 'resa'));
		$list[2] = array('id'=>2, 'title' => __('on_the_booking_title', 'resa'));
		return $list;
	}

	public static function typeReductionApplicationConditions(){
		$list = array();
		$list[1] = array('id'=>'service', 'title' => __('service_title', 'resa'));
		$list[2] = array('id'=>'amount', 'title' => __('appointment_amount_title', 'resa'));
		$list[3] = array('id'=>'date', 'title' => __('appointment_date_title', 'resa'));
		$list[4] = array('id'=>'time', 'title' => __('appointment_time_title', 'resa'));
		$list[5] = array('id'=>'days', 'title' => __('days_title', 'resa'));
		return $list;
	}

	public static function getAlertModels(){
		$list = array();
		$list[0] = array('type' => 0, 'name' => __('capacity_alert_name', 'resa'), 'description' => __('capacity_alert_description', 'resa'));
		$list[1] = array('type' => 1, 'name' => __('reductions_alert_name', 'resa'), 'description' => __('reductions_alert_description', 'resa'));
		$list[2] = array('type' => 2, 'name' => __('timeslot_alert_name', 'resa'), 'description' => __('timeslot_alert_description', 'resa'));
		$list[3] = array('type' => 3, 'name' => __('equipments_alert_name', 'resa'), 'description' => __('equipments_alert_description', 'resa'));
		return $list;
	}

	public static function getAfterAppointmentDays(){
		$list = array();
		array_push($list, array('number' => 1, 'title' => '1 ' . __('day_word', 'resa')));
		for($i = 2; $i <= 10; $i++){
			array_push($list, array('number' => $i, 'title' => $i . ' ' . __('days_word', 'resa')));
		}
		return $list;
	}


	public static function getJSONMonths()
	{
		$months = array(
			__('January_word', 'resa'),
			__('February_word', 'resa'),
			__('March_word', 'resa'),
			__('April_word', 'resa'),
			__('May_word', 'resa'),
			__('June_word', 'resa'),
			__('July_word', 'resa'),
			__('August_word', 'resa'),
			__('September_word', 'resa'),
			__('October_word', 'resa'),
			__('November_word', 'resa'),
			__('December_word', 'resa'));
		return json_encode($months);
	}

	public static function getJSONDays()
	{
		$days = array(
			__('Sunday_word', 'resa'),
			__('Monday_word', 'resa'),
			__('Tuesday_word', 'resa'),
			__('Wednesday_word', 'resa'),
			__('Thursday_word', 'resa'),
			__('Friday_word', 'resa'),
			__('Saturday_word', 'resa'));
		return json_encode($days);
	}

	public static function getJSONCountries() {
		return json_encode(self::getArrayCountries());
	}

	public static function getArrayCountries() {
		$countries = array(
			array('code'=>'AFG','name' => 'Afghanistan'),
			array('code'=>'ALA','name' => 'Åland Islands'),
			array('code'=>'ALB','name' => 'Albania'),
			array('code'=>'DZA','name' => 'Algeria'),
			array('code'=>'ASM','name' => 'American Samoa'),
			array('code'=>'AND','name' => 'Andorra'),
			array('code'=>'AGO','name' => 'Angola'),
			array('code'=>'AIA','name' => 'Anguilla'),
			array('code'=>'ATA','name' => 'Antarctica'),
			array('code'=>'ATG','name' => 'Antigua and Barbuda'),
			array('code'=>'ARG','name' => 'Argentina'),
			array('code'=>'ARM','name' => 'Armenia'),
			array('code'=>'ABW','name' => 'Aruba'),
			array('code'=>'AUS','name' => 'Australia'),
			array('code'=>'AUT','name' => 'Austria'),
			array('code'=>'AZE','name' => 'Azerbaijan'),
			array('code'=>'BHS','name' => 'Bahamas'),
			array('code'=>'BHR','name' => 'Bahrain'),
			array('code'=>'BGD','name' => 'Bangladesh'),
			array('code'=>'BRB','name' => 'Barbados'),
			array('code'=>'BLR','name' => 'Belarus'),
			array('code'=>'BEL','name' => 'Belgium'),
			array('code'=>'BLZ','name' => 'Belize'),
			array('code'=>'BEN','name' => 'Benin'),
			array('code'=>'BMU','name' => 'Bermuda'),
			array('code'=>'BTN','name' => 'Bhutan'),
			array('code'=>'BOL','name' => 'Bolivia, Plurinational State of'),
			array('code'=>'BES','name' => 'Bonaire, Sint Eustatius and Saba'),
			array('code'=>'BIH','name' => 'Bosnia and Herzegovina'),
			array('code'=>'BWA','name' => 'Botswana'),
			array('code'=>'BVT','name' => 'Bouvet Island'),
			array('code'=>'BRA','name' => 'Brazil'),
			array('code'=>'IOT','name' => 'British Indian Ocean Territory'),
			array('code'=>'BRN','name' => 'Brunei Darussalam'),
			array('code'=>'BGR','name' => 'Bulgaria'),
			array('code'=>'BFA','name' => 'Burkina Faso'),
			array('code'=>'BDI','name' => 'Burundi'),
			array('code'=>'KHM','name' => 'Cambodia'),
			array('code'=>'CMR','name' => 'Cameroon'),
			array('code'=>'CAN','name' => 'Canada'),
			array('code'=>'CPV','name' => 'Cape Verde'),
			array('code'=>'CYM','name' => 'Cayman Islands'),
			array('code'=>'CAF','name' => 'Central African Republic'),
			array('code'=>'TCD','name' => 'Chad'),
			array('code'=>'CHL','name' => 'Chile'),
			array('code'=>'CHN','name' => 'China'),
			array('code'=>'CXR','name' => 'Christmas Island'),
			array('code'=>'CCK','name' => 'Cocos (Keeling) Islands'),
			array('code'=>'COL','name' => 'Colombia'),
			array('code'=>'COM','name' => 'Comoros'),
			array('code'=>'COG','name' => 'Congo'),
			array('code'=>'COD','name' => 'Congo, the Democratic Republic of the'),
			array('code'=>'COK','name' => 'Cook Islands'),
			array('code'=>'CRI','name' => 'Costa Rica'),
			array('code'=>'CIV','name' => 'Côte d\'Ivoire'),
			array('code'=>'HRV','name' => 'Croatia'),
			array('code'=>'CUB','name' => 'Cuba'),
			array('code'=>'CUW','name' => 'Curaçao'),
			array('code'=>'CYP','name' => 'Cyprus'),
			array('code'=>'CZE','name' => 'Czech Republic'),
			array('code'=>'DNK','name' => 'Denmark'),
			array('code'=>'DJI','name' => 'Djibouti'),
			array('code'=>'DMA','name' => 'Dominica'),
			array('code'=>'DOM','name' => 'Dominican Republic'),
			array('code'=>'ECU','name' => 'Ecuador'),
			array('code'=>'EGY','name' => 'Egypt'),
			array('code'=>'SLV','name' => 'El Salvador'),
			array('code'=>'GNQ','name' => 'Equatorial Guinea'),
			array('code'=>'ERI','name' => 'Eritrea'),
			array('code'=>'EST','name' => 'Estonia'),
			array('code'=>'ETH','name' => 'Ethiopia'),
			array('code'=>'FLK','name' => 'Falkland Islands (Malvinas)'),
			array('code'=>'FRO','name' => 'Faroe Islands'),
			array('code'=>'FJI','name' => 'Fiji'),
			array('code'=>'FIN','name' => 'Finland'),
			array('code'=>'FRA','name' => 'France'),
			array('code'=>'GUF','name' => 'French Guiana'),
			array('code'=>'PYF','name' => 'French Polynesia'),
			array('code'=>'ATF','name' => 'French Southern Territories'),
			array('code'=>'GAB','name' => 'Gabon'),
			array('code'=>'GMB','name' => 'Gambia'),
			array('code'=>'GEO','name' => 'Georgia'),
			array('code'=>'DEU','name' => 'Germany'),
			array('code'=>'GHA','name' => 'Ghana'),
			array('code'=>'GIB','name' => 'Gibraltar'),
			array('code'=>'GRC','name' => 'Greece'),
			array('code'=>'GRL','name' => 'Greenland'),
			array('code'=>'GRD','name' => 'Grenada'),
			array('code'=>'GLP','name' => 'Guadeloupe'),
			array('code'=>'GUM','name' => 'Guam'),
			array('code'=>'GTM','name' => 'Guatemala'),
			array('code'=>'GGY','name' => 'Guernsey'),
			array('code'=>'GIN','name' => 'Guinea'),
			array('code'=>'GNB','name' => 'Guinea-Bissau'),
			array('code'=>'GUY','name' => 'Guyana'),
			array('code'=>'HTI','name' => 'Haiti'),
			array('code'=>'HMD','name' => 'Heard Island and McDonald Islands'),
			array('code'=>'VAT','name' => 'Holy See (Vatican City State)'),
			array('code'=>'HND','name' => 'Honduras'),
			array('code'=>'HKG','name' => 'Hong Kong'),
			array('code'=>'HUN','name' => 'Hungary'),
			array('code'=>'ISL','name' => 'Iceland'),
			array('code'=>'IND','name' => 'India'),
			array('code'=>'IDN','name' => 'Indonesia'),
			array('code'=>'IRN','name' => 'Iran, Islamic Republic of'),
			array('code'=>'IRQ','name' => 'Iraq'),
			array('code'=>'IRL','name' => 'Ireland'),
			array('code'=>'IMN','name' => 'Isle of Man'),
			array('code'=>'ISR','name' => 'Israel'),
			array('code'=>'ITA','name' => 'Italy'),
			array('code'=>'JAM','name' => 'Jamaica'),
			array('code'=>'JPN','name' => 'Japan'),
			array('code'=>'JEY','name' => 'Jersey'),
			array('code'=>'JOR','name' => 'Jordan'),
			array('code'=>'KAZ','name' => 'Kazakhstan'),
			array('code'=>'KEN','name' => 'Kenya'),
			array('code'=>'KIR','name' => 'Kiribati'),
			array('code'=>'PRK','name' => 'Korea, Democratic People\'s Republic of'),
			array('code'=>'KOR','name' => 'Korea, Republic of'),
			array('code'=>'KWT','name' => 'Kuwait'),
			array('code'=>'KGZ','name' => 'Kyrgyzstan'),
			array('code'=>'LAO','name' => 'Lao People\'s Democratic Republic'),
			array('code'=>'LVA','name' => 'Latvia'),
			array('code'=>'LBN','name' => 'Lebanon'),
			array('code'=>'LSO','name' => 'Lesotho'),
			array('code'=>'LBR','name' => 'Liberia'),
			array('code'=>'LBY','name' => 'Libya'),
			array('code'=>'LIE','name' => 'Liechtenstein'),
			array('code'=>'LTU','name' => 'Lithuania'),
			array('code'=>'LUX','name' => 'Luxembourg'),
			array('code'=>'MAC','name' => 'Macao'),
			array('code'=>'MKD','name' => 'Macedonia, the former Yugoslav Republic of'),
			array('code'=>'MDG','name' => 'Madagascar'),
			array('code'=>'MWI','name' => 'Malawi'),
			array('code'=>'MYS','name' => 'Malaysia'),
			array('code'=>'MDV','name' => 'Maldives'),
			array('code'=>'MLI','name' => 'Mali'),
			array('code'=>'MLT','name' => 'Malta'),
			array('code'=>'MHL','name' => 'Marshall Islands'),
			array('code'=>'MTQ','name' => 'Martinique'),
			array('code'=>'MRT','name' => 'Mauritania'),
			array('code'=>'MUS','name' => 'Mauritius'),
			array('code'=>'MYT','name' => 'Mayotte'),
			array('code'=>'MEX','name' => 'Mexico'),
			array('code'=>'FSM','name' => 'Micronesia, Federated States of'),
			array('code'=>'MDA','name' => 'Moldova, Republic of'),
			array('code'=>'MCO','name' => 'Monaco'),
			array('code'=>'MNG','name' => 'Mongolia'),
			array('code'=>'MNE','name' => 'Montenegro'),
			array('code'=>'MSR','name' => 'Montserrat'),
			array('code'=>'MAR','name' => 'Morocco'),
			array('code'=>'MOZ','name' => 'Mozambique'),
			array('code'=>'MMR','name' => 'Myanmar'),
			array('code'=>'NAM','name' => 'Namibia'),
			array('code'=>'NRU','name' => 'Nauru'),
			array('code'=>'NPL','name' => 'Nepal'),
			array('code'=>'NLD','name' => 'Netherlands'),
			array('code'=>'NCL','name' => 'New Caledonia'),
			array('code'=>'NZL','name' => 'New Zealand'),
			array('code'=>'NIC','name' => 'Nicaragua'),
			array('code'=>'NER','name' => 'Niger'),
			array('code'=>'NGA','name' => 'Nigeria'),
			array('code'=>'NIU','name' => 'Niue'),
			array('code'=>'NFK','name' => 'Norfolk Island'),
			array('code'=>'MNP','name' => 'Northern Mariana Islands'),
			array('code'=>'NOR','name' => 'Norway'),
			array('code'=>'OMN','name' => 'Oman'),
			array('code'=>'PAK','name' => 'Pakistan'),
			array('code'=>'PLW','name' => 'Palau'),
			array('code'=>'PSE','name' => 'Palestinian Territory, Occupied'),
			array('code'=>'PAN','name' => 'Panama'),
			array('code'=>'PNG','name' => 'Papua New Guinea'),
			array('code'=>'PRY','name' => 'Paraguay'),
			array('code'=>'PER','name' => 'Peru'),
			array('code'=>'PHL','name' => 'Philippines'),
			array('code'=>'PCN','name' => 'Pitcairn'),
			array('code'=>'POL','name' => 'Poland'),
			array('code'=>'PRT','name' => 'Portugal'),
			array('code'=>'PRI','name' => 'Puerto Rico'),
			array('code'=>'QAT','name' => 'Qatar'),
			array('code'=>'REU','name' => 'Réunion'),
			array('code'=>'ROU','name' => 'Romania'),
			array('code'=>'RUS','name' => 'Russian Federation'),
			array('code'=>'RWA','name' => 'Rwanda'),
			array('code'=>'BLM','name' => 'Saint Barthélemy'),
			array('code'=>'SHN','name' => 'Saint Helena, Ascension and Tristan da Cunha'),
			array('code'=>'KNA','name' => 'Saint Kitts and Nevis'),
			array('code'=>'LCA','name' => 'Saint Lucia'),
			array('code'=>'MAF','name' => 'Saint Martin (French part)'),
			array('code'=>'SPM','name' => 'Saint Pierre and Miquelon'),
			array('code'=>'VCT','name' => 'Saint Vincent and the Grenadines'),
			array('code'=>'WSM','name' => 'Samoa'),
			array('code'=>'SMR','name' => 'San Marino'),
			array('code'=>'STP','name' => 'Sao Tome and Principe'),
			array('code'=>'SAU','name' => 'Saudi Arabia'),
			array('code'=>'SEN','name' => 'Senegal'),
			array('code'=>'SRB','name' => 'Serbia'),
			array('code'=>'SYC','name' => 'Seychelles'),
			array('code'=>'SLE','name' => 'Sierra Leone'),
			array('code'=>'SGP','name' => 'Singapore'),
			array('code'=>'SXM','name' => 'Sint Maarten (Dutch part)'),
			array('code'=>'SVK','name' => 'Slovakia'),
			array('code'=>'SVN','name' => 'Slovenia'),
			array('code'=>'SLB','name' => 'Solomon Islands'),
			array('code'=>'SOM','name' => 'Somalia'),
			array('code'=>'ZAF','name' => 'South Africa'),
			array('code'=>'SGS','name' => 'South Georgia and the South Sandwich Islands'),
			array('code'=>'SSD','name' => 'South Sudan'),
			array('code'=>'ESP','name' => 'Spain'),
			array('code'=>'LKA','name' => 'Sri Lanka'),
			array('code'=>'SDN','name' => 'Sudan'),
			array('code'=>'SUR','name' => 'Suriname'),
			array('code'=>'SJM','name' => 'Svalbard and Jan Mayen'),
			array('code'=>'SWZ','name' => 'Swaziland'),
			array('code'=>'SWE','name' => 'Sweden'),
			array('code'=>'CHE','name' => 'Switzerland'),
			array('code'=>'SYR','name' => 'Syrian Arab Republic'),
			array('code'=>'TWN','name' => 'Taiwan, Province of China'),
			array('code'=>'TJK','name' => 'Tajikistan'),
			array('code'=>'TZA','name' => 'Tanzania, United Republic of'),
			array('code'=>'THA','name' => 'Thailand'),
			array('code'=>'TLS','name' => 'Timor-Leste'),
			array('code'=>'TGO','name' => 'Togo'),
			array('code'=>'TKL','name' => 'Tokelau'),
			array('code'=>'TON','name' => 'Tonga'),
			array('code'=>'TTO','name' => 'Trinidad and Tobago'),
			array('code'=>'TUN','name' => 'Tunisia'),
			array('code'=>'TUR','name' => 'Turkey'),
			array('code'=>'TKM','name' => 'Turkmenistan'),
			array('code'=>'TCA','name' => 'Turks and Caicos Islands'),
			array('code'=>'TUV','name' => 'Tuvalu'),
			array('code'=>'UGA','name' => 'Uganda'),
			array('code'=>'UKR','name' => 'Ukraine'),
			array('code'=>'ARE','name' => 'United Arab Emirates'),
			array('code'=>'GBR','name' => 'United Kingdom'),
			array('code'=>'USA','name' => 'United States'),
			array('code'=>'UMI','name' => 'United States Minor Outlying Islands'),
			array('code'=>'URY','name' => 'Uruguay'),
			array('code'=>'UZB','name' => 'Uzbekistan'),
			array('code'=>'VUT','name' => 'Vanuatu'),
			array('code'=>'VEN','name' => 'Venezuela, Bolivarian Republic of'),
			array('code'=>'VNM','name' => 'Viet Nam'),
			array('code'=>'VGB','name' => 'Virgin Islands, British'),
			array('code'=>'VIR','name' => 'Virgin Islands, U.S.'),
			array('code'=>'WLF','name' => 'Wallis and Futuna'),
			array('code'=>'ESH','name' => 'Western Sahara'),
			array('code'=>'YEM','name' => 'Yemen'),
			array('code'=>'ZMB','name' => 'Zambia'),
			array('code'=>'ZWE','name' => 'Zimbabwe')
		);
		return $countries;
	}

	public static function getLanguages(){
		$languages = array(
			//'de_DE' => array('Deutsch', 'de_DE', 'de'),
			'en_GB' => array('English', 'en_GB', 'en'),
			//'en_US' => array('English', 'en_US', 'en'),
			'fr_FR' => array('Français', 'fr_FR', 'fr'),
			'nl_NL' => array('Nederlands', 'nl_NL', 'nl')
			//'es_ES' => array('Español', 'es_ES', 'es'),
			//'pt_PT' => array('Português', 'pt_PT', 'pt'),
			//'pt_BR' => array('Português	', 'pt_BR', 'pt'),
			//'it_IT' => array('Italiano	', 'it_IT', 'it'),
			//'ru_RU' => array('Русский	', 'ru_RU', 'ru'),
		);
		return $languages;
	}

	public static function getLanguage($locale){
		$languages = self::getLanguages();
		if(empty($locale) || !isset($languages[$locale])) return $locale;
		return $languages[$locale][0];
	}

	public static function getCountry($country){
		$countries = self::getArrayCountries();
		foreach($countries as $localCountry){
			if(strtoupper($localCountry['code']) == strtoupper($country)){
				return $localCountry['name'];
			}
		}
		return '';
	}

	public static function calculateBookingPayment($status, $paymentState){
		$result = 'paiement_none';
		$isCancelledOrAbandonned = ($status == 'cancelled' || $status == 'abandonned');
		if(!$isCancelledOrAbandonned){
			if($paymentState == 'noPayment') $result = 'paiement_none';
			else if($paymentState == 'advancePayment') $result = 'paiement_incomplete';
			else if($paymentState == 'deposit') $result = 'paiement_incomplete';
			else if($paymentState == 'over') $result = 'paiement_overpaiement';
			else $result = 'paiement_done';
		}
		else {
			if($paymentState == 'noPayment') $result = 'paiement_remboursement_done';
			else if($paymentState == 'advancePayment') $result = 'paiement_remboursement';
			else if($paymentState == 'deposit') $result = 'paiement_remboursement';
			else if($paymentState == 'complete') $result = 'paiement_remboursement';
			else $result = 'paiement_remboursement_done';
		}
		return $result;
	}

	public static function getFilterSettingsPlace($currentRESAUser){
		$places = array();
		if(is_object($currentRESAUser->getFilterSettings()) && isset($currentRESAUser->getFilterSettings()->places)){
			$filterPlaces = (array)($currentRESAUser->getFilterSettings()->places);
			$places = self::getFilterSettingsPlaceWithPlaces($filterPlaces);
		}
		return $places;
	}

	public static function getFilterSettingsPlaceWithPlaces($filterPlaces){
		$places = array();
		foreach($filterPlaces as $key => $value){
			if($value) {
				array_push($places, $key);
			}
		}
		return $places;
	}

}
