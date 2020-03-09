<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * RESA_ReductionConditionService is a array on reduction condtion;
 */
class RESA_ReductionConditionService extends RESA_EntityDTO
{
	private $id;
	private $idReductionCondition;
	private $method;
	private $idService;
	private $priceList;
	private $methodQuantity;
	private $number;
	private $number2;
	private $dates;
	private $times;
	private $days;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_condition_service';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idReductionCondition` int(11) NOT NULL,
		  `method` int(11) NOT NULL,
		  `idService` int(11) NOT NULL,
		  `priceList` TEXT NOT NULL,
		  `methodQuantity` int(11) NOT NULL,
		  `number` float NULL,
		  `number2` float NULL,
		  `dates` TEXT NOT NULL,
		  `times` TEXT NOT NULL,
		  `days` TEXT NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idReductionCondition` (`idReductionCondition`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idReductionCondition` FOREIGN KEY (`idReductionCondition`) REFERENCES `'.RESA_ReductionCondition::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_ReductionConditionService();
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
		$this->idReductionCondition = -1;
		$this->method = 0;
		$this->idService = -1;
		$this->priceList = '';
		$this->methodQuantity = 0;
		$this->number = 0;
		$this->number2 = 0;
		$this->dates = array();
		$this->times = array();
		$this->days = array();
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
				'idReductionCondition' => $this->idReductionCondition,
				'method' => $this->method,
				'idService' => $this->idService,
				'priceList' => $this->priceList,
				'methodQuantity' => $this->methodQuantity,
				'number' => $this->number,
				'number2' => $this->number2,
				'dates' => serialize($this->dates),
				'times' => serialize($this->times),
				'days' => serialize($this->days)
			),
			array('id'=>$this->id),
			array (
			'%d',
			'%d',
			'%d',
			'%s',
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s'),
			array (
				'%d',
			));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReductionCondition' => $this->idReductionCondition,
				'method' => $this->method,
				'idService' => $this->idService,
				'priceList' => $this->priceList,
				'methodQuantity' => $this->methodQuantity,
				'number' => $this->number,
				'number2' => $this->number2,
				'dates' => serialize($this->dates),
				'times' => serialize($this->times),
				'days' => serialize($this->days)
			),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s'
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
		if(empty($this->priceList)) $priceList = '[]';
		else {
			$priceList = '[' . $this->formatArrayNullValue($this->priceList) . ']';
		}
		return '{
			"id":'. $this->id .',
			"idReductionCondition":'. $this->idReductionCondition .',
			"method":'.$this->method.',
			"idService":'.$this->idService.',
			"priceList":'.$priceList.',
			"number":'.$this->number.',
			"number2":'.$this->number2.',
			"methodQuantity":'.$this->methodQuantity.',
			"dates":'.json_encode($this->dates).',
			"times":'.json_encode($this->times).',
			"days":'.json_encode($this->days).'
		}';
	}


	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idReductionCondition = $json->idReductionCondition;
		$this->method = $json->method;
		$this->idService = $json->idService;
		if(is_array($json->priceList)){
			$this->priceList = implode(',', $json->priceList);
			if(count($json->priceList) > 0){
				$this->priceList = $this->formatArrayNullValue($this->priceList);
			}
		}
		else {
			$this->priceList = $json->priceList;
		}
		$this->methodQuantity = $json->methodQuantity;
		if(isset($json->number)) $this->number = $json->number;
		if(isset($json->number2)) $this->number2 = $json->number2;
		if(!is_array($json->dates)){
			$this->dates = unserialize($json->dates);
		}
		else $this->dates = $json->dates;
		if(!is_array($json->times)){
			$this->times = unserialize($json->times);
		}
		else $this->times = $json->times;
		if(!is_array($json->days)){
			$this->days = unserialize($json->days);
		}
		else $this->days = $json->days;
		if($this->id != -1)	{
			$this->setLoaded(true);
		}
	}

	/**
	 * Update price id if necessary.
	 */
	public function updateServiceId($oldServiceId, $newServiceId){
		if($oldServiceId == $this->idService){
			$this->idService = $newServiceId;
		}
	}

	/**
	 * Update price id if necessary.
	 */
	public function updatePriceId($oldPriceId, $newPriceId){
		$newValue = '';
		$splitted = explode(',', $this->priceList);
		foreach($splitted as $idPrice){
			if($newValue!='')
				$newValue .=',';
			if($oldPriceId == $idPrice)
				$idPrice = $newPriceId;
			$newValue .= $idPrice;
		}
		$this->priceList = $newValue;
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReductionCondition(){ return $this->idReductionCondition; }
	public function getMethod(){ return $this->method; }
	public function getIdService(){ return $this->idService; }
	public function getPriceList(){ return $this->priceList; }
	public function getMethodQuantity(){ return $this->methodQuantity; }
	public function getNumber(){ return $this->number; }
	public function getNumber2(){ return $this->number2; }
	public function getDates(){ return $this->dates; }
	public function getTimes(){ return $this->times; }
	public function getDays(){ return $this->days; }
	public function isMandatory(){ return $this->number > 0; }

	public function setIdReductionCondition($idReductionCondition){ $this->idReductionCondition = $idReductionCondition; }
	public function setMethod($method){ $this->method = $method; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setPriceList($priceList){ $this->priceList = $priceList; }
	public function setMethodQuantity($methodQuantity){ $this->methodQuantity = $methodQuantity; }
	public function setNumber($number){ $this->number = $number; }
	public function setNumber2($number2){ $this->number = $number2; }
	public function setDates($dates){ $this->dates = $dates; }
	public function setTimes($times){ $this->times = $times; }
	public function setDays($days){ $this->days = $days; }

	static public function compare($reductionConditionService1, $reductionConditionService2){
		return $reductionConditionService2->getIdService() - $reductionConditionService1->getIdService();
	}

	public function checkDateConditions(RESA_Appointment $appointment){
		$result = true;
		if($appointment->getIdService() != $this->getIdService()) $result = false;
		else {
			$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
			foreach($this->dates as $date){
				$dateTime = new DateTime( $date->date);
				if($date->method == 0) $result = $result && $startDate->format('Y-m-d') <= $dateTime->format('Y-m-d');
				else if($date->method == 1) $result = $result && $startDate->format('Y-m-d') == $dateTime->format('Y-m-d');
				else if($date->method == 2) $result = $result && $startDate->format('Y-m-d') >= $dateTime->format('Y-m-d');
			}
			if($result){
				foreach($this->times as $time){
					Logger::DEBUG($time->time);
					$dateTime = RESA_Algorithms::timeToDate($time->time, $startDate);
					if($time->method == 0) $result = $result && $startDate < $dateTime;
					else if($time->method == 1) $result = $result && $startDate >= $dateTime;
				}
			}
			if($result && !empty($this->days)){
				$result = $result && in_array($startDate->format('w'), $this->days);
			}
		}
		return $result;
	}
}
