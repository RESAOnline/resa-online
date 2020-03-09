<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_BookingCustomReduction extends RESA_EntityDTO
{
	private $id;
	private $idBooking;
	private $description;
	private $amount;
	private $vatValue;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_booking_custom_reduction';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idBooking` int(11) NOT NULL,
		  `description` TEXT NOT NULL,
		  `amount` float NOT NULL,
		  `vatValue` float NOT NULL,
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
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;

		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_BookingCustomReduction();
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
		$this->idBooking = -1;
		$this->description = '';
		$this->amount = 0;
		$this->vatValue = 0;
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
				'description' => $this->description,
				'amount' => $this->amount,
				'vatValue' => $this->vatValue
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%f',
				'%f'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idBooking' => $this->idBooking,
				'description' => $this->description,
				'amount' => $this->amount,
				'vatValue' => $this->vatValue
			),
			array (
				'%d',
				'%d',
				'%s',
				'%f',
				'%f'
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
			"description":"'.$this->description .'",
			"amount":'.$this->amount .',
			"vatValue":'.$this->vatValue.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		if(isset($json->id)) $this->id = $json->id;
		if(isset($json->idBooking)) $this->idBooking = $json->idBooking;
		$this->description = esc_html($json->description);
		$this->amount = $json->amount;
		if(isset($json->vatValue)) $this->vatValue = $json->vatValue;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setDescription($description){ $this->description = $description; }
	public function setAmount($amount){ $this->amount = $amount; }
	public function setVatValue($vatValue){ $this->vatValue = $vatValue; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getDescription(){ return $this->description; }
	public function getAmount(){ return $this->amount; }
	public function getVatValue(){ return $this->vatValue; }

}
