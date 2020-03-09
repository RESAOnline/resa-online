<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIUserController
{
	public static function getUserByToken(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response();
		if(!$currentRESAUser->isLoaded()) {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		else {
			$caisseUrl = '';
			$apiCaisseUrl = '';
			if(class_exists('RESA_CaisseOnline')){
				$caisseUrl = RESA_CaisseOnline::getInstance()->getURLCaisse();
				$apiCaisseUrl = RESA_CaisseOnline::getInstance()->getBaseURL();
			}
			$settings = array(
				'version' => RESA_Variables::getVersion(),
				'linkNewUpdate' => (RESA_Variables::haveNewVersion() && user_can($currentRESAUser->getID(), 'update_plugins'))?(admin_url().'update-core.php'):'',
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'backendV2Activated' => get_option('resa_backend_v2_activated', false),
				'wpDebugMode' => defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG,
				'supportURL' => RESA_Variables::getURLSupport($currentRESAUser->getEmail()),
				'staffManagement' => RESA_Variables::isStaffManagementActivated(),
				'equipmentsManagement' => RESA_Variables::isEquipmentsManagementActivated(),
				'groupsManagement' => RESA_Variables::isGroupsManagementActivated(),
				'caisse_online'=>array(
					'installed' => class_exists('RESA_CaisseOnline'),
					'activated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_caisse_online_activated')) == 'true',
					'caisse_url'=> $caisseUrl,
					'api_caisse_url'=> $apiCaisseUrl,
					'license_id'=>get_option('resa_settings_caisse_online_license_id'),
					'site_url' => site_url()
				),
				'stripeConnect' => array(
					'activated' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage() && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::checkConnection():false,
					'stripeConnectId' =>get_option('resa_settings_payment_stripe_connect_id'),
					'apiStripeConnectUrl' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage())?RESA_StripeConnect::getURL():'',
				),
				'swikly_link' => (class_exists('RESA_Swikly') &&  get_option('resa_settings_payment_swikly', false) && get_option('resa_settings_payment_swikly_display_link', false))?RESA_Swikly::getSwiklyLink():'',
				'places' => unserialize(get_option('resa_settings_places')),
				'RESANewsLastViewNumber' => $currentRESAUser->getLastRESANewsId()
			);
			$userJSON = json_decode($currentRESAUser->toJSON());
			if($currentRESAUser->isRESAStaff()){
				$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
				$userJSON->permissions = $associatedMember->getPermissions();
			}
			$result = array(
				'user' => $userJSON,
				'settings' => $settings
			);
			$response->set_data($result);
		}
		return $response;
	}

	/**
	 *
	 */
	public static function update(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$result = RESA_UpdatesManager::updateCustomers();
			$json = '{
				"result":'.RESA_Tools::toJSONBoolean($result).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 *
	 */
	public static function updateUserSettings(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			if(isset($data['filterSettings'])){
				$currentRESAUser->setFilterSettings(json_decode($data['filterSettings']));
				$currentRESAUser->save(false);
				$response->set_data(json_decode($currentRESAUser->toJSON()));
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad parameters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	/**
	 *
	 */
	public static function seeAllRESANews(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			if(isset($data['lastId']) && is_numeric($data['lastId'])){
				$currentRESAUser->setLastRESANewsID($data['lastId']);
				$currentRESAUser->save(false);
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad parameters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
