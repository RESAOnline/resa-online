<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_APIAlgorithmsController
{
	private static function isAdmin($currentRESAUser){
		if(!isset($currentRESAUser)) return false;
		return $currentRESAUser->isRESAManager() || $currentRESAUser->isAdministrator();
	}


	public static function getTimeslots(WP_REST_Request $request){
		$currentRESAUser = null;
		if(isset($request['token'])){
			$currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		}
		$response = new WP_REST_Response(array());
		$data = $request->get_json_params();
		$allTimeslots = [];
		$service = new RESA_Service();
		if(isset($data['service']) && isset($data['date']) && isset($data['servicesParameters']) && isset($data['idBooking']) && isset($data['allowInconsistencies']) && isset($data['frontForm']) && isset($data['typeAccount'])){
			$service->fromJSON(json_decode($data['service']));
			$date = stripslashes(sanitize_text_field($data['date']));
			$date = DateTime::createFromFormat('d-m-Y H:i:s' , $date);
			$servicesParameters = json_decode($data['servicesParameters']);
			$idBooking = sanitize_text_field($data['idBooking']);
			$allowInconsistencies = sanitize_text_field($data['allowInconsistencies']);
			$frontForm = sanitize_text_field($data['frontForm']);
			$typeAccount = sanitize_text_field($data['typeAccount']);
			$appointments = array();
			try{
				$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters,  new RESA_Customer(), true, $idBooking, (self::isAdmin($currentRESAUser) && !$frontForm), $typeAccount);
			}
			catch(Exception $e){
				//Logger::DEBUG($e);
			}
			$dateStart = DateTime::createFromFormat('d-m-Y H:i:s', $date->format('d-m-Y').' 00:00:00');
			$dateEnd = DateTime::createFromFormat('d-m-Y H:i:s', $date->format('d-m-Y').' 23:59:59');
			$allTimeslots = RESA_Algorithms::getTimeslots($dateStart, $dateEnd, $service, $appointments, $idBooking, (self::isAdmin($currentRESAUser) && !$frontForm), $typeAccount);
			for($i = 0; $i < count($allTimeslots); $i++){
				$allTimeslots[$i]['members'] = json_decode(RESA_Tools::formatJSONArray($allTimeslots[$i]['members']));
			}
		}
		$response->set_data($allTimeslots);
		return $response;
	}

}
