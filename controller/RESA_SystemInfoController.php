<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_SystemInfoController extends RESA_Controller
{
	public function getSlug()
	{
		return 'resa_systeminfo';
	}

	public function getPageName()
	{
		return __( 'system_info_title', 'resa' );
	}

	public function isSettings()
	{
		return true;
	}

	public function getClassDir()
	{
		return __DIR__;
	}


	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts()
	{
		return array_merge(self::$GLOBAL_SCRIPTS, array(
			'manager/SystemInfoManager',
			'controller/SystemInfoController',
			'directive/fileread'
		));
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		return array_merge(self::$GLOBAL_STYLES, array(
      'design-back'
		));
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{
		$this->renderer('RESA_systemInfo', array('date_format'=> RESA_Tools::wpToJSDateFormat(),
				'time_format'=> RESA_Tools::wpToJSTimeFormat()));
	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods() {
		$this->addAjaxMethod('getStatistics');
		$this->addAjaxMethod('ExtractAllData');
		$this->addAjaxMethod('ImportAllDataAJAX');
		$this->addAjaxMethod('SaveCustomers');
		$this->addAjaxMethod('SaveServices');
		$this->addAjaxMethod('SaveStaticGroups');
		$this->addAjaxMethod('SaveMembers');
		$this->addAjaxMethod('SaveReductions');
		$this->addAjaxMethod('SaveBookings');
		$this->addAjaxMethod('SaveGroups');
		$this->addAjaxMethod('SaveInfoCalendars');
		$this->addAjaxMethod('SaveServiceConstraints');
		$this->addAjaxMethod('SaveOptions');
		$this->addAjaxMethod('CustomersDiagnostic');
		$this->addAjaxMethod('ResolveCustomerConflict');
		$this->addAjaxMethod('SetTokensValidations');
		if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){
			$this->addAjaxMethod('DeleteAllBookings');
			$this->addAjaxMethod('ClearAllData');
			$this->addAjaxMethod('ForceLastUpdateDatabase');
		}
	}

	public function getStatistics(){
		Logger::DEBUG('begin');
		$data = array(
			'nbBookingsOnline' => RESA_Booking::countBookingsOnline(false),
			'nbBookingsBackend' => RESA_Booking::countBookingsOnline(true)
		);
		echo  '{
			"data":'.json_encode($data).'
		}';
		wp_die();
	}

	public function DeleteAllBookings(){
		Logger::INFO('begin');
		if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){
			$allBookings = RESA_Booking::getAllData();
			foreach($allBookings as $booking){
				$booking->deleteMe();
			}
			$allAlerts = RESA_Alert::getAllData(array());
			foreach($allAlerts as $alert){
				$alert->deleteMe();
			}
			$allGroups = RESA_Group::getAllData(array());
			foreach($allGroups as $group){
				$group->deleteMe();
			}
			$allServicesConstraints = RESA_ServiceConstraint::getAllData(array());
			foreach($allServicesConstraints as $entity){
				$entity->deleteMe();
			}

			echo 'OK';
		}
		wp_die();
	}

	public function ClearAllData(){
		Logger::INFO('begin');
		if(defined('WP_DEBUG') && WP_DEBUG && defined('WP_RESA_DEBUG') && WP_RESA_DEBUG){
			$deleteWpRESACustomers = false;
			if(isset($_REQUEST['deleteWpRESACustomers'])) $deleteWpRESACustomers = json_decode(stripslashes(sanitize_text_field($_REQUEST['deleteWpRESACustomers'])));
			$installManager = new RESA_InstallManager();
			$installManager->uninstall();
			$installManager->install();

			if($deleteWpRESACustomers){
				Logger::DEBUG('Delete resa');
				$customers = RESA_Customer::getAllData(array());
				foreach($customers as $customer){
					if($customer->isRESACustomer() && $customer->isWpUser()){
						$customer->deleteMe();
					}
				}
			}
			Logger::INFO('end');
			echo __('clear_all_data_feedback', 'resa');
		}
		wp_die();
	}

  /**
   * Extract all data...
   */
  public function ExtractAllData(){
		$filename = 'RESA_Data.json';
		$extractForNewIntall = false;
		if(isset($_REQUEST['extractForNewIntall'])) $extractForNewIntall = json_decode(stripslashes(sanitize_text_field($_REQUEST['extractForNewIntall'])));
		if($extractForNewIntall) $filename = 'RESA_NewInstall.json';
		$path = plugin_dir_path( __FILE__ ). '/../';
		$fileSQL = fopen($path.$filename, 'w') or die('Unable to open file');

		$parametersServices = array();
		if($extractForNewIntall) $parametersServices = array('oldService' => false);
		$allServices = RESA_Service::getAllDataWithOrderBy($parametersServices, 'id');
		$services = array();
		if($extractForNewIntall){
			foreach($allServices as $service){
				$service->setLinkOldServices('');
				array_push($services, $service);
			}
		}
		else $services = $allServices;
		$staticGroups = RESA_StaticGroup::getAllData(array());
		$members = RESA_Member::getAllData(array());
		$parametersReductions = array();
		if($extractForNewIntall) $parametersReductions = array('oldReduction' => false);
		$reductions = RESA_Reduction::getAllData($parametersReductions);

		$bookings = $groups = $customers = $infoCalendars = $serviceConstraints = [];
		if(!$extractForNewIntall){
			$bookings = RESA_Booking::getAllData(array());
			$groups = RESA_Group::getAllData(array());
			$customers = RESA_Customer::getAllData(array());
			$infoCalendars = RESA_InfoCalendar::getAllData(array());
			$serviceConstraints = RESA_ServiceConstraint::getAllData(array());
		}

		$wpRESAOptions = array();
		$resaInstall = new RESA_InstallManager();
		$optionNames = $resaInstall->getOptionNames();
		foreach($optionNames as $option){
			$wpRESAOptions[$option] = get_option($option);
		}

		$data = array('services' => json_decode(RESA_Tools::formatJSONArray($services)),
			'staticGroups' => json_decode(RESA_Tools::formatJSONArray($staticGroups)),
			'members' => json_decode(RESA_Tools::formatJSONArray($members)),
			'reductions' => json_decode(RESA_Tools::formatJSONArray($reductions)),
			'bookings' => json_decode(RESA_Tools::formatJSONArray($bookings)),
			'groups' => json_decode(RESA_Tools::formatJSONArray($groups)),
			'customers' =>json_decode(RESA_Tools::formatJSONArrayCustomer($customers)),
			'infoCalendars' => json_decode(RESA_Tools::formatJSONArray($infoCalendars)),
			'serviceConstraints' => json_decode(RESA_Tools::formatJSONArray($serviceConstraints)),
			'options' => $wpRESAOptions);

		fwrite($fileSQL, json_encode($data));
		fclose($fileSQL);
		echo plugin_dir_url(__FILE__ ). '/../../'.$filename;
		wp_die();
	}


	public function ImportAllDataAJAX(){
		if(isset($_POST['loadOptions']) && isset($_POST['loadCustomers'])){
			$loadOptions =	sanitize_text_field($_POST['loadOptions']) == 'true';
			$loadCustomers =	sanitize_text_field($_POST['loadCustomers']) == 'true';
			if(isset($_FILES['file'])){
				$string = file_get_contents($_FILES['file']['tmp_name']);
				$data = json_decode($string);

				$allServices = array();
				$allStaticGroups = array();
				$allMembers = array();
				$allReductions = array();
				$allBookings = array();
				$allGroups = array();
				$allCustomers = array();
				$allInfoCalendars = array();
				$allServiceContraints = array();
				$options = new stdClass();

				if(RESA_Tools::canUseForeach($data->bookings)){
					foreach($data->bookings as $bookingJSON){
						$booking = new RESA_Booking();
						$booking->fromJSON($bookingJSON);
						array_push($allBookings, $booking);
					}
				}

				if(RESA_Tools::canUseForeach($data->staticGroups)){
					foreach($data->staticGroups as $staticGroupJSON){
						$staticGroups = new RESA_StaticGroup();
						$staticGroups->fromJSON($staticGroupJSON);
						array_push($allStaticGroups, $staticGroups);
					}
				}

				if(RESA_Tools::canUseForeach($data->groups)){
					foreach($data->groups as $groupJSON){
						$group = new RESA_Group();
						$group->fromJSON($groupJSON);
						array_push($allGroups, $group);
					}
				}


				foreach($data->members as $memberJSON){
					$member = new RESA_Member();
					$member->fromJSON($memberJSON);
					array_push($allMembers, $member);
				}

				foreach($data->reductions as $reductionJSON){
					$reduction = new RESA_Reduction();
					$reduction->fromJSON($reductionJSON);
					array_push($allReductions, $reduction);
				}

				if(isset($data->customers) && $loadCustomers){
					foreach($data->customers as $customerJSON){
						array_push($allCustomers, $customerJSON);
					}
				}

				foreach($data->services as $serviceJSON){
					$service = new RESA_Service();
					$service->fromJSON($serviceJSON);
					array_push($allServices, $service);
				}

				if(isset($data->infoCalendars) && RESA_Tools::canUseForeach($data->infoCalendars)){
					foreach($data->infoCalendars as $infoCalendarJSON){
						$infoCalendar = new RESA_InfoCalendar();
						$infoCalendar->fromJSON($infoCalendarJSON);
						array_push($allInfoCalendars, $infoCalendar);
					}
				}

				if(isset($data->serviceConstraints) && RESA_Tools::canUseForeach($data->serviceConstraints)){
					foreach($data->serviceConstraints as $serviceConstraintJSON){
						$serviceConstraint = new RESA_ServiceConstraint();
						$serviceConstraint->fromJSON($serviceConstraintJSON);
						array_push($allServiceContraints, $serviceConstraint);
					}
				}

				if(isset($data->options) && $loadOptions){
					$options = $data->options;
				}

				echo '{
					"customers":'.json_encode($allCustomers).',
					"services":'.RESA_Tools::formatJSONArray($allServices).',
					"staticGroups":'.RESA_Tools::formatJSONArray($allStaticGroups).',
					"members":'.RESA_Tools::formatJSONArray($allMembers).',
					"reductions":'.RESA_Tools::formatJSONArray($allReductions).',
					"bookings":'.RESA_Tools::formatJSONArray($allBookings).',
					"groups":'.RESA_Tools::formatJSONArray($allGroups).',
					"infoCalendars":'.RESA_Tools::formatJSONArray($allInfoCalendars).',
					"serviceConstraints":'.RESA_Tools::formatJSONArray($allServiceContraints).',
					"options":'.json_encode($options).'
				}';

			}
			else throw new Exception();
		}
		else throw new Exception();

		wp_die();
	}


	public function SaveCustomers(){
		Logger::INFO('SaveCustomers');
		$customers = json_decode(stripslashes($_REQUEST['customers']));
		$allIdCustomers = array();

		RESA_Customer::initializeAutoIncrement();
		foreach($customers as $customerJSON){
			$customer = new RESA_Customer();
			$oldId = $customerJSON->ID;
			if($customerJSON->isWpUser){
				$customer->loadByLoginPassword($customerJSON->login, $customerJSON->password);
				if(!$customer->isLoaded()){
					$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email);
					if($customer->isLoaded()){
						$customerJSON->ID = $customer->getId();
						$customer->fromJSON($customerJSON);
						$customer->setRole(RESA_Variables::getCustomerRole());
						$customer->save();
						$customer->setPasswordInDB($customerJSON->password);
					}
				}
			}
			else {
				$customer->fromJSON($customerJSON);
				$customer->setLoaded(false);
				$customer->save();
			}
			array_push($allIdCustomers, array($oldId, $customer->getId()));
		}
		RESA_Customer::initializeAutoIncrement();
		echo json_encode($allIdCustomers);
		wp_die();
	}

	public static function sortServicesByNewId($entryA, $entryB){
		return $entryB[1]->getId() - $entryA[1]->getId();
	}

	public function SaveServices(){
		Logger::INFO('SaveServices');
		$allServices = array();
		$services = json_decode(stripslashes($_REQUEST['services']));
		foreach($services as $serviceJSON){
			$oldService = new RESA_Service();
			$oldService->fromJSON($serviceJSON);

			$service = new RESA_Service();
			$service->fromJSON($serviceJSON);
			$service->setNew();
			$service->save();
			$service->loadById($service->getId());
			$service->updateIdService($oldService->getId(), $service->getId());
			$service->updateService($oldService, $service);
			$service->save();

			array_push($allServices, array($oldService, $service));
		}
		usort($allServices, "RESA_SystemInfoController::sortServicesByNewId");
		for($i = 0; $i < count($allServices); $i++){
			$allServices[$i][0] = json_decode($allServices[$i][0]->toJSON());
			$allServices[$i][1] = json_decode($allServices[$i][1]->toJSON());
		}
		echo json_encode($allServices);
		wp_die();
	}

	public function SaveStaticGroups(){
		Logger::INFO('SaveStaticGroups');
		$staticGroups = json_decode(stripslashes($_REQUEST['staticGroups']));
		$mapOldServiceNewService = json_decode(stripslashes($_REQUEST['mapOldServiceNewService']));
		$allIdStaticGroup = array();
		foreach($staticGroups as $staticGroupJSON){
			$staticGroup = new RESA_StaticGroup();
			$staticGroup->fromJSON($staticGroupJSON);
			for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
				$values = $mapOldServiceNewService[$i];
				$oldIdService = new RESA_Service();
				$oldIdService->fromJSON($values[0]);
				$newIdService = new RESA_Service();
				$newIdService->fromJSON($values[1]);
				$staticGroup->updateService($oldIdService, $newIdService);
			}
			$oldId = $staticGroup->getId();
			$staticGroup->setNew();
			$staticGroup->save();
			$newId = $staticGroup->getId();
			array_push($allIdStaticGroup, array($oldId, $newId));
		}
		echo json_encode($allIdStaticGroup);
		wp_die();
	}

	public function SaveMembers(){
		Logger::INFO('SaveMembers');
		$members = json_decode(stripslashes($_REQUEST['members']));
		$mapOldServiceNewService = json_decode(stripslashes($_REQUEST['mapOldServiceNewService']));
		$allIdMembers = array();
		foreach($members as $memberJSON){
			$member = new RESA_Member();
			$member->fromJSON($memberJSON);
			for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
				$values = $mapOldServiceNewService[$i];
				$oldIdService = new RESA_Service();
				$oldIdService->fromJSON($values[0]);
				$newIdService = new RESA_Service();
				$newIdService->fromJSON($values[1]);
				$member->updateIdService($oldIdService->getId(), $newIdService->getId());
			}
			$oldId = $member->getId();
			$member->setNew();
			$member->save();
			$newId = $member->getId();
			array_push($allIdMembers, array($oldId, $newId));
		}
		echo json_encode($allIdMembers);
		wp_die();
	}

	public function SaveReductions(){
		Logger::INFO('SaveReductions');
		$reductions = json_decode(stripslashes($_REQUEST['reductions']));
		$mapOldServiceNewService = json_decode(stripslashes($_REQUEST['mapOldServiceNewService']));
		$allIdReductions = array();
		foreach($reductions as $reductionJSON){
			$reduction = new RESA_Reduction();
			$reduction->fromJSON($reductionJSON);
			for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
				$values = $mapOldServiceNewService[$i];
				$oldService = new RESA_Service();
				$oldService->fromJSON($values[0]);
				$newService = new RESA_Service();
				$newService->fromJSON($values[1]);
				$reduction->updateService($oldService, $newService);
			}
			$oldId = $reduction->getId();
			$reduction->setNew();
			$reduction->save();

			$newId = $reduction->getId();
			$reduction->loadById($reduction->getId());
			$reduction->updateIdReduction($oldId, $newId);
			$reduction->save();
			array_push($allIdReductions, array($oldId, $newId));
		}
		echo json_encode($allIdReductions);
		wp_die();
	}

	public function SaveBookings(){
		Logger::INFO('SaveBookings');
		$bookings = json_decode(stripslashes($_REQUEST['bookings']));
		$mapOldIdClientNewIdClient = json_decode(stripslashes($_REQUEST['mapOldIdClientNewIdClient']));
		$mapOldIdMemberNewIdMember = json_decode(stripslashes($_REQUEST['mapOldIdMemberNewIdMember']));
		$mapOldIdReductionNewIdReduction = json_decode(stripslashes($_REQUEST['mapOldIdReductionNewIdReduction']));
		$mapOldServiceNewService = json_decode(stripslashes($_REQUEST['mapOldServiceNewService']));
		$mapOldIdBookingNewIdBooking = json_decode(stripslashes($_REQUEST['mapOldIdBookingNewIdBooking']));
		foreach($bookings as $bookingJSON){
			$booking = new RESA_Booking();
			$booking->fromJSON($bookingJSON);
			for($i = count($mapOldIdClientNewIdClient) - 1; $i >= 0; $i--){
				$values = $mapOldIdClientNewIdClient[$i];
				if($booking->getIdCustomer() == $values[0] && $values[1] != -1){
					$booking->setIdCustomer($values[1]);
				}
			}
			for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
				$values = $mapOldServiceNewService[$i];
				$oldService = new RESA_Service();
				$oldService->fromJSON($values[0]);
				$newService = new RESA_Service();
				$newService->fromJSON($values[1]);
				$booking->updateService($oldService, $newService);
			}
			for($i = count($mapOldIdMemberNewIdMember) - 1; $i >= 0; $i--){
				$values = $mapOldIdMemberNewIdMember[$i];
				$booking->updateIdMember($values[0], $values[1]);
			}
			for($i = count($mapOldIdReductionNewIdReduction) - 1; $i >= 0; $i--){
				$values = $mapOldIdReductionNewIdReduction[$i];
				$booking->updateIdReduction($values[0], $values[1]);
			}
			$booking->setNew();
			$booking->save(false);
			array_push($mapOldIdBookingNewIdBooking, array($bookingJSON->id, $booking->getId()));
			$booking->loadById($booking->getId());
			for($i = 0; $i < count($mapOldIdBookingNewIdBooking); $i++){
				$values = $mapOldIdBookingNewIdBooking[$i];
				$booking->updateIdBooking($values[0], $values[1]);
			}
			$booking->save();
		}
		echo json_encode($mapOldIdBookingNewIdBooking);
		wp_die();
	}

	public function SaveGroups(){
		Logger::INFO('SaveGroups');
		if(isset($_REQUEST['groups']) && isset($_REQUEST['mapOldServiceNewService'])){
			$groups = json_decode(stripslashes(wp_kses_post($_REQUEST['groups'])));
			$mapOldServiceNewService = json_decode(stripslashes(wp_kses_post($_REQUEST['mapOldServiceNewService'])));
			$allIdGroups = array();
			foreach($groups as $groupJSON){
				$group = new RESA_Group();
				$group->fromJSON($groupJSON);
				for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
					$values = $mapOldServiceNewService[$i];
					$oldIdService = new RESA_Service();
					$oldIdService->fromJSON($values[0]);
					$newIdService = new RESA_Service();
					$newIdService->fromJSON($values[1]);
					$group->updateService($oldIdService, $newIdService);
				}
				$oldId = $group->getId();
				$group->setNew();
				$group->save();
				$newId = $group->getId();
				array_push($allIdGroups, array($oldId, $newId));
			}
			echo json_encode($allIdGroups);
		}
		wp_die();
	}

	public function SaveInfoCalendars(){
		Logger::INFO('SaveInfoCalendars');
		if(isset($_REQUEST['infoCalendars'])){
			$infoCalendars = json_decode(stripslashes(sanitize_text_field($_REQUEST['infoCalendars'])));
			foreach($infoCalendars as $infoCalendarJSON){
				$infoCalendar = new RESA_InfoCalendar();
				$infoCalendar->fromJSON($infoCalendarJSON);
				$infoCalendar->setNew();
				$infoCalendar->save();
			}
		}
		echo '{}';
		wp_die();
	}

	public function SaveServiceConstraints(){
		Logger::INFO('SaveServiceConstraints');
		if(isset($_REQUEST['serviceConstraints']) && isset($_REQUEST['mapOldServiceNewService'])){
			$serviceConstraints = json_decode(stripslashes(wp_kses_post($_REQUEST['serviceConstraints'])));
			$mapOldServiceNewService = json_decode(stripslashes(wp_kses_post($_REQUEST['mapOldServiceNewService'])));
			foreach($serviceConstraints as $serviceConstraintJSON){
				Logger::DEBUG($serviceConstraintJSON->id);
				$serviceConstraint = new RESA_ServiceConstraint();
				$serviceConstraint->fromJSON($serviceConstraintJSON);
				$serviceConstraint->setNew();
				for($i = count($mapOldServiceNewService) - 1; $i >= 0; $i--){
					$values = $mapOldServiceNewService[$i];
					$oldService = new RESA_Service();
					$oldService->fromJSON($values[0]);
					$newService = new RESA_Service();
					$newService->fromJSON($values[1]);
					$serviceConstraint->updateService($oldService, $newService);
				}
				$serviceConstraint->save();
			}
		}
		echo '{}';
		wp_die();
	}

	public function SaveOptions(){
		Logger::INFO('SaveOptions');
		if(isset($_REQUEST['options'])){
			$options = json_decode(stripslashes($_REQUEST['options']));
			foreach($options as $key => $value){
				if($key != 'resa_plugin_version'){
					update_option($key, $value);
				}
			}
		}
		echo 'ok';
		wp_die();
	}

	public function ForceLastUpdateDatabase(){
		Logger::INFO('begin');
		try {
			RESA_UpdatesManager::forceLastUpdateDatabase();
		}catch(Exception $e){
			//Logger::Error($e->getMessage());
			//echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		echo 'ok';
		wp_die();
	}

	public function CustomersDiagnostic(){
		Logger::INFO('begin');
		$result = array();
		$customers = RESA_Customer::getAllData(array());

		foreach($customers as $customer){
			foreach($customers as $anotherCustomer){
				if($customer->isWpUser() &&
					$customer->getId() == $anotherCustomer->getId() &&
					$customer->getEmail() != $anotherCustomer->getEmail() &&
					$customer->getPhone() != $anotherCustomer->getPhone()){
					array_push($result, array($customer, $anotherCustomer));

				}
			}
		}
		for($i = 0; $i < count($result); $i++){
			$result[$i][0] = json_decode($result[$i][0]->toJSON());
			$result[$i][1] = json_decode($result[$i][1]->toJSON());
		}
		echo json_encode($result);
		wp_die();
	}

	/**
	 * To resolve conflits
	 */
	public function ResolveCustomerConflict(){
		Logger::INFO('begin');
		try {
			if(isset($_REQUEST['idCustomer']) && isset($_REQUEST['idsBookings'])){
				$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
				$idsBookings = json_decode(stripslashes(sanitize_text_field($_REQUEST['idsBookings'])));
				$idBookingList = array();
				if(!empty($idsBookings)){
					$idBookingList = explode(',', $idsBookings);
				}
				$customers = RESA_Customer::getAllData(array('ID' => $idCustomer), false, true);
				$customer = NULL;
				foreach($customers as $localCustomer){
					if(!$localCustomer->isWpUser()){
						$customer = $localCustomer;
					}
				}
				if(isset($customer)){
					$bookings = array();
					foreach($idBookingList as $idBooking){
						$booking = new RESA_Booking();
						$booking->loadByIdCreation($idBooking);
						if(!$booking->isLoaded() || $booking->getIdCustomer() != $customer->getId()){
							throw new Exception('ID réservation '.$idBooking.' n\'est pas associé a ce client', 1);
						}
						else {
							array_push($bookings, $booking);
						}
					}
					$customer->deleteMe();
					$customer->setNew();
					$customer->save();
					foreach($bookings as $booking){
						$booking->setIdCustomer($customer->getId());
						$booking->clearModificationDate();
						$booking->save();
				  }
					echo '{"result":"ok"}';
				}
				else throw new Exception("Client non trouvé", 1);
			}
			else throw new Exception("Client non trouvé", 1);
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo '{"result":"error", "message":"'.$e->getMessage().'"}';
		}

		wp_die();
	}

	/**
	 * set tokens validation in function of ask payment
	 */
	public function SetTokensValidations(){
		Logger::INFO('begin');
		try {
			$allAskPayments = RESA_AskPayment::getAllData(array('expired' => false));
			$updated = 0;
			foreach($allAskPayments as $askPayment){
				$booking = new RESA_Booking();
				$booking->loadById($askPayment->getIdBooking());
				$customer = $booking->getCustomer();
				$expirationDate = DateTime::createFromFormat('Y-m-d H:i:s', $askPayment->getExpiredDate());
				if($customer->getTokenValidation() < $expirationDate){
					$updated++;
					$customer->setTokenValidation($expirationDate);
					$customer->save(false);
				}
			}
			echo '{"result":"ok","updated":'.$updated.'}';
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo '{"result":"error", "message":"'.$e->getMessage().'"}';
		}

		wp_die();
	}
}
