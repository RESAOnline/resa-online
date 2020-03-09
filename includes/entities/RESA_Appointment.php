<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Appointment extends RESA_EntityDTO
{
	private $id;
	private $idBooking;
	private $idService;
	private $idPlace;
	private $presentation;
	private $startDate;
	private $endDate;
	private $state;
	private $idParameter;
	private $noEnd;
	private $numbers;
	private $totalPrice;
	private $totalPriceWithoutReductions;
	private $participants;
	private $tags;
	private $internalIdLink;
	private $calendarId;
	private $appointmentNumberPrices;
	private $appointmentMembers;
	private $appointmentReductions;
	private $idClientObject; //Not saved in BDD
	private $beforeAppointmentNotified;
	private $afterAppointmentNotified;


	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_appointment';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idBooking` int(11) NOT NULL,
		  `idService` int(11) NOT NULL,
		  `idPlace` text NOT NULL,
		  `presentation` text NOT NULL,
		  `startDate` datetime NOT NULL,
		  `endDate` datetime NOT NULL,
		  `state` enum(\'ok\',\'waiting\',\'cancelled\',\'deleted\', \'abandonned\',\'updated\') NOT NULL,
		  `idParameter` int(11) NOT NULL,
		  `noEnd` tinyint(1) NOT NULL,
		  `numbers` int(11) NOT NULL,
		  `totalPrice` FLOAT NOT NULL,
		  `totalPriceWithoutReductions` FLOAT NOT NULL,
		  `participants` text NOT NULL,
		  `tags` text NOT NULL,
		  `internalIdLink` int(11) NOT NULL,
		  `calendarId` VARCHAR(255) NOT NULL,
		  `beforeAppointmentNotified` tinyint(1) NOT NULL,
		  `afterAppointmentNotified` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idBooking` (`idBooking`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_IdBooking` FOREIGN KEY (`idBooking`) REFERENCES `'.RESA_Booking::getTableName().'` (`id`) ON DELETE CASCADE;';
	}

	/**
	 * return the delete query
	 */
	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * return the number of appointments by ids services
	 */
	public static function countAppointmentsWithIdsServices($idsServices = array())
	{
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE idService IN (\''.join("','",$idsServices).'\') AND state<>\'updated\'');
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function countAllData($data = array()){
		global $wpdb;
		$filterPlace = '';
		if(isset($data['idPlace'])){
			$idPlaces = $data['idPlace'][0];
			unset($data['idPlace']);
			$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		}
		$WHERE = RESA_Tools::generateWhereClause($data) . $filterPlace;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' '.$WHERE);
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function numbersAllData($data = array()){
		global $wpdb;
		$filterPlace = '';
		if(isset($data['idPlace'])){
			$idPlaces = $data['idPlace'][0];
			unset($data['idPlace']);
			$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		}
		$WHERE = RESA_Tools::generateWhereClause($data) . $filterPlace;
		$count = $wpdb->get_var('SELECT SUM(numbers) FROM '. self::getTableName() . ' '.$WHERE);
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function totalPricesAllData($data = array()){
		global $wpdb;
		$filterPlace = '';
		if(isset($data['idPlace'])){
			$idPlaces = $data['idPlace'][0];
			unset($data['idPlace']);
			$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		}
		$WHERE = RESA_Tools::generateWhereClause($data) . $filterPlace;
		$count = $wpdb->get_var('SELECT SUM(totalPrice) FROM '. self::getTableName() . ' '.$WHERE);
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAppointmentsExpired($stateParameters) {
		$idBookings = array();
		global $wpdb;
		$actualDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$results = $wpdb->get_results('SELECT '.self::getTableName().'.id, '.self::getTableName().'.idBooking FROM `'.self::getTableName().'`
			INNER JOIN '.RESA_Booking::getTableName().' ON '.self::getTableName().'.idBooking = '.RESA_Booking::getTableName().'.id AND DATE_ADD('.RESA_Booking::getTableName().'.creationDate, INTERVAL 15 MINUTE) < \''. $actualDate .'\' AND '.RESA_Booking::getTableName().'.paymentState=\'noPayment\' AND '.RESA_Booking::getTableName().'.typePaymentChosen<>\'onTheSpot\' AND '.RESA_Booking::getTableName().'.typePaymentChosen<>\'later\' AND '.RESA_Booking::getTableName().'.typePaymentChosen<>\'swikly\' AND  '.RESA_Booking::getTableName().'.idCustomer='.RESA_Booking::getTableName().'.idUserCreator AND '.RESA_Booking::getTableName().'.quotation=0 AND '.self::getTableName().'.idParameter = '.$stateParameters->id.' AND '.self::getTableName().'.state=\''.$stateParameters->state.'\'');
		if(count($results) > 0){
			$idAppointments = array();
			foreach($results as $result){
				array_push($idAppointments, $result->id);
				if(!in_array($result->idBooking, $idBookings)){
					array_push($idBookings, $result->idBooking);
				}
			}
			$WHERE = RESA_Tools::generateWhereClause(array('id' => array($idAppointments)));
			$wpdb->query('UPDATE '. self::getTableName() . ' SET state=\'abandonned\' ' . $WHERE .' ORDER BY id');
		}
		return $idBookings;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY startDate');
		foreach($results as $result){
			$entity = new RESA_Appointment();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setAppointmentNumberPrices(RESA_AppointmentNumberPrice::getAllData(array('idAppointment'=>$entity->getId())));
			$entity->setAppointmentMembers(RESA_AppointmentMember::getAllData(array('idAppointment'=>$entity->getId())));
			$entity->setAppointmentReductions(RESA_AppointmentReduction::getAllData(array('idAppointment'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}
	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithLimit($data, $limit, $idPlaces = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		if(count($idPlaces) > 0){
			$WHERE .= RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', '');
		}

		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY startDate LIMIT ' .$limit);
		foreach($results as $result){
			$entity = new RESA_Appointment();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setAppointmentNumberPrices(RESA_AppointmentNumberPrice::getAllData(array('idAppointment'=>$entity->getId())));
			$entity->setAppointmentMembers(RESA_AppointmentMember::getAllData(array('idAppointment'=>$entity->getId())));
			$entity->setAppointmentReductions(RESA_AppointmentReduction::getAllData(array('idAppointment'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataInterval($startDate, $endDate, $updated = true)
	{
		$allData = array();
		global $wpdb;
		$closeWhere = '(startDate <= \''.$startDate.'\' AND \''.$startDate.'\' < endDate ) OR
			(startDate < \''.$endDate.'\' AND \''.$endDate.'\' < endDate ) OR
			(\''.$startDate.'\' <= startDate  AND endDate <= \''.$endDate.'\') OR
			(\''.$startDate.'\' < startDate  AND startDate < \''.$endDate.'\')';

		if(!$updated){
			$closeWhere = '('.$closeWhere.') AND state<>\'updated\'';
		}
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . '
			WHERE '.$closeWhere.' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Appointment();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setAppointmentNumberPrices(RESA_AppointmentNumberPrice::getAllData(array('idAppointment'=>$entity->getId())));
			$entity->setAppointmentMembers(RESA_AppointmentMember::getAllData(array('idAppointment'=>$entity->getId())));
			//$entity->setAppointmentReductions(RESA_AppointmentReduction::getAllData(array('idAppointment'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}


	public static function getMinMaxDate(){
		global $wpdb;
		$resultsMax = $wpdb->get_results('SELECT MAX(startDate) as startDate FROM '. self::getTableName());
		$maxDate = date('Y-m-d H:i:s', current_time('timestamp'));
		if(isset($resultsMax[0]->startDate)){
			$maxDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d H:i:s', $resultsMax[0]->startDate));
			$maxDate->add(new DateInterval('P1D'));
			$maxDate = $maxDate->format('Y-m-d H:i:s');
		}
		$resultsMin = $wpdb->get_results('SELECT MIN(startDate) as startDate FROM '. self::getTableName());
		$minDate = date('Y-m-d H:i:s', current_time('timestamp'));
		if(isset($resultsMin[0]->startDate)){
			$minDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d H:i:s', $resultsMin[0]->startDate));
			$minDate->sub(new DateInterval('P1D'));
			$minDate = $minDate->format('Y-m-d H:i:s');
		}
		return array('minDate' => $minDate, 'maxDate' => $maxDate);
	}

	public static function getIdBookings($startDate, $endDate, $state, $payments, $nbByPage, $page, $idPlaces){
		global $wpdb;
		$whereState = RESA_Tools::generateWhereClause(array('appointment.state' => array($state)), false);
		$wherePayments = RESA_Tools::generateWhereClause(array('booking.paymentState' => array($payments)), false);

		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$count = $wpdb->get_var('SELECT COUNT(DISTINCT appointment.idBooking) FROM '. self::getTableName() . ' AS appointment
			INNER JOIN '. RESA_Booking::getTableName() . ' AS booking ON booking.id=appointment.idBooking AND appointment.startDate >= \''.$startDate.'\' AND appointment.endDate <= \''.$endDate.'\' AND appointment.state<>\'updated\' AND '.$whereState.' AND '.$wherePayments.' '. $filterPlace);
		if(!isset($count)) $count = 0;
		$count = intval($count);

		$base = $page * $nbByPage;
		$results = $wpdb->get_results('SELECT appointment.idBooking FROM '. self::getTableName() . ' AS appointment
			INNER JOIN '. RESA_Booking::getTableName() . ' AS booking ON booking.id=appointment.idBooking AND appointment.startDate >= \''.$startDate.'\' AND appointment.endDate <= \''.$endDate.'\' AND appointment.state<>\'updated\' AND '.$whereState.' AND '.$wherePayments.' '. $filterPlace.' GROUP BY appointment.idBooking ORDER BY appointment.startDate LIMIT '.$base.','.($nbByPage));
		return array('idBookings' => $results, 'max' => $count);
	}

	/**
	 * members
	 */
	public static function getIdBookingsWithMember($startDate, $endDate, $state, $payments, $nbByPage, $page, $idMember, $idPlaces){
		global $wpdb;
		$whereState = RESA_Tools::generateWhereClause(array('appointment.state' => array($state)), false);
		$wherePayments = RESA_Tools::generateWhereClause(array('booking.paymentState' => array($payments)), false);

		$filterPlace = (count($idPlaces)>0)?RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', ''):'';
		$count = $wpdb->get_var('SELECT COUNT(DISTINCT appointment.idBooking) FROM '. self::getTableName() . ' as appointment
		INNER JOIN '. RESA_AppointmentMember::getTableName() . ' as member ON member.idAppointment=appointment.id AND member.idMember='.$idMember.'
		INNER JOIN '. RESA_Booking::getTableName() . ' AS booking ON booking.id=appointment.idBooking AND appointment.startDate >= \''.$startDate.'\' AND appointment.endDate <= \''.$endDate.'\' AND appointment.state<>\'updated\' AND '.$whereState.' AND '.$wherePayments.' '. $filterPlace);
		if(!isset($count)) $count = 0;
		$count = intval($count);

		$base = $page * $nbByPage;
		$results = $wpdb->get_results('SELECT idBooking FROM '. self::getTableName() . ' as appointment
		INNER JOIN '. RESA_AppointmentMember::getTableName() . ' as member ON member.idAppointment=appointment.id AND member.idMember='.$idMember.'
		INNER JOIN '. RESA_Booking::getTableName() . ' AS booking ON booking.id=appointment.idBooking AND appointment.startDate >= \''.$startDate.'\' AND appointment.endDate <= \''.$endDate.'\' AND appointment.state<>\'updated\' AND '.$whereState.' AND '.$wherePayments.' '. $filterPlace.' GROUP BY idBooking ORDER BY startDate LIMIT '.$base.','.($nbByPage));
		return array('idBookings' => $results, 'max' => $count);
	}

	/**
	 * Return all entities of this type
	 */
	public static function getCountAppointmentsAndGroupByIdServices($data, $idPlaces = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		if(count($idPlaces) > 0){
			$WHERE .= RESA_Tools::generateWhereWithPlace($idPlaces, 'idPlace', '', '');
		}
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT COUNT('. RESA_Service::getTableName() . '.id) AS numbers, '. RESA_Service::getTableName() . '.id as idService, DATE(startDate) AS date, name, color FROM `'. self::getTableName() . '` INNER JOIN '. RESA_Service::getTableName() . ' ON ('. RESA_Service::getTableName() . '.id = '. self::getTableName() . '.idService AND '. RESA_Service::getTableName() . '.oldService=0) OR ((linkOldServices=CONCAT(\'\', '. self::getTableName() . '.idService) OR linkOldServices LIKE CONCAT(\'%,\', '. self::getTableName() . '.idService, \'%\') OR linkOldServices LIKE CONCAT(\'%\', '. self::getTableName() . '.idService, \',%\')) AND oldService=0) '.$WHERE .' GROUP BY '. RESA_Service::getTableName() . '.id, DATE(startDate)');
		$allData = array();
		foreach($results as $result){
			$formated = $result;
			$formated->name = RESA_Tools::getTextByLocale(unserialize($formated->name), get_locale());
			array_push($allData, $formated);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllIdAppointments($data){
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT id, idBooking  FROM `'. self::getTableName() . '` '.$WHERE .' GROUP BY idBooking');
		$allData = array();
		foreach($results as $result){
			$formated = $result;
			array_push($allData, $formated);
		}
		return $allData;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idBooking = -1;
		$this->idService = -1;
		$this->idPlace = '';
		$this->presentation = '';
		$this->startDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->endDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->state = 'ok';
		$this->idParameter = -1;
		$this->noEnd = false;
		$this->numbers = 0;
		$this->totalPrice = 0;
		$this->totalPriceWithoutReductions = 0;
		$this->participants = [];
		$this->tags = [];
		$this->internalIdLink = -1;
		$this->calendarId = '';
		$this->beforeAppointmentNotified = false;
		$this->afterAppointmentNotified = false;
		$this->appointmentNumberPrices = array();
		$this->appointmentMembers = array();
		$this->appointmentReductions = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setAppointmentNumberPrices(RESA_AppointmentNumberPrice::getAllData(array('idAppointment'=>$this->getId())));
		$this->setAppointmentMembers(RESA_AppointmentMember::getAllData(array('idAppointment'=>$this->getId())));
		$this->setAppointmentReductions(RESA_AppointmentReduction::getAllData(array('idAppointment'=>$this->getId())));
		$this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save($verifyCapacity = true)
	{
		if($this->isLoaded())
		{
			$lastAppointment = new RESA_Appointment();
			$lastAppointment->loadById($this->id);
			$lastAppointmentNumberPrices = $lastAppointment->getAppointmentNumberPrices();
			$lastAppointmentMembers = $lastAppointment->getAppointmentMembers();
			$lastAppointmentReductions = $lastAppointment->getAppointmentReductions();
			$this->linkWPDB->update(self::getTableName(), array(
				'idService' => $this->idService,
				'idPlace' => $this->idPlace,
				'presentation' => $this->presentation,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'state' => $this->state,
				'idParameter' => $this->idParameter,
				'noEnd' => $this->noEnd,
				'numbers' => $this->numbers,
				'totalPrice' => $this->totalPrice,
				'totalPriceWithoutReductions' => $this->totalPriceWithoutReductions,
				'participants' => serialize($this->participants),
				'tags' => serialize($this->tags),
				'internalIdLink' => $this->internalIdLink,
				'calendarId' => $this->calendarId,
				'beforeAppointmentNotified' => $this->beforeAppointmentNotified,
				'afterAppointmentNotified' => $this->afterAppointmentNotified
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%f',
				'%f',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d'
			),
			array ('%d'));

			$idAppointmentNumberPrices = array();
			$idAppointmentMembers = array();
			$idAppointmentReductions = array();
			for($i = 0; $i < count($this->appointmentNumberPrices); $i++)
			{
				if(!$this->appointmentNumberPrices[$i]->isNew())
					array_push($idAppointmentNumberPrices, $this->appointmentNumberPrices[$i]->getId());
				$this->appointmentNumberPrices[$i]->save();
			}
			for($i = 0; $i < count($this->appointmentMembers); $i++)
			{
				if(!$this->appointmentMembers[$i]->isNew())
					array_push($idAppointmentMembers, $this->appointmentMembers[$i]->getIdMember());
				$this->appointmentMembers[$i]->save();
			}
			for($i = 0; $i < count($this->appointmentReductions); $i++)
			{
				if(!$this->appointmentReductions[$i]->isNew())
					array_push($idAppointmentReductions, $this->appointmentReductions[$i]->getId());
				$this->appointmentReductions[$i]->save();
			}

			for($i = 0; $i < count($lastAppointmentMembers); $i++) {
				if(!in_array($lastAppointmentMembers[$i]->getIdMember(), $idAppointmentMembers))
					$lastAppointmentMembers[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastAppointmentNumberPrices); $i++) {
				if(!in_array($lastAppointmentNumberPrices[$i]->getId(), $idAppointmentNumberPrices))
					$lastAppointmentNumberPrices[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastAppointmentReductions); $i++) {
				if(!in_array($lastAppointmentReductions[$i]->getId(), $idAppointmentReductions))
					$lastAppointmentReductions[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idBooking' => $this->idBooking,
				'idService' => $this->idService,
				'idPlace' => $this->idPlace,
				'presentation' => $this->presentation,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'state' => $this->state,
				'idParameter' => $this->idParameter,
				'noEnd' => $this->noEnd,
				'numbers' => $this->numbers,
				'totalPrice' => $this->totalPrice,
				'totalPriceWithoutReductions' => $this->totalPriceWithoutReductions,
				'participants' => serialize($this->participants),
				'tags' => serialize($this->tags),
				'internalIdLink' => $this->internalIdLink,
				'calendarId' => $this->calendarId,
				'beforeAppointmentNotified' => $this->beforeAppointmentNotified,
				'afterAppointmentNotified' => $this->afterAppointmentNotified
			),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%f',
				'%f',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d'
			));
			$this->id = $this->linkWPDB->insert_id;
			$idAppointment = $this->id;
			for($i = 0; $i < count($this->appointmentNumberPrices); $i++)
			{
				$this->appointmentNumberPrices[$i]->setIdAppointment($idAppointment);
				$this->appointmentNumberPrices[$i]->save();
			}
			for($i = 0; $i < count($this->appointmentMembers); $i++)
			{
				$this->appointmentMembers[$i]->setIdAppointment($idAppointment);
				$this->appointmentMembers[$i]->save();
			}
			for($i = 0; $i < count($this->appointmentReductions); $i++)
			{
				$this->appointmentReductions[$i]->setIdAppointment($idAppointment);
				$this->appointmentReductions[$i]->save();
			}
			$this->setLoaded(true);
		}
		//Create alert if necessary.
		if($verifyCapacity && !$this->isCancelled() && !$this->isAbandonned()){
			RESA_Algorithms::verifyCapacity($this);
		}
	}

	/**
	 * Save in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded())
		{
			RESA_AppointmentNumberPrice::deleteAllByIdAppointment($this->id);
		  RESA_AppointmentMember::deleteAllByIdAppointment($this->id);
		  RESA_AppointmentReduction::deleteAllByIdAppointment($this->id);
			$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
		}
	}

	public function getAppointmentLiteJSON(){
		$customer = $this->getCustomer();
		$appointment = json_decode($this->toJSON());
		if(isset($customer)) $appointment->customer = json_decode($customer->toJSON());
		$service = new RESA_Service();
		$service->loadById($appointment->idService);
		$appointment->service = $service->getName();
		$appointment->askParticipants = $service->isAskParticipants() == 1;
		$appointment->color = $service->getColor();
		foreach ($appointment->appointmentNumberPrices as $appointmentNumberPrice) {
			$servicePrice = new RESA_ServicePrice();
			$servicePrice->loadById($appointmentNumberPrice->idPrice);
			$appointmentNumberPrice->name = $servicePrice->getName();
			$appointmentNumberPrice->participantsParameter = $servicePrice->getParticipantsParameter();
			$appointmentNumberPrice->equipments = [];
			foreach ($servicePrice->getEquipments() as $idEquipment) {
				$equipment = new RESA_Equipment();
				$equipment->loadById($idEquipment);
				array_push($appointmentNumberPrice->equipments, $equipment->getCurrentName());
			}
		}
		foreach ($appointment->appointmentMembers as $appointmentMember) {
			$member = new RESA_Member();
			$member->loadById($appointmentMember->idMember);
			$appointmentMember->name = $member->getNickname();
		}
		return $json;
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		/*
		TODO
		"totalPrice":'.$this->getNeedToPay().',
		"numbers":'.$this->getNumbers().',
		*/
		$bodyJSON  = '"id":'.$this->id .',
		"idBooking":'.$this->idBooking.',
		"idService":'.$this->idService.',
		"idPlace":"'.$this->idPlace.'",
		"presentation":"'.$this->presentation .'",
		"startDate":"'.$this->startDate .'",
		"endDate":"'.$this->endDate .'",
		"state":"'.$this->state .'",
		"idParameter":'.$this->idParameter.',
		"noEnd":'.RESA_Tools::toJSONBoolean($this->noEnd).',
		"participants":'.json_encode($this->participants).',
		"tags":'.json_encode($this->tags).',
		"internalIdLink":'.$this->internalIdLink.',
		"calendarId":"'.$this->calendarId.'",
		"beforeAppointmentNotified":'.RESA_Tools::toJSONBoolean($this->beforeAppointmentNotified).',
		"afterAppointmentNotified":'.RESA_Tools::toJSONBoolean($this->afterAppointmentNotified).',
		"totalPrice":'.$this->totalPrice.',
		"totalPriceWithoutReductions":'.$this->totalPriceWithoutReductions.',
		"numbers":'.$this->numbers.',
		"appointmentNumberPrices":'.RESA_Tools::formatJSONArray($this->appointmentNumberPrices).',
		"appointmentMembers":'.RESA_Tools::formatJSONArray($this->appointmentMembers).',
		"appointmentReductions":'.RESA_Tools::formatJSONArray($this->appointmentReductions);
		if(isset($this->idClientObject)){
			$bodyJSON .= ',"idClientObject":'.$this->idClientObject;
		}
		return '{'.$bodyJSON.'}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idBooking = $json->idBooking;
		$this->idService = $json->idService;
		$this->idPlace = $json->idPlace;
		$this->presentation = $json->presentation;
		$this->startDate = $json->startDate;
		$this->endDate = $json->endDate;
		$this->state = $json->state;
		if(isset($json->idParameter)) $this->idParameter = $json->idParameter;
		if(isset($json->noEnd)) $this->noEnd = $json->noEnd;
		if(isset($json->totalPrice)) $this->totalPrice = $json->totalPrice;
		if(isset($json->totalPriceWithoutReductions)) $this->totalPriceWithoutReductions = $json->totalPriceWithoutReductions;
		if(isset($json->numbers)) $this->numbers = $json->numbers;
		if(isset($json->participants)) {
			if(is_array($json->participants)){
				$this->participants = $json->participants;
			}
			else if($json->participants!=false) {
				$this->participants = unserialize($json->participants);
			}
		}
		if(isset($json->tags)) {
			if(is_array($json->tags)){
				$this->tags = $json->tags;
			}
			else if($json->tags!=false) {
				$this->tags = unserialize($json->tags);
			}
		}
		if(isset($json->internalIdLink)) $this->internalIdLink = $json->internalIdLink;
		else $this->internalIdLink = $this->id;
		$this->calendarId = $json->calendarId;
		if(isset($json->afterAppointmentNotified)){
			$this->afterAppointmentNotified = $json->afterAppointmentNotified;
		}
		if(isset($json->appointmentNumberPrices))
		{
			$appointmentNumberPrices = array();
			for($i = 0; $i < count($json->appointmentNumberPrices); $i++)
			{
				$appointmentNumberPrice = new RESA_AppointmentNumberPrice();
				$appointmentNumberPrice->fromJSON($json->appointmentNumberPrices[$i]);
				array_push($appointmentNumberPrices, $appointmentNumberPrice);
			}
			$this->setAppointmentNumberPrices($appointmentNumberPrices);
		}
		if(isset($json->appointmentMembers))
		{
			$appointmentMembers = array();
			for($i = 0; $i < count($json->appointmentMembers); $i++)
			{
				$appointmentMember = new RESA_AppointmentMember();
				$appointmentMember->fromJSON($json->appointmentMembers[$i]);
				array_push($appointmentMembers, $appointmentMember);
			}
			$this->setAppointmentMembers($appointmentMembers);
		}
		if(isset($json->appointmentReductions))
		{
			$appointmentReductions = array();
			for($i = 0; $i < count($json->appointmentReductions); $i++)
			{
				$appointmentReduction = new RESA_AppointmentReduction();
				$appointmentReduction->fromJSON($json->appointmentReductions[$i]);
				array_push($appointmentReductions, $appointmentReduction);
			}
			$this->setAppointmentReductions($appointmentReductions);
		}
		if(isset($json->idClientObject)){
			$this->idClientObject = $json->idClientObject;
		}
		if($this->id != -1)	$this->setLoaded(true);
	}



	/**
	 * Update id service
	 */
	public function updateService($oldService, $newService)	{
		if($this->getIdService() == $oldService->getId()){
			$this->setIdService($newService->getId());
			$linkOldNewPrice = array();
			foreach($oldService->getServicePrices() as $oldServicePrice){
				foreach($newService->getServicePrices() as $newServicePrice){
					if($oldServicePrice->getSlug() == $newServicePrice->getSlug()){
						array_push($linkOldNewPrice, array($oldServicePrice->getId(), $newServicePrice->getId()));
					}
				}
			}
			foreach($this->getAppointmentNumberPrices() as $appointmentNumberPrice){
				foreach($linkOldNewPrice as $link){
					$appointmentNumberPrice->updatePriceId($link[0], $link[1]);
				}
			}
			foreach($this->getAppointmentReductions() as $appointmentReductions){
				foreach($linkOldNewPrice as $link){
					$appointmentReductions->updatePriceId($link[0], $link[1]);
				}
			}
		}
	}


	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->appointmentNumberPrices as $appointmentNumberPrices){
			$appointmentNumberPrices->setNew();
		}
		foreach($this->appointmentMembers as $appointmentMember){
			$appointmentMember->setNew();
		}
		foreach($this->appointmentReductions as $appointmentReduction){
			$appointmentReduction->setNew();
		}
	}
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdPlace($idPlace){ $this->idPlace = $idPlace; }
	public function setPresentation($presentation){ $this->presentation = $presentation; }
	public function setStartDate($startDate){ $this->startDate = $startDate; }
	public function setEndDate($endDate){ $this->endDate = $endDate; }
	public function setState($state){ $this->state = $state; }
	public function ok(){ $this->state = 'ok'; }
	public function cancel(){ $this->state = 'cancelled'; }
	public function waiting(){ $this->state = 'waiting'; }
	public function abandonned(){ $this->state = 'abandonned'; }
	public function setIdParameter($idParameter){ $this->idParameter = $idParameter; }
	public function setNoEnd($noEnd){ $this->noEnd = $noEnd; }
	public function setTotalPrice($totalPrice){ $this->totalPrice = $totalPrice; }
	public function setTotalPriceWithoutReduction($totalPriceWithoutReductions){ $this->totalPriceWithoutReductions = $totalPriceWithoutReductions; }
	public function setNumbers($numbers){ $this->numbers = $numbers; }
	public function setParticipants($participants){ $this->participants = $participants; }
	public function setTags($tags){ $this->tags = $tags; }
	public function setInternalIdLink($internalIdLink){ $this->internalIdLink = $internalIdLink; }
	public function addTag($tag){ array_push($this->tags, $tag); }
	public function setCalendarId($calendarId){ $this->calendarId = $calendarId; }
	public function setBeforeAppointmentNotified($beforeAppointmentNotified){ $this->beforeAppointmentNotified = $beforeAppointmentNotified; }
	public function setAfterAppointmentNotified($afterAppointmentNotified){ $this->afterAppointmentNotified = $afterAppointmentNotified; }
	public function setAppointmentNumberPrices($appointmentNumberPrices){ $this->appointmentNumberPrices = $appointmentNumberPrices; }
	public function setAppointmentMembers($appointmentMembers){ $this->appointmentMembers = $appointmentMembers; }
	public function setAppointmentReductions($appointmentReductions){ $this->appointmentReductions = $appointmentReductions; }
	public function setIdClientObject($idClientObject){ $this->idClientObject = $idClientObject; }


	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getIdService(){ return $this->idService; }
	public function getIdPlace(){ return $this->idPlace; }
	public function getPresentation(){ return $this->presentation; }
	public function getStartDate(){ return $this->startDate; }
	public function getEndDate(){ return $this->endDate; }
	public function getState(){ return $this->state; }
	public function isCancelled(){ return $this->state == 'cancelled'; }
	public function isOk(){ return $this->state == 'ok'; }
	public function isUpdated(){ return $this->state == 'updated'; }
	public function isWaiting(){ return $this->state == 'waiting'; }
	public function isAbandonned(){ return $this->state == 'abandonned'; }
	public function getIdParameter(){ return $this->idParameter; }
	public function isNoEnd(){ return $this->noEnd; }
	public function getTotalPrice(){
		$total = 0;
		if($this->isOk() || $this->isWaiting()){
			$total = $this->totalPrice;
		}
		return $total;
	}
	public function getTotalPriceWithoutReduction(){ return $this->totalPriceWithoutReductions; }
	public function getNumbers(){ return $this->numbers; }

	public function getParticipants(){
		$participants = [];
		$participants = array_merge($participants, $this->participants);
		foreach($this->appointmentNumberPrices as $appointmentNumberPrice){ $participants = array_merge($participants, $appointmentNumberPrice->getParticipants()); }
		return $participants;
	}
	public function getUriParticipants(){
		$idParticipants = [];
		foreach($this->getAppointmentNumberPrices() as $appointmentNumberPrice){
			for($i = 0; $i < count($appointmentNumberPrice->getParticipants()); $i++){
				$participant = $appointmentNumberPrice->getParticipants()[$i];
				array_push($idParticipants, $participant->uri);
			}
		}
		return $idParticipants;
	}
	public function getNumberParticipants(){
		return is_array($this->participants)?count($this->participants):0;
	}
	public function getTags(){ return $this->tags; }
	public function getInternalIdLink(){ return $this->internalIdLink; }
	public function getCalendarId(){ return $this->calendarId; }
	public function isAfterAppointmentNotified(){ return $this->afterAppointmentNotified; }
	public function isBeforeAppointmentNotified(){ return $this->beforeAppointmentNotified; }
	public function getAppointmentNumberPrices(){ return $this->appointmentNumberPrices; }
	public function getAppointmentMembers(){ return $this->appointmentMembers; }
	public function getAppointmentReductions(){ return $this->appointmentReductions; }
	public function getIdClientObject(){ return $this->idClientObject; }
	public function cloneMe(){
		$newAppointment = new RESA_Appointment();
		$newAppointment->fromJSON(json_decode($this->toJSON()));
		return $newAppointment;
	}

	public function getCustomer(){
		$booking = new RESA_Booking();
		$booking->loadById($this->getIdBooking());
		if($booking->isLoaded()){
			$customer = new RESA_Customer();
			$customer->loadById($booking->getIdCustomer());
			if($customer->isLoaded()){
				return $customer;
			}
		}
		return null;
	}


	public function generateParticipantUri($idCustomer){
		foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
			$appointmentNumberPrice->generateParticipantUri($idCustomer);
		}
	}

	public function updateIdReduction($oldId, $newId){
		foreach($this->appointmentReductions as $appointmentReduction){
			if($appointmentReduction->getIdReduction() == $oldId){
				$appointmentReduction->setIdReduction($newId);
			}
		}
	}

	public function updateIdMember($oldId, $newId){
		foreach($this->appointmentMembers as $appointmentMember){
			if($appointmentMember->getIdMember() == $oldId){
				$appointmentMember->setIdMember($newId);
			}
		}
	}

	public function mergeAppointment($appointment){
		$this->setNumbers($this->getNumbers() + $appointment->getNumbers());
		foreach($appointment->getAppointmentNumberPrices() as $anotherAppointmentNumberPrice){
			$appointmentNumberPrice = $this->getAppointmentNumberPriceByIdPrice($anotherAppointmentNumberPrice->getIdPrice());
			if(isset($appointmentNumberPrice)){
				$appointmentNumberPrice->setNumber($appointmentNumberPrice->getNumber() + $anotherAppointmentNumberPrice->getNumber());
			}else {
				array_push($this->appointmentNumberPrices, $anotherAppointmentNumberPrice);
			}
		}
	}

	public function getHours(){
		$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getStartDate());
		$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getEndDate());
		return ($endDate->getTimestamp() - $startDate->getTimestamp()) / 3600;
	}

	public function isSameDate($appointment){
		$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
		$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
		$thisStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getStartDate());
		$thisEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getEndDate());
		return RESA_Tools::resetTimeToDate($startDate) == RESA_Tools::resetTimeToDate($thisStartDate) &&
			RESA_Tools::resetTimeToDate($endDate) == RESA_Tools::resetTimeToDate($thisEndDate);
	}

	/**
	 * Is date passed
	 */
	public function isDatePassed(){
		$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getEndDate());
		$actualDate = DateTime::createFromFormat('Y-m-d H:i:s',date('Y-m-d H:i:s', current_time('timestamp')));
		return $actualDate > $endDate;
	}

	/**
	 * Change the state after payment
	 */
	public function changeStateAfterPayment($statesParameters, $backend){
		if($this->idParameter != -1 && is_array($statesParameters)){
			$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
			if($backend){
				if((isset($parameter) && isset($parameter->paiementStateBackend))){
					$this->state = $parameter->paiementStateBackend;
				}
			}
			else {
				if((isset($parameter) && isset($parameter->paiementState))){
					$this->state = $parameter->paiementState;
				}
			}
		}
		else $this->state = 'ok';
	}

	/**
	 * Change to expiration
	 */
	public function changeExpirationState($statesParameters, $backend){
		if($this->idParameter != -1 && is_array($statesParameters)){
			$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
			if($backend){
				if(isset($parameter) && $this->state == $parameter->stateBackend && isset($parameter->expiredBackend) && $parameter->expiredBackend){
					$this->state = 'abandonned';
				}
			}
			else {
				if(isset($parameter) && $this->state == $parameter->state && isset($parameter->expired) && $parameter->expired){
					$this->state = 'abandonned';
				}
			}
		}
	}

	/**
	 * \fn getAppointmentNumberPriceByIdPrice
	 * \brief return the appointmentNumberPrice
	 */
	public function getAppointmentNumberPriceByIdPrice($idPrice){
		foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
			if($appointmentNumberPrice->getIdPrice() === $idPrice)
				return $appointmentNumberPrice;
		}
		return null;
	}

	/**
	 * calculate numbers of persons
	 */
	public function calculateNumbers(){
		$numbers = 0;
		foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
			$price = new RESA_ServicePrice();
			$price->loadById($appointmentNumberPrice->getIdPrice());
			if($price->isLoaded() && !$price->isExtra()){
				$numbers += $appointmentNumberPrice->getNumber();
			}
		}
		return $numbers;
	}

	/**
	 * Calculate the total price without reduction
	 */
	public function calculateTotalPriceWithoutReduction(){
		$total = 0;
		$service = new RESA_Service();
		$service->loadById($this->idService);
		if($service->isLoaded()){
			foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
				$price = $service->getServicePriceById($appointmentNumberPrice->getIdPrice());
				if(isset($price) && !$appointmentNumberPrice->isDeactivated()){
					$total += $price->getTotalPrice($appointmentNumberPrice->getNumber(), $this->getHours());
				}
			}
		}
		return $total;
	}

	/**
	 * \fn calculateTotalPriceForm
	 * \brief calculate the total price function of idParameter
	 * \return int value
	 */
	public function calculateTotalPriceForm($statesParameters, $isAdvancePayment, $customer){
		$totalPriceForm = 0;
		if($this->isOK() || $this->idParameter == -1 || !is_array($statesParameters)){
			if($isAdvancePayment){
				$totalPriceForm = $this->calculateAdvancePayment();
			}
			else {
				$totalPriceForm = $this->totalPrice;
			}
		}
		else {
			$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
			if(isset($parameter) && isset($parameter->paiement) && $parameter->paiement){
				if($isAdvancePayment){
					$totalPriceForm = $this->calculateAdvancePayment($customer);
				}
				else {
					$totalPriceForm = $this->totalPrice;
				}
			}
		}
		return $totalPriceForm;
	}

	/**
	 * can calculate price add with default slug service price
	 */
	public function canCalculatePrice($service, $appointmentNumberPrice, $allSlugs){
		$servicePrice = $service->getDefaultServicePrice();
		if($servicePrice == null) return true;
		if(!in_array($servicePrice->getSlug(), $allSlugs)) return true;
		return $servicePrice->getId() == $appointmentNumberPrice->getIdPrice();
	}

	/**
	 * Calculate the total price
	 */
	public function calculateTotalPrice(){
		$needToPay = 0;
		$service = new RESA_Service();
		$service->loadById($this->idService);
		if($service->isLoaded()){
			foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
				$price = $service->getServicePriceById($appointmentNumberPrice->getIdPrice());
				if(isset($price) && !$appointmentNumberPrice->isDeactivated()){
					$numberPrice = $price->getTotalPrice($appointmentNumberPrice->getNumber(), $this->getHours());
					$reductionNumber = 0;
					$reductionPurcent = 0;
					foreach($this->appointmentReductions as $appointmentReduction){
						if($appointmentReduction->getIdPrice() == -1 || $appointmentReduction->getIdPrice() == $appointmentNumberPrice->getIdPrice()){
							$number = $appointmentReduction->getNumber();
							if($number == 0){
								//OLD version
								if($appointmentReduction->getType() == 0){
									if($appointmentReduction->getValue() < 0) $reductionNumber -= $appointmentReduction->getValue();
									else $reductionNumber += $appointmentReduction->getValue();
								}
								else if($appointmentReduction->getType() == 1){
									$reductionPurcent += $appointmentReduction->getValue();
								}
								else if($appointmentReduction->getType() == 2){
									if($appointmentReduction->getValue() < 0) $reductionNumber -= $appointmentReduction->getValue() * $appointmentNumberPrice->getNumber();
									else $reductionNumber += $appointmentReduction->getValue() * $appointmentNumberPrice->getNumber();
								}
								else if($appointmentReduction->getType() == 3){
									if($appointmentReduction->getValue() < 0) $numberPrice = -1 * $appointmentReduction->getValue() * $appointmentNumberPrice->getNumber();
									else $numberPrice = $appointmentReduction->getValue() * $appointmentNumberPrice->getNumber();
								}
								else if($appointmentReduction->getType() == 4){
									$unitPrice = $price->getPrice();
									if($price->isThresholded()){
										$unitPrice = $price->getThresholdPriceByNumber($appointmentNumberPrice->getNumber(), $this->getHours());
									}
									if($appointmentReduction->getValue() < 0){
										$reductionNumber += min(-1 * $appointmentReduction->getValue(), $appointmentNumberPrice->getNumber()) * $unitPrice;
									}
									else $reductionNumber += min($appointmentReduction->getValue(), $appointmentNumberPrice->getNumber()) *  $unitPrice;
								}
							}else {
								if($appointmentReduction->getType() == 0){
									if($appointmentReduction->getValue() < 0) $reductionNumber -= $appointmentReduction->getValue() * $number;
									else $reductionNumber += $appointmentReduction->getValue() * $number;
								}
								else if($appointmentReduction->getType() == 1){
									$reductionPurcent += $appointmentReduction->getValue();
								}
								else if($appointmentReduction->getType() == 2){
									if($appointmentReduction->getValue() < 0) $reductionNumber -= $appointmentReduction->getValue() * $number;
									else $reductionNumber += $appointmentReduction->getValue() * $number;
								}
								else if($appointmentReduction->getType() == 3){
									$numberPrice = $price->getPrice() * ($appointmentNumberPrice->getNumber() - $number);
										$numberPrice += $appointmentReduction->getValue() * $number;
								}
								else if($appointmentReduction->getType() == 4){
									$unitPrice = $price->getPrice();
									if($price->isThresholded()){
										$unitPrice = $price->getThresholdPriceByNumber($appointmentNumberPrice->getNumber(), $this->getHours()) / $appointmentNumberPrice->getNumber();
									}
									if($appointmentReduction->getValue() < 0){
										$reductionNumber += -1 * $appointmentReduction->getValue() * $number * $unitPrice;
									}
									else $reductionNumber += $appointmentReduction->getValue() * $number *  $unitPrice;
								}
							}
						}
					}
					$numberPrice -= $numberPrice * $reductionPurcent / 100;
					$numberPrice -= $reductionNumber;
					$needToPay += $numberPrice;
				}
			}
		}
		return $needToPay;
	}

	/**
	 * Calculate advance payment
	 */
	public function calculateAdvancePayment($customer){
		$advancePayment = 0;
		if($this->totalPrice > 0){
			$service = new RESA_Service();
			$service->loadById($this->idService);
			$advancePayment = $this->totalPrice;
			if($service->isLoaded()){
				$advancePayment = $this->totalPrice - ($this->totalPrice * (100 - $service->getAdvancePaymentWithCustomer($customer)) / 100);
			}
		}
		return $advancePayment;
	}

	/**
	 * Calculate the need to pay without reductions
	 * TO DELETE
	 */
	public function getNeedToPayWithoutReductions($advancePayment = false){
		$needToPay = 0;
		if($this->isOk() || $this->isWaiting()){
			$service = new RESA_Service();
			$service->loadById($this->idService);
			if($service->isLoaded()){
				foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
					$price = new RESA_ServicePrice();
					$price->loadById($appointmentNumberPrice->getIdPrice());
					if($price->isLoaded()){
						$needToPay += $price->getTotalPrice($appointmentNumberPrice->getNumber(), $this->getHours());
					}
				}
			}
		}
		return $needToPay;
	}

	/**
	 * \brief return equipments used
	 */
	public function getCapacityOfEquipments($service, $equipments = []){
		foreach($this->appointmentNumberPrices as $appointmentNumberPrice){
			$price = new RESA_ServicePrice();
			$price->loadById($appointmentNumberPrice->getIdPrice());
			if($price->isLoaded()){
				$found = false;
				$equipmentsPrice = $price->getEquipments();
				if(count($equipmentsPrice) > 0){
					$idEquipment = $equipmentsPrice[0];
					for($i = 0; $i < count($equipments); $i++){
						$equipment = $equipments[$i];
						if($equipment['idEquipment'] == $idEquipment){
							$equipments[$i]['number'] += $appointmentNumberPrice->getNumber();
							$found = true;
						}
					}
					if(!$found){
						array_push($equipments, array('idEquipment' => $idEquipment, 'number' => $appointmentNumberPrice->getNumber()));
					}
				}
			}
		}
		return $equipments;
	}

	/**
	 * \brief return the capacity used for member
	 */
	public function getMemberCapacityUsed($idMember){
		$capacity = 0;
		foreach($this->appointmentMembers as $appointmentMember){
			if($appointmentMember->getIdMember() == $idMember){
				$capacity += $appointmentMember->getNumber();
			}
		}
		return $capacity;
	}

	/**
	 * \brief return true if member is associated to this appointment
	 */
	public function haveMember($idMember){
		foreach($this->appointmentMembers as $appointmentMember){
			if($appointmentMember->getIdMember() == $idMember){
				return true;
			}
		}
		return false;
	}

	static public function compare($appointment1, $appointment2){
		return $appointment2->getIdService() - $appointment1->getIdService();
	}

	static public function compareEndDate($appointment1, $appointment2){
		$endDate1 = DateTime::createFromFormat('Y-m-d H:i:s', $appointment1->getEndDate());
		$endDate2 = DateTime::createFromFormat('Y-m-d H:i:s', $appointment2->getEndDate());
		if ($endDate1 == $endDate2) { return 0; }
  	return $endDate1 < $endDate2 ? -1 : 1;
	}

	/**
	 * return true if the idParameter define capacity field
	 */
	public function stateCountCapacity($statesParameters, $backend){
		$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
		if($backend) return !$this->isUpdated() && $this->idParameter != -1 && isset($parameter) && $parameter->capacityBackend && $parameter->stateBackend == $this->state;
		return !$this->isUpdated() && $this->idParameter != -1 && isset($parameter) && $parameter->capacity && $parameter->state == $this->state;
	}

	/**
	 * return true if the idParameter define capacity field
	 */
	public function statePayment($statesParameters){
		$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
		return !$this->isUpdated() && $this->idParameter != -1 && isset($parameter) && $parameter->paiement && $parameter->state == $this->state;
	}

	/**
	 * return true if the idParameter define expired field
	 */
	public function stateExpire($statesParameters, $backend){
		$parameter = RESA_Tools::getParameterById($statesParameters, $this->idParameter);
		if($backend) return !$this->isUpdated() && $this->idParameter != -1 && isset($parameter) && $parameter->expiredBackend && $parameter->stateBackend == $this->state;
		return !$this->isUpdated() && $this->idParameter != -1 && isset($parameter) && $parameter->expired && $parameter->state == $this->state;
	}

	public function savePrices(){
		$this->totalPrice = $this->calculateTotalPrice();
		$this->totalPriceWithoutReductions = $this->calculateTotalPriceWithoutReduction();
		return $this->totalPrice;
	}
}
