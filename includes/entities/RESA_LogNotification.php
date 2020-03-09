<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_LogNotification extends RESA_EntityDTO
{
	public static $ADD_NEW_BOOKING = 0;

	private $id;
	private $idType;
	private $idBooking;
	private $idCustomer;
	private $idUserCreator;
	private $creationDate;
	private $expirationDate;
	private $idPlaces;
	private $text;
	private $criticity;
	private $seen;
	private $clicked;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_log_notification';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idType` int(11) NOT NULL,
		  `idBooking` int(11) NOT NULL,
		  `idCustomer` int(11) NOT NULL,
		  `idUserCreator` int(11) NOT NULL,
		  `creationDate` datetime NOT NULL,
		  `expirationDate` datetime NOT NULL,
		  `idPlaces` TEXT NOT NULL,
		  `text` TEXT NOT NULL,
		  `criticity` int(11) NOT NULL,
		  `seen` TEXT NOT NULL,
		  `clicked` tinyint(1) NOT NULL,
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
	 * return the number of all data filtered
	 */
	public static function countData($data = array()){
		$WHERE = RESA_Tools::generateWhereClause($data);
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * return the number of all data filtered
	 */
	public static function countDataIdCustomer($idCustomer, $idPlaces = array()){
		global $wpdb;
		$whereQuery = 'WHERE seen NOT LIKE \'%,'.$idCustomer.',%\' AND seen <> \'1\' AND criticity=0';
		$whereQuery .= RESA_Tools::generateWhereWithPlace($idPlaces);
		$whereQuery .= ' ORDER BY id';
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' ' . $whereQuery);
		if(!isset($count)) $count = 0;
		return $count;
	}


	/**
	 *
	 */
	public static function getAllDataNotSeen($idCustomer){
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE seen NOT LIKE \'%,'.$idCustomer.',%\' AND seen <> \'1\' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_LogNotification();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * set all seen to true
	 */
	public static function seeAllLogNotifications(){
		global $wpdb;
		$wpdb->query('UPDATE '. self::getTableName() . ' SET seen=1 ');
	}

	/**
	 * set all clicked to true
	 */
	public static function clickAllLogNotifications($lastId = 0){
		global $wpdb;
		$wpdb->query('UPDATE '. self::getTableName() . ' SET clicked=1 WHERE id >= ' . $lastId);
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array(), $idPlaces = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$WHERE .= RESA_Tools::generateWhereWithPlace($idPlaces);
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id DESC');
		foreach($results as $result){
			$entity = new RESA_LogNotification();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type with limit
	 */
	public static function getAllDataWithLimit($data, $idPlaces = array(), $limit = 10)
  {
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$WHERE .= RESA_Tools::generateWhereWithPlace($idPlaces);
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id DESC LIMIT '.$limit);
		foreach($results as $result){
			$entity = new RESA_LogNotification();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 *
	 */
	public static function generateLogNotification($logModel, $booking, $idCustomer, $idUserCreator, $expirationDate, $text){
		$log = new RESA_LogNotification();
		$log->setIdType($logModel->type);
		$log->setIdBooking($booking->getIdCreation());
		$log->setIdCustomer($idCustomer);
		$log->setIdUserCreator($idUserCreator);
		$log->setCriticity($logModel->criticity);
		$log->setExpirationDate($expirationDate);
		$log->addIdPlaces($booking->getAllIdPlaces());
		$log->setText($text);
		if($logModel->criticity == 1){
			$log->seenIdCustomer($idUserCreator);
			$log->setClicked(true);
		}
		return $log;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idType = -1;
		$this->idBooking = -1;
		$this->idCustomer = -1;
		$this->idUserCreator = -1;
		$this->creationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->expirationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->idPlaces = '';
		$this->text = '';
		$this->criticity = 0;
		$this->seen = '';
		$this->clicked = false;
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
				'idBooking' => $this->idBooking,
				'idCustomer' => $this->idCustomer,
				'idUserCreator' => $this->idUserCreator,
				'creationDate' => $this->creationDate,
				'expirationDate' => $this->expirationDate,
				'idPlaces' => $this->idPlaces,
				'text' => $this->text,
				'criticity' => $this->criticity,
				'seen' => $this->seen,
				'clicked' => $this->clicked
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idType' => $this->idType,
				'idBooking' => $this->idBooking,
				'idCustomer' => $this->idCustomer,
				'idUserCreator' => $this->idUserCreator,
				'creationDate' => $this->creationDate,
				'expirationDate' => $this->expirationDate,
				'idPlaces' => $this->idPlaces,
				'text' => $this->text,
				'criticity' => $this->criticity,
				'seen' => $this->seen,
				'clicked' => $this->clicked
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
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

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$name = '';
		if($this->idUserCreator != -1 && $this->idUserCreator != $this->idCustomer){
			$userData = get_userdata($this->idUserCreator);
			if($userData !== FALSE){
				$name = $userData->user_nicename;
			}
		}
		return '{
			"id":'.$this->id .',
			"idType":'.$this->idType.',
			"idBooking":'.$this->idBooking .',
			"idCustomer":'.$this->idCustomer .',
			"idUserCreator":'.$this->idUserCreator .',
			"name":"'.$name.'",
			"creationDate":"'.$this->creationDate .'",
			"expirationDate":"'.$this->expirationDate .'",
			"idPlaces":"'.$this->idPlaces .'",
			"text":"'.$this->text .'",
			"criticity":'.$this->criticity .',
			"seen":"'.$this->seen.'",
			"clicked":'.RESA_Tools::toJSONBoolean($this->clicked).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idType = $json->idType;
		$this->idBooking = $json->idBooking;
		$this->idCustomer = $json->idCustomer;
		$this->idUserCreator = $json->idUserCreator;
		$this->creationDate = $json->creationDate;
		$this->expirationDate = $json->expirationDate;
		if(isset($json->idPlaces)) $this->idPlaces = $json->idPlaces;
		$this->text = $json->text;
		$this->criticity = $json->criticity;
		$this->seen = $json->seen;
		$this->clicked = $json->clicked;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setIdType($idType){ $this->idType = $idType; }
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setIdCustomer($idCustomer){ $this->idCustomer = $idCustomer; }
	public function setIdUserCreator($idUserCreator){ $this->idUserCreator = $idUserCreator; }
	public function setCreationDate($creationDate){ $this->creationDate = $creationDate; }
	public function setExpirationDate($expirationDate){ $this->expirationDate = $expirationDate; }
	public function setIdPlaces($idPlaces){ $this->idPlaces = $idPlaces; }
	public function addIdPlaces($idPlaces){
		foreach($idPlaces as $idPlace){
			if(!empty($idPlace) && strstr($this->idPlaces, ',' . $idPlace . ',') === false){
				$this->idPlaces .= ',' . $idPlace . ',';
			}
		}
	}
	public function setText($text){ $this->text = $text; }
	public function setCriticity($criticity){ $this->criticity = $criticity; }
	public function setSeen($seen){ $this->seen = $seen; }
	public function seenIdCustomer($idCustomer){
		if(strstr($this->seen, ',' . $idCustomer . ',') === false){
			$this->seen .= ',' . $idCustomer . ',';
		}
	}
	public function setClicked($clicked){ $this->clicked = $clicked; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdType(){ return $this->idType; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getIdCustomer(){ return $this->idCustomer; }
	public function getIdUserCreator(){ return $this->idUserCreator; }
	public function getCreationDate(){ return $this->creationDate; }
	public function getExpirationDate(){ return $this->expirationDate; }
	public function getCriticity(){ return $this->criticity; }
	public function getIdPlaces(){ return $this->idPlaces; }
	public function getText(){ return $this->text; }
	public function getSeen(){ return $this->seen; }
	public function haveSeen($idCustomer){ return strstr($this->seen, ',' . $idCustomer . ',') !== false; }
	public function isClicked(){ return $this->clicked; }

	public function replaceVendor($vendor){
		$this->text = str_replace('[vendor]', $vendor, $this->text);
	}

	public function cloneMe(){
		$log = new RESA_LogNotification();
		$log->fromJSON(json_decode($this->toJSON()));
		return $log;
	}

	static public function compare($logNotification1, $logNotification2){
		return $logNotification2->getId() - $logNotification1->getId();
	}
}
