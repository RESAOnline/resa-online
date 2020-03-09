<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_MemberAvailability extends RESA_EntityDTO
{
	private $id;
	private $idMember;
	private $name;
	private $color;
	private $startTime;
	private $endTime;
	private $dates;
	private $idCalendar;
	private $memberAvailabilityServices;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_member_availability';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `idMember` int(11) NOT NULL,
				`name` TEXT NOT NULL,
				`color` TEXT NOT NULL,
			  `startTime` time NOT NULL,
			  `endTime` time NOT NULL,
				`dates` TEXT NOT NULL,
				`idCalendar` VARCHAR(80) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idMember` (`idMember`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_IdMember` FOREIGN KEY (`idMember`) REFERENCES `'.RESA_Member::getTableName().'` (`id`) ON DELETE CASCADE;';
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
		$results = $wpdb->get_results('SELECT * FROM '.self::getTableName().' WHERE '.self::getTableName().'.idCalendar <> \'\'');
		$allData = array();
		foreach($results as $result){
			$entity = new RESA_MemberAvailability();
			$entity->fromJSON($result);
			$entity->setMemberAvailabilityServices(RESA_MemberAvailabilityService::getAllData(array('idMemberAvailability'=>$entity->getId())));
			$entity->setLoaded(true);
			array_push($allData, $entity);
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
			$entity = new RESA_MemberAvailability();
			$entity->fromJSON($result);
			$entity->setMemberAvailabilityServices(RESA_MemberAvailabilityService::getAllData(array('idMemberAvailability'=>$entity->getId())));
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
		$this->idMember = -1;
		$this->name = '';
		$this->color = '#008000';
		$this->startTime = '00:00:00';
		$this->endTime = '00:00:00';
		$this->dates = '';
		$this->idCalendar = '';
		$this->memberAvailabilityServices = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setMemberAvailabilityServices(RESA_MemberAvailabilityService::getAllData(array('idMemberAvailability'=>$this->getId())));
		$this->setLoaded(true);
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastMemberAvailability = new RESA_MemberAvailability();
			$lastMemberAvailability->loadById($this->id);
			$lastMemberAvailabilityServices = $lastMemberAvailability->getMemberAvailabilityServices();
			$lastIdServices = array();
			foreach($lastMemberAvailabilityServices as $memberAvailabilityService) {
				array_push($lastIdServices, $memberAvailabilityService->getIdService());
			}
			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'idMember' =>$this->idMember,
				'name' =>$this->name,
				'color' =>$this->color,
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'dates' => $this->dates,
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

			$idServices = array();
			for($i = 0; $i < count($this->memberAvailabilityServices); $i++) {
				if(in_array($this->memberAvailabilityServices[$i]->getIdService(), $lastIdServices)) {
					$this->memberAvailabilityServices[$i]->setLoaded(true);
					array_push($idServices, $this->memberAvailabilityServices[$i]->getIdService());
				}
				$this->memberAvailabilityServices[$i]->save();
			}

			//delete
			for($i = 0; $i < count($lastMemberAvailabilityServices); $i++) {
				if(!in_array($lastMemberAvailabilityServices[$i]->getIdService(), $idServices))
					$lastMemberAvailabilityServices[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idMember' =>$this->idMember,
				'name' =>$this->name,
				'color' =>$this->color,
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'dates' => $this->dates,
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
			$idMemberAvailability = $this->linkWPDB->insert_id;
			$this->id = $idMemberAvailability;
			for($i = 0; $i < count($this->memberAvailabilityServices); $i++)
			{
				$this->memberAvailabilityServices[$i]->setIdMemberAvailability($idMemberAvailability);
				$this->memberAvailabilityServices[$i]->save();
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
			for($i = 0; $i < count($this->memberAvailabilityServices); $i++) {
				$this->memberAvailabilityServices[$i]->deleteMe();
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
		 "idMember":'.$this->idMember.',
		 "name":"'.$this->name.'",
		 "color":"'.$this->color.'",
		 "startTime":"'.$this->startTime.'",
		 "endTime":"'.$this->endTime.'",
		 "dates":"'.$this->dates.'",
		 "idCalendar":"'.$this->idCalendar.'",
		 "memberAvailabilityServices":'.RESA_Tools::formatJSONArray($this->memberAvailabilityServices).'
		}';
	}
	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idMember = $json->idMember;
		$this->startTime = $json->startTime;
		$this->endTime = $json->endTime;
		if(isset($json->name)) $this->name = $json->name;
		if(isset($json->color)) $this->color = $json->color;
		if(isset($json->dates)) $this->dates = $json->dates;
		if(isset($json->idCalendar)) $this->idCalendar = $json->idCalendar;
		if(isset($json->memberAvailabilityServices))
		{
			$memberAvailabilityServices = array();
			for($i = 0; $i < count($json->memberAvailabilityServices); $i++)
			{
				$memberAvailabilityService = new RESA_MemberAvailabilityService();
				$memberAvailabilityService->fromJSON($json->memberAvailabilityServices[$i]);
				array_push($memberAvailabilityServices, $memberAvailabilityService);
			}
			$this->setMemberAvailabilityServices($memberAvailabilityServices);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}


	/**
	 * Return the capacity associated to a service
	 */
	public function getCapacityService($idService){
		foreach($this->memberAvailabilityServices as $memberAvailabilityService){
			if($memberAvailabilityService->getIdService() == $idService){
				return $memberAvailabilityService->getCapacity();
			}
		}
		return -1;
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function setIdMember($idMember){ $this->idMember = $idMember; }
	public function getIdMember(){ return $this->idMember; }
	public function setName($name){ $this->name = $name; }
	public function getName(){ return $this->name; }
	public function setColor($color){ $this->color = $color; }
	public function getColor(){ return $this->color; }
	public function getStartTime(){ return $this->startTime; }
	public function getEndTime(){ return $this->endTime; }
	public function setDates($dates){ $this->dates = $dates; }
	public function getDates(){ return $this->dates; }
	public function getArrayDates(){ return explode(',', $this->dates); }
	public function setIdCalendar($idCalendar){ $this->idCalendar = $idCalendar; }
	public function getIdCalendar(){ return $this->idCalendar; }
	public function setMemberAvailabilityServices($memberAvailabilityServices){ $this->memberAvailabilityServices = $memberAvailabilityServices; }
	public function getMemberAvailabilityServices(){ return $this->memberAvailabilityServices; }
}
