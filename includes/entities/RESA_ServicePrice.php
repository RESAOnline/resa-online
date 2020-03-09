<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_ServicePrice extends RESA_EntityDTO
{
	private $id;
	private $idService;
	private $name;
	private $slug;
	private $mention;
	private $presentation;
	private $price;
	private $mandatory;
	private $activateMinQuantity;
	private $minQuantity;
	private $activateMaxQuantity;
	private $maxQuantity;
	private $extra;
	private $thresholdPrices;
	private $vatList;
	private $participantsParameter;
	private $typesAccounts;
	private $equipments;
	private $activated;


	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_service_price';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idService` int(11) NOT NULL,
		  `name` text NOT NULL,
		  `slug` text NOT NULL,
		  `mention` text NOT NULL,
		  `presentation` text NOT NULL,
		  `price` double NOT NULL,
			`mandatory` tinyint(1) NOT NULL,
		  `activateMinQuantity` tinyint(1) NOT NULL,
		  `minQuantity` int(11) NOT NULL,
		  `activateMaxQuantity` tinyint(1) NOT NULL,
		  `maxQuantity` int(11) NOT NULL,
		  `extra` tinyint(1) NOT NULL,
			`thresholdPrices` TEXT NOT NULL,
			`vatList` TEXT NOT NULL,
			`participantsParameter` TEXT NOT NULL,
			`typesAccounts` TEXT NOT NULL,
			`equipments` TEXT NOT NULL,
		  `activated` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idService` (`idService`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_ServicePrice();
			$entity->fromJSON($result, true);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return the service price with slugs
	 */
	public static function getServicePriceWithSlugs($serviceSlug, $servicePriceSlug) {
		global $wpdb;
		$results = $wpdb->get_results('SELECT '.self::getTableName().'.id FROM '.self::getTableName().' INNER JOIN '.RESA_Service::getTableName().' ON '.RESA_Service::getTableName().'.id = '.self::getTableName().'.idService AND '.RESA_Service::getTableName().'.oldService = 0 AND '.RESA_Service::getTableName().'.slug = \'' . $serviceSlug . '\' AND '.self::getTableName().'.slug = \'' . $servicePriceSlug . '\'');
		if(count($results) == 1){
			$entity = new RESA_ServicePrice();
			$entity->loadById($results[0]->id);
			if($entity->isLoaded()){
				return $entity;
			}
		}
		return null;
	}


	/**
	 * Return the service price with slugs
	 */
	public static function haveServicePriceWith($idEquipment) {
		global $wpdb;
		$results = $wpdb->get_results('SELECT '.self::getTableName().'.equipments FROM '.self::getTableName().' INNER JOIN '.RESA_Service::getTableName().' ON '.RESA_Service::getTableName().'.id = '.self::getTableName().'.idService AND '.RESA_Service::getTableName().'.oldService = 0');
		foreach($results as $result){
			if(!empty($result->equipments)){
				$equipments = unserialize($result->equipments);
				if(in_array($idEquipment, $equipments)){
					return true;
				}
			}
		}
		return false;
	}


	/**
	 * update the slug of old service
	 */
	public static function updateSlugForAllServicePrices($oldSlug, $newSlug){
		global $wpdb;
		$result = $wpdb->query('UPDATE `'. self::getTableName() . '` SET slug=\''.$newSlug.'\' WHERE slug=\'' . $oldSlug.'\'');
		return $result !== false;
	}


	/**
	 * Return simple name
	 */
	public static function getServicePriceName($id){
		global $wpdb;
		$result = $wpdb->get_var('SELECT name FROM ' . self::getTableName() . ' WHERE id='.$id);
		if(isset($result)) return RESA_Tools::getTextByLocale(unserialize($result), get_locale());
		return '';
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idService = -1;
		$this->name = new stdClass();
		$this->slug = '';
		$this->mention = new stdClass();
		$this->presentation = new stdClass();
		$this->price = 0;
		$this->mandatory = false;
		$this->activateMinQuantity = false;
		$this->minQuantity = 1;
		$this->activateMaxQuantity = false;
		$this->maxQuantity = 1;
		$this->extra = false;
		$this->thresholdPrices = array();
		$this->vatList = array();
		$this->participantsParameter = '';
		$this->typesAccounts = array();
		$this->equipments = array();
		$this->activated = true;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		if(isset($result)){
			$this->fromJSON($result, true);
			$this->setLoaded(true);
		}
	}

	/**
	 * Save in database
	 */
	public function save()
	{
		if($this->isLoaded())
		{
			//Update
			$this->linkWPDB->update(self::getTableName(), array(
				'idService' =>$this->idService,
				'name'=>serialize($this->name),
				'slug'=>$this->slug,
				'mention'=>serialize($this->mention),
				'presentation'=>serialize($this->presentation),
				'price'=>$this->price,
				'mandatory' => $this->mandatory,
				'activateMinQuantity'=>$this->activateMinQuantity,
				'minQuantity'=>$this->minQuantity,
				'activateMaxQuantity'=>$this->activateMaxQuantity,
				'maxQuantity'=>$this->maxQuantity,
				'extra' => $this->extra,
				'thresholdPrices' => serialize($this->thresholdPrices),
				'vatList' => serialize($this->vatList),
				'participantsParameter' => $this->participantsParameter,
				'typesAccounts' => serialize($this->typesAccounts),
				'equipments' => serialize($this->equipments),
				'activated' => $this->activated
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idService' =>$this->idService,
				'name'=>serialize($this->name),
				'mention'=>serialize($this->mention),
				'slug'=>$this->slug,
				'presentation'=>serialize($this->presentation),
				'price'=>$this->price,
				'mandatory' => $this->mandatory,
				'activateMinQuantity'=>$this->activateMinQuantity,
				'minQuantity'=>$this->minQuantity,
				'activateMaxQuantity'=>$this->activateMaxQuantity,
				'maxQuantity'=>$this->maxQuantity,
				'extra'=>$this->extra,
				'thresholdPrices' => serialize($this->thresholdPrices),
				'vatList' => serialize($this->vatList),
				'participantsParameter' => $this->participantsParameter,
				'typesAccounts' => serialize($this->typesAccounts),
				'equipments' => serialize($this->equipments),
				'activated'=>$this->activated
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
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
		 "idService":'.$this->idService.',
		 "name":'.json_encode($this->name).',
		 "mention":'.json_encode($this->mention).',
		 "slug":"'.$this->slug.'",
		 "presentation":'.json_encode($this->presentation).',
		 "price":'.$this->price.',
		 "mandatory":'.RESA_Tools::toJSONBoolean($this->mandatory).',
		 "activateMinQuantity":'.RESA_Tools::toJSONBoolean($this->activateMinQuantity).',
		 "minQuantity":'.$this->minQuantity.',
		 "activateMaxQuantity":'.RESA_Tools::toJSONBoolean($this->activateMaxQuantity).',
		 "maxQuantity":'.$this->maxQuantity.',
		 "extra":'.RESA_Tools::toJSONBoolean($this->extra).',
		 "thresholdPrices":'.json_encode($this->thresholdPrices).',
		 "vatList":'.json_encode($this->vatList).',
		 "notThresholded":'.RESA_Tools::toJSONBoolean($this->isNotThresholded()).',
		 "participantsParameter":"'.$this->participantsParameter.'",
		 "typesAccounts":'.json_encode($this->typesAccounts).',
		 "equipments":'.json_encode($this->equipments).',
		 "activated":'.RESA_Tools::toJSONBoolean($this->activated).',
		 "private":'.RESA_Tools::toJSONBoolean($this->isPrivate()).'
		}';
	}
	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		$this->idService = $json->idService;
		if(!$formDatabase){
			foreach($json->name as $key => $name){
				$json->name->$key = esc_html($name);
			}
			$this->name = $json->name;
		}
		else {
			$this->name = unserialize($json->name);
		}
		if(isset($json->mention)){
			if(!$formDatabase && !empty($json->mention)){
				foreach($json->mention as $key => $mention){
					$json->mention->$key = esc_html($mention);
				}
				$this->mention = $json->mention;
			}
			else {
				$this->mention = unserialize($json->mention);
			}
		}
		$this->slug = esc_html($json->slug);
		if(!$formDatabase){
			foreach($json->presentation as $key => $presentation){
				$json->presentation->$key = str_replace(array("\n", "\r"), '', nl2br(esc_html($presentation)));
			}
			$this->presentation = $json->presentation;
		}else {
			$this->presentation = unserialize($json->presentation);
		}
		$this->price = $json->price;
		if(isset($json->mandatory)) $this->mandatory = $json->mandatory;
		$this->activateMinQuantity = $json->activateMinQuantity;
		$this->minQuantity = $json->minQuantity;
		$this->activateMaxQuantity = $json->activateMaxQuantity;
		$this->maxQuantity = $json->maxQuantity;
		if(isset($json->extra)) $this->extra = $json->extra;
		if(isset($json->thresholdPrices) && !empty($json->thresholdPrices)){
			if(!$formDatabase){
				$this->thresholdPrices = $json->thresholdPrices;
			}else {
				$this->thresholdPrices = unserialize($json->thresholdPrices);
			}
		}
		if(isset($json->vatList) && !empty($json->vatList)){
			if(!$formDatabase){
				$this->vatList = $json->vatList;
			}else {
				$this->vatList = unserialize($json->vatList);
			}
		}
		if(isset($json->participantsParameter)){
			$this->participantsParameter = $json->participantsParameter;
		}
		if(isset($json->typesAccounts) && !empty($json->typesAccounts)){
			if(!$formDatabase){
				$this->typesAccounts = $json->typesAccounts;
			}else {
				$this->typesAccounts = unserialize($json->typesAccounts);
			}
		}
		if(isset($json->equipments) && !empty($json->equipments)){
			if(!$formDatabase){
				$this->equipments = $json->equipments;
			}else {
				$this->equipments = unserialize($json->equipments);
			}
		}
		$this->activated = $json->activated;
		if($this->id != -1)	$this->setLoaded(true);
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function getIdService(){ return $this->idService; }
	public function getPrice(){ return $this->price; }
	public function isMandatory(){ return $this->mandatory; }
	public function isActivateMinQuantity(){ return $this->activateMinQuantity; }
	public function isActivateMaxQuantity(){ return $this->activateMaxQuantity; }
	public function getMinQuantity(){ return $this->minQuantity; }
	public function getMaxQuantity(){ return $this->maxQuantity; }
	public function getName(){ return RESA_Tools::getTextByLocale($this->name, get_locale()); }
	public function getSlug(){ return $this->slug; }
	public function isExtra(){ return $this->extra; }
	public function getThresholdPrices(){ return $this->thresholdPrices; }
	public function isNotThresholded(){ return is_bool($this->thresholdPrices) || count($this->thresholdPrices)==0; }
	public function isThresholded(){ return !$this->isNotThresholded(); }
	public function getVatList(){ return $this->vatList; }
	public function setVatList($vatList){ $this->vatList = $vatList; }
	public function getParticipantsParameter(){ return $this->participantsParameter; }
	public function setParticipantsParameter($participantsParameter){ $this->participantsParameter = $participantsParameter; }
	public function getTypesAccounts(){ return $this->typesAccounts; }
	public function getEquipments(){ return $this->equipments; }
	public function isActivated(){ return $this->activated; }
	public function isPrivate(){ return count($this->typesAccounts) == 1 && $this->typesAccounts[0] == 'private'; }

	public function getThresholdPriceByNumber($number, $hours){
		$price = 0;
		$found = false;
		foreach($this->thresholdPrices as $thresholdPrice){
			if($thresholdPrice->min <= $number && $number <= $thresholdPrice->max){
				$found = true;
				$byPerson = isset($thresholdPrice->byPerson)?$thresholdPrice->byPerson:0;
				$supPerson = isset($thresholdPrice->supPerson)?$thresholdPrice->supPerson:0;
				$price = isset($thresholdPrice->price)?$thresholdPrice->price:0;
				$price = ($number * $byPerson) + $thresholdPrice->price;
				if(isset($thresholdPrice->byHours) && $thresholdPrice->byHours){
					$price *= $hours;
				}
			}
		}
		if(!$found){
			$thresholdPrice = $this->thresholdPrices[count($this->thresholdPrices) - 1];
			$byPerson = isset($thresholdPrice->byPerson)?$thresholdPrice->byPerson:0;
			$supPerson = isset($thresholdPrice->supPerson)?$thresholdPrice->supPerson:0;
			$price = isset($thresholdPrice->price)?$thresholdPrice->price:0;
			$price = ($thresholdPrice->max * $byPerson) + $thresholdPrice->price + ($number - $thresholdPrice->max) * $supPerson;
			if(isset($thresholdPrice->byHours) && $thresholdPrice->byHours){
				$price *= $hours;
			}
		}
		return $price;
	}

	/**
	 * return true if there are enought equipment
	 */
	public function haveEnoughEquipments($equipments, $number){
		if(count($this->equipments) > 0){
			$idEquipment = $this->equipments[0];
			for($i = 0; $i < count($equipments); $i++){
				$equipment = $equipments[$i];
				if($equipment['idEquipment'] == $idEquipment &&
					(($equipment['number'] + $number) > $equipment['max'])){
						return false;
				}
			}
		}
		return true;
	}

	/**
	 * use equipments
	 */
	public function useEquipments($equipments, $number){
		if(count($this->equipments) > 0){
			$idEquipment = $this->equipments[0];
			for($i = 0; $i < count($equipments); $i++){
				if($equipments[$i]['idEquipment'] == $idEquipment){
					$equipments[$i]['number'] += $number;
				}
			}
		}
		return $equipments;
	}

	public function getTotalPrice($number, $hours){
		if($this->isNotThresholded()){
			return $number * $this->price;
		}
		else {
			return $this->getThresholdPriceByNumber($number, $hours);
		}
	}

	public function isTypeAccountOk($typeAccount){
		return $this->typesAccounts == null || count($this->typesAccounts) == 0 || in_array($typeAccount, $this->typesAccounts);
	}
}
