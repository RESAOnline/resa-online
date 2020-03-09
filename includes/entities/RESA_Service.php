<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Service extends RESA_EntityDTO
{
	private $id;
	private $position;
	private $image;
	private $modificationDate;
	private $name;
	private $slug;
	private $category;
	private $places;
	private $presentation;
	private $color;
	private $askParticipants;
	private $typeAvailabilities;
	private $advancePayment;
	private $advancePaymentByAccountTypes;
	private $minBookingMonths;
	private $minBookingDays;
	private $minBookingHours;
	private $maxBookingMonths;
	private $maxBookingDays;
	private $maxBookingHours;
	private $exclusive;
	private $defaultSlugServicePrice;
	private $notificationActivated;
	private $notificationSubject;
	private $notificationMessage;
	private $customTimeslotsTextActivated;
	private $customTimeslotsTextBase;
	private $customTimeslotsTextSelected;
	private $customTimeslotsTextCompleted;
	private $activated;
	private $linkOldServices;
	private $oldService; //var to indicate that is old service
	private $serviceAvailabilities;
	private $servicePrices;
	private $serviceMemberPriorities;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_service';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `position` int(11) NOT NULL,
		  `modificationDate` datetime NOT NULL,
		  `image` text NOT NULL,
		  `name` text NOT NULL,
		  `slug` text NOT NULL,
		  `category` text NOT NULL,
		  `places` text NOT NULL,
		  `presentation` text NOT NULL,
		  `color` text NOT NULL,
		  `askParticipants` tinyint(1) NOT NULL,
		  `typeAvailabilities` int(11) NOT NULL,
		  `advancePayment` int(11) NOT NULL,
		  `advancePaymentByAccountTypes` text NOT NULL,
		  `minBookingMonths` int(11) NOT NULL,
		  `minBookingDays` int(11) NOT NULL,
		  `minBookingHours` int(11) NOT NULL,
		  `maxBookingMonths` int(11) NOT NULL,
		  `maxBookingDays` int(11) NOT NULL,
		  `maxBookingHours` int(11) NOT NULL,
		  `exclusive` tinyint(1) NOT NULL,
		  `defaultSlugServicePrice` TEXT NOT NULL,
		  `notificationActivated` tinyint(1) NOT NULL,
		  `notificationSubject` TEXT NOT NULL,
		  `notificationMessage` TEXT NOT NULL,
		  `customTimeslotsTextActivated` tinyint(1) NOT NULL,
		  `customTimeslotsTextBase` TEXT NOT NULL,
		  `customTimeslotsTextSelected` TEXT NOT NULL,
		  `customTimeslotsTextCompleted` TEXT NOT NULL,
		  `activated` tinyint(1) NOT NULL,
		  `oldService` tinyint(1) NOT NULL,
		  `linkOldServices` TEXT NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return '';
	}

	/**
	 * return the delete query
	 */
	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * Return true if id parameter id used
	 */
	public static function getAllIdParameterUsed()
	{
		global $wpdb;
		$results = $wpdb->get_results('SELECT '. RESA_ServiceTimeslot::getTableName() . '.idParameter FROM '. self::getTableName() . '
			INNER JOIN '. RESA_ServiceAvailability::getTableName() . ' ON '. self::getTableName() . '.id = '. RESA_ServiceAvailability::getTableName() . '.idService
			INNER JOIN '. RESA_ServiceTimeslot::getTableName() . ' ON '. RESA_ServiceAvailability::getTableName() . '.id = '. RESA_ServiceTimeslot::getTableName() . '.idServiceAvailability GROUP BY '. RESA_ServiceTimeslot::getTableName() . '.idParameter');
		$allData = array();
		if(isset($results)){
			foreach($results as $result){
				array_push($allData, $result->idParameter);
			}
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllSlugsAndId()
	{
		global $wpdb;
		$results = $wpdb->get_results('SELECT id, slug FROM '. self::getTableName() . ' WHERE oldService=0');
		return $results;
	}

	/**
	 * Return the last id service
	 */
	public static function getLastIdService($id){
		global $wpdb;
		$result = $wpdb->get_var('SELECT id FROM ' . self::getTableName() . ' WHERE id='.$id.' AND oldService=0');
		if(!isset($result)){
			$result = $wpdb->get_var('SELECT id FROM ' . self::getTableName() . ' WHERE (linkOldServices=\''.$id.'\' OR linkOldServices LIKE \'%,'.$id.'%\' OR linkOldServices LIKE \'%'.$id.',%\') AND oldService=0');
		}
		return $result;
	}

	/**
	 * Return all services just simple RESA_services
	 */
	public static function getServicesLite()
	{
		global $wpdb;
		$results = $wpdb->get_results('SELECT id, color, image, name, slug, activated, position, category, places FROM '. self::getTableName() . ' WHERE oldService=0 ORDER BY position');
		$allData = array();
		if(isset($results)){
			foreach($results as $result){
				$result->image = unserialize($result->image);
				$result->name = unserialize($result->name);
				$result->activated = (bool)$result->activated;
				$result->places = unserialize($result->places);
				array_push($allData, $result);
			}
		}
		return $allData;
	}

	/**
	 * Return simple name
	 */
	public static function getServiceLite($id){
		global $wpdb;
		$result = $wpdb->get_row('SELECT  id, color, image, name, slug, activated, position, category, places, askParticipants FROM ' . self::getTableName() . ' WHERE id='.$id.' AND oldService=0');
		if(!isset($result)){
			$result = $wpdb->get_row('SELECT id, color, image, name, slug, activated, position, category, places, askParticipants FROM ' . self::getTableName() . ' WHERE (linkOldServices=\''.$id.'\' OR linkOldServices LIKE \'%,'.$id.'%\' OR linkOldServices LIKE \'%'.$id.',%\') AND oldService=0');
		}
		if(!isset($result)){
			$result = $wpdb->get_row('SELECT id, color, image, name, slug, activated, position, category, places, askParticipants FROM ' . self::getTableName() . ' WHERE id='.$id);
		}
		if(isset($result)){
			$result->image = unserialize($result->image);
			$result->name = unserialize($result->name);
			$result->activated = (bool)$result->activated;
			$result->places = unserialize($result->places);
		}
		return $result;
	}


	/**
	 * Return all services and this prices
	 */
	public static function getAllServicesAndThisPrices()
	{
		global $wpdb;
		$results = $wpdb->get_results('SELECT '. self::getTableName() . '.id, '. self::getTableName() . '.activated, '. self::getTableName() . '.slug, '. self::getTableName() . '.askParticipants, '. self::getTableName() . '.places, '. self::getTableName() . '.name, '. RESA_ServicePrice::getTableName() . '.id as price_id, '. RESA_ServicePrice::getTableName() . '.activated as price_activated, '. RESA_ServicePrice::getTableName() . '.name as price_name, '. RESA_ServicePrice::getTableName() . '.slug as price_slug, '. RESA_ServicePrice::getTableName() . '.equipments as price_equipments, '. RESA_ServicePrice::getTableName() . '.participantsParameter FROM '. self::getTableName() . ' INNER JOIN '. RESA_ServicePrice::getTableName() . ' ON '. self::getTableName() . '.id = '. RESA_ServicePrice::getTableName() . '.idService WHERE '. self::getTableName() . '.oldService = 0 ORDER BY '. self::getTableName() . '.position');
		$allData = array();
		if(isset($results)){
			$tempArray = array();
			foreach($results as $result){
				$serviceKey = 'service' . $result->id;
				if(!isset($tempArray[$serviceKey])){
					$tempArray[$serviceKey] = array(
						'id' => $result->id,
						'activated' => $result->activated,
						'slug' => $result->slug,
						'askParticipants' => $result->askParticipants,
						'name' => unserialize($result->name),
						'places' => unserialize($result->places),
						'prices' => array()
					);
				}
				array_push($tempArray[$serviceKey]['prices'], array(
					'id' => $result->price_id,
					'activated' => $result->price_activated,
					'name' => unserialize($result->price_name),
					'slug' => $result->price_slug,
					'equipments' => unserialize($result->price_equipments),
					'participantsParameter' => $result->participantsParameter
				));
			}
			foreach($tempArray as $key => $values){
				array_push($allData, $values);
			}
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array('oldService'=>false))
	{
		return self::getAllDataWithOrderBy($data, 'position');
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithOrderBy($data, $orderBy)
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY '. $orderBy);
		foreach($results as $result){
			$entity = new RESA_Service();
			$entity->fromJSON($result, true);
			$entity->setServiceAvailabilities(RESA_ServiceAvailability::getAllData(array('idService'=>$entity->getId())));
			$entity->setServicePrices(RESA_ServicePrice::getAllData(array('idService'=>$entity->getId())));
			$entity->setServiceMemberPriorities(RESA_ServiceMemberPriority::getAllData(array('idService'=>$entity->getId())));
			$entity->setLoaded(true);
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
	 * Return all entities of this type
	 */
	public static function getAllDataWithOrderByPlaces($data, $orderBy, $idPlaces = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		if(count($idPlaces) > 0){
			$WHERE .= RESA_Tools::generateWhereWithPlace($idPlaces, 'places', 'a:0:{}', '"');
		}
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY '. $orderBy);
		foreach($results as $result){
			$entity = new RESA_Service();
			$entity->fromJSON($result, true);
			$entity->setServiceAvailabilities(RESA_ServiceAvailability::getAllData(array('idService'=>$entity->getId())));
			$entity->setServicePrices(RESA_ServicePrice::getAllData(array('idService'=>$entity->getId())));
			$entity->setServiceMemberPriorities(RESA_ServiceMemberPriority::getAllData(array('idService'=>$entity->getId())));
			$entity->setLoaded(true);
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
	 *
	 */
	public static function serviceHasChanged($idService, $modificationDate){
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE modificationDate > \''.$modificationDate.'\' AND id='.$idService);
		if(!isset($count)) $count = 0;
		return $count > 0;
	}

	/**
	 * update the modifcation date of idService
	 */
	public static function updateModificationDate($idService){
		global $wpdb;
		$modificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$result = $wpdb->query('UPDATE `'. self::getTableName() . '` SET modificationDate=\''.$modificationDate.'\' WHERE id=' . $idService);
		return $result !== FALSE;
	}


	/**
	 * update the color of old service
	 */
	public static function updateColorForOldServices($service){
		global $wpdb;
		if($service->haveOldServices()){
			$idServices = explode(',', $service->getLinkOldServices());
			foreach($idServices as $idService){
				$result = $wpdb->query('UPDATE `'. self::getTableName() . '` SET color=\''.$service->getColor().'\' WHERE id=' . $idService);
			}
		}
		return true;
	}


	/**
	 * update the slug of old service
	 */
	public static function updateSlugForOldServices($service){
		global $wpdb;
		if($service->haveOldServices()){
			$idServices = explode(',', $service->getLinkOldServices());
			foreach($idServices as $idService){
				$result = $wpdb->query('UPDATE `'. self::getTableName() . '` SET slug=\''.$service->getSlug().'\' WHERE id=' . $idService);
			}
		}
		return true;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->position = -1;
		$this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->image = new stdClass();
		$this->name = new stdClass();
		$this->slug = '';
		$this->category = '';
		$this->places = array();
		$this->presentation = new stdClass();
		$this->color = '#27b673';
		$this->askParticipants = false;
		$this->typeAvailabilities = 0;
		$this->advancePayment = 100;
		$this->advancePaymentByAccountTypes = array();
		$this->minBookingMonths = 0;
		$this->minBookingDays = 0;
		$this->minBookingHours = 0;
		$this->maxBookingMonths = 12;
		$this->maxBookingDays = 0;
		$this->maxBookingHours = 0;
		$this->exclusive = false;
		$this->defaultSlugServicePrice = '';
		$this->notificationActivated = false;
		$this->notificationSubject = new stdClass();
		$this->notificationMessage = new stdClass();
		$this->customTimeslotsTextActivated = false;
		$this->customTimeslotsTextBase = new stdClass();
		$this->customTimeslotsTextSelected = new stdClass();
		$this->customTimeslotsTextCompleted = new stdClass();
		$this->activated = false;
		$this->linkOldServices = '';
		$this->oldService = false;
		$this->serviceAvailabilities = array();
		$this->servicePrices = array();
		$this->serviceMemberPriorities = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		if(isset($result)){
			$this->fromJSON($result, true);
			$this->setServiceAvailabilities(RESA_ServiceAvailability::getAllData(array('idService'=>$this->getId())));
			$this->setServicePrices(RESA_ServicePrice::getAllData(array('idService'=>$this->getId())));
			$this->setServiceMemberPriorities(RESA_ServiceMemberPriority::getAllData(array('idService'=>$this->getId())));
			$this->setLoaded(true);
		}
	}

	/**
	 * Load form database
	 */
	public function loadBy($data)
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' '.$WHERE);
		if(isset($result)){
			$this->fromJSON($result, true);
			$this->setServiceAvailabilities(RESA_ServiceAvailability::getAllData(array('idService'=>$this->getId())));
			$this->setServicePrices(RESA_ServicePrice::getAllData(array('idService'=>$this->getId())));
			$this->setServiceMemberPriorities(RESA_ServiceMemberPriority::getAllData(array('idService'=>$this->getId())));
			$this->setLoaded(true);
		}
	}

	/**
	 * Load form database
	 */
	public function loadByIdLastVersion($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id='.$id.' AND oldService=0');
		if(!isset($result)){
			$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE (linkOldServices=\''.$id.'\' OR linkOldServices LIKE \'%,'.$id.'%\' OR linkOldServices LIKE \'%'.$id.',%\') AND oldService=0');
		}
		if(isset($result)){
			$this->fromJSON($result, true);
			$this->setServiceAvailabilities(RESA_ServiceAvailability::getAllData(array('idService'=>$this->getId())));
			$this->setServicePrices(RESA_ServicePrice::getAllData(array('idService'=>$this->getId())));
			$this->setServiceMemberPriorities(RESA_ServiceMemberPriority::getAllData(array('idService'=>$this->getId())));
			$this->setLoaded(true);
		}
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$this->clearModificationDate();
			$lastService = new RESA_Service();
			$lastService->loadById($this->id);
			$lastServiceAvailabilities = $lastService->getServiceAvailabilities();
			$lastServicePrices = $lastService->getServicePrices();
			$lastServiceMemberPriorities = $lastService->getServiceMemberPriorities();
			$lastIdMembers = array();
			foreach($lastServiceMemberPriorities as $serviceMemberPriority) {
				array_push($lastIdMembers, $serviceMemberPriority->getIdMember());
			}
			$this->linkWPDB->update(self::getTableName(), array(
				'position' => $this->position,
				'modificationDate' => $this->modificationDate,
				'image' => serialize($this->image),
				'name' => serialize($this->name),
				'slug' => $this->slug,
				'category' => $this->category,
				'places' => serialize($this->places),
				'presentation' => serialize($this->presentation),
				'color' => $this->color,
				'askParticipants' => $this->askParticipants,
				'typeAvailabilities' => $this->typeAvailabilities,
				'advancePayment' => $this->advancePayment,
				'advancePaymentByAccountTypes' => serialize($this->advancePaymentByAccountTypes),
				'minBookingMonths' => $this->minBookingMonths,
				'minBookingDays' => $this->minBookingDays,
				'minBookingHours' => $this->minBookingHours,
				'maxBookingMonths' => $this->maxBookingMonths,
				'maxBookingDays' => $this->maxBookingDays,
				'maxBookingHours' => $this->maxBookingHours,
				'exclusive' => $this->exclusive,
				'defaultSlugServicePrice' => $this->defaultSlugServicePrice,
				'notificationActivated' => $this->notificationActivated,
				'notificationSubject' => serialize($this->notificationSubject),
				'notificationMessage' => serialize($this->notificationMessage),
				'customTimeslotsTextActivated' => $this->customTimeslotsTextActivated,
				'customTimeslotsTextBase' => serialize($this->customTimeslotsTextBase),
				'customTimeslotsTextSelected' => serialize($this->customTimeslotsTextSelected),
				'customTimeslotsTextCompleted' => serialize($this->customTimeslotsTextCompleted),
				'activated' => $this->activated && $this->isActivable(),
				'linkOldServices' => $this->linkOldServices,
				'oldService' => $this->oldService
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
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d'
			),
			array ('%d'));

			$idServiceAvailabilities = array();
			$idServicePrices = array();
			$idMembers = array();
			for($i = 0; $i < count($this->serviceAvailabilities); $i++)
			{
				if(!$this->serviceAvailabilities[$i]->isNew())
					array_push($idServiceAvailabilities, $this->serviceAvailabilities[$i]->getId());
				$this->serviceAvailabilities[$i]->save();
			}
			for($i = 0; $i < count($this->servicePrices); $i++)
			{
				if(!$this->servicePrices[$i]->isNew())
					array_push($idServicePrices, $this->servicePrices[$i]->getId());
				$this->servicePrices[$i]->save();
			}
			for($i = 0; $i < count($this->serviceMemberPriorities); $i++)
			{
				if(in_array($this->serviceMemberPriorities[$i]->getIdMember(), $lastIdMembers)) {
					$this->serviceMemberPriorities[$i]->setLoaded(true);
					array_push($idMembers, $this->serviceMemberPriorities[$i]->getIdMember());
				}
				$this->serviceMemberPriorities[$i]->save();
			}
			//delete
			for($i = 0; $i < count($lastServiceAvailabilities); $i++) {
				if(!in_array($lastServiceAvailabilities[$i]->getId(), $idServiceAvailabilities))
					$lastServiceAvailabilities[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastServicePrices); $i++) {
				if(!in_array($lastServicePrices[$i]->getId(), $idServicePrices))
					$lastServicePrices[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastServiceMemberPriorities); $i++) {
				if(!in_array($lastServiceMemberPriorities[$i]->getIdMember(), $idMembers))
					$lastServiceMemberPriorities[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'position' => $this->position,
				'modificationDate' => $this->modificationDate,
				'image' => serialize($this->image),
				'name' => serialize($this->name),
				'slug' => $this->slug,
				'category' => $this->category,
				'places' => serialize($this->places),
				'presentation' => serialize($this->presentation),
				'color' => $this->color,
				'askParticipants' => $this->askParticipants,
				'typeAvailabilities' => $this->typeAvailabilities,
				'advancePayment' => $this->advancePayment,
				'advancePaymentByAccountTypes' => serialize($this->advancePaymentByAccountTypes),
				'minBookingMonths' => $this->minBookingMonths,
				'minBookingDays' => $this->minBookingDays,
				'minBookingHours' => $this->minBookingHours,
				'maxBookingMonths' => $this->maxBookingMonths,
				'maxBookingDays' => $this->maxBookingDays,
				'maxBookingHours' => $this->maxBookingHours,
				'exclusive' => $this->exclusive,
				'defaultSlugServicePrice' => $this->defaultSlugServicePrice,
				'notificationActivated' => $this->notificationActivated,
				'notificationSubject' => serialize($this->notificationSubject),
				'notificationMessage' => serialize($this->notificationMessage),
				'customTimeslotsTextActivated' => $this->customTimeslotsTextActivated,
				'customTimeslotsTextBase' => serialize($this->customTimeslotsTextBase),
				'customTimeslotsTextSelected' => serialize($this->customTimeslotsTextSelected),
				'customTimeslotsTextCompleted' => serialize($this->customTimeslotsTextCompleted),
				'activated' => $this->activated && $this->isActivable(),
				'linkOldServices' => $this->linkOldServices,
				'oldService' => $this->oldService
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
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d'
			));
			$this->id = $this->linkWPDB->insert_id;
			$this->setLoaded(true);
			for($i = 0; $i < count($this->serviceAvailabilities); $i++)
			{
				$this->serviceAvailabilities[$i]->setIdService($this->id);
				$this->serviceAvailabilities[$i]->save();
			}
			for($i = 0; $i < count($this->servicePrices); $i++)
			{
				$this->servicePrices[$i]->setIdService($this->id);
				$this->servicePrices[$i]->save();
			}
			for($i = 0; $i < count($this->serviceMemberPriorities); $i++)
			{
				$this->serviceMemberPriorities[$i]->setIdService($this->id);
				$this->serviceMemberPriorities[$i]->save();
			}
		}
	}

	/**
	 * Save in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded())
		{
			if(RESA_Appointment::countAppointmentsWithIdsServices(array($this->id)) <= 0){
				RESA_ServiceConstraint::deleteAllByIdService($this->id);
				foreach($this->serviceAvailabilities as $serviceAvailability){ $serviceAvailability->deleteMe(); }
				foreach($this->servicePrices as $servicePrice){ $servicePrice->deleteMe(); }
				foreach($this->serviceMemberPriorities as $serviceMemberPriority){ $serviceMemberPriority->deleteMe(); }
				$this->linkWPDB->delete(self::getTableName(), array('id'=>$this->id), array ('%d'));
			}
			else {
				$this->setOldService(true);
				$this->save();
			}
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$notificationMessage = $this->notificationMessage;
		if(RESA_Tools::canUseForeach($notificationMessage)){
			foreach($notificationMessage as $key => $message){
				$notificationMessage->$key = ($message);
			}
		}
		$presentation = $this->presentation;
		if(RESA_Tools::canUseForeach($presentation)){
			foreach($presentation as $key => $message){
				$presentation->$key = ($message);
			}
		}
		return '{
			"id":'.$this->id.',
			"position":'.$this->position.',
			"modificationDate":"'.$this->modificationDate.'",
			"image":'.json_encode($this->image).',
			"name":'.json_encode($this->name).',
			"slug":"'.$this->slug.'",
			"category":"'.$this->category.'",
			"places":'.json_encode($this->places).',
			"presentation":'.json_encode($presentation).',
			"color":"'.$this->color.'",
			"askParticipants":'.RESA_Tools::toJSONBoolean($this->askParticipants).',
			"typeAvailabilities":'.$this->typeAvailabilities.',
			"advancePayment":'.$this->advancePayment.',
			"advancePaymentByAccountTypes":'.json_encode($this->advancePaymentByAccountTypes).',
			"startBookingDate":"'.$this->getStartBookingDate()->format(DateTime::W3C).'",
			"minDateAvailabilities":"'.$this->getMinDateAvailabilities()->format(DateTime::W3C).'",
			"maxDateAvailabilities":"'.$this->getMaxDateAvailabilities()->format(DateTime::W3C).'",
			"minBookingMonths":'.$this->minBookingMonths.',
			"minBookingDays":'.$this->minBookingDays.',
			"minBookingHours":'.$this->minBookingHours.',
			"maxBookingMonths":'.$this->maxBookingMonths.',
			"maxBookingDays":'.$this->maxBookingDays.',
			"maxBookingHours":'.$this->maxBookingHours.',
			"exclusive":'.RESA_Tools::toJSONBoolean($this->exclusive).',
			"defaultSlugServicePrice":"'.$this->defaultSlugServicePrice.'",
			"notificationActivated":'.RESA_Tools::toJSONBoolean($this->notificationActivated).',
			"notificationSubject":'.json_encode($this->notificationSubject).',
			"notificationMessage":'.json_encode($notificationMessage).',
			"customTimeslotsTextActivated":'.RESA_Tools::toJSONBoolean($this->customTimeslotsTextActivated).',
			"customTimeslotsTextBase":'.json_encode($this->customTimeslotsTextBase).',
			"customTimeslotsTextSelected":'.json_encode($this->customTimeslotsTextSelected).',
			"customTimeslotsTextCompleted":'.json_encode($this->customTimeslotsTextCompleted).',
			"activated":'.RESA_Tools::toJSONBoolean($this->activated && $this->isActivable()).',
			"linkOldServices":"'.$this->linkOldServices.'",
			"oldService":'.RESA_Tools::toJSONBoolean($this->oldService).',
			"serviceAvailabilities":'.RESA_Tools::formatJSONArray($this->serviceAvailabilities).',
			"servicePrices":'.RESA_Tools::formatJSONArray($this->servicePrices).',
			"serviceMemberPriorities":'.RESA_Tools::formatJSONArray($this->serviceMemberPriorities).'
		}';
	}


	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		$this->position = $json->position;
		if(isset($json->modificationDate)) $this->modificationDate = $json->modificationDate;
		if(isset($json->image) && !empty($json->image)){
			if(!$formDatabase && RESA_Tools::canUseForeach($json->image)){
				$this->image = $json->image;
			}
			else {
				$this->image = unserialize($json->image);
			}
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->name)){
			foreach($json->name as $key => $name){
				$json->name->$key = esc_html($name);
			}
			$this->name = $json->name;
		}
		else {
			$this->name = unserialize($json->name);
		}
		$this->slug = esc_html($json->slug);
		$this->category = esc_html($json->category);
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
				$json->presentation->$key = RESA_Tools::echapEolTab($presentation);
			}
			$this->presentation = $json->presentation;
		}else {
			$this->presentation = unserialize($json->presentation);
		}
		$this->color = esc_html($json->color);
		if(isset($json->askParticipants)) $this->askParticipants = $json->askParticipants;
		if(isset($json->typeAvailabilities)) $this->typeAvailabilities = $json->typeAvailabilities;
		$this->advancePayment = $json->advancePayment;
		if(isset($json->advancePaymentByAccountTypes)){
			if(!$formDatabase){
				$this->advancePaymentByAccountTypes = $json->advancePaymentByAccountTypes;
			}
			else if(!empty($json->advancePaymentByAccountTypes)) {
				$this->advancePaymentByAccountTypes = unserialize($json->advancePaymentByAccountTypes);
			}
		}
		$this->minBookingMonths = $json->minBookingMonths;
		$this->minBookingDays = $json->minBookingDays;
		$this->minBookingHours = $json->minBookingHours;
		$this->maxBookingMonths = $json->maxBookingMonths;
		$this->maxBookingDays = $json->maxBookingDays;
		$this->maxBookingHours = $json->maxBookingHours;
		$this->exclusive = $json->exclusive;
		if(isset($json->defaultSlugServicePrice)) $this->defaultSlugServicePrice = $json->defaultSlugServicePrice;
		if(isset($json->notificationActivated)) $this->notificationActivated = $json->notificationActivated;
		if(!$formDatabase && RESA_Tools::canUseForeach($json->notificationSubject)){
			foreach($json->notificationSubject as $key => $subject){
				$json->notificationSubject->$key = esc_html($subject);
			}
			$this->notificationSubject = $json->notificationSubject;
		}else if(!empty($json->notificationSubject)){
			$this->notificationSubject = unserialize($json->notificationSubject);
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->notificationMessage)){
			foreach($json->notificationMessage as $key => $message){
				$json->notificationMessage->$key = $message;
			}
			$this->notificationMessage = $json->notificationMessage;
		}else if(!empty($json->notificationMessage)){
			 $this->notificationMessage = unserialize($json->notificationMessage);
		}
		if(isset($json->customTimeslotsTextActivated)) $this->customTimeslotsTextActivated = $json->customTimeslotsTextActivated;
		if(!$formDatabase && RESA_Tools::canUseForeach($json->customTimeslotsTextBase)){
			foreach($json->customTimeslotsTextBase as $key => $message){
				$json->customTimeslotsTextBase->$key = $message;
			}
			$this->customTimeslotsTextBase = $json->customTimeslotsTextBase;
		}else if(!empty($json->customTimeslotsTextBase)){
			 $this->customTimeslotsTextBase = unserialize($json->customTimeslotsTextBase);
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->customTimeslotsTextSelected)){
			foreach($json->customTimeslotsTextSelected as $key => $message){
				$json->customTimeslotsTextSelected->$key = $message;
			}
			$this->customTimeslotsTextSelected = $json->customTimeslotsTextSelected;
		}else if(!empty($json->customTimeslotsTextSelected)){
			 $this->customTimeslotsTextSelected = unserialize($json->customTimeslotsTextSelected);
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->customTimeslotsTextCompleted)){
			foreach($json->customTimeslotsTextCompleted as $key => $message){
				$json->customTimeslotsTextCompleted->$key = $message;
			}
			$this->customTimeslotsTextCompleted = $json->customTimeslotsTextCompleted;
		}else if(!empty($json->customTimeslotsTextCompleted)){
			 $this->customTimeslotsTextCompleted = unserialize($json->customTimeslotsTextCompleted);
		}
		$this->activated = $json->activated;
		$this->linkOldServices = $json->linkOldServices;
		$this->oldService = $json->oldService;
		if(isset($json->serviceAvailabilities))
		{
			$serviceAvailabilities = array();
			for($i = 0; $i < count($json->serviceAvailabilities); $i++)
			{
				$serviceAvailability = new RESA_ServiceAvailability();
				$serviceAvailability->fromJSON($json->serviceAvailabilities[$i], $formDatabase);
				array_push($serviceAvailabilities, $serviceAvailability);
			}
			$this->setServiceAvailabilities($serviceAvailabilities);
		}
		if(isset($json->servicePrices))
		{
			$servicePrices = array();
			for($i = 0; $i < count($json->servicePrices); $i++)
			{
				$servicePrice = new RESA_ServicePrice();
				$servicePrice->fromJSON($json->servicePrices[$i], $formDatabase);
				array_push($servicePrices, $servicePrice);
			}
			$this->setServicePrices($servicePrices);
		}
		if(isset($json->serviceMemberPriorities))
		{
			$serviceMemberPriorities = array();
			for($i = 0; $i < count($json->serviceMemberPriorities); $i++)
			{
				$serviceMemberPriority = new RESA_ServiceMemberPriority();
				$serviceMemberPriority->fromJSON($json->serviceMemberPriorities[$i]);
				array_push($serviceMemberPriorities, $serviceMemberPriority);
			}
			$this->setServiceMemberPriorities($serviceMemberPriorities);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	/**
	 * \fn getTimeslots
	 * \brief return the timeslots for a date.
	 * \param dateStart
	 * \param dateEnd
	 * \param checkMinMaxBookingDate true if check min and max booking date
	 * \return a table of RESA_ServiceTimeslot
	 */
	public function getTimeslots($dateStart, $dateEnd, $isBackEnd = false, $typeAccount = 'type_account_0'){
		$timeslots = array();
		$minDate = RESA_Tools::resetTimeToDate($this->getMinBookingDate());
		$maxDate = RESA_Tools::resetTimeToDate($this->getMaxBookingDate());
		if(isset($dateStart) && ((RESA_Tools::resetTimeToDate($dateStart) >= $minDate && RESA_Tools::resetTimeToDate($dateStart) < $maxDate) || $isBackEnd)){
			$serviceAvailabilities = $this->getServiceAvailabilities();
			$timeslots = array();
			foreach($serviceAvailabilities as $serviceAvailability) {
				$dates = $serviceAvailability->getArrayDates();
				$stringDate = $dateStart->format('Y-m-d');
				if(in_array($stringDate, $dates) ||
				$serviceAvailability->isInGroupDates(RESA_Tools::resetTimeToDate($dateStart)) ||
				$serviceAvailability->isInManyDates(RESA_Tools::resetTimeToDate($dateStart))) {
					$actualDate = new DateTime();
					$actualDate->setTimestamp(current_time('timestamp'));
					$localMinBookingHours = new DateTime();
					$localMinBookingHours->setTimestamp(current_time('timestamp'));
					$localMinBookingHours = $localMinBookingHours->format('H') + $this->minBookingHours;
					foreach($serviceAvailability->getFilteredTimeslots($isBackEnd, $typeAccount) as $timeslot){
						$startHour = RESA_Algorithms::timeToDate($timeslot->getStartTime(), $dateStart)->format('H');
						$timeslotStartDate = RESA_Algorithms::timeToDate($timeslot->getStartTime(), $dateStart);
						$timeslotEndDate = RESA_Algorithms::timeToDate($timeslot->getEndTime(), $dateEnd);
						if($isBackEnd ||
							((($dateStart->format('Y-m-d') == $actualDate->format('Y-m-d') && $startHour > $localMinBookingHours) ||
							($dateStart->format('Y-m-d') != $actualDate->format('Y-m-d'))) && ($dateStart <= $timeslotStartDate && $timeslotEndDate <= $dateEnd))){
							array_push($timeslots, $timeslot);
						}
					}
				}
			}
		}
		usort($timeslots, array('RESA_ServiceTimeslot','compare'));
		return $timeslots;
	}

	/**
	 * \fn haveTimeslot
	 * \brief return have timeslot.
	 * \param dateStart
	 * \param dateEnd
	 * \return
	 */
	public function haveTimeslot($dateStart, $dateEnd){
		if(isset($dateStart)){
			$serviceAvailabilities = $this->getServiceAvailabilities();
			$timeslots = array();
			foreach($serviceAvailabilities as $serviceAvailability) {
				$dates = $serviceAvailability->getArrayDates();
				$stringDate = $dateStart->format('Y-m-d');
				if(in_array($stringDate, $dates) ||
				$serviceAvailability->isInGroupDates(RESA_Tools::resetTimeToDate($dateStart)) ||
				$serviceAvailability->isInManyDates(RESA_Tools::resetTimeToDate($dateStart))) {
					foreach($serviceAvailability->getTimeslots() as $timeslot){
						$timeslotStartDate = RESA_Algorithms::timeToDate($timeslot->getStartTime(), $dateStart);
						$timeslotEndDate = RESA_Algorithms::timeToDate($timeslot->getEndTime(), $dateEnd);
						if($dateStart == $timeslotStartDate && $timeslotEndDate == $dateEnd){
							return true;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Replace all oldId by the newId
	 */
	public function updateIdService($oldId, $newId){
		$tokens = explode(',', $this->linkOldServices);
		for($i = 0; $i < count($tokens); $i++){
			if($tokens[$i] == $oldId){
				$tokens[$i] = $newId;
			}
		}
		$this->linkOldServices = implode(',', $tokens);
	}


	/**
	 * Recreated the service
	 */
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->serviceAvailabilities as $serviceAvailability){
			$serviceAvailability->setNew();
		}
		foreach($this->servicePrices as $servicePrice){
			$servicePrice->setNew();
		}
	}

	public function isNew(){ return $this->id==-1; }

	public function getId(){ return $this->id; }
	public function setId($id){ $this->id = $id; }
	public function getPosition(){ return $this->position; }
	public function setPosition($position){ $this->position = $position; }
	public function setModificationDate($modificationDate){ $this->modificationDate = $modificationDate; }
	public function getModificationDate(){ return $this->modificationDate; }
	public function clearModificationDate(){ $this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp')); }
	public function setImage($image){ $this->image = $image; }
	public function getImage(){ return RESA_Tools::getTextByLocale($this->image, get_locale()); }
	public function setName($name){ $this->name = $name; }
	public function getName(){ return RESA_Tools::getTextByLocale($this->name, get_locale()); }
	public function getSlug(){ return $this->slug; }
	public function getCategory(){ return $this->category; }
	public function getPlaces(){ return $this->places; }
	public function setPlaces($places){ $this->places = $places; }
	public function addPlace($place){ array_push($this->places,$place); }

	public function isAskParticipants(){ return $this->askParticipants; }
	public function setAskParticipants($askParticipants){ $this->askParticipants = $askParticipants; }

	public function getTypeAvailabilities(){ return $this->typeAvailabilities; }
	public function setTypeAvailabilities($typeAvailabilities){ $this->typeAvailabilities = $typeAvailabilities; }

	public function getAdvancePayment(){ return $this->advancePayment; }
	public function setAdvancePayment($advancePayment){ $this->advancePayment = $advancePayment; }

	public function getAdvancePaymentByAccountTypes(){ return $this->advancePaymentByAccountTypes; }
	public function setAdvancePaymentByAccountTypes($advancePaymentByAccountTypes){ $this->advancePaymentByAccountTypes = $advancePaymentByAccountTypes; }

	public function getAdvancePaymentWithCustomer($customer){
		$advancePayment = $this->advancePayment;
		if(isset($customer)){
			foreach($this->advancePaymentByAccountTypes as $advancePaymentByAccountType){
				if($customer->getTypeAccount() == $advancePaymentByAccountType->typeAccount){
					$advancePayment = $advancePaymentByAccountType->advancePayment;
				}
			}
		}
		return $advancePayment;
	}

	public function getMinBookingDate(){
		$date = new DateTime();
		$date->setTimestamp(current_time('timestamp'));
		$date->add(new DateInterval('P'.$this->minBookingMonths.'M'));
		$date->add(new DateInterval('P'.$this->minBookingDays.'D'));
		$date->add(new DateInterval('PT'.$this->minBookingHours.'H'));
		return $date;
	}

	public function getMaxBookingDate(){
		$date = new DateTime();
		$date->setTimestamp(current_time('timestamp'));
		$date->add(new DateInterval('P'.$this->maxBookingMonths.'M'));
		$date->add(new DateInterval('P'.$this->maxBookingDays.'D'));
		$date->add(new DateInterval('PT'.$this->maxBookingHours.'H'));
		return $date;
	}

	/**
	 * Return the min date of availabilities
	 */
	public function getMinDateAvailabilities(){
		$actualDate = RESA_Tools::resetTimeToDate($this->getMinBookingDate());
		$date = DateTime::createFromFormat('m:d:Y','01:01:'.($actualDate->format('Y') + 1));
		$haveOneDate = false;
		foreach($this->getServiceAvailabilities() as $serviceAvailability) {
			if($this->typeAvailabilities == 0){
				$dates = $serviceAvailability->getArrayDates();
				foreach($dates as $localDate){
					if(!empty($localDate)){
						$haveOneDate = true;
						$dateTime = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $localDate));
						if($dateTime >= $actualDate && $dateTime < $date){
							$date = $dateTime;
						}
					}
				}
			}
			else if($this->typeAvailabilities == 1){
				$groupsDates = $serviceAvailability->getGroupDates();
				foreach($groupsDates as $dates){
					$haveOneDate = true;
					$localDate = RESA_Tools::resetTimeToDate(new DateTime($dates->startDate));
					if($localDate >= $actualDate && $localDate < $date){
						$date = $localDate;
					}
				}
			}
			else if($this->typeAvailabilities == 2){
				$manyDates = $serviceAvailability->getManyDates();
				foreach($manyDates as $dates){
					$exploded = explode(',', $dates);
					foreach($exploded as $localDateAux){
						$localDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $localDateAux));
						if($localDate >= $actualDate && $localDate < $date){
							$date = $localDate;
						}
					}
				}
			}
		}
		if(!isset($date) || empty($date) || !$haveOneDate) {
			$date = $actualDate;
		}
		$date = RESA_Tools::resetTimeToDate($date);
		return $date;
	}

	/**
	 * Return the max date of availabilities
	 */
	public function getMaxDateAvailabilities(){
		$actualDate = new DateTime();
		$date = DateTime::createFromFormat('m:d:Y','01:01:'.($actualDate->format('Y')));
		$haveOneDate = false;
		foreach($this->getServiceAvailabilities() as $serviceAvailability) {
			if($this->typeAvailabilities == 0){
				$dates = $serviceAvailability->getArrayDates();
				foreach($dates as $localDate){
					if(!empty($localDate)){
						$haveOneDate = true;
						$dateTime = DateTime::createFromFormat('Y-m-d', $localDate);
						if($dateTime > $date){
							$date = $dateTime;
						}
					}
				}
			}
			else if($this->typeAvailabilities == 1){
				$groupsDates = $serviceAvailability->getGroupDates();
				foreach($groupsDates as $dates){
					$haveOneDate = true;
					$localDate =RESA_Tools::resetTimeToDate(new DateTime($dates->startDate));
					if($localDate > $date){
						$date = $localDate;
					}
				}
			}
			else if($this->typeAvailabilities == 2){
				$manyDates = $serviceAvailability->getManyDates();
				foreach($manyDates as $dates){
					$exploded = explode(',', $dates);
					foreach($exploded as $localDateAux){
						$localDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $localDateAux));
						if($localDate > $date){
							$date = $localDate;
						}
					}
				}
			}
		}
		if(!isset($date) || !$haveOneDate) {
			$date = $actualDate;
		}
		$date = RESA_Tools::resetTimeToDate($date);
		return $date;
	}

	/**
	 * Calcule la date minimum de prise de rendez dans l'affichage du formulaire
	 */
	 public function getStartBookingDate(){
 		$date = $this->getMinDateAvailabilities();
 		$minDate = $this->getMinBookingDate();
 		if($minDate < $date){
 			$minDate = $date;
 		}
 		$dateEnd = DateTime::createFromFormat('d-m-Y H:i:s', $minDate->format('d-m-Y').' 23:59:59');
 		$timeslots = $this->getTimeslots($minDate, $dateEnd);
 		if(count($timeslots) == 0){
 			$dateInterval = new DateInterval('P1D');
 			$minDate->add($dateInterval);
			$minDate = RESA_Tools::resetTimeToDate($minDate);
 		}
 		return $minDate;
 	}


	public function setServiceAvailabilities($serviceAvailabilities){ $this->serviceAvailabilities = $serviceAvailabilities; }
	public function getServiceAvailabilities(){ return $this->serviceAvailabilities; }
	public function setServicePrices($servicePrices){ $this->servicePrices = $servicePrices; }
	public function getServicePrices(){ return $this->servicePrices; }
	public function setColor($color){ $this->color = $color; }
	public function getColor(){ return $this->color; }
	public function setServiceMemberPriorities($serviceMemberPriorities){ $this->serviceMemberPriorities = $serviceMemberPriorities; }
	public function getServiceMemberPriorities(){ return $this->serviceMemberPriorities; }

	public function isActivated(){ return $this->activated; }
	public function setActivated($activated){ $this->activated = $activated; }
	public function isExclusive(){ return $this->exclusive; }
	public function setExclusive($exclusive){ $this->exclusive = $exclusive; }
	public function haveDefaultSlugServicePrice(){ return !empty($this->defaultSlugServicePrice); }
	public function getDefaultSlugServicePrice(){ return $this->defaultSlugServicePrice; }
	public function setDefaultSlugServicePrice($defaultSlugServicePrice){ $this->defaultSlugServicePrice = $defaultSlugServicePrice; }
	public function isNotificationActivated(){ return $this->notificationActivated; }
	public function getNotificationSubject(){ return $this->notificationSubject; }
	public function getNotificationMessage(){ return $this->notificationMessage; }
	public function isCustomTimeslotsTextActivated(){ return $this->customTimeslotsTextActivated; }
	public function getCustomTimeslotsTextBase(){ return $this->customTimeslotsTextBase; }
	public function getCustomTimeslotsTextSelected(){ return $this->customTimeslotsTextSelected; }
	public function getCustomTimeslotsTextCompleted(){ return $this->customTimeslotsTextCompleted; }

	public function getOldService(){ return $this->oldService; }
	public function setOldService($oldService){ $this->oldService = $oldService; }
	public function getLinkOldServices(){ return $this->linkOldServices; }
	public function setLinkOldServices($linkOldServices){ $this->linkOldServices = $linkOldServices; }
	public function haveOldServices(){ return !empty($this->linkOldServices); }
	public function isSameService($idService){
		$tokens = explode(',', $this->linkOldServices);
		return $this->id == $idService || in_array($idService, $tokens);
	}
	public function addLinkOldService($idService){
		if(!empty($this->linkOldServices)) $this->linkOldServices .= ',';
		$this->linkOldServices .= $idService;
	}

	public function getAllIds(){
		$tokens = array();
		if(!empty($this->linkOldServices)){
			$tokens = explode(',', $this->linkOldServices);
		}
		array_push($tokens, $this->id);
		return $tokens;
	}

	/**
	 *
	 */
	public function getServicePriceById($id){
		foreach($this->servicePrices as $servicePrice){
			if($servicePrice->getId() == $id)
				return $servicePrice;
		}
		return null;
	}

	/**
	 *
	 */
	public function getDefaultServicePrice(){
		$servicePrice = null;
		if($this->haveDefaultSlugServicePrice()){
			$servicePrice = $this->getServicePriceBySlug($this->defaultSlugServicePrice);
		}
		return $servicePrice;
	}

	/**
	 *
	 */
	public function getAllEquipments(){
		$equipments = [];
		foreach($this->servicePrices as $servicePrice){
			foreach($servicePrice->getEquipments() as $equipment){
				 if(!in_array($equipment, $equipments)){
					 array_push($equipments, $equipment);
				 }
			}
		}
		return $equipments;
	}

	/**
	 *
	 */
	public function oneServicePriceWithNotEquipments(){
		foreach($this->servicePrices as $servicePrice){
			if(!$servicePrice->isExtra() && count($servicePrice->getEquipments()) == 0){
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 */
	public function getServicePriceBySlug($slug){
		foreach($this->servicePrices as $servicePrice){
			if($servicePrice->getSlug() == $slug)
				return $servicePrice;
		}
		return null;
	}


	/**
	 * return true if appointment have all mandatory prices
	 */
	public function haveAllMandatoryPrices(RESA_Appointment $appointment){
		foreach($this->servicePrices as $servicePrice){
			if($servicePrice->isMandatory() && null == $appointment->getAppointmentNumberPriceByIdPrice($servicePrice->getId())){
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 */
	public function isSynchonizedCaisseOnline(){
		foreach($this->servicePrices as $servicePrice){
			$vatList = $servicePrice->getVatList();
			for($i = 0; $i < count($vatList); $i++){
				if(isset($vatList[$i]->product)){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * return true if the service is activable
	 * if there are some disponibilities or timeslots (or price)
	 */
	public function isActivable(){
		$activable = true;
		foreach($this->name as $key => $value){
			$activable = $activable && !is_null($value) && !empty($value);
		}
		$haveOneScheduleAndTimeslot = false;
		foreach($this->serviceAvailabilities as $serviceAvailabilities){
			$haveOneScheduleAndTimeslot = $haveOneScheduleAndTimeslot||
				((count($serviceAvailabilities->getArrayDates()) > 0 ||
				count($serviceAvailabilities->getGroupDates()) > 0) &&
				count($serviceAvailabilities->getTimeslots()) > 0);
		}
		$activable = $activable && $haveOneScheduleAndTimeslot;
		if($activable){
			$haveOnePrice = false;
			foreach($this->servicePrices as $servicePrices){
				$haveOnePrice = $haveOnePrice || ($servicePrices->isActivated() && $servicePrices->getPrice() >= 0);
			}
			$activable = $activable && $haveOnePrice;
		}
		return $activable;
	}

	/**
	 * return the list of id parameters in timeslot used in service
	 */
	public function getListIdParameters(){
		$idParameters = [];
		foreach($this->getServiceAvailabilities() as $serviceAvailability) {
			foreach($serviceAvailability->getTimeslots() as $timeslot){
				$idParameter = $timeslot->getIdParameter();
				if($idParameter!=-1 && !in_array($idParameter, $idParameters)){
					array_push($idParameters, $idParameter);
				}
			}
		}
		return $idParameters;
	}

	public function updateService($oldService, $newService){
		$linkOldNewPrice = array();
		foreach($oldService->getServicePrices() as $oldServicePrice){
			foreach($newService->getServicePrices() as $newServicePrice){
				if($oldServicePrice->getSlug() == $newServicePrice->getSlug()){
					array_push($linkOldNewPrice, array($oldServicePrice->getId(), $newServicePrice->getId()));
				}
			}
		}
		foreach($this->serviceAvailabilities as $serviceAvailability){
			foreach($serviceAvailability->getTimeslots() as $timeslot){
				$idsServicePrices = explode(',', $timeslot->getIdsServicePrices());
				for($i = 0; $i < count($idsServicePrices); $i++){
					foreach($linkOldNewPrice as $oldNewPrice){
						if($idsServicePrices[$i] == $oldNewPrice[0]) {
							$idsServicePrices[$i] = $oldNewPrice[1];
						}
					}
				}
				$timeslot->setIdsServicePrices(implode(',', $idsServicePrices));
			}
		}
	}

	public function needCreateNewVersion($oldService){
		if($oldService->isAskParticipants() != $this->isAskParticipants()){
			return true;
		}
		if($oldService->isExclusive() != $this->isExclusive()){
			return true;
		}
		for($i = 0; $i < count($oldService->getServicePrices()); $i++){
			$servicePrice = $oldService->getServicePrices()[$i];
			$oldServicePrice = $this->getServicePriceById($servicePrice->getId());
			if(isset($oldServicePrice)){
				if($servicePrice->getPrice() != $oldServicePrice->getPrice()){
					return true;
				}
				if($servicePrice->getParticipantsParameter() != $oldServicePrice->getParticipantsParameter()){
					return true;
				}
				if($servicePrice->getThresholdPrices() != $oldServicePrice->getThresholdPrices()){
					return true;
				}
				if($servicePrice->getVatList() != $oldServicePrice->getVatList()){
					return true;
				}
			}
			else if(RESA_AppointmentNumberPrice::haveAppointmentNumberPricesWithIdServicePrice($servicePrice->getId())){
				Logger::DEBUG('Create new version');
				return true;
			}
		}
		return false;
	}
}
