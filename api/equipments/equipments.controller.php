<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIEquipmentsController
{
	public static function initEquipments(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$equipments = RESA_Equipment::getAllData(array(), 'position');

			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places'))
			);
			$json = '{
				"equipments":'.RESA_Tools::formatJSONArray($equipments).',
				"services":'.json_encode(RESA_Service::getAllServicesAndThisPrices()).',
				"skeletonEquipment":'. (new RESA_Equipment())->toJSON() .',
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


	public static function equipmentsLite(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$equipments = RESA_Equipment::getAllData(array(), 'position');
			$response->set_data(json_decode(RESA_Tools::formatJSONArray($equipments)));
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


	public static function saveEquipments(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['equipments'])){
				$equipmentsInPost = json_decode($data['equipments']);
				$data = RESA_Equipment::getAllData();
				$idEquipments = array();
				for($i = 0; $i < count($equipmentsInPost); $i++) {
					if(isset($equipmentsInPost[$i]->isUpdated) && $equipmentsInPost[$i]->isUpdated){
						$equipment = new RESA_Equipment();
						$equipment->fromJSON($equipmentsInPost[$i]);
						$equipment->save();
						if(!$equipment->isNewEquipment()){
							array_push($idEquipments, $equipment->getId());
						}
					}
					else {
						array_push($idEquipments, $equipmentsInPost[$i]->id);
					}
				}
				for($i = 0; $i < count($data); $i++) {
					if(!in_array($data[$i]->getId(), $idEquipments)){
						$data[$i]->deleteMe();
					}
				}
			}
			$response->set_data(json_decode(RESA_Tools::formatJSONArray(RESA_Equipment::getAllData(array(), 'position'))));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
