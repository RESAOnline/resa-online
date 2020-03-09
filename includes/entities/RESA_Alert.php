<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Alert extends RESA_EntityDTO
{
	public static $CAPACITY_ALERT = 0;
	public static $REDUCTIONS_ALERT = 1;
	public static $TIMESLOT_ALERT = 2;
	public static $EQUIPMENT_ALERT = 3;

	private $id;
	private $idType;
	private $name;
	private $description;
	private $idService;
	private $startDate;
	private $endDate;
	private $idBooking;
	private $idAppointment;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_alert';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idType` int(11) NOT NULL,
		  `name` text NOT NULL,
		  `description` text NOT NULL,
		  `idService` int(11) NOT NULL,
		  `startDate` datetime NOT NULL,
		  `endDate` datetime NOT NULL,
		  `idBooking` int(11) NOT NULL,
		  `idAppointment` int(11) NOT NULL,
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
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Alert();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithDate($startDate, $endDate, $dataIdBooking = null, $dataIdAppointments = null) {
		$WHERE = '';
		if(isset($dataIdBooking) > 0) $WHERE = RESA_Tools::generateWhereClause($dataIdBooking, false);
		if(isset($dataIdAppointments) > 0){
			if(empty($WHERE)) $WHERE = RESA_Tools::generateWhereClause($dataIdAppointments, false);
			else $WHERE .= ' OR ' . RESA_Tools::generateWhereClause($dataIdAppointments, false);
		}
		$closeWhere = '(startDate <= \''.$startDate.'\' AND \''.$startDate.'\' < endDate ) OR
			(startDate < \''.$endDate.'\' AND \''.$endDate.'\' < endDate ) OR
			(\''.$startDate.'\' <= startDate  AND endDate <= \''.$endDate.'\') OR
			(\''.$startDate.'\' < startDate  AND startDate < \''.$endDate.'\')';
		if(empty($WHERE)) $WHERE .= ' ('. $closeWhere .') ';
		else $WHERE .= ' OR ('. $closeWhere .') ';
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE '. $WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Alert();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithIds($dataIdBooking = null, $dataIdAppointments = null) {
		$WHERE = '';
		if(isset($dataIdBooking) > 0) $WHERE = RESA_Tools::generateWhereClause($dataIdBooking, false);
		if(isset($dataIdAppointments) > 0){
			if(empty($WHERE)) $WHERE = RESA_Tools::generateWhereClause($dataIdAppointments, false);
			else $WHERE .= ' OR ' . RESA_Tools::generateWhereClause($dataIdAppointments, false);
		}
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE '. $WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Alert();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	public static function generateAlert($model){
		$alert = new RESA_Alert();
		$alert->setIdType($model['type']);
		$alert->setName($model['name']);
		$alert->setDescription($model['description']);
		return $alert;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idType = -1;
		$this->name = '';
		$this->description = '';
		$this->idService = -1;
		$this->startDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->endDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->idBooking = -1;
		$this->idAppointment = -1;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$this->linkWPDB->update(self::getTableName(), array(
				'idType' => $this->idType,
				'name' => $this->name,
				'description' => $this->description,
				'idService' => $this->idService,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'idBooking' => $this->idBooking,
				'idAppointment' => $this->idAppointment
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d'
			),
			array ('%d'));
		}
		else
		{
				$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idType' => $this->idType,
				'name' => $this->name,
				'description' => $this->description,
				'idService' => $this->idService,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'idBooking' => $this->idBooking,
				'idAppointment' => $this->idAppointment
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d'
			));
			$this->id = $this->linkWPDB->insert_id;
			$this->setLoaded(true);
		}
	}

	/**
	 * Save in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded())
		{
			$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"id":'.$this->id .',
			"idType":'.$this->idType.',
			"name":"'.$this->name.'",
			"description":"'.$this->description .'",
			"idService":"'.$this->idService .'",
			"startDate":"'.$this->startDate .'",
			"endDate":"'.$this->endDate .'",
			"idBooking":'.$this->idBooking .',
			"idAppointment":'.$this->idAppointment .'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idType = $json->idType;
		$this->name = $json->name;
		$this->description = $json->description;
		$this->idService = $json->idService;
		$this->startDate = $json->startDate;
		$this->endDate = $json->endDate;
		$this->idBooking = $json->idBooking;
		$this->idAppointment = $json->idAppointment;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setIdType($idType){ $this->idType = $idType; }
	public function setName($name){ $this->name = $name; }
	public function setDescription($description){ $this->description = $description; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setStartDate($startDate){ $this->startDate = $startDate; }
	public function setEndDate($endDate){ $this->endDate = $endDate; }
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setIdAppointment($idAppointment){ $this->idAppointment = $idAppointment; }


	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdType(){ return $this->idType; }
	public function getName(){ return $this->name; }
	public function getDescription(){ return $this->description; }
	public function getIdService(){ return $this->idService; }
	public function getStartDate(){ return $this->startDate; }
	public function getEndDate(){ return $this->endDate; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getIdAppointment(){ return $this->idAppointment; }
	public function isAssignToBooking(){ return $this->idBooking != -1; }
	public function cloneMe(){
		$alert = new RESA_Alert();
		$alert->fromJSON(json_decode($this->toJSON()));
		return $alert;
	}


	public function isSameDate($alert){
		$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $alert->getStartDate());
		$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $alert->getEndDate());
		$thisStartDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getStartDate());
		$thisEndDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->getEndDate());
		return RESA_Tools::resetTimeToDate($startDate) == RESA_Tools::resetTimeToDate($thisStartDate) &&
			RESA_Tools::resetTimeToDate($endDate) == RESA_Tools::resetTimeToDate($thisEndDate);
	}
}
