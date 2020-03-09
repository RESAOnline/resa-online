<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ReductionApplication extends RESA_EntityDTO
{
	private $id;
	private $idReduction;
	private $type;
	private $applicationType;
	private $applicationTypeOn;
	private $value;
	private $vatAmount;
	private $onlyOne;
	private $reductionConditionsApplicationList;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_application';
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
		  `applicationType` int(11) NOT NULL,
		  `applicationTypeOn` int(11) NOT NULL,
		  `value` TEXT NOT NULL,
		  `vatAmount` int(11) NOT NULL,
		  `onlyOne` tinyint(1) NOT NULL,
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
			$entity = new RESA_ReductionApplication();
			$entity->fromJSON($result);
			$entity->setReductionConditionsApplicationList(RESA_ReductionConditionsApplication::getAllData(array('idReductionApplication'=>$entity->getId())));
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
		$this->type = -1;
		$this->applicationType = 0;
		$this->applicationTypeOn = 0;
		$this->value = '';
		$this->vatAmount = 0;
		$this->onlyOne = false;
		$this->reductionConditionsApplicationList = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result);
		$this->setReductionConditionsApplicationList(RESA_ReductionConditionsApplication::getAllData(array('idReductionApplication'=>$this->getId())));
		$this->setLoaded(true);
	}


	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			$lastThisReductionApplication = new RESA_ReductionApplication();
			$lastThisReductionApplication->loadById($this->id);
			$lastReductionConditionsApplicationList = $lastThisReductionApplication->getReductionConditionsApplicationList();

			$this->linkWPDB->update(self::getTableName(), array(
				'idReduction' => $this->idReduction,
				'type' => $this->type,
				'applicationType' => $this->applicationType,
				'applicationTypeOn' => $this->applicationTypeOn,
				'value' => $this->value,
				'vatAmount' => $this->vatAmount,
				'onlyOne' => $this->onlyOne
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
			),
			array (
			'%d'));

			$idReductionConditionsApplicationList = array();
			for($i = 0; $i < count($this->reductionConditionsApplicationList); $i++)
			{
				if(!$this->reductionConditionsApplicationList[$i]->isNew())
					array_push($idReductionConditionsApplicationList, $this->reductionConditionsApplicationList[$i]->getId());
				$this->reductionConditionsApplicationList[$i]->save();
			}
			for($i = 0; $i < count($lastReductionConditionsApplicationList); $i++) {
				if(!in_array($lastReductionConditionsApplicationList[$i]->getId(), $idReductionConditionsApplicationList))
					$lastReductionConditionsApplicationList[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReduction' => $this->idReduction,
				'type' => $this->type,
				'applicationType' => $this->applicationType,
				'applicationTypeOn' => $this->applicationTypeOn,
				'value' => $this->value,
				'vatAmount' => $this->vatAmount,
				'onlyOne' => $this->onlyOne
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
			));
			$idReductionApplication = $this->linkWPDB->insert_id;
			$this->id = $idReductionApplication;
			for($i = 0; $i < count($this->reductionConditionsApplicationList); $i++)
			{
				$this->reductionConditionsApplicationList[$i]->setIdReductionApplication($idReductionApplication);
				$this->reductionConditionsApplicationList[$i]->save();
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
			for($i = 0; $i < count($this->reductionConditionsApplicationList); $i++)
			{
				$this->reductionConditionsApplicationList[$i]->deleteMe();
			}
			$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$value = '"'.$this->value.'"';
		if($this->type != 5 && !empty($this->value)){
			$value = $this->value;
		}
		return '{
			"id":'. $this->id .',
			"idReduction":'. $this->idReduction .',
			"type":'. $this->type .',
			"applicationType":'. $this->applicationType.',
			"applicationTypeOn":'. $this->applicationTypeOn.',
			"value":'.$value.',
			"vatAmount":'.$this->vatAmount.',
			"onlyOne":'.RESA_Tools::toJSONBoolean($this->onlyOne).',
			"reductionConditionsApplicationList":'.RESA_Tools::formatJSONArray($this->reductionConditionsApplicationList).'
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
		$this->applicationType = $json->applicationType;
		$this->applicationTypeOn = $json->applicationTypeOn;
		$this->value = RESA_Tools::echapEolTab(nl2br(esc_html($json->value)));
		if(isset($json->vatAmount)) $this->vatAmount = $json->vatAmount;
		if(isset($json->onlyOne)) $this->onlyOne = esc_html($json->onlyOne);
		if(isset($json->reductionConditionsApplicationList))
		{
			$reductionConditionsApplicationList = array();
			for($i = 0; $i < count($json->reductionConditionsApplicationList); $i++)
			{
				$reductionConditionApplication = new RESA_ReductionConditionsApplication();
				$reductionConditionApplication->fromJSON($json->reductionConditionsApplicationList[$i]);
				array_push($reductionConditionsApplicationList, $reductionConditionApplication);
			}
			$this->setReductionConditionsApplicationList($reductionConditionsApplicationList);
		}
		if($this->id != -1)	{
			$this->setLoaded(true);
		}
	}


	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->reductionConditionsApplicationList as $reductionConditionsApplication){
			$reductionConditionsApplication->setNew();
		}
	}

	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReduction(){ return $this->idReduction; }
	public function getReductionConditionsApplicationList(){ return $this->reductionConditionsApplicationList; }
	public function getType(){ return $this->type; }
	public function getApplicationType(){ return $this->applicationType; }
	public function getApplicationTypeOn(){ return $this->applicationTypeOn; }
	public function getValue(){ return $this->value; }
	public function getVatAmount(){ return $this->vatAmount; }
	public function isOnlyOne(){ return $this->onlyOne; }

	public function setIdReduction($idReduction){ $this->idReduction = $idReduction; }
	public function setReductionConditionsApplicationList($reductionConditionsApplicationList){ $this->reductionConditionsApplicationList = $reductionConditionsApplicationList; }
	public function setType($type){ $this->type = $type; }
	public function setApplicationType($applicationType){ $this->applicationType = $applicationType; }
	public function setApplicationTypeOn($applicationTypeOn){ $this->applicationTypeOn = $applicationTypeOn; }
	public function setValue($value){ $this->value = $value; }
	public function setVatAmount($vatAmount){ $this->vatAmount = $vatAmount; }
	public function setOnlyOne($onlyOne){ $this->onlyOne = $onlyOne; }
}
