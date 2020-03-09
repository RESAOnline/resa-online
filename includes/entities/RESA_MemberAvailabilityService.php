<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_MemberAvailabilityService extends RESA_EntityDTO
{
	private $idMemberAvailability;
	private $idService;
	private $capacity;


	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_member_availability_service';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `idMemberAvailability` int(11) NOT NULL,
		  `idService` int(11) NOT NULL,
		  `capacity` int(11) NOT NULL,
		  KEY `idMemberAvailability` (`idMemberAvailability`),
		  KEY `idService` (`idService`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idMemberAvailability` FOREIGN KEY (`idMemberAvailability`) REFERENCES `'.RESA_MemberAvailability::getTableName().'` (`id`)  ON DELETE CASCADE, ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE;';
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
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY idService');
		foreach($results as $result){
			$entity = new RESA_MemberAvailabilityService();
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
		$this->idMemberAvailability = -1;
		$this->idService = -1;
		$this->capacity = 0;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		/*$result = $this->linkWPDB->get_row('SELECT * FROM '. self::getTableName() . ' WHERE idMemberAvailability = '.$id.' ORDER BY idService');
		$service = new RESA_Service();
		$service->loadById($result->idService);
		$this->fromJSON(json_encore(
			array('memberAvailability'=> $memberAvailability, 'service'=> $service)));
		$this->setLoaded(true);*/
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$this->linkWPDB->update(self::getTableName(), array(
				'capacity' => $this->capacity
			),
			array(
				'idMemberAvailability' => $this->idMemberAvailability,
				'idService' => $this->idService
			),
			array ('%d'),
			array ('%d',
				'%d'
			));
		}
		else if( $this->idService > 0)
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'idMemberAvailability' => $this->idMemberAvailability,
				'idService' => $this->idService,
				'capacity' => $this->capacity
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
			$this->linkWPDB->delete(self::getTableName(),array(
				'idMemberAvailability' => $this->idMemberAvailability,
				'idService' => $this->idService
			),
			array (
				'%d',
				'%d'
			));
		}
	}


		/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"idMemberAvailability":'.$this->idMemberAvailability.',
			"idService":'.$this->idService.',
			"capacity":'.$this->capacity.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->idMemberAvailability = $json->idMemberAvailability;
		$this->idService = $json->idService;
		$this->capacity = $json->capacity;
	}

	public function getIdService(){ return $this->idService; }
	public function getIdMemberAvailability(){ return $this->idMemberAvailability; }
	public function getCapacity(){ return $this->capacity; }

	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdMemberAvailability($idMemberAvailability){ $this->idMemberAvailability = $idMemberAvailability; }
	public function setCapacity($capacity){ $this->capacity = $capacity; }
}
