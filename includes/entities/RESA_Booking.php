<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_Booking extends RESA_EntityDTO
{
	private $id;
	private $idCreation;
	private $idCustomer;
	private $idUserCreator;
	private $creationDate;
	private $modificationDate;
	private $tva;
	private $typePaymentChosen;
	private $transactionId;
	private $totalPrice;
	private $advancePayment;
	private $paymentState;
	private $quotation;
	private $quotationRequest;
	private $note;
	private $publicNote;
	private $staffNote;
	private $customerNote;
	private $alreadySentEmail;
	private $lastDateSentEmailQuotation;
	private $numberSentEmailQuotation;
	private $allowInconsistencies;
	private $oldBooking;
	private $linkOldBookings;
	private $appointments;
	private $payments;
	private $askPayments;
	private $bookingReductions;
	private $bookingCustomReductions;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_booking';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idCreation` int(11) NOT NULL,
		  `idCustomer` int(11) NOT NULL,
		  `idUserCreator` int(11) NOT NULL,
		  `creationDate` datetime NOT NULL,
		  `modificationDate` datetime NOT NULL,
		  `tva` int(11) NOT NULL,
		  `typePaymentChosen` TEXT NOT NULL,
		  `transactionId` TEXT NOT NULL,
		  `totalPrice` FLOAT NOT NULL,
		  `advancePayment` FLOAT NOT NULL,
		  `paymentState` enum(\'noPayment\',\'advancePayment\',\'deposit\',\'complete\',\'over\') NOT NULL,
		  `quotation` tinyint(1) NOT NULL,
		  `quotationRequest` tinyint(1) NOT NULL,
		  `close` tinyint(1) NOT NULL,
		  `note` TEXT NOT NULL,
		  `publicNote` TEXT NOT NULL,
		  `staffNote` TEXT NOT NULL,
		  `customerNote` TEXT NOT NULL,
		  `alreadySentEmail` tinyint(1) NOT NULL,
		  `lastDateSentEmailQuotation` datetime NOT NULL,
		  `numberSentEmailQuotation` int(11) NOT NULL,
		  `allowInconsistencies` tinyint(1) NOT NULL,
		  `oldBooking` tinyint(1) NOT NULL,
		  `linkOldBookings` TEXT NOT NULL,
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

	public static function isQuotationQuery($idBooking){
		global $wpdb;
		$results = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE id='.$idBooking.' AND quotation=1');
		if(isset($results) && $results > 0) return true;
		return false;
	}

	public static function isBookingExpired($idBooking){
		global $wpdb;
		$actualDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$results = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE id='.$idBooking.' AND quotation=0 AND
			DATE_ADD(creationDate, INTERVAL 15 MINUTE) < \''.$actualDate.'\' AND paymentState=\'noPayment\' AND typePaymentChosen<>\'onTheSpot\' AND typePaymentChosen<>\'later\' AND typePaymentChosen<>\'swikly\' AND idCustomer=idUserCreator');
		if(isset($results) && $results > 0) return true;
		return false;
	}

	public static function isBookingBackend($idBooking){
		global $wpdb;
		$results = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE id='.$idBooking.' AND idCustomer<>idUserCreator');
		if(isset($results) && $results > 0) return true;
		return false;
	}


	/**
	 * Return all entities of this type
	 */
	public static function countBookingsOnline($backend) {
		global $wpdb;
		$sign = '=';
		if($backend){
			$sign = '<>';
		}
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE linkOldBookings=\'\' AND idCustomer ' . $sign . ' idUserCreator');
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array('oldBooking'=>false), $startDate = false, $endDate = false, $idPlaces = false)
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		foreach($results as $result){
			$addInResults = true;
			$entity = new RESA_Booking();
			$entity->fromJSON($result);
			$entity->setLoaded(true);

			if($startDate || $endDate || ($idPlaces != false)) {
				$arrayWhereAppointment = array('idBooking'=>$entity->getId());
				if($startDate){
					$arrayWhereAppointment['startDate'] = array($startDate, '>=');
				}
				if($endDate){
					if(isset($arrayWhereAppointment['startDate'])) {
						$arrayWhereAppointment['startDate'] = array('AND', array($arrayWhereAppointment['startDate']));
						array_push($arrayWhereAppointment['startDate'][1], array($endDate, '<='));
					}
					else {
						$arrayWhereAppointment['startDate'] = array($endDate, '<=');
					}
				}
				if($idPlaces != false && is_array($idPlaces)  && count($idPlaces) > 0){
					$arrayWhereAppointment['idPlace'] = array($idPlaces);
				}
				$addInResults = RESA_Appointment::countAllData($arrayWhereAppointment) > 0;
			}
			if($addInResults) {
				$entity->setAppointments(RESA_Appointment::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setPayments(RESA_Payment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$payments = $entity->getPayments();
					foreach($tokens as $idBooking){
						$oldPayments = RESA_Payment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldPayments as $payment){
							$payment->setIdBooking($entity->getId());
						}
						$payments = array_merge($payments, $oldPayments);
					}
					$entity->setPayments($payments);
				}
				$entity->setAskPayments(RESA_AskPayment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$askPayments = $entity->getAskPayments();
					foreach($tokens as $idBooking){
						$oldAskPayments = RESA_AskPayment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldAskPayments as $askPayment){
							$askPayment->setIdBooking($entity->getId());
						}
						$askPayments = array_merge($askPayments, $oldAskPayments);
					}
					$entity->setAskPayments($askPayments);
				}
				$entity->setBookingReductions(RESA_BookingReduction::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setBookingCustomReductions(RESA_BookingCustomReduction::getAllData(array('idBooking'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function countDataWithLimit($data, $begin = '', $offset = '')
	{
		global $wpdb;
		$WHERE = RESA_Tools::generateWhereClause($data);
		$limit = '';
		if($begin != '' || $offset!='') $limit = ' LIMIT '.$begin.','.$offset;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' '.$WHERE . $limit);
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithLimit($data, $begin, $offset, $idPlaces = false)
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' LIMIT '.$begin.','.$offset.'');
		foreach($results as $result){
			$addInResults = true;
			$entity = new RESA_Booking();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			if($idPlaces != false && is_array($idPlaces) && count($idPlaces) > 0){
				$arrayWhereAppointment = array('idBooking'=>$entity->getId(), 'idPlace' => array($idPlaces));
				$addInResults = RESA_Appointment::countAllData($arrayWhereAppointment) > 0;
			}
			if($addInResults) {
				$entity->setAppointments(RESA_Appointment::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setPayments(RESA_Payment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$payments = $entity->getPayments();
					foreach($tokens as $idBooking){
						$oldPayments = RESA_Payment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldPayments as $payment){
							$payment->setIdBooking($entity->getId());
						}
						$payments = array_merge($payments, $oldPayments);
					}
					$entity->setPayments($payments);
				}
				$entity->setAskPayments(RESA_AskPayment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$askPayments = $entity->getAskPayments();
					foreach($tokens as $idBooking){
						$oldAskPayments = RESA_AskPayment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldAskPayments as $askPayment){
							$askPayment->setIdBooking($entity->getId());
						}
						$askPayments = array_merge($askPayments, $oldAskPayments);
					}
					$entity->setAskPayments($askPayments);
				}
				$entity->setBookingReductions(RESA_BookingReduction::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setBookingCustomReductions(RESA_BookingCustomReduction::getAllData(array('idBooking'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	public static function getUpdateQuotations($lastModificationDateBooking, $allIdCreation, $idPlaces = false){
		$allData = array();
		global $wpdb;
		$idsCreation = join(",",$allIdCreation);
		if(empty($idsCreation)) $idsCreation = '\'\'';
		$WHERE = 'WHERE oldBooking=false && (quotation=1 || idCreation IN ('.$idsCreation.')) && modificationDate > \'' . $lastModificationDateBooking.'\'';
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' ' . $WHERE);
		foreach($results as $result){
			$addInResults = true;
			$entity = new RESA_Booking();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			if($idPlaces != false && is_array($idPlaces)){
				$arrayWhereAppointment = array('idBooking'=>$entity->getId(), 'idPlace' => array($idPlaces));
				$addInResults = RESA_Appointment::countAllData($arrayWhereAppointment) > 0;
			}
			if($addInResults) {
				$entity->setAppointments(RESA_Appointment::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setPayments(RESA_Payment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$payments = $entity->getPayments();
					foreach($tokens as $idBooking){
						$oldPayments = RESA_Payment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldPayments as $payment){
							$payment->setIdBooking($entity->getId());
						}
						$payments = array_merge($payments, $oldPayments);
					}
					$entity->setPayments($payments);
				}
				$entity->setAskPayments(RESA_AskPayment::getAllData(array('idBooking'=>$entity->getId())));
				if(!empty($entity->getLinkOldBookings())){
					$tokens = explode(',', $entity->getLinkOldBookings());
					$askPayments = $entity->getAskPayments();
					foreach($tokens as $idBooking){
						$oldAskPayments = RESA_AskPayment::getAllData(array('idBooking'=>$idBooking));
						foreach($oldAskPayments as $askPayment){
							$askPayment->setIdBooking($entity->getId());
						}
						$askPayments = array_merge($askPayments, $oldAskPayments);
					}
					$entity->setAskPayments($askPayments);
				}
				$entity->setBookingReductions(RESA_BookingReduction::getAllData(array('idBooking'=>$entity->getId())));
				$entity->setBookingCustomReductions(RESA_BookingCustomReduction::getAllData(array('idBooking'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	/**
	 *
	 */
	public static function getIdCustomerByIdBooking($idBooking){
		global $wpdb;
		$result = $wpdb->get_var('SELECT idCustomer FROM ' . self::getTableName() . ' WHERE id =\'' . $idBooking . '\'');
		if(!isset($result)) $result = -1;
		return $result;
	}

	/**
	 *
	 */
	public static function getBookingLite($idBooking){
		global $wpdb;
		$return = (object)array();
		$result = $wpdb->get_row('SELECT id, idCreation, idCustomer, quotation, paymentState, totalPrice, note, staffNote, linkOldBookings,
			(SELECT COUNT(id) FROM '. RESA_Appointment::getTableName() . ' WHERE idBooking='. self::getTableName() . '.id) as appointments
			FROM '. self::getTableName() . ' WHERE id='.$idBooking);
		if(isset($result)){
			$results = $wpdb->get_results('SELECT state FROM '. RESA_Appointment::getTableName() . ' WHERE idBooking='.$idBooking);
			$status = 'ok';
			$cancelled = true; $abandonned = true; $updated = true; $waiting = false;
			foreach($results as $states){
				$cancelled = $cancelled && $states->state == 'cancelled';
				$abandonned = $abandonned && $states->state == 'abandonned';
				$updated = $updated && $states->state == 'updated';
				$waiting = $waiting || $states->state == 'waiting';
			}
			if($cancelled){ $status = 'cancelled'; }
			else if($abandonned){ $status = 'abandonned'; }
			else if($waiting){ $status = 'waiting'; }
			else if($updated){ $status = 'updated'; }
			$result->status = $status;

			//askPayments
			$oldIdBookings = explode(',', $result->linkOldBookings);
			array_push($oldIdBookings, $idBooking);
			$result->askPaymentsStatus = ($result->status == 'ok')?0:RESA_AskPayment::getStateOfAskPayment($oldIdBookings);
			$return = $result;
		}
		return $result;
	}


	/**
	 * Return all entities of this type
	 */
	public static function haveBookingsWithVouchers() {
		return RESA_AppointmentReduction::haveReductionsWithVouchers() ||
			RESA_BookingReduction::haveReductionsWithVouchers();
	}

	/**
	 *
	 */
	public static function getAllIdBookingLite($idBooking){
		global $wpdb;
		$return = array();
		$result = $wpdb->get_var('SELECT linkOldBookings FROM ' . self::getTableName() . ' WHERE (idCreation = \'' . $idBooking . '\' || id =\'' . $idBooking . '\')  AND oldBooking = false');
		if(!isset($result)) $result = '';

		$allIdBookings = array();
		$oldIdBookings = explode(',', $result);
		foreach($oldIdBookings as $id){
			if(!empty($id)){
				array_push($allIdBookings, $id);
			}
		}
		array_push($allIdBookings, $idBooking);
		return $allIdBookings;
	}


	/**
	 *
	 */
	public static function getAllIdBookingsWithIdBoooking($idBooking, $limit = ''){
		global $wpdb;
		$results = $wpdb->get_results('SELECT id FROM ' . self::getTableName() . ' WHERE (idCreation LIKE \'%' . $idBooking . '%\' || id LIKE \'%' . $idBooking . '%\')  AND oldBooking = false ORDER BY id ' . $limit);
		return $results;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idCreation = -1;
		$this->idCustomer = -1;
		$this->idUserCreator = -1;
		$this->creationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->tva = -1;
		$this->typePaymentChosen = '';
		$this->transactionId = '';
		$this->totalPrice = 0;
		$this->advancePayment = -1;
		$this->paymentState = 'noPayment';
		$this->quotation = false;
		$this->quotationRequest = false;
		$this->close = false;
		$this->note = '';
		$this->publicNote = '';
		$this->staffNote = '';
		$this->customerNote = '';
		$this->alreadySentEmail = false;
		$this->lastDateSentEmailQuotation =  date('Y-m-d H:i:s', current_time('timestamp'));
		$this->numberSentEmailQuotation = 0;
		$this->allowInconsistencies = false;
		$this->oldBooking = false;
		$this->linkOldBookings = '';
		$this->appointments = array();
		$this->payments = array();
		$this->askPayments = array();
		$this->bookingReductions = array();
		$this->bookingCustomReductions = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->loadByResult($result);
	}

	/**
	 * Load form database
	 */
	public function loadByIdCreation($idCreation)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE idCreation = \'' . $idCreation . '\' AND oldBooking=0');
		if(isset($result)){
			$this->loadByResult($result);
		}
		else {
			$this->loadById($idCreation);
		}
	}

	/**
	 * Load form database
	 */
	public function loadByLastIdCreation($id){
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE (idCreation = \'' . $id . '\' || id =\'' . $id . '\')  AND oldBooking = false');
		$this->loadByResult($result);
	}

	/**
	 * Load form database
	 */
	public function loadByTransactionId($id){
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE (transactionId=\''.$id.'\' OR transactionId LIKE \'%,'.$id.'%\' OR transactionId LIKE \'%'.$id.',%\')');
		$this->loadByResult($result);
	}

	/**
	 *
	 */
	private function loadByResult($result){
		$this->fromJSON($result);
		if($this->isLoaded()){
			$this->setAppointments(RESA_Appointment::getAllData(array('idBooking'=>$this->getId())));
			$this->setPayments(RESA_Payment::getAllData(array('idBooking'=>$this->getId())));
			if(!empty($this->linkOldBookings)){
				$tokens = explode(',', $this->linkOldBookings);
				$payments = $this->getPayments();
				foreach($tokens as $idBooking){
					$oldPayments = RESA_Payment::getAllData(array('idBooking'=>$idBooking));
					foreach($oldPayments as $payment){
						$payment->setIdBooking($this->getId());
					}
					$payments = array_merge($payments, $oldPayments);
				}
				$this->setPayments($payments);
			}
			$this->setAskPayments(RESA_AskPayment::getAllData(array('idBooking'=>$this->getId())));
			if(!empty($this->linkOldBookings)){
				$tokens = explode(',', $this->linkOldBookings);
				$askPayments = $this->getAskPayments();
				foreach($tokens as $idBooking){
					$oldAskPayments = RESA_AskPayment::getAllData(array('idBooking'=>$idBooking));
					foreach($oldAskPayments as $askPayment){
						$askPayment->setIdBooking($this->getId());
					}
					$askPayments = array_merge($askPayments, $oldAskPayments);
				}
				$this->setAskPayments($askPayments);
			}
			$this->setBookingReductions(RESA_BookingReduction::getAllData(array('idBooking'=>$this->getId())));
			$this->setBookingCustomReductions(RESA_BookingCustomReduction::getAllData(array('idBooking'=>$this->getId())));
		}
	}

	/**
	 * reload data
	 */
	public function reload(){
		$this->loadById($this->id);
	}

	/**
	 * return the associated customer
	 */
	public function getCustomer(){
		$customer = new RESA_Customer();
		$customer->loadByIdWithoutBookings($this->getIdCustomer());
		if($customer->isLoaded()){
			return $customer;
		}
		return null;
	}


	/**
	 * Save in database
	 */
	public function save($checkAlert = true)
	{
		if($this->isLoaded())
		{
			$lastBooking = new RESA_Booking();
			$lastBooking->loadById($this->id);
			$lastAppointments = $lastBooking->getAppointments();
			$lastPayments = $lastBooking->getPayments();
			$lastAskPayments = $lastBooking->getAskPayments();
			$lastBookingReductions = $lastBooking->getBookingReductions();
			$lastBookingCustomReductions = $lastBooking->getBookingCustomReductions();

			$this->linkWPDB->update(self::getTableName(), array(
				'idCreation' => $this->idCreation,
				'idCustomer' => $this->idCustomer,
				'idUserCreator' => $this->idUserCreator,
				'creationDate' => $this->creationDate,
				'modificationDate' => $this->modificationDate,
				'tva' => $this->tva,
				'typePaymentChosen' => $this->typePaymentChosen,
				'transactionId' => $this->transactionId,
				'totalPrice' => $this->totalPrice,
				'advancePayment' => $this->advancePayment,
				'paymentState' => $this->paymentState,
				'quotation' => $this->quotation,
				'quotationRequest' => $this->quotationRequest,
				'close' => $this->close,
				'note' => $this->note,
				'publicNote' => $this->publicNote,
				'staffNote' => $this->staffNote,
				'customerNote' => $this->customerNote,
				'alreadySentEmail' => $this->alreadySentEmail,
				'lastDateSentEmailQuotation' => $this->lastDateSentEmailQuotation,
				'numberSentEmailQuotation' => $this->numberSentEmailQuotation,
				'allowInconsistencies' => $this->allowInconsistencies,
				'oldBooking' => $this->oldBooking,
				'linkOldBookings' => $this->linkOldBookings
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%f',
				'%f',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s'
			),
			array ('%d'));

			$idAppointments = array();
			for($i = 0; $i < count($this->appointments); $i++)
			{
				if(!$this->appointments[$i]->isNew())
					array_push($idAppointments, $this->appointments[$i]->getId());
				$this->appointments[$i]->save((!$this->isOldBooking() && $checkAlert));
			}
			$idPayments = array();
			for($i = 0; $i < count($this->payments); $i++)
			{
				if(!$this->payments[$i]->isNew())
					array_push($idPayments, $this->payments[$i]->getId());
				$this->payments[$i]->save();
			}
			$idAskPayments = array();
			for($i = 0; $i < count($this->askPayments); $i++)
			{
				if(!$this->askPayments[$i]->isNew())
					array_push($idAskPayments, $this->askPayments[$i]->getId());
				$this->askPayments[$i]->save();
			}
			$idBookingReductions = array();
			for($i = 0; $i < count($this->bookingReductions); $i++)
			{
				if(!$this->bookingReductions[$i]->isNew())
					array_push($idBookingReductions, $this->bookingReductions[$i]->getId());
				$this->bookingReductions[$i]->save();
			}
			$idBookingCustomReductions = array();
			for($i = 0; $i < count($this->bookingCustomReductions); $i++)
			{
				if(!$this->bookingCustomReductions[$i]->isNew())
					array_push($idBookingCustomReductions, $this->bookingCustomReductions[$i]->getId());
				$this->bookingCustomReductions[$i]->save();
			}

			//delete
			for($i = 0; $i < count($lastAppointments); $i++) {
				if(!in_array($lastAppointments[$i]->getId(), $idAppointments))
					$lastAppointments[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastPayments); $i++) {
				if(!in_array($lastPayments[$i]->getId(), $idPayments))
					$lastPayments[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastAskPayments); $i++) {
				if(!in_array($lastAskPayments[$i]->getId(), $idAskPayments))
					$lastAskPayments[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastBookingReductions); $i++) {
				if(!in_array($lastBookingReductions[$i]->getId(), $idBookingReductions))
					$lastBookingReductions[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastBookingCustomReductions); $i++) {
				if(!in_array($lastBookingCustomReductions[$i]->getId(), $idBookingCustomReductions))
					$lastBookingCustomReductions[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idCreation' => $this->idCreation,
				'idCustomer' => $this->idCustomer,
				'idUserCreator' => $this->idUserCreator,
				'creationDate' => $this->creationDate,
				'modificationDate' => $this->modificationDate,
				'tva' => $this->tva,
				'typePaymentChosen' => $this->typePaymentChosen,
				'transactionId' => $this->transactionId,
				'totalPrice' => $this->totalPrice,
				'advancePayment' => $this->advancePayment,
				'paymentState' => $this->paymentState,
				'quotation' => $this->quotation,
				'quotationRequest' => $this->quotationRequest,
				'close' => $this->close,
				'note' => $this->note,
				'publicNote' => $this->publicNote,
				'staffNote' => $this->staffNote,
				'customerNote' => $this->customerNote,
				'alreadySentEmail' => $this->alreadySentEmail,
				'lastDateSentEmailQuotation' => $this->lastDateSentEmailQuotation,
				'numberSentEmailQuotation' => $this->numberSentEmailQuotation,
				'allowInconsistencies' => $this->allowInconsistencies,
				'oldBooking' => $this->oldBooking,
				'linkOldBookings' => $this->linkOldBookings
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%f',
				'%f',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s'
			));
			$idBooking = $this->linkWPDB->insert_id;
			$this->id = $idBooking;
			$this->idCreation = $idBooking;
			$this->setLoaded(true);
			for($i = 0; $i < count($this->appointments); $i++)
			{
				$this->appointments[$i]->setIdBooking($idBooking);
				$this->appointments[$i]->save((!$this->isOldBooking() && $checkAlert));
			}
			for($i = 0; $i < count($this->payments); $i++)
			{
				$this->payments[$i]->setIdBooking($idBooking);
				$this->payments[$i]->save();
			}
			for($i = 0; $i < count($this->askPayments); $i++)
			{
				$this->askPayments[$i]->setIdBooking($idBooking);
				$this->askPayments[$i]->save();
			}
			for($i = 0; $i < count($this->bookingReductions); $i++)
			{
				$this->bookingReductions[$i]->setIdBooking($idBooking);
				$this->bookingReductions[$i]->save();
			}
			for($i = 0; $i < count($this->bookingCustomReductions); $i++)
			{
				$this->bookingCustomReductions[$i]->setIdBooking($idBooking);
				$this->bookingCustomReductions[$i]->save();
			}
		}
		//Create alert if necessary.
		if(!$this->isOldBooking() && empty($this->linkOldBookings) && $checkAlert){
			RESA_Algorithms::verifyReductions($this);
		}
	}

	/**
	 * Delete in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded()){
			foreach($this->appointments as $appointment){ $appointment->deleteMe(); }
			foreach($this->bookingReductions as $bookingReduction){ $bookingReduction->deleteMe(); }
			foreach($this->bookingCustomReductions as $bookingCustomReductions){ $bookingCustomReductions->deleteMe(); }
			foreach($this->payments as $payment){ $payment->deleteMe();	}
			foreach($this->askPayments as $askPayment){ $askPayment->deleteMe(); }
			if(!$this->isOldBooking() && !empty($this->linkOldBookings)){
				foreach($this->getOldIdBookings() as $idBooking){
					if($this->id != $idBooking){
						$oldBooking = new RESA_Booking();
						$oldBooking->loadById($idBooking);
						$oldBooking->deleteMe();
					}
				}
			}
			$this->linkWPDB->delete(self::getTableName(), array('id'=>$this->id), array ('%d'));

		}
	}

	/**
	 * new generation of function to load a simple
	 */
	public function getBookingLiteJSON(){
		$groupActivated = RESA_Variables::isGroupsManagementActivated();
		$customer = $this->getCustomer();
		$json = json_decode($this->toJSON());
		$json->idPaymentState = RESA_Variables::calculateBookingPayment($json->status, $json->paymentState);
		$json->oneUpdate = $this->getIdCreation() != $this->id;
		$json->realIdCreation = $this->getIdCreation();
		$json->startDate = $this->getAppointmentFirstDate();
		$json->quotationExpired = $this->isQuotationExpired();
		if(isset($customer)) $json->customer = json_decode($customer->toJSON());
		$json->nbAppointments = count($json->appointments);
		foreach($json->appointments as $appointment){
			$service = RESA_Service::getServiceLite($appointment->idService);
			$appointment->service = RESA_Tools::getTextByLocale($service->name, get_locale());
			$appointment->askParticipants = $service->askParticipants == 1;
			$appointment->color = $service->color;
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
				if($groupActivated){
					for($i = 0; $i < count($appointmentNumberPrice->participants); $i++){
						$groupName = RESA_Group::getGroupNameParticipantURI($appointment->idPlace, $service->id,
							$appointment->startDate, $appointment->endDate, $appointmentNumberPrice->participants[$i]->uri);
						$appointmentNumberPrice->participants[$i]->meta_group = RESA_Group::formatedName($customer, $groupName);
					}
				}
			}
			foreach ($appointment->appointmentMembers as $appointmentMember) {
				$appointmentMember->name = RESA_Member::getMemberName($appointmentMember->idMember);
			}
		}
		return $json;
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$userCreator = 'en ligne';
		if($this->idCustomer != $this->idUserCreator){
			$customer = new RESA_Customer();
			$customer->loadByIdWithoutBookings($this->idUserCreator);
			$userCreator = $customer->getDisplayName();
		}
		$advancePayment = $this->getAdvancePayment();
		if(!$this->haveAdvancePayment()){
			$advancePayment = $this->calculateAdvancePayment();
		}
		$status = $this->getStatus();
		return '{
			"id":'. $this->id .',
			"idCreation":'. $this->idCreation .',
			"idCustomer":'. $this->idCustomer .',
			"idUserCreator":'. $this->idUserCreator .',
			"userCreator":"'. $userCreator .'",
			"creationDate":"'.$this->creationDate.'",
			"modificationDate":"'.$this->modificationDate.'",
			"tva":'.$this->tva.',
			"typePaymentChosen":"'.$this->typePaymentChosen.'",
			"transactionId":"'.$this->transactionId.'",
			"paymentState":"'.$this->paymentState.'",
			"quotation":'.RESA_Tools::toJSONBoolean($this->quotation).',
			"quotationRequest":'.RESA_Tools::toJSONBoolean($this->quotationRequest).',
			"close":'.RESA_Tools::toJSONBoolean($this->close).',
			"note":"'.$this->note.'",
			"publicNote":"'.$this->publicNote.'",
			"staffNote":"'.$this->staffNote.'",
			"customerNote":"'.$this->customerNote.'",
			"alreadySentEmail":'.RESA_Tools::toJSONBoolean($this->alreadySentEmail).',
			"lastDateSentEmailQuotation":"'.$this->lastDateSentEmailQuotation.'",
			"numberSentEmailQuotation":'.$this->numberSentEmailQuotation.',
			"allowInconsistencies":'.RESA_Tools::toJSONBoolean($this->allowInconsistencies).',
			"oldBooking":'.RESA_Tools::toJSONBoolean($this->oldBooking).',
			"linkOldBookings":"'.$this->linkOldBookings.'",
			"appointments":'.RESA_Tools::formatJSONArray($this->appointments).',
			"payments":'.RESA_Tools::formatJSONArray($this->payments).',
			"askPayments":'.RESA_Tools::formatJSONArray($this->askPayments).',
			"bookingReductions":'.RESA_Tools::formatJSONArray($this->bookingReductions).',
			"bookingCustomReductions":'.RESA_Tools::formatJSONArray($this->bookingCustomReductions).',
			"totalPrice":'.$this->totalPrice.',
			"needToPay":'.$this->getNeedToPay().',
			"haveAdvancePayment":'.RESA_Tools::toJSONBoolean($this->haveAdvancePayment()).',
			"advancePayment":'.$advancePayment.',
			"status":"'.$status.'",
			"waitingSubState":'.$this->getWaitingSubState($status).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		if($json != null){
			$this->id = $json->id;
			if(isset($json->idCreation)) $this->idCreation = $json->idCreation;
			$this->idCustomer = $json->idCustomer;
			$this->idUserCreator = $json->idUserCreator;
			$this->creationDate = $json->creationDate;
			if(isset($json->modificationDate)) $this->modificationDate = $json->modificationDate;
			else date('Y-m-d H:i:s', 0);
			$this->tva = $json->tva;
			if(isset($json->typePaymentChosen)) $this->typePaymentChosen = $json->typePaymentChosen;
			if(isset($json->transactionId)) $this->transactionId = $json->transactionId;
			if(isset($json->totalPrice)) $this->totalPrice = $json->totalPrice;
			if(isset($json->advancePayment)) $this->advancePayment = $json->advancePayment;
			if(isset($json->quotation)) $this->quotation = $json->quotation;
			if(isset($json->quotationRequest)) $this->quotationRequest = $json->quotationRequest;
			if(isset($json->close)) $this->close = $json->close;
			if(isset($json->paymentState)) $this->paymentState = $json->paymentState;
			if(isset($json->note)) {
				$this->note = RESA_Tools::echapEolTab($json->note);
			}
			if(isset($json->publicNote)) {
				$this->publicNote = RESA_Tools::echapEolTab($json->publicNote);
			}
			if(isset($json->staffNote)) {
				$this->staffNote = RESA_Tools::echapEolTab($json->staffNote);
			}
			if(isset($json->customerNote)) {
				$this->customerNote = RESA_Tools::echapEolTab($json->customerNote);
			}
			if(isset($json->alreadySentEmail)){
				$this->alreadySentEmail = $json->alreadySentEmail;
			}
			if(isset($json->lastDateSentEmailQuotation)){
				$this->lastDateSentEmailQuotation = $json->lastDateSentEmailQuotation;
			}
			if(isset($json->numberSentEmailQuotation)){
				$this->numberSentEmailQuotation = $json->numberSentEmailQuotation;
			}
			if(isset($json->allowInconsistencies)) {
				$this->allowInconsistencies = $json->allowInconsistencies;
			}
			$this->oldBooking = $json->oldBooking;
			$this->linkOldBookings = $json->linkOldBookings;
			if(isset($json->appointments))
			{
				$appointments = array();
				for($i = 0; $i < count($json->appointments); $i++)
				{
					$appointment = new RESA_Appointment();
					$appointment->fromJSON($json->appointments[$i]);
					array_push($appointments, $appointment);
				}
				$this->setAppointments($appointments);
			}
			if(isset($json->payments))
			{
				$payments = array();
				for($i = 0; $i < count($json->payments); $i++)
				{
					$payment = new RESA_Payment();
					$payment->fromJSON($json->payments[$i]);
					array_push($payments, $payment);
				}
				$this->setPayments($payments);
			}
			if(isset($json->askPayments))
			{
				$askPayments = array();
				for($i = 0; $i < count($json->askPayments); $i++)
				{
					$askPayment = new RESA_AskPayment();
					$askPayment->fromJSON($json->askPayments[$i]);
					array_push($askPayments, $askPayment);
				}
				$this->setAskPayments($askPayments);
			}
			if(isset($json->bookingReductions))
			{
				$bookingReductions = array();
				for($i = 0; $i < count($json->bookingReductions); $i++)
				{
					$bookingReduction = new RESA_BookingReduction();
					$bookingReduction->fromJSON($json->bookingReductions[$i]);
					array_push($bookingReductions, $bookingReduction);
				}
				$this->setBookingReductions($bookingReductions);
			}
			if(isset($json->bookingCustomReductions))
			{
				$bookingCustomReductions = array();
				for($i = 0; $i < count($json->bookingCustomReductions); $i++)
				{
					$bookingCustomReduction = new RESA_BookingCustomReduction();
					$bookingCustomReduction->fromJSON($json->bookingCustomReductions[$i]);
					array_push($bookingCustomReductions, $bookingCustomReduction);
				}
				$this->setBookingCustomReductions($bookingCustomReductions);
			}
			if($this->id != -1)	$this->setLoaded(true);
		}
	}

	/**
	 * \fn getNumberPersons
	 * \brief return the total number of persons
	 */
	public function getNumberPersons(){
		$number = 0;
		foreach($this->appointments as $appointment){
			$number += $appointment->getNumbers();
		}
		return $number;
	}


	/**
	 * TO DELETE
	 * To calculate the total price, with more parameters
	 */
	public function getRealTotalPrice($advancePayment, $form, $statesParameters){
		$needToPay = 0;
		if($this->isOk() || $this->isWaiting()){
			foreach($this->appointments as $appointment){
				if(!$form){
					$needToPay += $appointment->getNeedToPay($advancePayment);
				}
				else {
					$needToPay += $appointment->getNeedToPayForm($statesParameters, $advancePayment);
				}
			}
			$reductionNumber = 0;
			$reductionPurcent = 0;
			foreach($this->bookingReductions as $bookingReduction){
				$reduction = new RESA_Reduction();
				$reduction->loadById($bookingReduction->getIdReduction());
				if($reduction->isLoaded()){
					$number = $bookingReduction->getNumber();
					if($number == 0){
						if($bookingReduction->getType() == 0){
							if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue();
							else $reductionNumber += $bookingReduction->getValue();
						}
						else if($bookingReduction->getType() == 1){
							$reductionPurcent += $bookingReduction->getValue();
						}
						else if($bookingReduction->getType() == 2){
							if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $this->getNumberPersons();
							else $reductionNumber += $bookingReduction->getValue() * $this->getNumberPersons();
						}
					}
					else {
						if($bookingReduction->getType() == 0){
							if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $number;
							else $reductionNumber += $bookingReduction->getValue() * $number;
						}
						else if($bookingReduction->getType() == 1){
							$reductionPurcent += $bookingReduction->getValue();
						}
						else if($bookingReduction->getType() == 2){
							if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $number;
							else $reductionNumber += $bookingReduction->getValue() * $number;
						}
					}
				}
			}
			$needToPay -= $needToPay * $reductionPurcent / 100;
			$needToPay -= $reductionNumber;
			foreach($this->bookingCustomReductions as $bookingCustomReduction){
				$needToPay += $bookingCustomReduction->getAmount();
			}
		}
		return $this->roundPrice($needToPay);
	}

	/**
	 * \fn calculateTotalPrice
	 * \brief return the total of needed to pay
	 * \return int value
	 */
	public function calculateTotalPrice(){
		$needToPay = 0;
		foreach($this->appointments as $appointment){
			$needToPay += $appointment->getTotalPrice();
		}
		return $this->applyReductionToValue($needToPay);
	}

	/**
	 * \fn applyReductionToValue
	 * \brief apply reduction to value
	 * \return int value
	 */
	public function applyReductionToValue($value){
		$reductionNumber = 0;
		$reductionPurcent = 0;
		foreach($this->bookingReductions as $bookingReduction){
			$number = $bookingReduction->getNumber();
			if($number == 0){
				if($bookingReduction->getType() == 0){
					if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue();
					else $reductionNumber += $bookingReduction->getValue();
				}
				else if($bookingReduction->getType() == 1){
					$reductionPurcent += $bookingReduction->getValue();
				}
				else if($bookingReduction->getType() == 2){
					if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $this->getNumberPersons();
					else $reductionNumber += $bookingReduction->getValue() * $this->getNumberPersons();
				}
			}
			else {
				if($bookingReduction->getType() == 0){
					if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $number;
					else $reductionNumber += $bookingReduction->getValue() * $number;
				}
				else if($bookingReduction->getType() == 1){
					$reductionPurcent += $bookingReduction->getValue();
				}
				else if($bookingReduction->getType() == 2){
					if($bookingReduction->getValue() < 0) $reductionNumber -= $bookingReduction->getValue() * $number;
					else $reductionNumber += $bookingReduction->getValue() * $number;
				}
			}
		}
		$value -= $value * $reductionPurcent / 100;
		$value -= $reductionNumber;
		foreach($this->bookingCustomReductions as $bookingCustomReduction){
			$value += $bookingCustomReduction->getAmount();
		}
		return $this->roundPrice($value);
	}



	/**
	 * \fn round the price
	 * \brief arround the price
	 * \return int value
	 */
	public function roundPrice($needToPay){
		return round($needToPay, 2);
	}

	/**
	 * \fn getTotalPriceWithAllParams
	 * \brief return just price
	 * \return int value
	 */
	public function getTotalPriceWithAllParams($isAdvancePayment, $form, $statesParameters){
		if($form) return $this->calculateTotalPriceForm($statesParameters, $isAdvancePayment);
		else if($advancePayment) return $this->calculateAdvancePayment();
		else return $this->totalPrice;
	}

	/**
	 * \fn getAdvancePayment
	 * \brief return the total of needed to pay
	 * \return int value
	 */
	public function calculateAdvancePayment(){
		$advancePayment = 0;
		for($i = 0; $i < count($this->appointments); $i++){
			$appointment = $this->appointments[$i];
			$advancePayment += $appointment->calculateAdvancePayment($this->getCustomer());
		}
		return round($advancePayment,2);
	}

	/**
	 * \fn calculateTotalPriceForm
	 * \brief calculate the total price without appointments function of idParameter
	 * \return int value
	 */
	public function calculateTotalPriceForm($statesParameters, $isAdvancePayment){
		$advancePayment = 0;
		for($i = 0; $i < count($this->appointments); $i++){
			$appointment = $this->appointments[$i];
			$advancePayment += $appointment->calculateTotalPriceForm($statesParameters, $isAdvancePayment, $this->getCustomer());
		}
		return $this->applyReductionToValue($advancePayment);
	}


	/**
	 * \fn getNeedToPay
	 * \brief return the value needed to pay
	 * \return int value
	 */
	public function getNeedToPay(){
		$needToPay = $this->totalPrice;
		foreach($this->payments as $payment){
			if($payment->isOk()){
				if(!$payment->isRepayment()){
					$needToPay -= $payment->getValue();
				}
				else {
					$needToPay += $payment->getValue();
				}
			}
		}
		return round($needToPay,2);
	}

	/**
	 * Return the status of the booking
	 */
	public function getStatus(){
		$status = 'ok';
		$cancelled = true;
		$abandonned = true;
		$waiting = false;
		foreach($this->appointments as $appointment){
			$cancelled = $cancelled && $appointment->isCancelled();
			$abandonned = $abandonned && $appointment->isAbandonned();
			$waiting = $waiting || $appointment->isWaiting();
		}
		if($cancelled){
			$status = 'cancelled';
		}
		else if($abandonned){
			$status = 'abandonned';
		}
		else if($waiting){
			$status = 'waiting';
		}
		else if($this->oldBooking){
			$status = 'updated';
		}
		return $status;
	}

	/**
	 * Return all promoCodes used
	 */
	 public function getPromoCodes(){
		 $promoCodes = array();
		 foreach($this->bookingReductions as $bookingReduction){
			 $promoCode = $bookingReduction->getPromoCode();
			 if(!empty($promoCode) && !in_array($promoCode, $promoCodes)){
				 array_push($promoCodes, $promoCode);
			 }
		 }
		 foreach($this->appointments as $appointment){
			 foreach($appointment->getAppointmentReductions() as $appointmentReductions){
				 $promoCode = $appointmentReductions->getPromoCode();
				 if(!empty($promoCode) && !in_array($promoCode, $promoCodes)){
					 array_push($promoCodes, $promoCode);
				 }
			 }
		 }
		 return $promoCodes;
	 }

	 /**
 	 * return true if this booking is ok
 	 */
 	public function isOk(){
 		return $this->getStatus() == 'ok';
 	}

	/**
	 * return true if this booking is cancelled
	 */
	public function isCancelled(){
		return $this->getStatus() == 'cancelled';
	}

	/**
	 * return true if this booking is abandonned
	 */
	public function isAbandonned(){
		return $this->getStatus() == 'abandonned';
	}

	/**
	 * return true is waiting
	 */
	public function isWaiting(){
		return $this->getStatus() == 'waiting';
	}

	/**
	 *
	 */
	public function stateAskPayment($statesParameters){
		if(!$this->isCancelled() && !$this->isAbandonned()){
			foreach($this->appointments as $appointment){
				if($appointment->statePayment($statesParameters)){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 *
	 */
	public function changeStatesAfterPayment($statesParameters, $force = false){
		if(!$this->isCancelled() && (!$this->isAbandonned() || $force)){
			foreach($this->appointments as $appointment){
				$appointment->changeStateAfterPayment($statesParameters, !$this->isOnline());
			}
		}
	}

	/**
	 *
	 */
	public function changeExpirationState($statesParameters){
		if(!$this->isCancelled() && !$this->isAbandonned() && $this->getTypePaymentChosen() != 'swikly'){
			foreach($this->appointments as $appointment){
				$appointment->changeExpirationState($statesParameters, !$this->isOnline());
			}
		}
	}


	/**
	 * Cancel the booking
	 */
	public function cancelBooking(){
		foreach($this->appointments as $appointment){
			$appointment->cancel();
		}
	}

	/**
	 * Put abandonned the booking
	 */
	public function abandonnedBooking(){
		foreach($this->appointments as $appointment){
			$appointment->abandonned();
		}
	}

	/**
	 * Put waiting the booking
	 */
	public function waitingBooking(){
		foreach($this->appointments as $appointment){
			$appointment->waiting();
		}
	}

	/**
	 * Put ok the booking
	 */
	public function okBooking(){
		foreach($this->appointments as $appointment){
			$appointment->ok();
		}
	}

	public function getId(){ return $this->id; }
	public function setId($id){ $this->id = $id; }
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->appointments as $appointment){
			$appointment->setNew();
		}
		foreach($this->payments as $payments){
			$payments->setNew();
		}
		foreach($this->askPayments as $askPayments){
			$askPayments->setNew();
		}
		foreach($this->bookingReductions as $bookingReduction){
			$bookingReduction->setNew();
		}
		foreach($this->bookingCustomReductions as $bookingCustomReduction){
			$bookingCustomReduction->setNew();
		}
		$this->clearModificationDate();
	}

	public function setIdCreation($idCreation){ $this->idCreation = $idCreation;}
	public function getIdCreation(){
		 if($this->idCreation==-1) return $this->id;
		 else return $this->idCreation;
	}

	public function setIdCustomer($idCustomer){ $this->idCustomer = $idCustomer;}
	public function getIdCustomer(){ return $this->idCustomer; }

	public function setIdUserCreator($idUserCreator){ $this->idUserCreator = $idUserCreator;}
	public function getIdUserCreator(){ return $this->idUserCreator; }
	public function isCreateByAdmin(){ return $this->idCustomer!=$this->idUserCreator; }
	public function isOnline(){ return $this->idCustomer == $this->idUserCreator; }

	public function setCreationDate($creationDate){ $this->creationDate = $creationDate; }
	public function getCreationDate(){ return $this->creationDate; }

	public function setModificationDate($modificationDate){ $this->modificationDate = $modificationDate; }
	public function getModificationDate(){ return $this->modificationDate; }
	public function clearModificationDate(){ $this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp')); }

	public function setTVA($tva){ $this->tva = $tva; }
	public function getTVA(){ return $this->tva; }
	public function haveTVA(){ return $this->tva!=-1; }

	public function setTypePaymentChosen($typePaymentChosen){ $this->typePaymentChosen = $typePaymentChosen; }
	public function getTypePaymentChosen(){ return $this->typePaymentChosen; }

	public function setTransactionId($transactionId){ $this->transactionId = $transactionId; }
	public function getTransactionId(){ return $this->transactionId; }
	public function addTransactionId($transactionId){
		if(!empty($this->transactionId)) $this->transactionId .= ',';
		$this->transactionId .= $transactionId;
	}

	public function setTotalPrice($totalPrice){ $this->totalPrice = $totalPrice; }
	public function getTotalPrice(){ return $this->totalPrice; }

	public function setAdvancePayment($advancePayment){ $this->advancePayment = $advancePayment; }
	public function getAdvancePayment(){ return $this->advancePayment; }
	public function haveAdvancePayment(){ return $this->advancePayment != -1; }

	public function setPaymentState($paymentState){ $this->paymentState = $paymentState; }
	public function getPaymentState(){ return $this->paymentState; }
	public function isPaymentStateNoPayment(){ return $this->paymentState == 'noPayment'; }
	public function isPaymentStateAdvancePayment(){ return $this->paymentState == 'advancePayment'; }
	public function isPaymentDepositPayment(){ return $this->paymentState == 'deposit'; }
	public function isPaymentStateComplete(){ return $this->paymentState == 'complete'; }
	public function isPaymentStateOver(){ return $this->paymentState == 'over'; }

	public function setQuotation($quotation){ $this->quotation = $quotation; }
	public function isQuotation(){ return $this->quotation; }
	public function isNotQuotation(){ return !$this->quotation; }

	public function setQuotationRequest($quotationRequest){ $this->quotationRequest = $quotationRequest; }
	public function isQuotationRequest(){ return $this->quotationRequest; }

	public function isQuotationExpired(){
		return $this->isQuotation() && $this->isMaxNumberSentEmailQuotation(intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_times')));
	}

	public function setClose($close){ $this->close = $close; }
	public function isClose(){ return $this->close; }

	public function setNote($note){ $this->note = $note; }
	public function getNote(){ return $this->note; }

	public function setPublicNote($publicNote){ $this->publicNote = $publicNote; }
	public function getPublicNote(){ return $this->publicNote; }

	public function setStaffNote($staffNote){ $this->staffNote = $staffNote; }
	public function getStaffNote(){ return $this->staffNote; }

	public function setCustomerNote($customerNote){ $this->customerNote = $customerNote; }
	public function getCustomerNote(){ return $this->customerNote; }

	public function setAlreadySentEmail($alreadySentEmail){ $this->alreadySentEmail = $alreadySentEmail;}
	public function isAlreadySentEmail(){ return $this->alreadySentEmail;}

	public function setLastDateSentEmailQuotation($lastDateSentEmailQuotation){ $this->lastDateSentEmailQuotation = $lastDateSentEmailQuotation;}
	public function getLastDateSentEmailQuotation(){ return $this->lastDateSentEmailQuotation;}
	public function clearLastDateSentEmailQuotation(){
		$this->lastDateSentEmailQuotation = date('Y-m-d H:i:s', current_time('timestamp'));
	}

	public function setNumberSentEmailQuotation($numberSentEmailQuotation){ $this->numberSentEmailQuotation = $numberSentEmailQuotation; }
	public function getNumberSentEmailQuotation(){ return $this->numberSentEmailQuotation; }
	public function isMaxNumberSentEmailQuotation($max){ return $this->numberSentEmailQuotation >= $max; }

	public function setAllowInconsistencies($allowInconsistencies){	$this->allowInconsistencies = $allowInconsistencies; }
	public function isAllowInconsistencies(){ return $this->allowInconsistencies; }

	public function setOldBooking($oldBooking){ $this->oldBooking = $oldBooking;}
	public function isOldBooking(){ return $this->oldBooking;}

	public function setLinkOldBookings($linkOldBookings){ $this->linkOldBookings = $linkOldBookings;}
	public function addLinkOldBookings($idBooking){
		if(!empty($this->linkOldBookings)) $this->linkOldBookings .= ',';
		$this->linkOldBookings .= $idBooking;
	}
	public function getLinkOldBookings(){ return $this->linkOldBookings; }
	public function getOldIdBookings(){
		$oldIdBookings = explode(',', $this->linkOldBookings);
		$results = array();
		foreach($oldIdBookings as $idBooking){
			if(!empty($idBooking)){
				array_push($results, $idBooking);
			}
		}
		return $results;
	}
	public function getAllIdBookings(){
		$allIdBookings = $this->getOldIdBookings();
		if(!isset($allIdBookings)) $allIdBookings = array();
		array_push($allIdBookings, $this->id);
		return $allIdBookings;
	}

	public function isSameBooking($idBooking){
		$tokens = explode(',', $this->linkOldBookings);
		return $this->id == $idBooking || in_array($idBooking, $tokens);
	}

	public function setAppointments($appointments){ $this->appointments = $appointments;}
	public function getAppointments(){ return $this->appointments;}

	public function setPayments($payments){ $this->payments = $payments;}
	public function getPayments(){ return $this->payments;}

	public function setAskPayments($askPayments){ $this->askPayments = $askPayments;}
	public function getAskPayments(){ return $this->askPayments;}

	public function setBookingReductions($bookingReductions){ $this->bookingReductions = $bookingReductions;}
	public function getBookingReductions(){ return $this->bookingReductions;}

	public function setBookingCustomReductions($bookingCustomReductions){ $this->bookingCustomReductions = $bookingCustomReductions;}
	public function getBookingCustomReductions(){ return $this->bookingCustomReductions;}

	public function getAllIdPlaces(){
		$idPlaces = array();
		foreach($this->appointments as $appointment){
			$idPlace = $appointment->getIdPlace();
			if(!empty($idPlace) && !in_array($idPlace, $idPlaces)){
				array_push($idPlaces, $idPlace);
			}
		}
		return $idPlaces;
	}

	public function getAllIdAppointments(){
		$appointments = $this->getAppointments();
		$idAppointments = array();
		foreach($appointments as $appointment){
			array_push($idAppointments, $appointment->getId());
		}
		return $idAppointments;
	}

	public function isFirstAppointment($appointment){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return false;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return $appointments[0]->getId() == $appointment->getId();
	}

	public function isLastAppointment($appointment){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return false;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return end($appointments)->getId() == $appointment->getId();
	}

	public function isDatePassed(){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return true;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return $appointments[0]->isDatePassed();
	}

	public function isAllDatePassed(){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return true;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return end($appointments)->isDatePassed();
	}

	public function getAppointmentFirstDate(){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return true;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return $appointments[0]->getStartDate();
	}

	public function getAppointmentEndDate(){
		$appointments = $this->getAppointments();
		if(count($appointments) == 0) return true;
		usort($appointments, array("RESA_Appointment", "compareEndDate"));
		return end($appointments)->getEndDate();
	}

	/**
	 * get places
	 */
	 public function getIdPlaces(){
		 $idPlaces = array();
		 foreach($this->appointments as $appointment){
			 if(!in_array($appointment->getIdPlace(), $idPlaces)){
				 array_push($idPlaces, $appointment->getIdPlace());
			 }
		 }
		 return $idPlaces;
	 }

	/**
	 * Replace all oldId by the newId
	 */
	public function updateService($oldService, $newService){
		foreach($this->appointments as $appointment){
			$appointment->updateService($oldService, $newService);
		}
	}


	public function updateIdReduction($oldId, $newId){
		foreach($this->appointments as $appointment){
			$appointment->updateIdReduction($oldId, $newId);
		}
		foreach($this->bookingReductions as $bookingReduction){
			if($bookingReduction->getIdReduction() == $oldId){
				$bookingReduction->setIdReduction($newId);
			}
		}
	}

	public function updateIdMember($oldId, $newId){
		foreach($this->appointments as $appointment){
			$appointment->updateIdMember($oldId, $newId);
		}
	}

	public function updateIdBooking($oldId, $newId){
		if($this->idCreation == $oldId){
			$this->idCreation = $newId;
		}
		$tokens = explode(',', $this->linkOldBookings);
		for($i = 0; $i < count($tokens); $i++){
			if($tokens[$i] == $oldId){
				$tokens[$i] = $newId;
			}
		}
		$this->linkOldBookings = implode(',', $tokens);
	}

	/**
	 * return true if booking reductions as same reduction
	 */
	public function haveSameReductions($anotherBooking){
		if(count($anotherBooking->getBookingReductions()) != count($this->bookingReductions)) return false;
		else {
			$anotherBookingReductions = $anotherBooking->getBookingReductions();
			foreach($this->bookingReductions as $bookingReduction){
				$find = false;
				$i = 0;
				while(!$find && $i < count($anotherBookingReductions)){
					$anotherBookingReduction = $anotherBookingReductions[$i];
					$find = $bookingReduction->equals($anotherBookingReduction);
					$i++;
				}
				if(!$find){
					return false;
				}
			}
			$allAppointmentReductions = array();
			$allAnotherAppointmentReductions = array();
			foreach($this->appointments as $appointment){
				array_merge($allAppointmentReductions, $appointment->getAppointmentReductions());
			}
			foreach($anotherBooking->getAppointments() as $appointment){
				array_merge($allAnotherAppointmentReductions, $appointment->getAppointmentReductions());
			}

			foreach($allAppointmentReductions as $appointmentReductions){
				$find = false;
				$i = 0;
				while(!$find && $i < count($allAnotherAppointmentReductions)){
					$allAnotherAppointmentReduction = $allAnotherAppointmentReductions[$i];
					$find = $appointmentReductions->equals($allAnotherAppointmentReduction);
					$i++;
				}
				if(!$find){
					return false;
				}
			}
		}
		return true;
	}

	public function haveThisPayment($idPayment){
		foreach($this->payments as $payment){
			if($payment->getId() == $idPayment){
				return true;
			}
		}
		return false;
	}

	/**
	 * Return the dates of appointement and numbers appointments
	 */
	public function getIntervals(){
		$intervals = array();
		foreach($this->appointments as $appointment){
			$found = false;
			$index = 0;
			$date = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
			$date = $date->format(get_option('date_format'));
			while(!$found && $index < count($intervals)){
				$interval = $intervals[$index];
				if($interval['date'] === $date){
					$found = true;
					$interval['number']++;
				}
				$index++;
			}
			if(!$found){
				array_push($intervals, array(
					'date' => $date,
					'number' => 1
				));
			}
		}
		return $intervals;
	}

	/**
	 * add waiting payment
	 */
	public function addWaitingPayment($typePayment, $totalPrice, $idReference = ''){
		$alreadyPayment = $this->getWaitingPayment();
		if(is_null($alreadyPayment)){
			$payment = new RESA_Payment();
			$payment->setIdBooking($this->id);
			$payment->setTypePayment($typePayment);
			$payment->setValue($totalPrice);
			$payment->setIdReference($idReference);
			$payment->pending();
			array_push($this->payments, $payment);
		}
		else {
			$alreadyPayment->setTypePayment($typePayment);
			$alreadyPayment->setValue($totalPrice);
			$alreadyPayment->setIdReference($idReference);
		}
	}

	/**
	 * get waiting payment
	 */
	public function getWaitingPayment(){
		foreach($this->payments as $payment){
			if($payment->isPending()){
				return $payment;
			}
		}
		return null;
	}

	/**
	 * save prices
	 */
	public function savePrices(){
		for($i = 0; $i < count($this->appointments); $i++){
			$appointment = $this->appointments[$i];
			$appointment->savePrices();
		}
		$this->totalPrice = $this->calculateTotalPrice();
	}

	//To delete when 0.9 update done
	public function saveNumbers(){
		for($i = 0; $i < count($this->appointments); $i++){
			$appointment = $this->appointments[$i];
			$appointment->setNumbers($appointment->calculateNumbers());
		}
	}



	/**
	 * return the sub state of waiting states
	 * -1  no waiting
	 *  0 just waiting
	 *  1 paiement
	 *  2 expiré
	 */
	public function getWaitingSubState($status = ''){
		if(empty($status)){
			$status = $this->getStatus();
		}
		if($status != 'waiting') return -1;
		$paiement = false;
		$expired = false;
		for($i = 0; $i < count($this->askPayments); $i++){
			$askPayment = $this->askPayments[$i];
			if($askPayment->isExpiredDate()){
				$expired = true;
			}
			else {
				$paiement = true;
			}
		}
		$substate = 0;
		if($paiement) $substate = 1;
		else if($expired) $substate = 2;
		return $substate;
	}

	public function getParticipants(){
		$participants = array();
		foreach($this->appointments as $appointment){
			$participants = array_merge($participants, $appointment->getParticipants());
		}
		return $participants;
	}

	private function isAlreadyCreatedUri($alreadyCreated, $uri, $appointment, $idService){
		foreach($alreadyCreated as $value){
			if($value['uri'] == $uri &&
				 $value['idPlace'] == $appointment->getIdPlace() &&
					$value['idService'] == $idService &&
						$value['startDate'] == $appointment->getStartDate() &&
							$value['endDate'] == $appointment->getEndDate()){
				return true;
			}
		}
		return false;
	}

	public function generateParticipantsUri($idCustomer){
		$alreadyCreated = array();
		for($i = 0; $i < count($this->appointments); $i++){
			$appointment = $this->appointments[$i];
			$idService = RESA_Service::getLastIdService($appointment->getIdService());
			$appointmentNumberPrices = $appointment->getAppointmentNumberPrices();
			for($j = 0; $j < count($appointmentNumberPrices); $j++){
				$appointmentNumberPrice = $appointmentNumberPrices[$j];
				$participants = $appointmentNumberPrice->getParticipants();
				for($k = 0; $k < count($participants); $k++){
					$participant = $participants[$k];
					if(!isset($participant->uri) || empty($participant->uri) || $this->isAlreadyCreatedUri($alreadyCreated, $participant->uri, $appointment, $idService)){
						$participant->uri = RESA_Tools::generateOneParticipantUri($idCustomer, $participant);
					}
					array_push($alreadyCreated,
						array('uri' => $participant->uri,
							'idPlace' => $appointment->getIdPlace(),
							'idService' => $idService,
							'startDate' => $appointment->getStartDate(),
							'endDate' => $appointment->getEndDate())
					);
					$participants[$k] = $participant;
				}
				 $appointmentNumberPrices[$j]->setParticipants($participants);
			}
			$this->appointments[$i]->setAppointmentNumberPrices($appointmentNumberPrices);
		}
	}



}
