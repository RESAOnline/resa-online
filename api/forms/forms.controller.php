<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIFormsController
{
	public static function getAllPages(){
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
		$results = array('forms' => array(), 'accounts' => array(), 'askAccounts' => array());
		foreach ( $pages as $page) {
			if(strstr($page->post_content, '[RESA_form form') !== false){
				array_push($results['forms'], array(
					'title' => $page->post_title,
					'content' => $page->post_content,
					'url' => get_page_link($page->ID)
				));
			}
			else if(strstr($page->post_content, '[RESA_account]') !== false){
				array_push($results['accounts'], array(
					'title' => $page->post_title,
					'content' => $page->post_content,
					'url' => get_page_link($page->ID)
				));
			}
			else if(strstr($page->post_content, '[RESA_askAccount') !== false){
				array_push($results['askAccounts'], array(
					'title' => $page->post_title,
					'content' => $page->post_content,
					'url' => get_page_link($page->ID)
				));
			}
		}

		return $results;
	}

	public static function getForms(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$forms = RESA_Form::getAllData();
			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'customer_account_url' => get_option('resa_settings_customer_account_url'),
				'custom_css' => get_option('resa_settings_global_custom_css')
			);

			$allPages = self::getAllPages();
			$json = '{
				"allFormPages":'.json_encode($allPages['forms']).',
				"allCSS":'.json_encode(RESA_APISettingsController::getCSSFiles()).',
				"forms":'.RESA_Tools::formatJSONArray($forms).',
				"services":'.json_encode(RESA_Service::getAllServicesAndThisPrices()).',
				"skeletonForm":'. (new RESA_Form())->toJSON() .',
				"settings":'.json_encode($settings).',
				"promoCodes":'.json_encode(RESA_Reduction::getAllPromoCodes()).',
				"allAskAccountsPage":'.json_encode($allPages['askAccounts']).',
				"allAccountsPage":'.json_encode($allPages['accounts']).'
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function saveForms(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['customer_account_url'])){
				update_option('resa_settings_customer_account_url', esc_html($data['customer_account_url']));
			}
			if(isset($data['forms'])){
				$formsInPost = json_decode($data['forms']);
				$data = RESA_Form::getAllData();
				$idForms = array();
				for($i = 0; $i < count($formsInPost); $i++) {
					if(isset($formsInPost[$i]->isUpdated) && $formsInPost[$i]->isUpdated){
						$form = new RESA_Form();
						$form->fromJSON($formsInPost[$i]);
						$form->generateCSS();
						$form->save();
						if(!$form->isNew()){
							array_push($idForms, $form->getId());
						}
					}
					else {
						array_push($idForms, $formsInPost[$i]->id);
					}
				}

				for($i = 0; $i < count($data); $i++) {
					if(!in_array($data[$i]->getId(), $idForms)){
						$data[$i]->deleteMe();
					}
				}
			}
			$response->set_data(json_decode(RESA_Tools::formatJSONArray(RESA_Form::getAllData())));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function pageForm(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters() && isset($data['idForm']) && isset($data['name'])){

			$idForm = $data['idForm'];
			$title = $data['name'];
			$wp_error = null;
			$post_id = wp_insert_post(array(
				'post_title'    => $title,
    		'post_content'  => '[RESA_form form="form'.$idForm.'"]',
    		'post_status'   => 'publish',
				'post_type'			=> 'page'
			), $wp_error);

			$json = '{
				"allFormPages":'.json_encode(self::getAllPages()['forms']).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function pageAskAccount(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters() && isset($data['idTypeAccount']) && isset($data['name'])){
			$idTypeAccount = $data['idTypeAccount'];
			$title = $data['name'];
			$wp_error = null;
			$post_id = wp_insert_post(array(
				'post_title'    => $title,
    		'post_content'  => '[RESA_askAccount typeAccount="'.$idTypeAccount.'"]',
    		'post_status'   => 'publish',
				'post_type'			=> 'page'
			), $wp_error);

			$json = '{
				"allAskAccountsPage":'.json_encode(self::getAllPages()['askAccounts']).'
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
