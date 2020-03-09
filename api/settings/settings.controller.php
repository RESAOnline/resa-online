<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APISettingsController
{
	public static function getPages(){
		$args = array(
			'sort_order' => 'asc',
			'sort_column' => 'post_title',
			'hierarchical' => 1,
			'exclude' => '',
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'child_of' => 0,
			'parent' => -1,
			'exclude_tree' => '',
			'number' => '',
			'offset' => 0,
			'post_type' => 'page',
			'post_status' => 'publish'
		);
		$pages = get_pages($args);
		$results = array();
		foreach ( $pages as $page) {
			array_push($results, array(
				'title' => $page->post_title,
				'isAccount' => strstr($page->post_content, '[RESA_account]')!==false,
				'isForm' => strstr($page->post_content, '[RESA_form')!==false,
				'url' => get_page_link($page->ID)
			));
		}
		return $results;
	}


	public static function init(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'company_name' => get_option('resa_settings_company_name'),
				'company_logo' => get_option('resa_settings_company_logo'),
				'company_address' => get_option('resa_settings_company_address'),
				'company_phone' => get_option('resa_settings_company_phone'),
				'company_type' => get_option('resa_settings_company_type'),
				'company_siret' => get_option('resa_settings_company_siret'),
				'customer_account_url' => get_option('resa_settings_customer_account_url'),
				'customer_booking_url' => get_option('resa_settings_customer_booking_url'),
				'types_accounts' => unserialize(get_option('resa_settings_types_accounts')),
				'calendar_start_time' => intval(get_option('resa_settings_calendar_start_time')),
				'calendar_end_time' => intval(get_option('resa_settings_calendar_end_time')),
				'calendar_split_time' => intval(get_option('resa_settings_calendar_split_time')),
				'calendar_drag_and_drop_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_drag_and_drop_activated')) == 'true',
				'calendar_enabled_enabled_color' => RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_enabled_enabled_color')) == 'true',
				'calendar_enabled_disabled_color' => RESA_Tools::toJSONBoolean(get_option('resa_settings_calendar_enabled_disabled_color')) == 'true',
				'calendar_disabled_days_front_color' => get_option('resa_settings_calendar_disabled_days_front_color'),
				'calendar_enabled_days_front_color' => get_option('resa_settings_calendar_enabled_days_front_color'),
				'calendar_info_calendar_color' => get_option('resa_settings_calendar_info_calendar_color'),
				'calendar_service_constraint_color' => get_option('resa_settings_calendar_service_constraint_color'),

				'colors' => unserialize(get_option('resa_settings_colors')),
				'places' => unserialize(get_option('resa_settings_places')),
				'form_category_services' => unserialize(get_option('resa_settings_form_category_services')),
				'custom_tags' => unserialize(get_option('resa_settings_custom_tags')),

				'staff_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_staff_management_actived'))=='true',
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_old_appointments_displayed'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_old_appointments_displayed'))=='true',
				'staff_display_customer'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_customer'))=='true',
				'staff_display_total'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_total'))=='true',
				'staff_display_payments'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_payments'))=='true',
				'staff_display_numbers'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_numbers'))=='true',
				'staff_display_bookings_tab' => RESA_Tools::toJSONBoolean(get_option('resa_staff_display_bookings_tab'))=='true',
				'equipments_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_equipments_management_actived'))=='true',
				'groups_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_groups_management_actived'))=='true',

				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),

				'display_remaining_position_overcapacity' => RESA_Tools::toJSONBoolean(get_option('resa_settings_form_display_remaining_position_overcapacity')) == 'true',
				'custom_css' => get_option('resa_settings_global_custom_css'),
				'browser_not_compatible_sentence' => unserialize(get_option('resa_settings_browser_not_compatible_sentence')),

				'log_notifications' => unserialize(get_option('resa_settings_log_notifications')),

				'mailbox_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_mailbox_activated'))=='true',
				'mailbox' => unserialize(get_option('resa_settings_mailbox')),

				'currency'=>get_option('resa_settings_payment_currency'),
				'payment_activate'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_activate'))=='true',
				'payment_not_activated_text'=>unserialize(get_option('resa_settings_payment_not_activated_text')),
				'ask_advance_payment'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_ask_advance_payment'))=='true',
				'ask_advance_payment_type_accounts' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
				'ask_text_advance_payment'=>unserialize(get_option('resa_settings_payment_ask_text_advance_payment')),
				'return_url_success'=>get_option('resa_settings_payment_return_url_success'),
				'return_url_error'=>get_option('resa_settings_payment_return_url_error'),
				'quotation_return_url_success'=>get_option('resa_settings_quotation_return_url_success'),
				'display_tva_on_bill'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_display_tva_on_bill'))=='true',
				'informations_on_bill'=>unserialize(get_option('resa_settings_informations_on_bill')),
				'informations_on_quotation'=>unserialize(get_option('resa_settings_informations_on_quotation')),
				'vat_list'=>unserialize(get_option('resa_settings_vat_list')),
				'on_the_spot'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_on_the_spot')) == 'true',
				'later'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_later')) == 'true',
				'transfer'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_transfer')) == 'true',
				'transfer_text'=>unserialize(get_option('resa_settings_payment_transfer_text')),
				'cheque'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_cheque')) == 'true',
				'cheque_text'=>unserialize(get_option('resa_settings_payment_cheque_text')),
				'paypal'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_paypal')) == 'true',
				'paypal_title'=> unserialize(get_option('resa_settings_payment_paypal_title')),
				'paypal_idShop'=>get_option('resa_settings_payment_paypal_idShop'),
				'paypal_mode'=>get_option('resa_settings_payment_paypal_mode'),
				'systempay'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_systempay')) == 'true',
				'systempay_title' => unserialize(get_option('resa_settings_payment_systempay_title')),
				'systempay_id_shop'=>get_option('resa_settings_payment_systempay_id_shop'),
				'systempay_certificate'=>get_option('resa_settings_payment_systempay_certificate'),
				'systempay_mode'=>get_option('resa_settings_payment_systempay_mode'),
				'monetico' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_monetico')) == 'true',
				'monetico_title' => unserialize(get_option('resa_settings_payment_monetico_title')),
				'monetico_tpe' => get_option('resa_settings_payment_monetico_tpe'),
				'monetico_id_shop' => get_option('resa_settings_payment_monetico_id_shop'),
				'monetico_key' => get_option('resa_settings_payment_monetico_key'),
				'monetico_mode' => get_option('resa_settings_payment_monetico_mode'),
				'stripe' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_stripe')) == 'true',
				'stripe_title' => unserialize(get_option('resa_settings_payment_stripe_title')),
				'stripe_public_key' => get_option('resa_settings_payment_stripe_public_key'),
				'stripe_private_key' => get_option('resa_settings_payment_stripe_private_key'),
				'stripe_connect_id' => get_option('resa_settings_payment_stripe_connect_id', ''),
				'stripe_connect_conditions_validated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_stripe_connect_conditions_validated', 0)) == 'true',
				'stripeConnect' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_stripe_connect', 0)) == 'true',
				'stripe_mode_payment_state' => get_option('resa_settings_payment_stripe_mode_payment_state', 0),
				'paybox' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_paybox')) == 'true',
				'paybox_title' => unserialize(get_option('resa_settings_payment_paybox_title')),
				'paybox_site' => get_option('resa_settings_payment_paybox_site'),
				'paybox_rank' => get_option('resa_settings_payment_paybox_rank'),
				'paybox_id' => get_option('resa_settings_payment_paybox_id'),
				'paybox_secret' => get_option('resa_settings_payment_paybox_secret'),
				'paybox_mode' => get_option('resa_settings_payment_paybox_mode'),
				'swikly' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_swikly')) == 'true',
				'swikly_title' => unserialize(get_option('resa_settings_payment_swikly_title')),
				'swikly_user_secret' => get_option('resa_settings_payment_swikly_user_secret'),
				'swikly_user_api' => get_option('resa_settings_payment_swikly_user_api'),
				'swikly_api_key' => get_option('resa_settings_payment_swikly_api_key'),
				'swikly_api_secret' => get_option('resa_settings_payment_swikly_api_secret'),
				'swikly_deposit_type' => get_option('resa_settings_payment_swikly_deposit_type'),
				'swikly_process' => get_option('resa_settings_payment_swikly_process', 0),
				'swikly_process_type' => get_option('resa_settings_payment_swikly_process_type', 0),
				'swikly_process_value' => get_option('resa_settings_payment_swikly_process_value', 0),
				'swikly_sent_swik_with_ask_payment' => get_option('resa_settings_payment_swikly_sent_swik_with_ask_payment', false),
				'swikly_display_link' => get_option('resa_settings_payment_swikly_display_link', false),
				'swikly_mode' => get_option('resa_settings_payment_swikly_mode'),
				'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),

				'daily_display_weather' => get_option('resa_daily_display_weather', 1),
				'daily_url_weather' => get_option('resa_daily_url_weather', ''),
				'daily_group_number' => get_option('resa_daily_group_number', 10),

				'caisse_online_activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated'))=='true',
				'caisse_online_license_id' => get_option('resa_settings_caisse_online_license_id'),
				'caisse_online_site_url' => site_url(),
				'caisse_online_mode' => get_option('resa_settings_caisse_online_mode', 1),

				'facebook_activated' => get_option('resa_facebook_activated', false),
				'facebook_api_id' => get_option('resa_facebook_api_id', ''),
				'facebook_api_version' => get_option('resa_facebook_api_version', 'v3.2'),

				'backend_v2_activated' => get_option('resa_backend_v2_activated', false)
			);

			$colors = array(
				array('id' => 'tag_vert','name'=> __('Green_word','resa')),
				array('id' => 'tag_jaune','name'=> __('Yellow_word','resa')),
				array('id' => 'tag_orange','name'=> __('Orange_word','resa')),
				array('id' => 'tag_marron','name'=> __('Brown_word','resa')),
				array('id' => 'tag_rouge','name'=> __('Red_word','resa')),
				array('id' => 'tag_bleu','name'=> __('Blue_word','resa')),
			);

			$data = array(
				'allPages' => self::getPages(),
				'allCSS' => self::getCSSFiles(),
				'services' => RESA_Service::getAllServicesAndThisPrices(),
				'allLanguages' => RESA_Variables::getLanguages(),
				'colors' => $colors,
				'idParametersUsed' => RESA_Service::getAllIdParameterUsed(),
				'statesList' => RESA_Variables::statesList(),
				'allConnected' => RESA_ImapManager::getInstance()->allConnected(),
				'supportURL' => RESA_Variables::getURLSupport($currentRESAUser->getEmail()),
				'caisseOnlineInstalled' => class_exists('RESA_CaisseOnline'),
				'swiklyInstalled' => class_exists('RESA_Swikly'),
				'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
				'resaOnlineFacebookInstalled' => class_exists('RESA_FacebookLogin'),
				'needUpdate' => get_option('resa_update_customers', -1) != -1,
				'settings' => $settings
			);
			$response->set_data($data);
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function save(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['settings'])){
				$settings = json_decode($data['settings']);
				update_option('resa_settings_languages', serialize($settings->languages));
				update_option('resa_settings_company_name', esc_html($settings->company_name));
				update_option('resa_settings_company_logo', esc_html($settings->company_logo));
				update_option('resa_settings_company_address', esc_html($settings->company_address));
				update_option('resa_settings_company_phone', esc_html($settings->company_phone));
				update_option('resa_settings_company_type', esc_html($settings->company_type));
				update_option('resa_settings_company_siret', esc_html($settings->company_siret));
				update_option('resa_settings_customer_account_url', esc_html($settings->customer_account_url));
				update_option('resa_settings_customer_booking_url', esc_html($settings->customer_booking_url));
				foreach($settings->types_accounts as $typeAccount){
					foreach($typeAccount->name as $key => $name){
						$typeAccount->name->$key = esc_html($name);
					}
				}
				update_option('resa_settings_types_accounts', serialize($settings->types_accounts));

				update_option('resa_settings_calendar_start_time', $settings->calendar_start_time);
				update_option('resa_settings_calendar_end_time', $settings->calendar_end_time);
				update_option('resa_settings_calendar_split_time', $settings->calendar_split_time);
				update_option('resa_settings_calendar_drag_and_drop_activated', $settings->calendar_drag_and_drop_activated);
				update_option('resa_settings_calendar_enabled_enabled_color', $settings->calendar_enabled_enabled_color);
				update_option('resa_settings_calendar_enabled_disabled_color', $settings->calendar_enabled_disabled_color);
				update_option('resa_settings_calendar_disabled_days_front_color', $settings->calendar_disabled_days_front_color);
				update_option('resa_settings_calendar_enabled_days_front_color', $settings->calendar_enabled_days_front_color);
				update_option('resa_settings_calendar_info_calendar_color', $settings->calendar_info_calendar_color);
				update_option('resa_settings_calendar_service_constraint_color', $settings->calendar_service_constraint_color);

				$colors = get_option('resa_settings_colors', true);
				update_option('resa_settings_colors', serialize($settings->colors));
				if(print_r($colors, true) != serialize($settings->colors)){
					self::regenerateCSSForm();
				}
				update_option('resa_settings_places', serialize($settings->places));
				update_option('resa_settings_form_category_services', serialize($settings->form_category_services));
				update_option('resa_settings_custom_tags', serialize($settings->custom_tags));

				update_option('resa_staff_management_actived', esc_html($settings->staff_management_actived));
				update_option('resa_staff_word_many', serialize($settings->staff_word_many));
				update_option('resa_staff_word_single', serialize($settings->staff_word_single));
				update_option('resa_staff_old_appointments_displayed', esc_html($settings->staff_old_appointments_displayed));
				update_option('resa_staff_appointment_display_customer', esc_html($settings->staff_display_customer));
				update_option('resa_staff_appointment_display_total', esc_html($settings->staff_display_total));
				update_option('resa_staff_appointment_display_payments', esc_html($settings->staff_display_payments));
				update_option('resa_staff_appointment_display_numbers', esc_html($settings->staff_display_numbers));
				update_option('resa_staff_display_bookings_tab', esc_html($settings->staff_display_bookings_tab));
				update_option('resa_equipments_management_actived', esc_html($settings->equipments_management_actived));
				update_option('resa_groups_management_actived', esc_html($settings->groups_management_actived));

				update_option('resa_settings_form_participants_parameters', serialize($settings->form_participants_parameters));

				update_option('resa_settings_form_display_remaining_position_overcapacity', $settings->display_remaining_position_overcapacity);
				update_option('resa_settings_global_custom_css', $settings->custom_css);
				update_option('resa_settings_browser_not_compatible_sentence',  serialize($settings->browser_not_compatible_sentence));

				update_option('resa_settings_log_notifications', serialize($settings->log_notifications));

				update_option('resa_settings_mailbox_activated', $settings->mailbox_activated);
				update_option('resa_settings_mailbox', serialize($settings->mailbox));

				update_option('resa_settings_payment_currency', esc_html($settings->currency));
				update_option('resa_settings_payment_activate', esc_html($settings->payment_activate));
				update_option('resa_settings_payment_not_activated_text', serialize($settings->payment_not_activated_text));
				update_option('resa_settings_payment_ask_advance_payment', esc_html($settings->ask_advance_payment));
				update_option('resa_settings_payment_ask_advance_payment_type_accounts', serialize($settings->ask_advance_payment_type_accounts));
				update_option('resa_settings_payment_ask_text_advance_payment', serialize($settings->ask_text_advance_payment));
				update_option('resa_settings_payment_return_url_success', esc_html($settings->return_url_success));
				update_option('resa_settings_quotation_return_url_success', esc_html($settings->quotation_return_url_success));
				update_option('resa_settings_payment_return_url_error', esc_html($settings->return_url_error));
				update_option('resa_settings_display_tva_on_bill', esc_html($settings->display_tva_on_bill));
				update_option('resa_settings_informations_on_bill', serialize($settings->informations_on_bill));
				update_option('resa_settings_informations_on_quotation', serialize($settings->informations_on_quotation));
				update_option('resa_settings_vat_list', serialize($settings->vat_list));
				update_option('resa_settings_payment_on_the_spot', esc_html($settings->on_the_spot));
				update_option('resa_settings_payment_later', esc_html($settings->later));
				update_option('resa_settings_payment_transfer', esc_html($settings->transfer));
				update_option('resa_settings_payment_transfer_text', serialize($settings->transfer_text));
				update_option('resa_settings_payment_cheque', esc_html($settings->cheque));
				update_option('resa_settings_payment_cheque_text', serialize($settings->cheque_text));
				update_option('resa_settings_payment_paypal', esc_html($settings->paypal));
				update_option('resa_settings_payment_paypal_title', serialize($settings->paypal_title));
				update_option('resa_settings_payment_paypal_idShop', esc_html($settings->paypal_idShop));
				update_option('resa_settings_payment_paypal_mode', esc_html($settings->paypal_mode));
				update_option('resa_settings_payment_systempay', esc_html($settings->systempay));
				update_option('resa_settings_payment_systempay_title', serialize($settings->systempay_title));
				update_option('resa_settings_payment_systempay_id_shop', esc_html($settings->systempay_id_shop));
				update_option('resa_settings_payment_systempay_certificate', esc_html($settings->systempay_certificate));
				update_option('resa_settings_payment_systempay_mode', esc_html($settings->systempay_mode));
				update_option('resa_settings_payment_monetico', esc_html($settings->monetico));
				update_option('resa_settings_payment_monetico_title', serialize($settings->monetico_title));
				update_option('resa_settings_payment_monetico_tpe', esc_html($settings->monetico_tpe));
				update_option('resa_settings_payment_monetico_id_shop', esc_html($settings->monetico_id_shop));
				update_option('resa_settings_payment_monetico_key', esc_html($settings->monetico_key));
				update_option('resa_settings_payment_monetico_mode', esc_html($settings->monetico_mode));
				update_option('resa_settings_payment_stripe', esc_html($settings->stripe));
				update_option('resa_settings_payment_stripe_title', serialize($settings->stripe_title));
				update_option('resa_settings_payment_stripe_public_key', esc_html($settings->stripe_public_key));
				update_option('resa_settings_payment_stripe_private_key', esc_html($settings->stripe_private_key));
				update_option('resa_settings_payment_stripe_connect', esc_html($settings->stripeConnect));
				update_option('resa_settings_payment_stripe_connect_id', esc_html($settings->stripe_connect_id));
				update_option('resa_settings_payment_stripe_connect_conditions_validated', esc_html($settings->stripe_connect_conditions_validated));
				update_option('resa_settings_payment_stripe_mode_payment_state', esc_html($settings->stripe_mode_payment_state));
				update_option('resa_settings_payment_paybox', esc_html($settings->paybox));
				update_option('resa_settings_payment_paybox_title', serialize($settings->paybox_title));
				update_option('resa_settings_payment_paybox_site', esc_html($settings->paybox_site));
				update_option('resa_settings_payment_paybox_rank', esc_html($settings->paybox_rank));
				update_option('resa_settings_payment_paybox_id', esc_html($settings->paybox_id));
				update_option('resa_settings_payment_paybox_secret', esc_html($settings->paybox_secret));
				update_option('resa_settings_payment_paybox_mode', esc_html($settings->paybox_mode));
				update_option('resa_settings_payment_swikly', esc_html($settings->swikly));
				update_option('resa_settings_payment_swikly_user_secret', esc_html($settings->swikly_user_secret));
				update_option('resa_settings_payment_swikly_user_api', esc_html($settings->swikly_user_api));
				update_option('resa_settings_payment_swikly_title', serialize($settings->swikly_title));
				update_option('resa_settings_payment_swikly_api_key', esc_html($settings->swikly_api_key));
				update_option('resa_settings_payment_swikly_api_secret', esc_html($settings->swikly_api_secret));
				update_option('resa_settings_payment_swikly_deposit_type', esc_html($settings->swikly_deposit_type));
				update_option('resa_settings_payment_swikly_process', esc_html($settings->swikly_process));
				update_option('resa_settings_payment_swikly_process_type', esc_html($settings->swikly_process_type));
				update_option('resa_settings_payment_swikly_process_value', esc_html($settings->swikly_process_value));
				update_option('resa_settings_payment_swikly_sent_swik_with_ask_payment', esc_html($settings->swikly_sent_swik_with_ask_payment));
				update_option('resa_settings_payment_swikly_display_link', esc_html($settings->swikly_display_link));

				update_option('resa_settings_payment_swikly_mode', esc_html($settings->swikly_mode));

				update_option('resa_settings_states_parameters', serialize($settings->states_parameters));

				update_option('resa_daily_display_weather', esc_html($settings->daily_display_weather));
				update_option('resa_daily_url_weather', esc_html($settings->daily_url_weather));
				update_option('resa_daily_group_number', esc_html($settings->daily_group_number));

				update_option('resa_settings_caisse_online_activated', $settings->caisse_online_activated);
				update_option('resa_settings_caisse_online_license_id', $settings->caisse_online_license_id);
				update_option('resa_settings_caisse_online_site_url', $settings->caisse_online_site_url);
				update_option('resa_settings_caisse_online_mode', $settings->caisse_online_mode);

				update_option('resa_facebook_activated', $settings->facebook_activated);
				update_option('resa_facebook_api_id', $settings->facebook_api_id);
				update_option('resa_facebook_api_version', $settings->facebook_api_version);

				update_option('resa_backend_v2_activated', $settings->backend_v2_activated);
			}

			update_option('resa_first_parameter_done', 1);

			$caisseUrl = '';
			$apiCaisseUrl = '';
			if(class_exists('RESA_CaisseOnline')){
				$caisseUrl = RESA_CaisseOnline::getInstance()->getURLCaisse();
				$apiCaisseUrl = RESA_CaisseOnline::getInstance()->getBaseURL();
				RESA_CaisseOnline::getInstance()->updateRESASettings();
			}
			$data = array(
				'supportURL' => RESA_Variables::getURLSupport($currentRESAUser->getEmail()),
				'allConnected' => RESA_ImapManager::getInstance(true)->allConnected(),
				'staffManagement' => RESA_Variables::isStaffManagementActivated(),
				'equipmentsManagement' => RESA_Variables::isEquipmentsManagementActivated(),
				'groupsManagement' => RESA_Variables::isGroupsManagementActivated(),
				'caisse_online'=>array(
					'activated'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true',
					'caisse_url' => $caisseUrl,
					'api_caisse_url' => $apiCaisseUrl,
					'license_id'=>get_option('resa_settings_caisse_online_license_id'),
					'site_url' => site_url()
				),
				'stripeConnect' => array(
					'activated' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage() && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::checkConnection():false,
					'stripeConnectId' =>get_option('resa_settings_payment_stripe_connect_id'),
					'apiStripeConnectUrl' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage())?RESA_StripeConnect::getURL():'',
				),
				'swikly_link' => (class_exists('RESA_Swikly') && get_option('resa_settings_payment_swikly', false) && get_option('resa_settings_payment_swikly_display_link', false))?RESA_Swikly::getSwiklyLink():'',
				'paymentsTypeList' => RESA_Variables::paymentsTypeList(),
				'backendV2Activated' => get_option('resa_backend_v2_activated', false)
			);
			$response->set_data($data);
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function settingsLite(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['timeslots_mentions'])) {
				update_option('resa_settings_timeslots_mentions', serialize(json_decode($data['timeslots_mentions'])));
			}
			if(isset($data['tabs_activities'])) {
				update_option('resa_planning_tabs_activities', serialize(json_decode($data['tabs_activities'])));
			}
			if(isset($data['notifications_templates'])) {
				update_option('resa_settings_notifications_email_notifications_templates', serialize(json_decode($data['notifications_templates'])));
				update_option('resa_settings_notifications_email_notifications_templates_last_modification_date', (new DateTime())->format('Y-m-d H:i:s'));
			}
			$response->set_data(array('result' => 'success'));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function clearSynchronizationCustomers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$customers = RESA_Customer::getAllData();
			for($i = 0; $i < count($customers); $i++){
				$customer = $customers[$i];
				$customer->clearIdCaisseOnline();
				$customer->save(false);
			}
			$json = '{}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function forceCaisseOnline(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(class_exists('RESA_CaisseOnline')){
				RESA_CaisseOnline::getInstance()->clearDateCron();
			}
			$json = '{}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function configurationFile(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$arrayUploadDir = wp_get_upload_dir();
			$filename = 'wordpressConfig.txt';
			$fileSQL = fopen($arrayUploadDir['basedir'].'/'  . $filename, 'w') or die('Unable to open file');

			$environment = json_decode(new W18T());
			$environment->plugins = get_plugins();
			fwrite($fileSQL, print_r($environment, true));
			fclose($fileSQL);

			$json = '{
				"fileUrl":"'.$arrayUploadDir['baseurl'].'/'.$filename.'"
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	private static function regenerateCSSForm(){
		$forms = RESA_Form::getAllData();
		foreach($forms as $form){
			$form->generateCSS();
		}
	}

	/**
	 * return all CSS files
	 */
	public static function getCSSFiles(){
		$dir = RESA_Variables::getRESACSSDir();
		$css = array();
		if (is_dir($dir)) {
			if ($handle = opendir($dir)) {
				while (false !== ($file = readdir($handle))) {
					if(strlen($file) > 4 && substr($file, -3) == 'css' && substr($file, 0, 2) != '__'){
						array_push($css, substr($file, 0, -4));
					}
				}
				closedir($handle);
			}
		}
		else {
			mkdir($dir);
		}
		return $css;
	}

	/**
	 *
	 */
	public static function uploadCSSFile(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$files = $request->get_file_params();
			if(isset($files['file'])){
				$targetDir = RESA_Variables::getRESACSSDir();
				$targetFile = $targetDir . '/'. $files['file']['name'];
				$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
				if(!is_dir($targetDir)){	mkdir($targetDir); }
				if($imageFileType == 'css'){
					move_uploaded_file($files['file']['tmp_name'], $targetFile);
					$json = '{
						"allCSS":'.json_encode(self::getCSSFiles()).'
					}';
					$response->set_data(json_decode($json));
				}
				else {
					$response->set_status(401);
					$response->set_data(array('error' => 'file_not_css', 'message' => 'Not CSS file'));
				}
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'file_not_found', 'message' => 'File not found'));
			}
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 * return the link and
	 */
	public static function settingsAcceptStripeConnect(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(!get_option('resa_settings_payment_stripe_connect_conditions_validated', 0)){
				$message = '<h3>Activation de Stripe Connect</h3><p>Informations:<br />- Site : '.site_url().'<br />-Nom entreprise : '.get_option('resa_settings_company_name'). '<br />-Téléphone : '.get_option('resa_settings_company_phone'). '<br />-Email / Nom : '.$currentRESAUser->getEmail(). ' / '.$currentRESAUser->getDisplayName().'<br /></p>';
				if(RESA_Mailer::sendMessage('contact@resa-online.fr', 'RESA Stripe Connect', $message)){
					update_option('resa_settings_payment_stripe_connect_conditions_validated', true);
					$response->set_data(array(
						'OAuthLink' => RESA_StripeConnect::getOAuthLink()
					));
				}
				else {
					$response->set_status(201);
					$response->set_data(array('error' => 'not_send_message', 'message' => 'Nous n\'avons pas pu envoyer la confirmation par Email à nos équipes. Veuillez corriger ce problème pour pouvoir activer Stripe Connect avec RESA Online'));
				}
			}
			else {
				$response->set_data(array(
					'OAuthLink' => RESA_StripeConnect::getOAuthLink()
				));
			}
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 * Export file
	 */
	public static function exportFile(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$arrayUploadDir = wp_get_upload_dir();
			$date = new DateTime();
			$filename = 'resaDataSQL_' . $date->format('Y_m_d_H_i_s') .  '.sql';
			$fileSQL = fopen($arrayUploadDir['basedir'].'/'  . $filename, 'w') or die('Unable to open file');
			fwrite($fileSQL, RESA_ExportImport::exportRESAData());
			fclose($fileSQL);
			$json = '{
				"fileUrl":"'.$arrayUploadDir['baseurl'].'/'.$filename.'"
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	/**
	 *
	 */
	public static function importFile(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$files = $request->get_file_params();
			if(isset($files['file'])){
				$imageFileType = strtolower(pathinfo($files['file']['name'], PATHINFO_EXTENSION));
				if($imageFileType == 'sql'){
					$string = file_get_contents($_FILES['file']['tmp_name']);
					RESA_ExportImport::importRESAData($string);
					$response->set_data(array());
				}
				else {
					$response->set_status(401);
					$response->set_data(array('error' => 'file_not_sql', 'message' => 'Not SQL file'));
				}
			}
			else {
				$response->set_status(404);
				$response->set_data(array('error' => 'file_not_found', 'message' => 'File not found'));
			}
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
