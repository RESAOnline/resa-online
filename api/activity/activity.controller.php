<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIActivityController
{
	public static function getActivity(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$activity = new RESA_Service();
			if($request['id'] != -1){
				$activity->loadByIdLastVersion($request['id']);
			}
			$settings = array(
				'staffManagementActivated' => RESA_Variables::isStaffManagementActivated(),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'equipmentsManagementActivated' => RESA_Variables::isEquipmentsManagementActivated(),
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'timeslots_mentions' => unserialize(get_option('resa_settings_timeslots_mentions')),
				'categories_services' => unserialize(get_option('resa_settings_form_category_services')),
				'advancePaymentActivated' => RESA_Tools::toJSONBoolean(get_option('resa_settings_payment_ask_advance_payment')) == 'true',
				'advancePaymentTypeAccountsActivated' => unserialize(get_option('resa_settings_payment_ask_advance_payment_type_accounts')),
				'typesAccounts' => unserialize(get_option('resa_settings_types_accounts')),
				'form_participants_parameters' => unserialize(get_option('resa_settings_form_participants_parameters')),
				'states_parameters' => unserialize(get_option('resa_settings_states_parameters')),
				'vatList' => unserialize(get_option('resa_settings_vat_list')),
				'calendars' => unserialize(get_option('resa_settings_calendars')),
				'currency'=>get_option('resa_settings_payment_currency')
			);
			$members = RESA_Member::getAllDataWithIdService($activity->getId());
			$equipments = RESA_Equipment::getAllData(array(), 'position');
			$json = '{
				"activity":'.$activity->toJSON().',
				"members":'.RESA_Tools::formatJSONArray($members).',
				"equipments":'.RESA_Tools::formatJSONArray($equipments).',
				"skeletonServiceAvailability":'. (new RESA_ServiceAvailability())->toJSON().',
				"skeletonServicePrice":'. (new RESA_ServicePrice())->toJSON() .',
				"skeletonServiceTimeslot":'. (new RESA_ServiceTimeslot())->toJSON() .',
				"skeletonServiceMemberPriority":'. (new RESA_ServiceMemberPriority())->toJSON() .',
				"settings":'.json_encode($settings).',
				"activities":'.json_encode(RESA_Service::getAllServicesAndThisPrices()).',
				"slugs":'.json_encode(RESA_Service::getAllSlugsAndId()).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			$response->set_status(401);
		}
		return $response;
	}

	public static function saveActivity(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$activity = new RESA_Service();
			$activityJSON = json_decode($data['activity']);
			$activity->fromJSON($activityJSON);
			if(!$activity->isNew() && $activity->isExclusive()){
				$memberLinkServices = RESA_MemberLinkService::getAllData(array('idService' => $activity->getId()));
				foreach($memberLinkServices as $memberLinkService){
					$memberLinkService->deleteMe();
				}
			}
			if(isset($activityJSON->servicePrices)){
				foreach($activityJSON->servicePrices as $servicePriceJSON){
					if(isset($servicePriceJSON->oldSlug) && $servicePriceJSON->oldSlug != $servicePriceJSON->slug){
						RESA_ServicePrice::updateSlugForAllServicePrices($servicePriceJSON->oldSlug, $servicePriceJSON->slug);
					}
				}
			}
			if(!self::needCreateNewVersion($activity)){
				$activity->save();
			}
			//Color
			if(isset($activityJSON->colorChange) && $activityJSON->colorChange && !empty($activity->getLinkOldServices())){
				RESA_Service::updateColorForOldServices($activity);
			}
			if(isset($activityJSON->oldSlug) && !empty($activityJSON->oldSlug) && $activityJSON->oldSlug != $activityJSON->slug){
				RESA_Service::updateSlugForOldServices($activity);
				RESA_Form::updateSlugsServices($activityJSON->oldSlug, $activityJSON->slug);
			}
			if(class_exists('RESA_CaisseOnline')){
				RESA_CaisseOnline::getInstance()->updateRESAService($activity);
			}

			$json = '{
				"activity":'.$activity->toJSON().',
				"activities":'.json_encode(RESA_Service::getAllServicesAndThisPrices()).',
				"slugs":'.json_encode(RESA_Service::getAllSlugsAndId()).'
			}';
			$response->set_data(json_decode($json));
		}
		else {
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			$response->set_status(401);
		}
		return $response;
	}

	/**
	 * needCreateNewVersion
	 */
	private static function needCreateNewVersion($activity){
		$newVersion = false;
		if(!$activity->isNew() &&
		RESA_Appointment::countAllData(array('idService' => $activity->getId())) > 0){
			$oldService = new RESA_Service();
			$oldService->loadById($activity->getId());
			if($activity->needCreateNewVersion($oldService)){
				$newVersion = true;
				Logger::INFO('Create a new version of service : '.$activity->getId());
				$oldService->setOldService(true);
				$oldService->save();

				$activity->setNew();
				$activity->addLinkOldService($oldService->getId());
				$activity->save();

				$tabsActivities = unserialize(get_option('resa_planning_tabs_activities', serialize(array())));
				for($i = 0; $i < count($tabsActivities); $i++){
					$tabsActivity = $tabsActivities[$i];
					for($j = 0; $j < count($tabsActivity->activities); $j++){
						if($tabsActivity->activities[$j] == $oldService->getId()){
							$tabsActivity->activities[$j] = $activity->getId() . '';
						}
					}
				}
				update_option('resa_planning_tabs_activities', serialize($tabsActivities));

				$allMembers = RESA_Member::getAllData();
				foreach($allMembers as $member){
					$member->updateIdService($oldService->getId(), $activity->getId());
					$member->save();
				}
				$allReductions = RESA_Reduction::getAllData();
				foreach($allReductions as $reduction){
					$reduction->updateService($oldService, $activity);
					$reduction->save();
				}
				RESA_ServiceConstraint::updateIdServices($oldService->getId(), $activity->getId());

				$allStaticGroups = RESA_StaticGroup::getAllData();
				foreach($allStaticGroups as $staticGroups){
					if($staticGroups->updateService($oldService, $activity)){
						$staticGroups->save();
					}
				}
				RESA_Group::updateIdServices($oldService->getId(), $activity->getId());
				$activity->updateService($oldService, $activity);
				$activity->save();
			}
		}
		return $newVersion;
	}

	/**
	 *
	 */
	public static function importActivityPrice(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$idActivityPrice = json_decode($data['idActivityPrice']);
			$activityPrice = new RESA_ServicePrice();
			$activityPrice->loadById($idActivityPrice);
			$response->set_data(json_decode($activityPrice->toJSON()));
		}
		else {
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			$response->set_status(401);
		}
		return $response;
	}

	/**
	 *
	 */
	public static function activityHasChanged(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$idService = $data['idService'];
			$modificationDate = $data['modificationDate'];
			$serviceHasChanged = RESA_Service::serviceHasChanged($idService, $modificationDate);
			$response->set_data(array('result' =>$serviceHasChanged));
		}
		else {
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			$response->set_status(401);
		}
		return $response;
	}

	/**
	 *
	 */
	public static function justOneActivity(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$activity = new RESA_Service();
			if($request['id'] != -1){
				$activity->loadByIdLastVersion($request['id']);
			}
			$response->set_data(json_decode($activity->toJSON()));
		}
		else {
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
			$response->set_status(401);
		}
		return $response;
	}


}
