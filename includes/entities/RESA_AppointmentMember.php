<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_AppointmentMember extends RESA_EntityDTO
{
	private $idAppointment;
	private $idMember;
	private $number;



	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_appointment_member';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `idAppointment` int(11) NOT NULL,
		  `idMember` int(11) NOT NULL,
		  `number` int(11) NOT NULL,
		  KEY `idAppointment` (`idAppointment`),
		  KEY `idMember` (`idMember`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
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
	 * return the number of appointmentMember
	 */
	public static function countAppointmentMemberWithIdMember($idMember){
		global $wpdb;
		$results = $wpdb->get_var('SELECT COUNT(idAppointment) FROM  '. self::getTableName() . ' INNER JOIN '. RESA_Appointment::getTableName() . ' ON '. self::getTableName() . '.idAppointment = '. RESA_Appointment::getTableName() . '.id AND '. RESA_Appointment::getTableName() . '.state<>\'updated\' WHERE '. self::getTableName() . '.idMember='.$idMember);
		if(isset($results)) return $results;
		return 0;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY number');
		foreach($results as $result){
			$entity = new RESA_AppointmentMember();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * delete all data with idAppointment
	 */
	public static  function deleteAllByIdAppointment($idAppointment){
		global $wpdb;
		$wpdb->delete(self::getTableName(),array('idAppointment'=>$idAppointment), array ('%d'));
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->idAppointment = -1;
		$this->idMember = -1;
		$this->number = 0;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		// $result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		// $this->fromJSON($result);
		// $this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$this->linkWPDB->update(self::getTableName(), array(
				'number' => $this->number
			),
			array(
				'idAppointment' => $this->idAppointment,
				'idMember' => $this->idMember
			),
			array (
				'%d'
			),
			array ('%d','%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'idAppointment' => $this->idAppointment,
				'idMember' => $this->idMember,
				'number' => $this->number
			),
			array (
				'%d',
				'%d',
				'%d'
			));
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
			$this->linkWPDB->delete(self::getTableName(),array('idAppointment'=>$this->idAppointment,'idMember'=>$this->idMember,),array ('%d','%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"idAppointment":'.$this->idAppointment.',
			"idMember":'.$this->idMember.',
			"number":'.$this->number.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->idAppointment = $json->idAppointment;
		$this->idMember = $json->idMember;
		$this->number = $json->number;
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdAppointment($idAppointment){ $this->idAppointment = $idAppointment; }
	public function setIdMember($idMember){ $this->idMember = $idMember; }
	public function setNumber($number){ $this->number = $number; }

	public function isNew(){ return $this->idAppointment == -1; }
	public function getIdAppointment(){ return $this->idAppointment; }
	public function getIdMember(){ return $this->idMember; }
	public function getNumber(){ return $this->number; }
}
