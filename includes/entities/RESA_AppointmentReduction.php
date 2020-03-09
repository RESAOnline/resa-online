<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AppointmentReduction extends RESA_EntityDTO
{
	private $id;
	private $idAppointment;
	private $idReduction;
	private $idPrice;
	private $type;
	private $value;
	private $vatAmount;
	private $number;
	private $promoCode;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_appointment_reduction';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idAppointment` int(11) NOT NULL,
		  `idReduction` int(11) NOT NULL,
		  `idPrice` int(11) NOT NULL,
		  `type` int(11) NOT NULL,
		  `value` TEXT NOT NULL,
		  `vatAmount` int(11) NOT NULL,
		  `number` int(11) NOT NULL,
		  `promoCode` TEXT NOT NULL,
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
	 * Return the of appointment reduction used reduction
	 */
	public static function numberOfReductionsUsed($idReduction){
		global $wpdb;
		$results = $wpdb->get_var('SELECT COUNT(idAppointment) FROM  '. self::getTableName() . ' INNER JOIN '. RESA_Appointment::getTableName() . ' ON '. self::getTableName() . '.idAppointment = '. RESA_Appointment::getTableName() . '.id AND '. RESA_Appointment::getTableName() . '.state<>\'updated\' WHERE '. self::getTableName() . '.idReduction='.$idReduction);
		if(isset($results)) return $results;
		return 0;
	}

	/**
	 * Return the of appointment reduction used promo code (only last version and not cancelled)
	 */
	public static function numberOfPromoCodeUsed($promoCode, $idBooking){
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT('. self::getTableName() . '.idAppointment) FROM '. self::getTableName() . ' INNER JOIN '. RESA_Appointment::getTableName() . ' ON '. self::getTableName() . '.idAppointment = '. RESA_Appointment::getTableName() . '.id AND '. RESA_Appointment::getTableName() . '.state <> \'cancelled\' AND '. RESA_Appointment::getTableName() . '.state <> \'deleted\' AND '. RESA_Appointment::getTableName() . '.state <> \'abandonned\' AND '. RESA_Appointment::getTableName() . '.state <> \'updated\' AND '. RESA_Appointment::getTableName() . '.idBooking <> '.$idBooking.' WHERE '. self::getTableName() . '.promoCode = \''.$promoCode.'\'');
		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return true if there are a vouchers
	 */
	public static function haveReductionsWithVouchers() {
		global $wpdb;
		$count = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE promoCode <> \'\'');
		if(isset($count) && $count > 0) return true;
		return false;
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
			$entity = new RESA_AppointmentReduction();
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
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idAppointment = -1;
		$this->idReduction = -1;
		$this->idPrice = -1;
		$this->type = -1;
		$this->value = '';
		$this->vatAmount = 0;
		$this->number = 0;
		$this->promoCode = '';
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
				'idReduction' => $this->idReduction,
				'idPrice' => $this->idPrice,
				'type' => $this->type,
				'value' => $this->value,
				'vatAmount' => $this->vatAmount,
				'number' => $this->number,
				'promoCode' => $this->promoCode
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
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
				'idReduction' => $this->idReduction,
				'idPrice' => $this->idPrice,
				'type' => $this->type,
				'value' => $this->value,
				'vatAmount' => $this->vatAmount,
				'number' => $this->number,
				'promoCode' => $this->promoCode
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
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
			"idReduction":'.$this->idReduction.',
			"idPrice":'.$this->idPrice.',
			"type":'.$this->type.',
			"value":"'.$this->value .'",
			"vatAmount":'.$this->vatAmount.',
			"number":'.$this->number .',
			"promoCode":"'.$this->promoCode .'"
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idAppointment = $json->idAppointment;
		$this->idReduction = $json->idReduction;
		$this->idPrice = $json->idPrice;
		$this->type = $json->type;
		$this->value = $json->value;
		if(isset($json->vatAmount)) $this->vatAmount = $json->vatAmount;
		if(isset($json->number)) $this->number = $json->number;
		$this->promoCode = $json->promoCode;
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
	public function setIdReduction($idReduction){ $this->idReduction = $idReduction; }
	public function setIdPrice($idPrice){ $this->idPrice = $idPrice; }
	public function setType($type){ $this->type = $type; }
	public function setValue($value){ $this->value = $value; }
	public function setVatAmount($vatAmount){ $this->vatAmount = $vatAmount; }
	public function setNumber($number){ $this->number = $number; }
	public function setPromoCode($promoCode){ $this->promoCode = $promoCode; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdAppointment(){ return $this->idAppointment; }
	public function getIdReduction(){ return $this->idReduction; }
	public function getIdPrice(){ return $this->idPrice; }
	public function getType(){ return $this->type; }
	public function getValue(){ return $this->value; }
	public function getVatAmount(){ return $this->vatAmount; }
	public function getNumber(){ return $this->number; }
	public function getPromoCode(){ return $this->promoCode; }

	/**
	 * return true if this reductions has same...
	 */
	public function equals($appointmentReduction){
		return $appointmentReduction->getType() == $this->getType() &&
			$appointmentReduction->getValue() == $this->getValue();
	}
}
