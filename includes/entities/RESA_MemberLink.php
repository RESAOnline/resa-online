<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_MemberLink extends RESA_EntityDTO
{
	private $id;
	private $idMember;
	private $typeCapacityMethod;
	private $memberLinkServices;


	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_member_link';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `idMember` int(11) NOT NULL,
			  `typeCapacityMethod` int(11) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `idMember` (`idMember`)
			) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_IdMember` FOREIGN KEY (`idMember`) REFERENCES `'.RESA_Member::getTableName().'` (`id`) ON DELETE CASCADE;';
	}

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
			$entity = new RESA_MemberLink();
			$entity->fromJSON($result);
			$entity->setMemberLinkServices(RESA_MemberLinkService::getAllData(array('idMemberLink'=>$entity->getId())));
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
		$this->idMember = 0;
		$this->typeCapacityMethod = 0;
		$this->memberLinkServices = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setMemberLinkServices(RESA_MemberLinkService::getAllData(array('idMemberLink'=>$this->getId())));
		$this->setLoaded(true);
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastMemberLink = new RESA_MemberLink();
			$lastMemberLink->loadById($this->id);
			$lastMemberLinkServices = $lastMemberLink->getMemberLinkServices();

			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'idMember' =>$this->idMember,
				'typeCapacityMethod' => $this->typeCapacityMethod
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d'
			),
			array ('%d'));

			$idMemberLinkServices = array();
			for($i = 0; $i < count($this->memberLinkServices); $i++) {
				if(!$this->memberLinkServices[$i]->isNew())
					array_push($idMemberLinkServices, $this->memberLinkServices[$i]->getId());
				$this->memberLinkServices[$i]->save();
			}
			for($i = 0; $i < count($lastMemberLinkServices); $i++) {
				if(!in_array($lastMemberLinkServices[$i]->getId(), $idMemberLinkServices))
					$lastMemberLinkServices[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idMember' =>$this->idMember,
				'typeCapacityMethod' => $this->typeCapacityMethod
			),
			array (
				'%d',
				'%d',
				'%d'
			));
			$idMemberLink = $this->linkWPDB->insert_id;
			$this->id = $idMemberLink;
			for($i = 0; $i < count($this->memberLinkServices); $i++)
			{
				$this->memberLinkServices[$i]->setMemberLink($idMemberLink);
				$this->memberLinkServices[$i]->save();
			}
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
		 "idMember":'.$this->idMember.',
		 "typeCapacityMethod":'.$this->typeCapacityMethod.',
		 "memberLinkServices":'.RESA_Tools::formatJSONArray($this->memberLinkServices).'
		}';
	}
	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idMember = $json->idMember;
		$this->typeCapacityMethod = $json->typeCapacityMethod;
		if(isset($json->memberLinkServices))
		{
			$memberLinkServices = array();
			for($i = 0; $i < count($json->memberLinkServices); $i++)
			{
				$memberLinkService = new RESA_MemberLinkService();
				$memberLinkService->fromJSON($json->memberLinkServices[$i]);
				array_push($memberLinkServices, $memberLinkService);
			}
			$this->setMemberLinkServices($memberLinkServices);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	/**
	 * return true if this id service exist in this link
	 */
	public function isContainService($idService){
		foreach($this->memberLinkServices as $memberLinkService){
			if($memberLinkService->getIdService() == $idService)
				return true;
		}
		return false;
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdMember(){ return $this->idMember; }
	public function getTypeCapacityMethod(){ return $this->typeCapacityMethod; }
	public function getMemberLinkServices(){ return $this->memberLinkServices; }

	public function setIdMember($idMember){ $this->idMember = $idMember; }
	public function setTypeCapacityMethod($typeCapacityMethod){ $this->typeCapacityMethod = $typeCapacityMethod; }
	public function setMemberLinkServices($memberLinkServices){ $this->memberLinkServices = $memberLinkServices; }
}
