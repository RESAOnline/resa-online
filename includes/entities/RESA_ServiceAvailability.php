<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_ServiceAvailability extends RESA_EntityDTO
{
	private $id;
	private $idService;
	private $name;
	private $color;
	private $dates;
	private $groupDates;
	private $manyDates;
	private $idCalendar;
	private $timeslots;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_service_availability';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `idService` int(11) NOT NULL,
				`name` TEXT NOT NULL,
				`color` TEXT NOT NULL,
				`dates` TEXT NOT NULL,
				`groupDates` TEXT NOT NULL,
				`manyDates` TEXT NOT NULL,
				`idCalendar` VARCHAR(80) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idService` (`idService`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'`
		ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE;';
	}

	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllCalendarSynchronized($data = array())
	{
		global $wpdb;
		$results = $wpdb->get_results('SELECT '.self::getTableName().'.id FROM '.self::getTableName().' INNER JOIN '.RESA_Service::getTableName().' ON '.self::getTableName().'.idService = '.RESA_Service::getTableName().'.id AND '.RESA_Service::getTableName().'.oldService=\'0\' WHERE '.self::getTableName().'.idCalendar <> \'\'');
		/*
		$results = $wpdb->get_results('SELECT '.self::getTableName().'.id FROM '.self::getTableName().' INNER JOIN '.RESA_Service::getTableName().' ON '.self::getTableName().'.idService = '.RESA_Service::getTableName().'.id AND '.RESA_Service::getTableName().'.oldService=\'0\' WHERE '.self::getTableName().'.idCalendar <> \'\'');
		*/
		$allData = array();
		foreach($results as $result){
			$entity = new RESA_ServiceAvailability();
			$entity->loadById($result->id);
			if($entity->isLoaded()){
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_ServiceAvailability();
			$entity->fromJSON($result, true);
			$entity->setTimeslots(RESA_ServiceTimeslot::getAllData(array('idServiceAvailability'=>$entity->getId())));
			$entity->setLoaded(true);
			array_push($allData, $entity);
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
		$this->idService = -1;
		$this->name = '';
		$this->color = '#008000';
		$this->dates = '';
		$this->groupDates = array();
		$this->manyDates = array();
		$this->idCalendar = '';
		$this->timeslots = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result, true);
		$this->setTimeslots(RESA_ServiceTimeslot::getAllData(array('idServiceAvailability'=>$this->getId())));
		$this->setLoaded(true);
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastServiceAvailability = new RESA_ServiceAvailability();
			$lastServiceAvailability->loadById($this->id);
			$lastTimeslots = $lastServiceAvailability->getTimeslots();

			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'idService' =>$this->idService,
				'name' =>$this->name,
				'color' =>$this->color,
				'dates' => $this->dates,
				'groupDates' => serialize($this->groupDates),
				'manyDates' => serialize($this->manyDates),
				'idCalendar' => $this->idCalendar
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array ('%d'));
			$idTimeslots = array();
			for($i = 0; $i < count($this->timeslots); $i++) {
				if(!$this->timeslots[$i]->isNew())
					array_push($idTimeslots, $this->timeslots[$i]->getId());
				$this->timeslots[$i]->save();
			}
			for($i = 0; $i < count($lastTimeslots); $i++) {
				if(!in_array($lastTimeslots[$i]->getId(), $idTimeslots))
					$lastTimeslots[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idService' =>$this->idService,
				'name' =>$this->name,
				'color' =>$this->color,
				'dates' => $this->dates,
				'groupDates' => serialize($this->groupDates),
				'manyDates' => serialize($this->manyDates),
				'idCalendar' => $this->idCalendar
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			));
			$this->id = $this->linkWPDB->insert_id;
			$idServiceAvailability = $this->linkWPDB->insert_id;
			for($i = 0; $i < count($this->timeslots); $i++)
			{
				$this->timeslots[$i]->setIdServiceAvailability($idServiceAvailability);
				$this->timeslots[$i]->save();
			}
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
			foreach($this->timeslots as $timeslots){ $timeslots->deleteMe(); }
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
		 "idService":'.$this->idService.',
		 "name":"'.$this->name.'",
		 "color":"'.$this->color.'",
		 "dates":"'.$this->dates.'",
		 "groupDates":'.json_encode($this->groupDates).',
		 "manyDates":'.json_encode($this->manyDates).',
		 "idCalendar":"'.$this->idCalendar.'",
		 "timeslots":'.RESA_Tools::formatJSONArray($this->timeslots).'
		}';
	}
	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		$this->idService = $json->idService;
		if(isset($json->name)) $this->name = $json->name;
		if(isset($json->color)) $this->color = $json->color;
		if(isset($json->dates)) $this->dates = $json->dates;
		if(isset($json->groupDates)){
			if(is_array($json->groupDates)){
				$this->groupDates = $json->groupDates;
			}else if($json->groupDates!=false) {
				$this->groupDates = unserialize($json->groupDates);
			}
		}
		if(isset($json->manyDates)){
			if(is_array($json->manyDates)){
				$this->manyDates = $json->manyDates;
			}else if($json->manyDates!=false) {
				$this->manyDates = unserialize($json->manyDates);
			}
		}
		if(isset($json->idCalendar)) $this->idCalendar = $json->idCalendar;
		if(isset($json->timeslots))
		{
			$timeslots = array();
			for($i = 0; $i < count($json->timeslots); $i++)
			{
				$timeslot = new RESA_ServiceTimeslot();
				$timeslot->fromJSON($json->timeslots[$i], $formDatabase);
				array_push($timeslots, $timeslot);
			}
			$this->setTimeslots($timeslots);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}


	public function isNew(){ return $this->id == -1; }
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->timeslots as $timeslots){
			$timeslots->setNew();
		}
	}
	public function getId(){ return $this->id; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function getIdService(){ return $this->idService; }
	public function setName($name){ $this->name = $name; }
	public function getName(){ return $this->name; }
	public function setColor($color){ $this->color = $color; }
	public function getColor(){ return $this->color; }
	public function setDates($dates){ $this->dates = $dates; }
	public function getDates(){ return $this->dates; }
	public function getArrayDates(){ return explode(',', $this->dates); }
	public function setGroupDates($groupDates){ $this->groupDates = $groupDates; }
	public function getGroupDates(){ return $this->groupDates; }
	public function setManyDates($manyDates){ $this->manyDates = $manyDates; }
	public function getManyDates(){ return $this->manyDates; }
	public function setIdCalendar($idCalendar){ $this->idCalendar = $idCalendar; }
	public function getIdCalendar(){ return $this->idCalendar; }
	public function isInGroupDates($date){
		foreach($this->groupDates as $dates){
			$startDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $dates->startDate));
			if(empty($startDate)) $startDate = RESA_Tools::resetTimeToDate(new DateTime($dates->startDate));
			$endDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $dates->endDate));
			if(empty($endDate)) $endDate = RESA_Tools::resetTimeToDate(new DateTime($dates->endDate));
			if($startDate <= $date && $date <= $endDate) {
				return true;
			}
		}
		return false;
	}
	public function isInManyDates($date){
		foreach($this->manyDates as $dates){
			$exploded = explode(',', $dates);
			foreach($exploded as $localDate){
				$formatLocalDate = RESA_Tools::resetTimeToDate(DateTime::createFromFormat('Y-m-d', $localDate));
				if($formatLocalDate == $date){
					return true;
				}
			}
		}
		return false;
	}
	public function setTimeslots($timeslots){ $this->timeslots = $timeslots; }
	public function getTimeslots(){ return $this->timeslots; }
	public function getFilteredTimeslots($isBackEnd, $typeAccount){
		$result = array();
		foreach($this->timeslots as $timeslot){
			if($isBackEnd || (!$isBackEnd && !$timeslot->isPrivate() && $timeslot->haveTypeAccount($typeAccount))){
				array_push($result, $timeslot);
			}
		}
		return $result;
	}
}
