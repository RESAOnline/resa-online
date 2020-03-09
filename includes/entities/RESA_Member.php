<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Member extends RESA_EntityDTO
{
	private $id;
	private $position;
	private $lastname;
	private $firstname;
	private $nickname;
	private $email;
	private $places;
	private $presentation;
	private $memberAvailabilities;
	private $memberLinks;
	private $activated;
	private $idCustomerLinked;
	private $permissions;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_staff';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `position` int(11) NOT NULL,
		  `lastname` text NOT NULL,
		  `firstname` text NOT NULL,
		  `nickname` text NOT NULL,
		  `email` text NULL,
		  `places` text NOT NULL,
		  `presentation` text NOT NULL,
		  `activated` tinyint(1) NOT NULL,
			`idCustomerLinked` int(11) NOT NULL,
		  `permissions` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return '';
	}

	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array(), $orderBy = 'id')
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY ' .$orderBy);
		foreach($results as $result){
			$entity = new RESA_Member();
			$entity->fromJSON($result, true);
			$entity->setLoaded(true);
			$entity->setMemberAvailabilities(RESA_MemberAvailability::getAllData(array('idMember'=>$entity->getId())));
			$entity->setMemberLinks(RESA_MemberLink::getAllData(array('idMember'=>$entity->getId())));
			if(array_key_exists('activated', $data)){
				if($entity->isActivable() == $data['activated']){
					array_push($allData, $entity);
				}
			}
			else {
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	/**
	 * Return all member with associate service
	 */
	public static function getAllDataWithIdService($idService)
	{
		global $wpdb;
		$maTable = RESA_MemberAvailability::getTableName();
		$masTable = RESA_MemberAvailabilityService::getTableName();
		$mTable = self::getTableName();

		$results = $wpdb->get_results('
			SELECT '.$mTable.'.id as id FROM '. $mTable . ' INNER JOIN '.$maTable.' ON '. $mTable . '.id = '.$maTable.'.idMember INNER JOIN '.$masTable.' ON '. $maTable . '.id = '.$masTable.'.idMemberAvailability WHERE idService='.$idService.' GROUP BY id');
		$allData = array();
		foreach($results as $result){
			$member = new RESA_Member();
			$member->loadById($result->id);
			if($member->isLoaded()){
				array_push($allData, $member);
			}
		}

		return $allData;
	}

	/**
	 *
	 */
	public static function getMembersLite() {
		global $wpdb;
		$results = $wpdb->get_results('SELECT id, nickname, position, places FROM '. self::getTableName() . ' WHERE activated=1 ORDER BY position');
		$allData = array();
		if(isset($results)){
			foreach($results as $result){
				$result->places = unserialize($result->places);
				array_push($allData, $result);
			}
		}
		return $allData;
	}

	/**
	 * Return simple name
	 */
	public static function getMemberLite($id){
		global $wpdb;
		$result = $wpdb->get_row('SELECT nickname, places FROM ' . self::getTableName() . ' WHERE id='.$id);
		return $result;
	}

	/**
	 * Return simple name
	 */
	public static function getMemberName($id){
		global $wpdb;
		$result = $wpdb->get_var('SELECT nickname FROM ' . self::getTableName() . ' WHERE id='.$id);
		if(isset($result)) return $result;
		return '';
	}

	/**
	 * Return simple name
	 */
	public static function getAssociatedMember($id){
		$associatedMember = new RESA_Member();
		$membersIdCustomerLinked = RESA_Member::getAllData(array('idCustomerLinked' => $id));
		if(count($membersIdCustomerLinked) >= 1 && $membersIdCustomerLinked[0]->isSetPermissions()){
			$associatedMember = $membersIdCustomerLinked[0];
			$associatedMember->applyPermissions($settings);
		}
		return $associatedMember;
	}


	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->position = -1;
		$this->lastname = 'Lastname';
		$this->firstname = 'Firstname';
		$this->nickname = 'Nickname';
		$this->email = '';
		$this->places = array();
		$this->presentation = new stdClass();
		$this->activated = false;
		$this->idCustomerLinked = -1;
		$this->permissions = array();
		$this->memberAvailabilities = array();
		$this->memberLinks = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result, true);
		$this->setMemberAvailabilities(RESA_MemberAvailability::getAllData(array('idMember'=>$this->getId())));
		$this->setMemberLinks(RESA_MemberLink::getAllData(array('idMember'=>$this->getId())));
		$this->setLoaded(true);
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastMember = new RESA_Member();
			$lastMember->loadById($this->id);
			$lastMemberAvailabilities = $lastMember->getMemberAvailabilities();
			$lastMemberLinks = $lastMember->getMemberLinks();

			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'position' => $this->position,
				'lastname' => $this->lastname,
				'firstname' => $this->firstname,
				'nickname' => $this->nickname,
				'email' => $this->email,
				'places' => serialize($this->places),
				'presentation' => serialize($this->presentation),
				'activated' => $this->activated,
				'idCustomerLinked' => $this->idCustomerLinked,
				'permissions' => serialize($this->permissions)
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s'
			),
			array ('%d'));

			$idMemberAvailabilities = array();
			$idMemberLinks = array();
			for($i = 0; $i < count($this->memberAvailabilities); $i++)
			{
				if(!$this->memberAvailabilities[$i]->isNew())
					array_push($idMemberAvailabilities, $this->memberAvailabilities[$i]->getId());
				$this->memberAvailabilities[$i]->save();
			}
			for($i = 0; $i < count($this->memberLinks); $i++)
			{
				if(!$this->memberLinks[$i]->isNew())
					array_push($idMemberLinks, $this->memberLinks[$i]->getId());
				$this->memberLinks[$i]->save();
			}
			//delete
			for($i = 0; $i < count($lastMemberAvailabilities); $i++) {
				if(!in_array($lastMemberAvailabilities[$i]->getId(), $idMemberAvailabilities))
					$lastMemberAvailabilities[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastMemberLinks); $i++) {
				if(!in_array($lastMemberLinks[$i]->getId(), $idMemberLinks))
					$lastMemberLinks[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'position' => $this->position,
				'lastname' => $this->lastname,
				'firstname' => $this->firstname,
				'nickname' => $this->nickname,
				'email' => $this->email,
				'places' => serialize($this->places),
				'presentation' => serialize($this->presentation),
				'activated' => $this->activated,
				'idCustomerLinked' => $this->idCustomerLinked,
				'permissions' => serialize($this->permissions)
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s'
			));
			$idMember = $this->linkWPDB->insert_id;
			$this->id = $idMember;
			for($i = 0; $i < count($this->memberAvailabilities); $i++)
			{
				$this->memberAvailabilities[$i]->setIdMember($idMember);
				$this->memberAvailabilities[$i]->save();
			}
			for($i = 0; $i < count($this->memberLinks); $i++)
			{
				$this->memberLinks[$i]->setIdMember($idMember);
				$this->memberLinks[$i]->save();
			}
			$this->setLoaded(true);
		}

	}

	/**
	 * Save in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded() && $this->isDeletable())
		{
			for($i = 0; $i < count($this->memberAvailabilities); $i++) {
				$this->memberAvailabilities[$i]->deleteMe();
			}
			for($i = 0; $i < count($this->memberLinks); $i++) {
				$this->memberLinks[$i]->deleteMe();
			}
			$this->linkWPDB->delete(self::getTableName(), array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"id":'.$this->id.',
			"position":'.$this->position.',
			"lastname":"'.$this->lastname.'",
			"firstname":"'.$this->firstname.'",
			"nickname":"'.$this->nickname.'",
			"email":"'.$this->email.'",
			"places":'.json_encode($this->places).',
			"presentation":'.json_encode($this->presentation).',
			"activated":'.RESA_Tools::toJSONBoolean($this->activated  && $this->isActivable()).',
			"idCustomerLinked":'.$this->idCustomerLinked.',
			"permissions":'.json_encode($this->permissions).',
			"isDeletable":'.RESA_Tools::toJSONBoolean($this->isDeletable()).',
			"memberAvailabilities":'.RESA_Tools::formatJSONArray($this->memberAvailabilities).',
			"memberLinks":'.RESA_Tools::formatJSONArray($this->memberLinks).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		if(isset($json->position)) $this->position = $json->position;
		$this->lastname = esc_html($json->lastname);
		$this->firstname = esc_html($json->firstname);
		$this->nickname = esc_html($json->nickname);
		$this->email = esc_html($json->email);
		if(isset($json->places)) {
			if(is_array($json->places)){
				$this->places = $json->places;
			}
			else if($json->places!=false) {
				$this->places = unserialize($json->places);
			}
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->presentation)){
			foreach($json->presentation as $key => $presentation){
				$json->presentation->$key = str_replace(array("\n", "\r"), '', nl2br(esc_html($presentation)));
			}
			$this->presentation = $json->presentation;
		}else {
			$this->presentation = unserialize($json->presentation);
		}
		$this->activated = $json->activated;
		if(isset($json->idCustomerLinked)) $this->idCustomerLinked = $json->idCustomerLinked;
		if(isset($json->permissions)) {
			if(is_object($json->permissions)){
				$this->permissions = (array)$json->permissions;
			}
			else if($json->permissions!=false) {
				$this->permissions = unserialize($json->permissions);
			}
		}
		if(isset($json->memberAvailabilities)){
			$memberAvailabilities = array();
			for($i = 0; $i < count($json->memberAvailabilities); $i++)
			{
				$memberAvailability = new RESA_MemberAvailability();
				$memberAvailability->fromJSON($json->memberAvailabilities[$i]);
				array_push($memberAvailabilities, $memberAvailability);
			}
			$this->setMemberAvailabilities($memberAvailabilities);
		}
		if(isset($json->memberLinks)){
			$memberLinks = array();
			for($i = 0; $i < count($json->memberLinks); $i++)
			{
				$memberLink = new RESA_MemberLink();
				$memberLink->fromJSON($json->memberLinks[$i]);
				array_push($memberLinks, $memberLink);
			}
			$this->setMemberLinks($memberLinks);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}


	/**
	 * \fn getAvailabilityForService
	 * \brief return the availability for a date and a service.
	 * \param date the date
	 * \param idService the service
	 * \return a table of RESA_ServiceTimeslot
	 */
	 public function getAvailabilityForService($date, $idService){
		$available = false;
		$allAvailabilities = array();
		foreach($this->memberAvailabilities as $memberAvailability) {
			$dates = $memberAvailability->getArrayDates();
			$stringDate = $date->format('Y-m-d');
			if(in_array($stringDate, $dates)) {
				$localAvailabilities = array();
				$index = -1;
				for($i = 0; $i < count($allAvailabilities); $i++){
					$availabilities = $allAvailabilities[$i];
					$startTime = RESA_Algorithms::timeToDate($availabilities[0]);
					$endTime = RESA_Algorithms::timeToDate($availabilities[1]);
					if(RESA_Algorithms::timeToDate($memberAvailability->getStartTime()) == $startTime &&
						RESA_Algorithms::timeToDate($memberAvailability->getEndTime()) == $endTime){
						$index = $i;
					}
				}
				if($index == -1){
					array_push($localAvailabilities, $memberAvailability->getStartTime(), $memberAvailability->getEndTime());
				}
				else $localAvailabilities = $allAvailabilities[$index];
				$idServiceTocapacity = array();
				if(count($localAvailabilities) > 2)
					$idServiceTocapacity = $localAvailabilities[2];

				for($memberIndexService = 0; $memberIndexService < count($memberAvailability->getMemberAvailabilityServices());  $memberIndexService++){
					$memberAvailabilityService = $memberAvailability->getMemberAvailabilityServices()[$memberIndexService];
					$service = new RESA_Service();
					$service->loadById($memberAvailabilityService->getIdService());
					if($service->isSameService($idService)){
						$idServiceTocapacity[$idService] = $memberAvailabilityService->getCapacity();
					}
					else $idServiceTocapacity[$memberAvailabilityService->getIdService()] = $memberAvailabilityService->getCapacity();
				}
				if(count($localAvailabilities) > 2) {
					$localAvailabilities[2] = $idServiceTocapacity;
				}
				else array_push($localAvailabilities, $idServiceTocapacity);
				if($index == -1){
					array_push($allAvailabilities, $localAvailabilities);
				}else {
					$allAvailabilities[$index] = $localAvailabilities;
				}

			}
		}
		return $allAvailabilities;
	}

	/**
	 * \fn getAvailabilityForService
	 * \brief return the availability for a date and a service.
	 * \param date the date
	 * \param idService the service
	 * \return a table of RESA_ServiceTimeslot
	 */
	 public function isAvailable($date){
		$available = false;
		$allAvailabilities = array();
		foreach($this->memberAvailabilities as $memberAvailability) {
			$dates = $memberAvailability->getArrayDates();
			$stringDate = $date->format('Y-m-d');
			return in_array($stringDate, $dates);
		}
		return $allAvailabilities;
	}


	/**
	 * return true if the id service is associated to one (or more) availability(ies)
	 */
	public function isAssociatedToService($service) {
		foreach($this->memberAvailabilities as $memberAvailability){
			$newMemberAvailabilityServices = [];
			foreach($memberAvailability->getMemberAvailabilityServices() as $memberAvailabilityServices){
				$localService = new RESA_Service();
				$localService->loadById($memberAvailabilityServices->getIdService());
				if($localService->isSameService($service->getId())){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * return the associated member link if exist null them
	 */
	public function getMemberLinkByService($idService){
		foreach($this->memberLinks as $memberLink){
			if($memberLink->isContainService($idService)){
				return $memberLink;
			}
		}
		return null;
	}

	/**
	 * Return the capacity associated to a service
	 */
	public function getCapacityService($idService){

	}


	/**
	 * Replace all oldId by the newId
	 */
	public function updateIdService($oldId, $newId){
		foreach($this->memberAvailabilities as $memberAvailability){
			$newMemberAvailabilityServices = [];
			foreach($memberAvailability->getMemberAvailabilityServices() as $memberAvailabilityServices){
				if($memberAvailabilityServices->getIdService() == $oldId){
					$oldCapacity = $memberAvailabilityServices->getCapacity();
					$memberAvailabilityServices = new RESA_MemberAvailabilityService();
					$memberAvailabilityServices->setIdMemberAvailability($memberAvailability->getId());
					$memberAvailabilityServices->setIdService($newId);
					$memberAvailabilityServices->setCapacity($oldCapacity);
				}
				array_push($newMemberAvailabilityServices, $memberAvailabilityServices);
			}
			$memberAvailability->setMemberAvailabilityServices($newMemberAvailabilityServices);
		}
		foreach($this->memberLinks as $memberLink){
			$newMemberLinkServices = [];
			foreach($memberLink->getMemberLinkServices() as $memberLinkService){
				if($memberLinkService->getIdService() == $oldId){
					$memberLinkService = new RESA_MemberLinkService();
					$memberLinkService->setMemberLink($memberLink->getId());
					$memberLinkService->setIdService($newId);
				}
				array_push($newMemberLinkServices, $memberLinkService);
			}
			$memberLink->setMemberLinkServices($newMemberLinkServices);
		}
	}

	/**
	 * return true if is deletable
	 */
	public function isDeletable(){
		return RESA_AppointmentMember::countAppointmentMemberWithIdMember($this->id) <= 0;
	}

	/**
	 * return true if the service is activable
	 * if there are some disponibilities or timeslots (or price)
	 */
	public function isActivable(){
		$activable = true;
		$haveOneScheduleAndTimeslot = false;
		foreach($this->memberAvailabilities as $memberAvailability){
			$haveOneScheduleAndTimeslot = $haveOneScheduleAndTimeslot ||
				count($memberAvailability->getArrayDates()) > 0;
		}
		return $activable;
	}


	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->memberAvailabilities as $memberAvailability){
			$memberAvailability->setNew();
		}
		foreach($this->memberLinks as $memberLink){
			$memberLink->setNew();
		}
	}

	public function isNewMember(){ return $this->id==-1; }
	public function getId(){ return $this->id; }
	public function getPosition(){ return $this->position; }
	public function setPosition($position){ $this->position = $position; }
	public function getLastName(){ return $this->lastname; }
	public function getFirstName(){ return $this->firstname; }
	public function getNickname(){ return $this->nickname; }
	public function getPlaces(){ return $this->places; }
	public function setPlaces($places){ $this->places = $places; }
	public function addPlace($place){ array_push($this->places,$place); }
	public function isInPlaces($idPlaces){
		foreach($idPlaces as $idPlace){
			if($this->isInPlace($idPlace)){
				return true;
			}
		}
		return false;
	}
	public function isInPlace($idPlace){ return in_array($idPlace, $this->places); }
	public function isNoPlace(){ return count($this->places) == 0; }
	public function getEmail(){ return $this->email; }
	public function setMemberAvailabilities($memberAvailabilities){ $this->memberAvailabilities = $memberAvailabilities; }
	public function getMemberAvailabilities(){ return $this->memberAvailabilities; }
	public function setMemberLinks($memberLinks){ $this->memberLinks = $memberLinks; }
	public function getMemberLinks(){ return $this->memberLinks; }
	public function setActivated($activated){ $this->activated = $activated; }
	public function isActivated(){ return $this->activated; }
	public function setIdCustomerLinked($idCustomerLinked){ $this->idCustomerLinked = $idCustomerLinked;}
	public function getIdCustomerLinked(){ return $this->idCustomerLinked; }
	public function setPermissions($permissions){ $this->permissions = $permissions;}
	public function getPermissions(){ return $this->permissions; }
	public function isSetPermissions(){ return isset($this->permissions['only_appointments']); }
	public function getPermissionOnlyAppointments(){ return isset($this->permissions['only_appointments'])?$this->permissions['only_appointments']:1; }
	public function getPermissionOldAppointments(){ return isset($this->permissions['old_appointments'])?$this->permissions['old_appointments']:0; }
	public function getPermissionDisplayCustomer(){ return isset($this->permissions['display_customer'])?$this->permissions['display_customer']:1; }
	public function getPermissionDisplayTotal(){ return isset($this->permissions['display_total'])?$this->permissions['display_total']:0; }
	public function getPermissionDisplayPayments(){ return isset($this->permissions['display_payments'])?$this->permissions['display_payments']:0; }
	public function getPermissionDisplayNumbers(){ return isset($this->permissions['display_numbers'])?$this->permissions['display_numbers']:1; }
	public function getPermissionDisplayBookingsTab(){ return isset($this->permissions['display_bookings_tab'])?$this->permissions['display_bookings_tab']:0; }

	public function applyPermissions(&$settings){
		$settings['staff_only_appointments_displayed'] = $this->getPermissionOnlyAppointments() == 1;
		$settings['staff_old_appointments_displayed'] = $this->getPermissionOldAppointments() == 1;
		$settings['staff_display_customer'] = $this->getPermissionDisplayCustomer() == 1;
		$settings['staff_display_total'] = $this->getPermissionDisplayTotal() == 1;
		$settings['staff_display_payments']  = $this->getPermissionDisplayPayments() == 1;
		$settings['staff_display_numbers']  = $this->getPermissionDisplayNumbers() == 1;
		$settings['staff_display_bookings_tab']  = $this->getPermissionDisplayBookingsTab() == 1;
	}

}
