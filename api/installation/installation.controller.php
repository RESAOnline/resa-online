<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIInstallationController
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
				'url' => get_page_link($page->ID)
			));
		}
		return $results;
	}

	public static function initialize(WP_REST_Request $request){
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
				'staff_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_staff_management_actived'))=='true',
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'equipments_management_actived' => RESA_Tools::toJSONBoolean(get_option('resa_equipments_management_actived'))=='true',
				'on_the_spot'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_on_the_spot')) == 'true',
				'later'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_later')) == 'true',
				'transfer'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_transfer')) == 'true',
				'transfer_text'=>unserialize(get_option('resa_settings_payment_transfer_text')),
				'cheque'=>RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_cheque')) == 'true',
				'cheque_text'=>unserialize(get_option('resa_settings_payment_cheque_text')),
				'resa_first_parameter_done' => RESA_Tools::toJSONBoolean(get_option('resa_first_parameter_done'))=='true',
			);
			$json = '{
				"allPages":'.json_encode(self::getPages()).',
				"allLanguages":'.json_encode(RESA_Variables::getLanguages()).',
				"settings":'.json_encode($settings).'
			}';

			$response->set_data(json_decode($json));
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
			$settings = json_decode($data['settings']);
			update_option('resa_settings_languages', serialize($settings->languages));
			update_option('resa_settings_company_name', esc_html($settings->company_name));
			update_option('resa_settings_company_logo', esc_html($settings->company_logo));
			update_option('resa_settings_company_address', esc_html($settings->company_address));
			update_option('resa_settings_company_phone', esc_html($settings->company_phone));
			update_option('resa_settings_company_type', esc_html($settings->company_type));
			update_option('resa_settings_company_siret', esc_html($settings->company_siret));
			update_option('resa_staff_management_actived', esc_html($settings->staff_management_actived));
			update_option('resa_staff_word_many', serialize($settings->staff_word_many));
			update_option('resa_staff_word_single', serialize($settings->staff_word_single));
			update_option('resa_equipments_management_actived', esc_html($settings->equipments_management_actived));
			update_option('resa_settings_payment_on_the_spot', esc_html($settings->on_the_spot));
			update_option('resa_settings_payment_later', esc_html($settings->later));
			update_option('resa_settings_payment_transfer', esc_html($settings->transfer));
			update_option('resa_settings_payment_transfer_text', serialize($settings->transfer_text));
			update_option('resa_settings_payment_cheque', esc_html($settings->cheque));
			update_option('resa_settings_payment_cheque_text', serialize($settings->cheque_text));
			update_option('resa_first_parameter_done', 1);

			$settings = array(
				'resa_first_parameter_done' => RESA_Tools::toJSONBoolean(get_option('resa_first_parameter_done'))=='true'
			);
			$data = array(
				'settings' => $settings,
				'staffManagement' => RESA_Variables::isStaffManagementActivated(),
				'equipmentsManagement' => RESA_Variables::isEquipmentsManagementActivated()
			);
			$response->set_data(json_decode(json_encode($data)));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function pages(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$page = array('title' => '', 'content' => '', 'url' => '');
			if(get_option('resa_settings_customer_account_url', '') == ''){
				$wp_error = null;
				$page['title'] = __('customer_account_page_name', 'resa');
				$page['content'] = '[RESA_account]';
				$post_id = wp_insert_post(array(
					'post_title'    => $page['title'],
	    		'post_content'  => $page['content'],
	    		'post_status'   => 'publish',
					'post_type'			=> 'page'
				), $wp_error);
				if(! is_wp_error( $wp_error ) ) {
					$page['url'] = get_permalink($post_id);
					update_option('resa_settings_customer_account_url', $page['url']);
				}
			}
			if(get_option('resa_settings_customer_booking_url', '') == ''){
				$wp_error = null;
				$page['title'] = __('customer_form_page_name', 'resa');
				$page['content'] = '[RESA_form form="form1"]';
				$post_id = wp_insert_post(array(
					'post_title'    => $page['title'],
	    		'post_content'  => $page['content'],
	    		'post_status'   => 'publish',
					'post_type'			=> 'page'
				), $wp_error);
				if(! is_wp_error( $wp_error ) ) {
					$page['url'] = get_permalink($post_id);
					update_option('resa_settings_customer_booking_url', $page['url']);
				}
			}
			$settings = array(
				'page' => $page,
				'customer_account_url' => get_option('resa_settings_customer_account_url'),
				'customer_booking_url' => get_option('resa_settings_customer_booking_url')
			);

			$json = '{
				"settings":'.json_encode($settings).'
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
