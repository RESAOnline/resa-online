<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_ServiceTimeslot extends RESA_EntityDTO
{
	public static $CAPACITY_MEMBERS = 0;
	public static $CAPACITY_FIXED = 1;

	private $id;
	private $idServiceAvailability;
	private $startTime;
	private $endTime;
	private $capacity;
	private $typeCapacity;
	private $equipmentsActivated;
	private $noStaff;
	private $exclusive;
	private $maxAppointments;
	private $membersExclusive;
	private $activateExclusiveFixedCapacity;
	private $overCapacity;
	private $noEnd;
	private $idParameter;
	private $idsServicePrices;
	private $typesAccounts;
	private $idMention;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_service_timeslot';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idServiceAvailability` int(11) NOT NULL,
		  `startTime` time NOT NULL,
		  `endTime` time NOT NULL,
		  `capacity` int(11) NOT NULL,
		  `typeCapacity` int(11) NOT NULL,
		  `equipmentsActivated` tinyint(1) NOT NULL,
		  `noStaff` tinyint(1) NOT NULL,
		  `exclusive` tinyint(1) NOT NULL,
		  `maxAppointments` int(11) NOT NULL,
		  `membersExclusive` tinyint(1) NOT NULL,
		  `activateExclusiveFixedCapacity` tinyint(1) NOT NULL,
		  `overCapacity` tinyint(1) NOT NULL,
		  `noEnd` tinyint(1) NOT NULL,
		  `idParameter` int(11) NOT NULL,
			`idsServicePrices` TEXT NOT NULL,
			`idMention` TEXT NOT NULL,
			`typesAccounts` TEXT NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idServiceAvailability` (`idServiceAvailability`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idServiceAvailability` FOREIGN KEY (`idServiceAvailability`) REFERENCES `'.RESA_ServiceAvailability::getTableName().'` (`id`) ON DELETE CASCADE;';
	}


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
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY startTime, endTime');
		foreach($results as $result){
			$entity = new RESA_ServiceTimeslot();
			$entity->fromJSON($result, true);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}


	/**
	 * compare the timeslots by startDate and endDate
	 */
	public static function compare(RESA_ServiceTimeslot $timeslot1, RESA_ServiceTimeslot $timeslot2){
		$value = 0;
		$startDate1 = RESA_Algorithms::timeToDate($timeslot1->getStartTime());
		$startDate2 = RESA_Algorithms::timeToDate($timeslot2->getStartTime());
		if($startDate1 == $startDate2){
			$endDate1 = RESA_Algorithms::timeToDate($timeslot1->getEndTime());
			$endDate2 = RESA_Algorithms::timeToDate($timeslot2->getEndTime());
			if($endDate1 == $endDate2) return 0;
			return ($endDate1 > $endDate2) ? 1 : -1;
		}
		return ($startDate1 > $startDate2) ? 1 : -1;
		return $value;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idServiceAvailability = 0;
		$this->startTime = '00:00:00';
		$this->endTime = '00:00:00';
		$this->capacity = 20;
		$this->typeCapacity = 0;
		$this->equipmentsActivated = true;
		$this->noStaff = 0;
		$this->exclusive = 0;
		$this->maxAppointments = 1;
		$this->membersExclusive = 0;
		$this->activateExclusiveFixedCapacity = 0;
		$this->overCapacity = 0;
		$this->noEnd = 0;
		$this->idParameter = 1;
		$this->idsServicePrices = '';
		$this->idMention = '';
		$this->typesAccounts = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result, true);
		$this->setLoaded(true);
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'capacity' => $this->capacity,
				'typeCapacity' => $this->typeCapacity,
				'equipmentsActivated' => $this->equipmentsActivated,
				'noStaff' => $this->noStaff,
				'exclusive' => $this->exclusive,
				'maxAppointments' => $this->maxAppointments,
				'membersExclusive' => $this->membersExclusive,
				'activateExclusiveFixedCapacity' => $this->activateExclusiveFixedCapacity,
				'overCapacity' => $this->overCapacity,
				'noEnd' => $this->noEnd,
				'idParameter' => $this->idParameter,
				'idsServicePrices' => $this->idsServicePrices,
				'idMention' => $this->idMention,
				'typesAccounts' => serialize($this->typesAccounts),
			),
			array('id'=>$this->id),
			array (
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			),
			array ('%d'));

		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idServiceAvailability' =>$this->idServiceAvailability,
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'capacity' => $this->capacity,
				'typeCapacity' => $this->typeCapacity,
				'equipmentsActivated' => $this->equipmentsActivated,
				'noStaff' => $this->noStaff,
				'exclusive' => $this->exclusive,
				'maxAppointments' => $this->maxAppointments,
				'membersExclusive' => $this->membersExclusive,
				'activateExclusiveFixedCapacity' => $this->activateExclusiveFixedCapacity,
				'overCapacity' => $this->overCapacity,
				'noEnd' => $this->noEnd,
				'idParameter' => $this->idParameter,
				'idsServicePrices' => $this->idsServicePrices,
				'idMention' => $this->idMention,
				'typesAccounts' => serialize($this->typesAccounts),
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
			));
			$this->id = $this->linkWPDB->insert_id;
			$this->setLoaded(true);
			return $this->id;
		}
	}

	/**
	 * Save in database
	 */
	public function deleteMe()
	{
		if($this->isLoaded())
		{
			$this->linkWPDB->delete(self::getTableName(), array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 *
	 */
	public function formatArrayNullValue($param){
		$splitted = explode(',', $param);
		$format = '';
		for($i = 0; $i < count($splitted); $i++){
			if($i != 0) $format .=',';
			if(isset($splitted[$i]) && $splitted[$i] != 'null' && !empty($splitted[$i])){
				$format .= $splitted[$i];
			}
			else $format .= '-1';
		}
		return $format;
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		if(empty($this->idsServicePrices)) $idsServicePrices = '[]';
		else {
			$idsServicePrices = '[' . $this->formatArrayNullValue($this->idsServicePrices) . ']';
		}
		return '{
			"id":'.$this->id.',
			"idServiceAvailability":'.$this->idServiceAvailability.',
			"startTime":"'.$this->startTime.'",
			"endTime":"'.$this->endTime.'",
			"capacity":'.$this->capacity.',
			"typeCapacity":'.$this->typeCapacity.',
			"equipmentsActivated":'.RESA_Tools::toJSONBoolean($this->equipmentsActivated).',
			"noStaff":'.RESA_Tools::toJSONBoolean($this->noStaff).',
			"exclusive":'.RESA_Tools::toJSONBoolean($this->exclusive).',
			"maxAppointments":'.$this->maxAppointments.',
			"membersExclusive":'.$this->membersExclusive.',
			"activateExclusiveFixedCapacity":'.$this->activateExclusiveFixedCapacity.',
			"overCapacity":'.RESA_Tools::toJSONBoolean($this->overCapacity).',
			"noEnd":'.RESA_Tools::toJSONBoolean($this->noEnd).',
			"idParameter":'.$this->idParameter.',
			"idsServicePrices":'.$idsServicePrices.',
 		 	"typesAccounts":'.json_encode($this->typesAccounts).',
			"idMention":"'.$this->idMention.'",
			"allServicePrices":'.RESA_Tools::toJSONBoolean(empty($this->idsServicePrices)).'
		}';
	}

	/**
	 * Return this to array value
	 */
	public function toArray()
	{
		if(empty($this->idsServicePrices)) $idsServicePrices = '[]';
		else {
			$idsServicePrices = '[' . $this->formatArrayNullValue($this->idsServicePrices) . ']';
		}
		return array(
			'id' => $this->id,
			'startTime' => $this->startTime,
			'endTime' => $this->endTime,
			'capacity' => $this->capacity,
			'typeCapacity' => $this->typeCapacity,
			'equipmentsActivated' => $this->equipmentsActivated,
			'noStaff' => $this->isNoStaff(),
			'exclusive' => $this->exclusive,
			'maxAppointments' => $this->maxAppointments,
			'membersExclusive' => $this->membersExclusive,
			'activateExclusiveFixedCapacity' => $this->activateExclusiveFixedCapacity,
			'overCapacity' => $this->overCapacity,
			'noEnd' => $this->noEnd,
			'idParameter' => $this->idParameter,
			'idsServicePrices' => json_decode($idsServicePrices),
			'idMention' => $this->idMention
		);
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		$this->idServiceAvailability = $json->idServiceAvailability;
		$this->startTime = $json->startTime;
		$this->endTime = $json->endTime;
		$this->capacity = $json->capacity;
		if(isset($json->typeCapacity)) $this->typeCapacity = $json->typeCapacity;
		if(isset($json->equipmentsActivated)) $this->equipmentsActivated = $json->equipmentsActivated;
		$this->noStaff = $json->noStaff;
		$this->exclusive = $json->exclusive;
		if(isset($json->maxAppointments)) $this->maxAppointments = $json->maxAppointments;
		if(isset($json->membersExclusive)) $this->membersExclusive = $json->membersExclusive;
		if(isset($json->activateExclusiveFixedCapacity)) $this->activateExclusiveFixedCapacity = $json->activateExclusiveFixedCapacity;
		if(isset($json->overCapacity)) $this->overCapacity = $json->overCapacity;
		if(isset($json->noEnd)) $this->noEnd = $json->noEnd;
		if(isset($json->idParameter)) $this->idParameter = $json->idParameter;
		if(isset($json->idsServicePrices)){
			if(is_array($json->idsServicePrices)){
				$this->idsServicePrices = implode(',', $json->idsServicePrices);
				if(count($json->idsServicePrices) > 0){
					$this->idsServicePrices = $this->formatArrayNullValue($this->idsServicePrices);
				}
			}
			else {
				$this->idsServicePrices = $json->idsServicePrices;
			}
		}
		if(isset($json->idMention)) $this->idMention = $json->idMention;
		if(isset($json->typesAccounts) && !empty($json->typesAccounts)){
			if(!$formDatabase){
				$this->typesAccounts = $json->typesAccounts;
			}else {
				$this->typesAccounts = unserialize($json->typesAccounts);
			}
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	public function isNew(){ return $this->id == -1; }
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function getId(){ return $this->id; }
	public function getStartTime(){ return $this->startTime; }
	public function getEndTime(){ return $this->endTime; }
	public function getCapacity(){ return $this->capacity; }
	public function getTypeCapacity(){ return $this->typeCapacity; }
	public function isEquipmentsActivated(){ return $this->equipmentsActivated; }
	public function isNoStaff(){ return $this->typeCapacity == 2 && $this->noStaff; }
	public function isExclusive(){ return $this->exclusive; }
	public function getMaxAppointments(){ return $this->maxAppointments; }
	public function isMembersExclusive(){ return $this->membersExclusive; }
	public function isActivateExclusiveFixedCapacity(){ return $this->activateExclusiveFixedCapacity; }
	public function getIdServiceAvailability(){ return $this->idServiceAvailability; }
	public function isOverCapacity(){ return $this->overCapacity; }
	public function isNoEnd(){ return $this->noEnd; }
	public function getIdParameter(){ return $this->idParameter; }
	public function getParameter(){ return $this->idParameter; }
	public function getIdsServicePrices(){ return $this->idsServicePrices; }
	public function getIdMention(){ return $this->idMention; }
	public function getTypesAccounts(){ return $this->typesAccounts; }
	public function isInIdsServicePrices($idServicePrice){
		if(empty($this->idsServicePrices)) return true;
		else {
			$splitted = explode(',', $this->idsServicePrices);
			return in_array($idServicePrice, $splitted);
		}
	}

	public function setExclusive($exclusive){ $this->exclusive = $exclusive; }
	public function setMaxAppointments($maxAppointments){ $this->maxAppointments = $maxAppointments; }
	public function setMembersExclusive($membersExclusive){ $this->membersExclusive = $membersExclusive; }
	public function setActivateExclusiveFixedCapacity($activateExclusiveFixedCapacity){ $this->activateExclusiveFixedCapacity = $activateExclusiveFixedCapacity; }
	public function setIdServiceAvailability($idServiceAvailability){ $this->idServiceAvailability = $idServiceAvailability; }
	public function setOverCapacity($overCapacity){ $this->overCapacity = $overCapacity; }
	public function setNoEnd($noEnd){ $this->noEnd = $noEnd; }
	public function setIdParameter($idParameter){ $this->idParameter = $idParameter; }
	public function setIdsServicePrices($idsServicePrices){ $this->idsServicePrices = $idsServicePrices; }
	public function setIdMention($idMention){ $this->idMention = $idMention; }

	public function isPrivate(){ return count($this->typesAccounts) == 1 && $this->typesAccounts[0] == 'private'; }
	public function haveTypeAccount($typeAccount){
		return count($this->typesAccounts) == 0 ||
			(count($this->typesAccounts) > 0 && $this->typesAccounts[0] != 'private' && in_array($typeAccount, $this->typesAccounts));
	}
}
