<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ReductionConditions extends RESA_EntityDTO
{
	private $id;
	private $idReduction;
	private $reductionConditions;
	private $type;
	private $merge; //Merge all appointments

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_conditions';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idReduction` int(11) NOT NULL,
		  `type` int(11) NOT NULL,
		  `merge` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idReduction` (`idReduction`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idReduction` FOREIGN KEY (`idReduction`) REFERENCES `'.RESA_Reduction::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_ReductionConditions();
			$entity->fromJSON($result);
			$entity->setReductionConditions(RESA_ReductionCondition::getAllData(array('idReductionConditions'=>$entity->getId())));
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
		$this->idReduction = -1;
		$this->type = 0;
		$this->merge = 0;
		$this->reductionConditions = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setReductionConditions(RESA_ReductionCondition::getAllData(array('idReductionConditions'=>$this->getId())));
		$this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastThisReductionConditions = new RESA_ReductionConditions();
			$lastThisReductionConditions->loadById($this->id);
			$lastReductionConditions = $lastThisReductionConditions->getReductionConditions();

			$this->linkWPDB->update(self::getTableName(), array(
				'idReduction' => $this->idReduction,
				'type' => $this->type,
				'merge' => $this->merge
			),
			array('id'=>$this->id),
			array (
				'%s',
				'%d',
				'%d'
			),
			array (
			'%d'));

			$idReductionConditions = array();
			for($i = 0; $i < count($this->reductionConditions); $i++)
			{
				if(!$this->reductionConditions[$i]->isNew())
					array_push($idReductionConditions, $this->reductionConditions[$i]->getId());
				$this->reductionConditions[$i]->save();
			}
			for($i = 0; $i < count($lastReductionConditions); $i++) {
				if(!in_array($lastReductionConditions[$i]->getId(), $idReductionConditions))
					$lastReductionConditions[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReduction' => $this->idReduction,
				'type' => $this->type,
				'merge' => $this->merge
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d'
			));
			$idReductionConditions = $this->linkWPDB->insert_id;
			$this->id = $idReductionConditions;
			for($i = 0; $i < count($this->reductionConditions); $i++)
			{
				$this->reductionConditions[$i]->setIdReductionConditions($idReductionConditions);
				$this->reductionConditions[$i]->save();
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
			for($i = 0; $i < count($this->reductionConditions); $i++)
			{
				$this->reductionConditions[$i]->deleteMe();
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
			"idReduction":'. $this->idReduction .',
			"type":'. $this->type .',
			"merge":'. RESA_Tools::toJSONBoolean($this->merge) .',
			"reductionConditions":'.RESA_Tools::formatJSONArray($this->reductionConditions).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idReduction = $json->idReduction;
		$this->type = $json->type;
		$this->merge = $json->merge;
		if(isset($json->reductionConditions))
		{
			$reductionConditions = array();
			for($i = 0; $i < count($json->reductionConditions); $i++)
			{
				$reductionCondition = new RESA_ReductionCondition();
				$reductionCondition->fromJSON($json->reductionConditions[$i]);
				array_push($reductionConditions, $reductionCondition);
			}
			$this->setReductionConditions($reductionConditions);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	/**
	 * \fn getConditionPriceId
	 * \brief return the id of price if condition price exist
	 */
	public function getConditionPriceId(){
		foreach($this->reductionConditions as $reductionCondition){
			if($reductionCondition->getType() == 'price')
				return $reductionCondition->getParam2();
		}
		return -1;
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->reductionConditions as $reductionCondition){
			$reductionCondition->setNew();
		}
	}

	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReduction(){ return $this->idReduction; }
	public function getReductionConditions(){ return $this->reductionConditions; }
	public function getType(){ return $this->type; }
	public function isMerge(){ return $this->merge; }

	public function setIdReduction($idReduction){ $this->idReduction = $idReduction; }
	public function setReductionConditions($reductionConditions){ $this->reductionConditions = $reductionConditions; }
	public function setType($type){ $this->type = $type; }
	public function setMerge($merge){ $this->merge = $merge; }
}
