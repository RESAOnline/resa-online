<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ReductionConditionsApplication extends RESA_EntityDTO
{
	private $id;
	private $idReductionApplication;
	private $reductionConditionsApplications;
	private $type;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_conditions_application';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idReductionApplication` int(11) NOT NULL,
		  `type` int(11) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idReductionApplication` (`idReductionApplication`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idReduction` FOREIGN KEY (`idReductionApplication`) REFERENCES `'.RESA_ReductionApplication::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_ReductionConditionsApplication();
			$entity->fromJSON($result);
			$entity->setReductionConditionsApplications(RESA_ReductionConditionApplication::getAllData(array('idReductionConditionsApplication'=>$entity->getId())));
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
		$this->idReductionApplication = -1;
		$this->type = 0;
		$this->reductionConditionsApplications = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setReductionConditionsApplications(RESA_ReductionConditionApplication::getAllData(array('idReductionConditionsApplication'=>$this->getId())));
		$this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastThisReductionConditionsApplication = new RESA_ReductionConditionsApplication();
			$lastThisReductionConditionsApplication->loadById($this->id);
			$lastReductionConditionsApplications =
				$lastThisReductionConditionsApplication->getReductionConditionsApplications();

			$this->linkWPDB->update(self::getTableName(), array(
				'idReductionApplication' => $this->idReductionApplication,
				'type' => $this->type
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d'
			),
			array (
			'%d'));
			$idReductionConditionsApplications = array();
			for($i = 0; $i < count($this->reductionConditionsApplications); $i++)
			{
				if(!$this->reductionConditionsApplications[$i]->isNew()){
					array_push($idReductionConditionsApplications, $this->reductionConditionsApplications[$i]->getId());
				}
				$this->reductionConditionsApplications[$i]->save();
			}
			for($i = 0; $i < count($lastReductionConditionsApplications); $i++) {
				if(!in_array($lastReductionConditionsApplications[$i]->getId(), $idReductionConditionsApplications)){
					$lastReductionConditionsApplications[$i]->deleteMe();
				}
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReductionApplication' => $this->idReductionApplication,
				'type' => $this->type
			),
			array (
				'%d',
				'%d',
				'%d'
			));
			$idReductionConditions = $this->linkWPDB->insert_id;
			$this->id = $idReductionConditions;
			for($i = 0; $i < count($this->reductionConditionsApplications); $i++)
			{
				$this->reductionConditionsApplications[$i]->setIdReductionConditionsApplication($idReductionConditions);
				$this->reductionConditionsApplications[$i]->save();
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
			for($i = 0; $i < count($this->reductionConditionsApplications); $i++)
			{
				$this->reductionConditionsApplications[$i]->deleteMe();
			}
			$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		return '{
			"id":'. $this->id .',
			"idReductionApplication":'. $this->idReductionApplication .',
			"type":'. $this->type .',
			"reductionConditionsApplications":'.RESA_Tools::formatJSONArray($this->reductionConditionsApplications).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idReductionApplication = $json->idReductionApplication;
		$this->type = $json->type;
		if(isset($json->reductionConditionsApplications))
		{
			$reductionConditionsApplications = array();
			for($i = 0; $i < count($json->reductionConditionsApplications); $i++)
			{
				$reductionConditionApplication = new RESA_ReductionConditionApplication();
				$reductionConditionApplication->fromJSON($json->reductionConditionsApplications[$i]);
				array_push($reductionConditionsApplications, $reductionConditionApplication);
			}
			$this->setReductionConditionsApplications($reductionConditionsApplications);
		}
		if($this->id != -1)	{
			$this->setLoaded(true);
		}
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->reductionConditionsApplications as $reductionConditionsApplication){
			$reductionConditionsApplication->setNew();
		}
	}


	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReductionApplication(){ return $this->idReductionApplication; }
	public function getReductionConditionsApplications(){ return $this->reductionConditionsApplications; }
	public function getType(){ return $this->type; }

	public function setIdReductionApplication($idReductionApplication){ $this->idReductionApplication = $idReductionApplication; }
	public function setReductionConditionsApplications($reductionConditionsApplications){ $this->reductionConditionsApplications = $reductionConditionsApplications; }
	public function setType($type){ $this->type = $type; }
}
