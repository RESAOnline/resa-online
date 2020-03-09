<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_ServiceMemberPriority extends RESA_EntityDTO
{
	private $idService;
	private $idMember;
	private $priority;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_service_member_priority';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `idService` int(11) NOT NULL,
		  `idMember` int(11) NOT NULL,
		  `priority` int(11) NOT NULL,
		  KEY `idService` (`idService`),
		  KEY `idMember` (`idMember`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `'.self::getTableName().'_idMember` FOREIGN KEY (`idMember`) REFERENCES `'.RESA_Member::getTableName().'` (`id`) ON DELETE CASCADE ;';
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
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY priority');
		foreach($results as $result){
			$entity = new RESA_ServiceMemberPriority();
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
		$this->idMember = -1;
		$this->idService = -1;
		$this->priority = 0;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		/*$result = $this->linkWPDB->get_row('SELECT * FROM '. self::getTableName() . ' WHERE idService = '.$id.' ORDER BY idMember');
		$serviceMemberPriority = new RESA_ServiceMemberPriority();
		$serviceMemberPriority->loadById($result->idService);
		$this->fromJSON(json_encore(
			array('Member'=> $Member, 'service'=> $service)));
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
				'priority' => $this->priority
			),
			array(
				'idService' => $this->idService,
				'idMember' => $this->idMember
			),
			array ('%d'),
			array ('%d',
				'%d'
			));
		}
		else if( $this->idMember > 0)
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'idService' => $this->idService,
				'idMember' => $this->idMember,
				'priority' => $this->priority
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
				'idService' => $this->idService,
				'idMember' => $this->idMember
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
			"idService":'.$this->idService.',
			"idMember":'.$this->idMember.',
			"priority":'.$this->priority.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->idMember = $json->idMember;
		$this->idService = $json->idService;
		$this->priority = $json->priority;
	}

	public function getIdService(){ return $this->idService; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function getIdMember(){ return $this->idMember; }
	public function setIdMember($idMember){ $this->idMember = $idMember; }
}
