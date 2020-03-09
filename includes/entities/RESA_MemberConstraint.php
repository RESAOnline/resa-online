<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_MemberConstraint extends RESA_EntityDTO
{
	public static $CLOSED = 0;

	private $id;
	private $idService;
	private $idMember;
	private $startDate;
	private $endDate;
  private $state;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_member_constraint';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idService` int(11) NOT NULL,
		  `idMember` int(11) NOT NULL,
		  `startDate` datetime NOT NULL,
		  `endDate` datetime NOT NULL,
		  `state` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idService` (`idService`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idMember` FOREIGN KEY (`idMember`) REFERENCES `'.RESA_Member::getTableName().'` (`id`)  ON DELETE CASCADE;';
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
			$entity = new RESA_MemberConstraint();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataInterval($idMember, $startDate, $endDate)
	{
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE idMember = '.$idMember.' && (startDate <= \''.$startDate.'\' AND endDate > \''.$startDate.'\') ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_MemberConstraint();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithDate($startDate, $endDate) {
		$WHERE = 'WHERE (startDate <= \''.$startDate.'\' AND \''.$startDate.'\' < endDate ) OR
			(startDate < \''.$endDate.'\' AND \''.$endDate.'\' < endDate ) OR
			(\''.$startDate.'\' <= startDate  AND endDate <= \''.$endDate.'\') OR
			(\''.$startDate.'\' < startDate  AND startDate < \''.$endDate.'\')';
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '. $WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_MemberConstraint();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * delete all data with id Service
	 */
	public static function deleteAllByIdMember($idMember){
		global $wpdb;
		$wpdb->delete(self::getTableName(), array('idMember'=>$idMember), array ('%d'));
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idService = -1;
		$this->idMember = -1;
		$this->startDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->endDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->state = self::$CLOSED;
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
				'idService' => $this->idService,
				'idMember' => $this->idMember,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'state' => $this->state
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idService' => $this->idService,
				'idMember' => $this->idMember,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'state' => $this->state
			),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
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

	public function getMemberConstraintLiteJSON(){
		$json = json_decode($this->toJSON());
		$member = RESA_Member::getMemberLite($this->getIdMember());
		$json->member = $member->nickname;
		$json->places = $member->places;
		return $json;
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"id":'.$this->id .',
			"idService":"'.$this->idService .'",
			"idMember":"'.$this->idMember .'",
			"startDate":"'.$this->startDate .'",
			"endDate":"'.$this->endDate .'",
			"state":'.$this->state .',
			"type":"constraint"
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idService = $json->idService;
		$this->idMember = $json->idMember;
		$this->startDate = $json->startDate;
		$this->endDate = $json->endDate;
		$this->state = $json->state;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){ $this->id = -1; $this->setLoaded(false); }
	public function setIdType($idType){ $this->idType = $idType; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdMember($idMember){ $this->idMember = $idMember; }
	public function setStartDate($startDate){ $this->startDate = $startDate; }
	public function setEndDate($endDate){ $this->endDate = $endDate; }
	public function setState($state){ $this->state = $state; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdService(){ return $this->idService; }
	public function getIdMember(){ return $this->idMember; }
	public function getStartDate(){ return $this->startDate; }
	public function getEndDate(){ return $this->endDate; }
	public function getState(){ return $this->state; }
	public function cloneMe(){
		$alert = new RESA_MemberConstraint();
		$alert->fromJSON(json_decode($this->toJSON()));
		return $alert;
	}

	public function updateService($oldService, $newService){
		if($this->idService == $oldService->getId()){
			$this->idService = $newService->getId();
		}
	}
}
