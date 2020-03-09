<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_InfoCalendar extends RESA_EntityDTO
{
	private $id;
	private $idPlaces;
	private $idCustomer;
	private $date;
	private $dateEnd;
	private $startTime;
	private $endTime;
	private $note;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_info_calendar';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idUserCreator` int(11) NOT NULL,
		  `idPlaces` TEXT NOT NULL,
		  `date` datetime NOT NULL,
		  `dateEnd` datetime NOT NULL,
		  `startTime` TEXT NOT NULL,
		  `endTime` TEXT NOT NULL,
		  `note` TEXT NOT NULL,
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
			$entity = new RESA_InfoCalendar();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 *
	 */
	 public static function generateWhereWithPlace($idPlaces = array()){
		 $whereIdPlace = '';
 			if(count($idPlaces) > 0){
	 			foreach($idPlaces as $place){
	 				if(!empty($whereIdPlace)) $whereIdPlace .= ' OR ';
	 				$whereIdPlace .= ' idPlaces LIKE \'%,'.$place.',%\'';
	 			}
	 			$whereIdPlace = ' AND (( ' . $whereIdPlace . ' ) OR idPlaces="")';
 		 }
		 return $whereIdPlace;
	 }


	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithDate($startDate, $endDate, $idPlaces) {
		$closeWhere = 'WHERE ((date <= \''.$startDate.'\' AND \''.$startDate.'\' < dateEnd ) OR
			(date < \''.$endDate.'\' AND \''.$endDate.'\' < dateEnd ) OR
			(\''.$startDate.'\' <= date  AND dateEnd <= \''.$endDate.'\') OR
			(\''.$startDate.'\' < date  AND date < \''.$endDate.'\'))';
		$closeWhere .= self::generateWhereWithPlace($idPlaces);

		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '. $closeWhere .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_InfoCalendar();
			$entity->fromJSON($result);
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
		$this->idUserCreator = -1;
		$this->idPlaces = '';
		$this->date = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->dateEnd = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->startTime = '';
		$this->endTime = '';
		$this->note = '';
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
	 * reload data
	 */
	public function reload(){
		$this->loadById($this->id);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastBooking = new RESA_InfoCalendar();
			$lastBooking->loadById($this->id);

			$this->linkWPDB->update(self::getTableName(), array(
				'idUserCreator' => $this->idUserCreator,
				'idPlaces' => $this->idPlaces,
				'date' => $this->date,
				'dateEnd' => $this->dateEnd,
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'note' => $this->note
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
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idUserCreator' => $this->idUserCreator,
				'idPlaces' => $this->idPlaces,
				'date' => $this->date,
				'dateEnd' => $this->dateEnd,
				'startTime' => $this->startTime,
				'endTime' => $this->endTime,
				'note' => $this->note
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
			));
			$this->id = $this->linkWPDB->insert_id;
			$this->setLoaded(true);
		}
	}

	/**
	 * Delete in database
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
			"id":'. $this->id .',
			"idUserCreator":'. $this->idUserCreator .',
			"idPlaces":"'. $this->idPlaces .'",
			"date":"'.$this->date.'",
			"dateEnd":"'.$this->dateEnd.'",
			"startTime":"'.$this->startTime.'",
			"endTime":"'.$this->endTime.'",
			"note":"'.$this->note.'"
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idUserCreator = $json->idUserCreator;
		if(isset($json->idPlaces)) $this->idPlaces = $json->idPlaces;
		$this->date = $json->date;
		if(isset($json->dateEnd)) $this->dateEnd = $json->dateEnd;
		if(isset($json->startTime)) $this->startTime = $json->startTime;
		if(isset($json->endTime)) $this->endTime = $json->endTime;
		$this->note = str_replace(array("\n", "\r", "\t"), '', nl2br(esc_html($json->note)));
		if($this->id != -1)	$this->setLoaded(true);
	}

	public function getId(){ return $this->id; }
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function setIdUserCreator($idUserCreator){ $this->idUserCreator = $idUserCreator;}
	public function getIdUserCreator(){ return $this->idUserCreator; }

	public function setIdPlaces($idPlaces){ $this->idPlaces = $idPlaces; }
	public function getIdPlaces(){ return $this->idPlaces; }

	public function setDate($date){ $this->date = $date; }
	public function getDate(){ return $this->date; }

	public function setDateEnd($dateEnd){ $this->dateEnd = $dateEnd; }
	public function getDateEnd(){ return $this->dateEnd; }

	public function setStartTime($startTime){ $this->startTime = $startTime; }
	public function getStartTime(){ return $this->startTime; }

	public function setEndTime($endTime){ $this->endTime = $endTime; }
	public function getEndTime(){ return $this->endTime; }

	public function setNote($note){ $this->note = $note; }
	public function getNote(){ return $this->note; }
}
