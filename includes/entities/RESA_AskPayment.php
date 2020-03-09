<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AskPayment extends RESA_EntityDTO
{
	private $id;
	private $idBooking;
	private $idUserCreator;
	private $date;
	private $expiredDate;
	private $expired; //need to load a log notification
	private $types;
	private $value;
	private $typeAdvancePayment;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_ask_payment';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idBooking` int(11) NOT NULL,
		  `idUserCreator` int(11) NOT NULL,
		  `date` datetime NOT NULL,
		  `expiredDate` datetime NOT NULL,
		  `expired` tinyint(1) NOT NULL,
		  `types` TEXT NOT NULL,
		  `value` float NOT NULL,
		  `typeAdvancePayment` int(11) NOT NULL,
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
	 * Return all entities of this type
	 */
	public static function getAllDataExpiredButNotNotified()
	{
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE expiredDate < NOW() AND expired = 0 ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_AskPayment();
			$entity->fromJSON($result);
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
			$entity = new RESA_AskPayment();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * expire to true
	 */
	public static function setExpiredToTrue(RESA_Booking $booking){
		global $wpdb;
		$oldIdBookings = $booking->getOldIdBookings();
		array_push($oldIdBookings, $booking->getId());
		$WHERE = RESA_Tools::generateWhereClause(array(
			'idBooking' => array($oldIdBookings)
		));
		$result = $wpdb->query('UPDATE `'. self::getTableName() . '` SET expired=1 '. $WHERE);
		return $result !== FALSE;
	}

	/**
	 * return the state of ask payment for a booking
	 * @return int 0 to no ask payment
	 * 						 1 to have ask payment
	 * 						 2 expired ask payment
	 */
	public static function getStateOfAskPayment($oldIdBookings){
		global $wpdb;
		$WHERE = RESA_Tools::generateWhereClause(array(
			'idBooking' => array($oldIdBookings)
		));
		$currentDate = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', current_time('timestamp')));
		$results = $wpdb->get_results('SELECT expiredDate FROM '. self::getTableName() . ' '.$WHERE);
		if(count($results) == 0) return 0;
		else {
			foreach($results as $result){
				$expiredDate = DateTime::createFromFormat('Y-m-d H:i:s', $result->expiredDate);
				if($currentDate <= $expiredDate) return 1;
			}
		}
		return 2;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idBooking = -1;
		$this->idUserCreator = -1;
		$this->date = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->expiredDate = null;
		$this->calculateExpiredDate(2);
		$this->expired = false;
		$this->types = '';
		$this->value = 0;
		$this->typeAdvancePayment = 0;
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
				'idBooking' => $this->idBooking,
				'idUserCreator' => $this->idUserCreator,
				'date' => $this->date,
				'expiredDate' => $this->expiredDate,
				'expired' => $this->expired,
				'types' => $this->types,
				'value' => $this->value,
				'typeAdvancePayment' => $this->typeAdvancePayment
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
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
				'idBooking' => $this->idBooking,
				'idUserCreator' => $this->idUserCreator,
				'date' => $this->date,
				'expiredDate' => $this->expiredDate,
				'expired' => $this->expired,
				'types' => $this->types,
				'value' => $this->value,
				'typeAdvancePayment' => $this->typeAdvancePayment
			),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
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
			"idBooking":'.$this->idBooking .',
			"idUserCreator":'.$this->idUserCreator .',
			"date":"'.$this->date .'",
			"expiredDate":"'.$this->expiredDate .'",
			"expired":'.RESA_Tools::toJSONBoolean($this->expired).',
			"types":"'.$this->types .'",
			"value":'.$this->value .',
			"typeAdvancePayment":'.$this->typeAdvancePayment .'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idBooking = $json->idBooking;
		$this->idUserCreator = $json->idUserCreator;
		$this->date = $json->date;
		if(isset($json->expiredDate)) $this->expiredDate = $json->expiredDate;
		if(isset($json->expired)) $this->expired = $json->expired;
		$this->types = $json->types;
		$this->value = $json->value;
		if(isset($json->typeAdvancePayment)) $this->typeAdvancePayment = $json->typeAdvancePayment;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setIdUserCreator($idUserCreator){ $this->idUserCreator = $idUserCreator; }
	public function setDate($date){ $this->date = $date; }
	public function setExpiredDate($expiredDate){ $this->expiredDate = $expiredDate; }
	public function setExpired($expired){ $this->expired = $expired; }
	public function setTypesPayment($types){ $this->types = $types; }
	public function setValue($value){ $this->value = $value; }
	public function setTypeAdvancePayment($typeAdvancePayment){ $this->typeAdvancePayment = $typeAdvancePayment; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getIdUserCreator(){ return $this->idUserCreator; }
	public function getDate(){ return $this->date; }
	public function getExpiredDate(){ return $this->expiredDate; }
	public function isExpiredBoolean(){ return $this->expired; }
	public function getTypes(){ return $this->types; }
	public function getValue(){ return $this->value; }
	public function getTypeAdvancePayment(){ return $this->typeAdvancePayment; }

	public function calculateExpiredDate($days){
		$this->expiredDate = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', current_time('timestamp')));
		$this->expiredDate->add(new DateInterval('P'.$days.'D'));
		$this->expiredDate = $this->expiredDate->format('Y-m-d H:i:s');
	}
	public function isExpiredDate(){
		$currentDate = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', current_time('timestamp')));
		$expiredDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->expiredDate);
		return $currentDate > $expiredDate;
	}

}
