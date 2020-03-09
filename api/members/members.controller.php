<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIMembersController
{
	public static function initMembers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			$members = RESA_Member::getAllData(array(), 'position');
			$staffUsers = RESA_Customer::getAllDataStaffUsers();

			$settings = array(
				'languages' => unserialize(get_option('resa_settings_languages')),
				'places' => unserialize(get_option('resa_settings_places')),
				'staff_word_many' => unserialize(get_option('resa_staff_word_many')),
				'staff_word_single' => unserialize(get_option('resa_staff_word_single')),
				'staff_old_appointments_displayed'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_old_appointments_displayed'))=='true',
				'staff_display_customer'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_customer'))=='true',
				'staff_display_total'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_total'))=='true',
				'staff_display_payments'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_payments'))=='true',
				'staff_display_numbers'=> RESA_Tools::toJSONBoolean(get_option('resa_staff_appointment_display_numbers'))=='true',
				'staff_display_bookings_tab' => RESA_Tools::toJSONBoolean(get_option('resa_staff_display_bookings_tab'))=='true',
				'calendars' => unserialize(get_option('resa_settings_calendars')),
				'editUserLink' => admin_url('user-edit.php?user_id=')
			);
			$json = '{
				"members":'.RESA_Tools::formatJSONArray($members).',
				"services":'.json_encode(RESA_Service::getServicesLite()).',
				"skeletonMember":'. (new RESA_Member())->toJSON() .',
				"skeletonMemberAvailability":'. (new RESA_MemberAvailability())->toJSON() .',
				"skeletonMemberLink":'. (new RESA_MemberLink())->toJSON() .',
				"skeletonMemberLinkService":'. (new RESA_MemberLinkService())->toJSON() .',
				"staffUsers":'.RESA_Tools::formatJSONArray($staffUsers).',
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


	public static function saveMembers(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['members'])){
				$membersInPost = json_decode($data['members']);
				$data = RESA_Member::getAllData();
				$idMembers = array();
				for($i = 0; $i < count($membersInPost); $i++) {
					if(isset($membersInPost[$i]->isUpdated) && $membersInPost[$i]->isUpdated){
						$member = new RESA_Member();
						$member->fromJSON($membersInPost[$i]);
						$member->save();
						if($member->getIdCustomerLinked() > -1){
							$staffUser = new RESA_Customer();
							$staffUser->loadByIdWithoutBookings($member->getIdCustomerLinked());
							if($staffUser->isLoaded()){
								$staffUser->setEmail($member->getEmail());
								$staffUser->save();
							}
						}
						if(!$member->isNewMember()){
							array_push($idMembers, $member->getId());
						}
					}
					else {
						array_push($idMembers, $membersInPost[$i]->id);
					}
				}
				for($i = 0; $i < count($data); $i++) {
					if(!in_array($data[$i]->getId(), $idMembers)){
						RESA_MemberConstraint::deleteAllByIdMember($data[$i]->getId());
						$data[$i]->deleteMe();
					}
				}
			}
			$response->set_data(json_decode(RESA_Tools::formatJSONArray(RESA_Member::getAllData(array(), 'position'))));
		}
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function createRESAMember(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$data = $request->get_json_params();
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
			if(isset($data['member'])){
				$memberJSON = json_decode($data['member']);
				$customer = new RESA_Customer();
				$login = $memberJSON->nickname;
				if(!username_exists($login) && !email_exists($memberJSON->email)){
					if(!isset($memberJSON->password) || empty($memberJSON->password)){
						$memberJSON->password = wp_generate_password();
					}
					$customer->create($login, $memberJSON->password, $memberJSON->email, true);
					$customer->setRole(RESA_Variables::getStaffRole());
					$customer->setFirstName($memberJSON->firstname);
					$customer->setLastName($memberJSON->lastname);
					$customer->setDisplayName($memberJSON->lastname.' '.$memberJSON->firstname);
					$customer->save();
					$json = $customer->toJSON();
					$response->set_data(json_decode($json));
				}
				else {
					$customer->loadByEmail($memberJSON->email);
					if($customer->getRole() != RESA_Variables::getStaffRole()){
						$response->set_status(401);
						$response->set_data(array('error' => 'already_create', 'message' => 'Compte déjà créer mais en tant que compte RESA client, veuillez le changer dans l\'interface de gestion des utilisateurs'));
					}
					else {
						$json = $customer->toJSON();
						$response->set_data(json_decode($json));
					}
				}
			}
			else {
				$response->set_status(401);
				$response->set_data(array('error' => 'bad_parameters', 'message' => 'Bad paramters'));
			}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}

	public static function getMembersLite(WP_REST_Request $request){
		$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded()){
			$response->set_data(RESA_Member::getMembersLite());
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
