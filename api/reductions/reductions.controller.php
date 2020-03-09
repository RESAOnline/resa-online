<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIReductionsController
{
	public static function initReductions(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'payment_currency' => get_option('resa_settings_payment_currency'),
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'customer_booking_url' => get_option('resa_settings_customer_booking_url'),
				'vat_list' => unserialize(get_option('resa_settings_vat_list')),
			);
			$allReductions = RESA_Reduction::getAllData();
			$anotherReductions = false;
			$reductions = array();
			foreach($allReductions as $reduction){
				if($reduction->isNeedCouponCode()){
					array_push($reductions, $reduction);
				}
				else {
					$anotherReductions = true;
				}
			}

			$json = '{
				"reductions":'.RESA_Tools::formatJSONArray($reductions).',
				"anotherReductions":'.RESA_Tools::toJSONBoolean($anotherReductions).',
				"services":'.json_encode(RESA_Service::getServicesLite()).',
				"skeletonReduction":'. (new RESA_Reduction())->toJSON() .',
				"skeletonReductionConditions":'. (new RESA_ReductionConditions())->toJSON() .',
				"skeletonReductionCondition":'. (new RESA_ReductionCondition())->toJSON() .',
				"skeletonReductionConditionService":'. (new RESA_ReductionConditionService())->toJSON() .',
				"skeletonReductionApplication":'. (new RESA_ReductionApplication())->toJSON() .',
				"skeletonReductionConditionsApplication":'. (new RESA_ReductionConditionsApplication())->toJSON() .',
				"skeletonReductionConditionApplication":'. (new RESA_ReductionConditionApplication())->toJSON() .',
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

	public static function currentPromoCodes(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$response->set_data(RESA_Reduction::getAllPromoCodes());
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}



	public static function saveReductions(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['reductions'])){
				$reductionsInPost = json_decode($data['reductions']);
				$allReductions = RESA_Reduction::getAllData();
				$reductions = array();
				foreach($allReductions as $reduction){
					if($reduction->isNeedCouponCode()){
						array_push($reductions, $reduction);
					}
				}
				$idReductions = array();
				for($i = 0; $i < count($reductionsInPost); $i++) {
					if(isset($reductionsInPost[$i]->isUpdated) && $reductionsInPost[$i]->isUpdated){
						$reduction = new RESA_Reduction();
						$reduction->fromJSON($reductionsInPost[$i]);
						$oldReductionSaved = false;
						$oldId = $reduction->getId();
						if(!$reduction->isNew() &&
							(RESA_BookingReduction::numberOfReductionsUsed($reduction->getId()) > 0 ||
							RESA_AppointmentReduction::numberOfReductionsUsed($reduction->getId()) > 0)){
							$oldReductionSaved = true;
							$oldReduction = new RESA_Reduction();
							$oldReduction->loadById($reduction->getId());
							if($reduction->needCreateNewVersion($oldReduction)){
								$oldReduction->setOldReduction(true);
								$oldReduction->save();
								array_push($idReductions, $oldReduction->getId());
								$reduction->setNew();
								Logger::DEBUG('New version of reduction');
								$reduction->addLinkOldReduction($oldReduction->getId());
							}
						}
						$reduction->save();
						if(!$reduction->isNew())
							array_push($idReductions, $reduction->getId());
					}
					else array_push($idReductions, $reductionsInPost[$i]->id);
				}

				for($i = 0; $i < count($reductions); $i++) {
					if(!in_array($reductions[$i]->getId(), $idReductions)){
						$reductions[$i]->deleteMe();
					}
				}
			}

			$allReductions = RESA_Reduction::getAllData();
			$reductions = array();
			foreach($allReductions as $reduction){
				if($reduction->isNeedCouponCode()){
					array_push($reductions, $reduction);
				}
			}
			$response->set_data(json_decode(RESA_Tools::formatJSONArray($reductions)));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

}
