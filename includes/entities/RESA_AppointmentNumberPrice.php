<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AppointmentNumberPrice extends RESA_EntityDTO
{
	private $id;
	private $idAppointment;
	private $idPrice;
	private $number;
	private $totalPrice;
	private $deactivated;
	private $participants;


	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_appointment_number_price';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idAppointment` int(11) NOT NULL,
		  `idPrice` int(11) NOT NULL,
		  `number` int(11) NOT NULL,
		  `totalPrice` FLOAT NOT NULL,
		  `deactivated` tinyint(1) NOT NULL,
		  `participants` text NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idAppointment` (`idAppointment`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idAppointment` FOREIGN KEY (`idAppointment`) REFERENCES `'.RESA_Appointment::getTableName().'` (`id`) ON DELETE CASCADE;';
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
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . '  '.$WHERE.' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_AppointmentNumberPrice();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * delete all data with idAppointment
	 */
	public static function deleteAllByIdAppointment($idAppointment){
		global $wpdb;
		$wpdb->delete(self::getTableName(),array('idAppointment'=>$idAppointment), array ('%d'));
	}

	/**
	 * return the number of appointments by ids services
	 */
	public static function haveAppointmentNumberPricesWithIdServicePrice($idServicePrice){
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE idPrice=\'' . $idServicePrice.'\'');
		if(!isset($count)) return false;
		return $count > 0;
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idAppointment = -1;
		$this->idPrice = -1;
		$this->number = 0;
		$this->totalPrice = 0;
		$this->deactivated = false;
		$this->participants = [];
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
				'idPrice' => $this->idPrice,
				'number' => $this->number,
				'totalPrice' => $this->totalPrice,
				'deactivated' => $this->deactivated,
				'participants' => serialize($this->participants)
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%f',
				'%d',
				'%s'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idAppointment' => $this->idAppointment,
				'idPrice' => $this->idPrice,
				'number' => $this->number,
				'totalPrice' => $this->totalPrice,
				'deactivated' => $this->deactivated,
				'participants' => serialize($this->participants)
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%f',
				'%d',
				'%s'
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
			"idAppointment":'.$this->idAppointment.',
			"idPrice":'.$this->idPrice.',
			"number":'.$this->number.',
			"totalPrice":'.$this->totalPrice.',
			"deactivated":'.RESA_Tools::toJSONBoolean($this->deactivated).',
			"participants":'.json_encode($this->getParticipants()).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idAppointment = $json->idAppointment;
		$this->idPrice = $json->idPrice;
		$this->number = $json->number;
		if(isset($json->totalPrice)) $this->totalPrice = $json->totalPrice;
		if(isset($json->deactivated)) $this->deactivated = $json->deactivated;
		if(isset($json->participants)) {
			if(is_array($json->participants)){
				$this->participants = $json->participants;
			}
			else if($json->participants!=false) {
				$this->participants = unserialize($json->participants);
			}
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	/**
	 * Update price id if necessary.
	 */
	public function updatePriceId($oldPriceId, $newPriceId){
		if($this->idPrice == $oldPriceId){
			$this->idPrice = $newPriceId;
		}
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdAppointment($idAppointment){ $this->idAppointment = $idAppointment; }
	public function setIdPrice($idPrice){ $this->idPrice = $idPrice; }
	public function setNumber($number){ $this->number = $number; }
	public function setTotalPrice($totalPrice){ $this->totalPrice = $totalPrice; }
	public function setDeactivated($deactivated){ $this->deactivated = $deactivated; }
	public function setParticipants($participants){ $this->participants = $participants; }
	public function addParticipants($participants){
		$maxId = -1;
		foreach($this->participants as $participant){
			if(isset($participant->id)) max($maxId, $participant->id);
		}
		$maxId++;
		foreach($participants as $participant){
			$participant->id = $maxId;
			array_push($this->participants, $participant);
			$maxId++;
		}
	}
	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdAppointment(){ return $this->idAppointment; }
	public function getIdPrice(){ return $this->idPrice; }
	public function getNumber(){ return $this->number; }
	public function getTotalPrice(){ return $this->totalPrice; }
	public function isDeactivated(){ return $this->deactivated; }
	public function getParticipants(){ return $this->participants; }
	public function getNumberParticipants(){ return is_array($this->participants)?count($this->participants):0; }

	public function generateParticipantUri($idCustomer){
		$alreadyCreated = array();
		for($i = 0; $i < count($this->participants); $i++){
			$participant = $this->participants[$i];
			if(!isset($participant->uri) || empty($participant->uri) || in_array($participant->uri, $alreadyCreated)){
				$participant->uri = RESA_Tools::generateOneParticipantUri($idCustomer, $participant);
			}
			array_push($alreadyCreated, $participant->uri);
			$this->participants[$i] = $participant;
		}
	}
}
