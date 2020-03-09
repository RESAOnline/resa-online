<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIActivitiesController
{
	public static function initActivities(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$activities = RESA_Service::getServicesLite();
			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'categories_services' => unserialize(get_option('resa_settings_form_category_services'))
			);
			$json = '{
				"activities":'.json_encode($activities).',
				"settings":'.json_encode($settings).',
				"forms":'.json_encode(RESA_Form::getServicesForms()).'
			}';

			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function saveActivities(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['categories'])) update_option('resa_settings_form_category_services', serialize($data['categories']));
			if(isset($data['places'])) update_option('resa_settings_places', serialize($data['places']));
			if(isset($data['activities'])){
				$services = RESA_Service::getServicesLite();
				$activities = json_decode($data['activities']);
				$idServices = array();
				for($i = 0; $i < count($activities); $i++) {
					$activity = $activities[$i];
					array_push($idServices, $activity->id);
					if(isset($activity->isUpdated) && $activity->isUpdated){
						$service = new RESA_Service();
						$service->loadById($activity->id);
						if($service->isLoaded()){
							$service->setPosition($activity->position);
							$service->setActivated($activity->activated);
							$service->save();
						}
					}
				}
				foreach ($services as $serviceLite) {
					if(!in_array($serviceLite->id, $idServices)){
						$service = new RESA_Service();
						$service->loadById($serviceLite->id);
						if($service->isLoaded()){
							$service->deleteMe();
						}
					}
				}
			}
			$json = '{}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
