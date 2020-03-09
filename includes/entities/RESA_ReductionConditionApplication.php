<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * RESA_ReductionConditionApplication in function of type reduction.
 */
class RESA_ReductionConditionApplication extends RESA_EntityDTO
{
	private $id;
	private $idReductionConditionsApplication;
	//code, service, amount, date, time, registerDate
	private $type;
	private $param1;
	private $param2;
	private $param3;
	private $param4;
	private $param5;
	private $param6;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_condition_application';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idReductionConditionsApplication` int(11) NOT NULL,
		  `type` enum(\'code\', \'service\', \'amount\',\'date\', \'time\', \'registerDate\', \'days\') NOT NULL,
		  `param1` TEXT NOT NULL,
		  `param2` TEXT NOT NULL,
		  `param3` TEXT NOT NULL,
		  `param4` TEXT NOT NULL,
		  `param5` TEXT NOT NULL,
		  `param6` TEXT NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idReductionConditionsApplication` (`idReductionConditionsApplication`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idReductionConditions` FOREIGN KEY (`idReductionConditionsApplication`) REFERENCES `'.RESA_ReductionConditionsApplication::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_ReductionConditionApplication();
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
		$this->idReductionConditionsApplication = -1;
		$this->type = 'service';
		$this->param1 = '';
		$this->param2 = '';
		$this->param3 = '';
		$this->param4 = '';
		$this->param5 = '';
		$this->param6 = '';
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
				'idReductionConditionsApplication' => $this->idReductionConditionsApplication,
				'type' => $this->type,
				'param1' => $this->param1,
				'param2' => $this->param2,
				'param3' => $this->param3,
				'param4' => $this->param4,
				'param5' => $this->param5,
				'param6' => $this->param6
			),
			array('id' => $this->id),
			array (
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'),
			array (
				'%d'
			));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReductionConditionsApplication' => $this->idReductionConditionsApplication,
				'type' => $this->type,
				'param1' => $this->param1,
				'param2' => $this->param2,
				'param3' => $this->param3,
				'param4' => $this->param4,
				'param5' => $this->param5,
				'param6' => $this->param6
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
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
			$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
		}
	}

	/**
	 *
	 */
	public function formatArrayNullValue($param){
		$splitted = explode(',', $param);
		$format = '';
		for($i = 0; $i < count($splitted); $i++){
			if($i != 0) $format .=',';
			if(isset($splitted[$i]) && $splitted[$i] != 'null' && !empty($splitted[$i])){
				$format .= $splitted[$i];
			}
			else $format .= '-1';
		}
		return $format;
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$param1 = '"'.$this->param1.'"';
		$param2 = '"'.$this->param2.'"';
		$param3 = '"'.$this->param3.'"';
		if($this->type == 'service' || $this->type == 'days'){
			if(empty($this->param3)) $param3 = '[]';
			else {
				$param3 = '[' . $this->formatArrayNullValue($this->param3) . ']';
			}
		}else if($this->type == 'amount' && !empty($this->param3)){
			$param3 = $this->param3;
		}
		$param4 = '"'.$this->param4.'"';
		if($this->type == 'service' && !empty($this->param5))
			$param5 = $this->param5;
		else $param5 = '"'.$this->param5.'"';
		$param6 = '"'.$this->param6.'"';

		return '{
			"id":'. $this->id .',
			"idReductionConditionsApplication":'. $this->idReductionConditionsApplication .',
			"type":"'.$this->type.'",
			"param1":'.$param1.',
			"param2":'.$param2.',
			"param3":'.$param3.',
			"param4":'.$param4.',
			"param5":'.$param5.',
			"param6":'.$param6.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idReductionConditionsApplication = $json->idReductionConditionsApplication;
		$this->type = $json->type;
		$this->param1 = $json->param1;
		$this->param2 = $json->param2;
		if(is_array($json->param3)){
			$this->param3 = implode(',', $json->param3);
			if(count($json->param3) > 0){
				$this->param3 = $this->formatArrayNullValue($this->param3);
			}
		}
		else {
			$this->param3 = $json->param3;
		}
		$this->param4 = $json->param4;
		$this->param5 = $json->param5;
		$this->param6 = $json->param6;
		if($this->id != -1){
			$this->setLoaded(true);
		}
	}

	/**
	 * Update price id if necessary.
	 */
	public function updatePriceId($oldPriceId, $newPriceId){
		if($this->type == 'service'){
			$newValue = '';
			$splitted = explode(',',$this->param3);
			foreach($splitted as $idPrice){
				if($newValue!='')
					$newValue .=',';
				if($oldPriceId == $idPrice)
					$idPrice = $newPriceId;
				$newValue .= $idPrice;
			}
			$this->param3 = $newValue;
		}
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReductionConditionsApplication(){ return $this->idReductionConditionsApplication; }
	public function getType(){ return $this->type; }
	public function getParam1(){ return $this->param1; }
	public function getParam2(){ return $this->param2; }
	public function getParam3(){ return $this->param3; }
	public function getParam4(){ return $this->param4; }
	public function getParam5(){ return $this->param5; }
	public function getParam6(){ return $this->param6; }

	public function setIdReductionConditionsApplication($idReductionConditionsApplication){ $this->idReductionConditionsApplication = $idReductionConditionsApplication; }
	public function setType($type){ $this->type = $type; }
	public function setParam1($param1){ $this->param1 = $param1; }
	public function setParam2($param2){ $this->param2 = $param2; }
	public function setParam3($param3){ $this->param3 = $param3; }
	public function setParam4($param4){ $this->param4 = $param4; }
	public function setParam5($param5){ $this->param5 = $param5; }
	public function setParam6($param6){ $this->param6 = $param6; }
}
