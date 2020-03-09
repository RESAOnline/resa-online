<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_MemberLinkService extends RESA_EntityDTO
{
	private $id;
	private $idMemberLink;
	private $idService;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_member_link_service';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `idMemberLink` int(11) NOT NULL,
			  `idService` int(11) NOT NULL,
			   PRIMARY KEY (`id`),
			   KEY `idMemberLink` (`idMemberLink`),
			   KEY `idService` (`idService`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idMemberLink` FOREIGN KEY (`idMemberLink`) REFERENCES `'.RESA_MemberLink::getTableName().'` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE ;';
	}

	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array()) {
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_MemberLinkService();
			$entity->fromJSON($result);
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
		$this->idMemberLink = -1;
		$this->idService = -1;
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
				'idMemberLink' =>$this->idMemberLink,
				'idService' => $this->idService
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idMemberLink' =>$this->idMemberLink,
				'idService' => $this->idService
			),
			array (
				'%d',
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
			$this->linkWPDB->delete(self::getTableName(), array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
		 "id":'.$this->id.',
		 "idMemberLink":'.$this->idMemberLink.',
		 "idService":'.$this->idService.'
		}';
	}
	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idMemberLink = $json->idMemberLink;
		$this->idService = $json->idService;
		if($this->id != -1)	$this->setLoaded(true);
	}
	public function isNew(){ return $this->idMemberLink == -1; }
	public function getId(){ return $this->id; }
	public function getIdMemberLink(){ return $this->idMemberLink; }
	public function getIdService(){ return $this->idService; }

	public function setMemberLink($idMemberLink){ $this->idMemberLink = $idMemberLink; }
	public function setIdService($idService){ $this->idService = $idService; }
}
