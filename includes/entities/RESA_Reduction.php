<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_Reduction extends RESA_EntityDTO
{
	private $id;
	private $position;
	private $name;
	private $presentation;
	private $activated;
	private $visibility;
	private $combinable;
	private $linkOldReductions;
	private $oldReduction;
	private $reductionConditionsList;
	private $reductionApplications;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
			`position` int(11) NOT NULL,
		  `name` TEXT NOT NULL,
		  `presentation` TEXT NOT NULL,
		  `activated` tinyint(1) NOT NULL,
			`visibility` int(11) NOT NULL,
		  `combinable` tinyint(1) NOT NULL,
		  `oldReduction` tinyint(1) NOT NULL,
		  `linkOldReductions` TEXT NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return '';
	}

	/**
	 * return the delete query
	 */
	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * get current promo codes
	 */
	public static function getAllPromoCodes(){
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT '. RESA_ReductionCondition::getTableName() . '.param2 as code FROM '. RESA_ReductionCondition::getTableName() . ' INNER JOIN '.RESA_ReductionConditions::getTableName().' ON '.RESA_ReductionConditions::getTableName().'.id = '. RESA_ReductionCondition::getTableName() . '.idReductionConditions AND '. RESA_ReductionCondition::getTableName() . '.type = \'code\' INNER JOIN '.self::getTableName().' ON '.self::getTableName().'.id = '.RESA_ReductionConditions::getTableName().'.idReduction AND '.RESA_Reduction::getTableName().'.activated = \'1\'  AND '.RESA_Reduction::getTableName().'.oldReduction = \'0\' ORDER BY code');
		foreach($results as $result){
			array_push($allData, $result->code);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array('oldReduction'=>false))
	{
		return self::getAllDataWithOrderBy($data, 'position');
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithOrderBy($data, $orderBy)
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY '. $orderBy);
		foreach($results as $result){
			$entity = new RESA_Reduction();
			$entity->fromJSON($result, true);
			$entity->setLoaded(true);
			$entity->setReductionConditionsList(RESA_ReductionConditions::getAllData(array('idReduction'=>$entity->getId())));
			$entity->setReductionApplications(RESA_ReductionApplication::getAllData(array('idReduction'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * return true if have a reduction
	 */
	public static function haveOneReductions(){
		global $wpdb;
		$results = $wpdb->get_var('SELECT COUNT(id) FROM '. self::getTableName() . ' WHERE oldReduction=0');
		if(isset($results) && $results > 0) return true;
		return false;
	}


	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->position = -1;
		$this->name =new stdClass();
		$this->presentation = new stdClass();
		$this->activated = false;
		$this->visibility = 0;
		$this->combinable = true;
		$this->oldReduction = false;
		$this->linkOldReductions = '';
		$this->reductionConditionsList = array();
		$this->reductionApplications = array();
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		if(isset($result)){
			$this->fromJSON($result, true);
			$this->setReductionConditionsList(RESA_ReductionConditions::getAllData(array('idReduction'=>$this->getId())));
			$this->setReductionApplications(RESA_ReductionApplication::getAllData(array('idReduction'=>$this->getId())));
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
			$lastReduction = new RESA_Reduction();
			$lastReduction->loadById($this->id);
			$lastReductionConditionsList = $lastReduction->getReductionConditionsList();
			$lastReductionApplications = $lastReduction->getReductionApplications();
			$this->linkWPDB->update(self::getTableName(), array(
				'position' => $this->position,
				'name' => serialize($this->name),
				'presentation' => serialize($this->presentation),
				'activated' => $this->activated,
				'visibility' => $this->visibility,
				'combinable' => $this->combinable,
				'oldReduction' => $this->oldReduction,
				'linkOldReductions' => $this->linkOldReductions
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s'
			),
			array (
			'%d'));

			$idReductionConditionsList = array();
			$idReductionApplications = array();
			for($i = 0; $i < count($this->reductionConditionsList); $i++)
			{
				if(!$this->reductionConditionsList[$i]->isNew())
					array_push($idReductionConditionsList, $this->reductionConditionsList[$i]->getId());
				$this->reductionConditionsList[$i]->save();
			}
			for($i = 0; $i < count($this->reductionApplications); $i++)
			{
				if(!$this->reductionApplications[$i]->isNew())
					array_push($idReductionApplications, $this->reductionApplications[$i]->getId());
				$this->reductionApplications[$i]->save();
			}
			for($i = 0; $i < count($lastReductionConditionsList); $i++) {
				if(!in_array($lastReductionConditionsList[$i]->getId(), $idReductionConditionsList))
					$lastReductionConditionsList[$i]->deleteMe();
			}
			for($i = 0; $i < count($lastReductionApplications); $i++) {
				if(!in_array($lastReductionApplications[$i]->getId(), $idReductionApplications))
					$lastReductionApplications[$i]->deleteMe();
			}
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'position' => $this->position,
				'name' => serialize($this->name),
				'presentation' => serialize($this->presentation),
				'activated' => $this->activated,
				'visibility' => $this->visibility,
				'combinable' => $this->combinable,
				'oldReduction' => $this->oldReduction,
				'linkOldReductions' => $this->linkOldReductions
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s'
			));
			$idReduction = $this->linkWPDB->insert_id;
			$this->id = $idReduction;
			for($i = 0; $i < count($this->reductionConditionsList); $i++)
			{
				$this->reductionConditionsList[$i]->setIdReduction($idReduction);
				$this->reductionConditionsList[$i]->save();
			}
			for($i = 0; $i < count($this->reductionApplications); $i++)
			{
				$this->reductionApplications[$i]->setIdReduction($idReduction);
				$this->reductionApplications[$i]->save();
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
			if((RESA_BookingReduction::numberOfReductionsUsed($this->getId()) <= 0 &&
			RESA_AppointmentReduction::numberOfReductionsUsed($this->getId()) <= 0)){
				for($i = 0; $i < count($this->reductionConditionsList); $i++) {
					$this->reductionConditionsList[$i]->deleteMe();
				}
				for($i = 0; $i < count($this->reductionApplications); $i++) {
					$this->reductionApplications[$i]->deleteMe();
				}
				$this->linkWPDB->delete(self::getTableName(),array('id'=>$this->id),array ('%d'));
			}
			else {
				$this->setOldReduction(true);
				$this->save();
			}
		}
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON()
	{
		$text = '{
			"id":'. $this->id .',
			"position":'. $this->position .',
			"name":'. json_encode($this->name) .',
			"presentation":'.json_encode($this->presentation).',
			"activated":'.RESA_Tools::toJSONBoolean($this->activated).',
			"visibility":'.$this->visibility.',
			"combinable":'.RESA_Tools::toJSONBoolean($this->combinable).',
			"linkOldReductions":"'.$this->linkOldReductions.'",
			"oldReduction":'.RESA_Tools::toJSONBoolean($this->oldReduction).',
			"reductionConditionsList":'.RESA_Tools::formatJSONArray($this->reductionConditionsList).',
			"reductionApplications":'.RESA_Tools::formatJSONArray($this->reductionApplications).'
		}';
		return $text;
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		if(isset($json->position)) $this->position = $json->position;
		if(!$formDatabase){
			foreach($json->name as $key => $name){
				$json->name->$key = esc_html($name);
			}
			$this->name = $json->name;
		}
		else {
			$this->name = unserialize($json->name);
		}
		if(!$formDatabase){
			foreach($json->presentation as $key => $presentation){
				$json->presentation->$key = str_replace(array("\n", "\r"), '', nl2br(esc_html($presentation)));
			}
			$this->presentation = $json->presentation;
		}else {
			$this->presentation = unserialize($json->presentation);
		}
		$this->activated = $json->activated;
		if(isset($json->visibility)) $this->visibility = $json->visibility;
		$this->combinable = $json->combinable;
		$this->oldReduction = $json->oldReduction;
		$this->linkOldReductions = $json->linkOldReductions;
		if(isset($json->reductionConditionsList))
		{
			$reductionConditionsList = array();
			for($i = 0; $i < count($json->reductionConditionsList); $i++)
			{
				$reductionConditions = new RESA_ReductionConditions();
				$reductionConditions->fromJSON($json->reductionConditionsList[$i]);
				array_push($reductionConditionsList, $reductionConditions);
			}
			$this->setReductionConditionsList($reductionConditionsList);
		}
		if(isset($json->reductionApplications))
		{
			$reductionApplications = array();
			for($i = 0; $i < count($json->reductionApplications); $i++)
			{
				$reductionApplication = new RESA_ReductionApplication();
				$reductionApplication->fromJSON($json->reductionApplications[$i]);
				array_push($reductionApplications, $reductionApplication);
			}
			$this->setReductionApplications($reductionApplications);
		}
		if($this->id != -1)	$this->setLoaded(true);
	}

	public function updateIdService($oldServiceId, $newServiceId)	{
		$oldService = new RESA_Service();
		$oldService->loadById($oldServiceId);
		$newService = new RESA_Service();
		$newService->loadById($newServiceId);
		$this->updateService($oldService, $newService);
	}

	/**
	 * Update id service
	 */
	public function updateService($oldService, $newService)	{
		$linkOldNewPrice = array();
		foreach($oldService->getServicePrices() as $oldServicePrice){
			foreach($newService->getServicePrices() as $newServicePrice){
				if($oldServicePrice->getSlug() == $newServicePrice->getSlug()){
					array_push($linkOldNewPrice, array($oldServicePrice->getId(), $newServicePrice->getId()));
				}
			}
		}
		foreach($this->reductionConditionsList as $reductionConditions){
			foreach($reductionConditions->getReductionConditions() as $reductionCondition){
				if($reductionCondition->getType()=='services'){
					$reductionCondition->updateServiceId($oldService->getId(), $newService->getId());
					foreach($linkOldNewPrice as $link){
						$reductionCondition->updatePriceId($link[0], $link[1]);
					}
				}
			}
		}
		foreach($this->reductionApplications as $reductionApplications){
			foreach($reductionApplications->getReductionConditionsApplicationList() as $reductionConditionsApplication){
				foreach($reductionConditionsApplication->getReductionConditionsApplications() as $reductionConditionApplication){
					if($reductionConditionApplication->getType()=='service' && $reductionConditionApplication->getParam2() == $oldService->getId()){
						$reductionConditionApplication->setParam2($newService->getId());
						foreach($linkOldNewPrice as $link){
							$reductionConditionApplication->updatePriceId($link[0], $link[1]);
						}
					}
				}
			}
		}
	}

	/**
	 * is need coupon code
	 */
	public function isNeedCouponCode(){
		foreach($this->reductionConditionsList as $reductionConditions){
			foreach($reductionConditions->getReductionConditions() as $reductionCondition){
				if($reductionCondition->getType()=='code' && $reductionCondition->getParam1() == 0){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * true if service
	 */
	public function isReductionByService(){
		foreach($this->reductionConditionsList as $reductionConditions){
			foreach($reductionConditions->getReductionConditions() as $reductionCondition){
				if($reductionCondition->getType()=='service'){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Replace all oldId by the newId
	 */
	public function updateIdReduction($oldId, $newId){
		$tokens = explode(',', $this->linkOldReductions);
		for($i = 0; $i < count($tokens); $i++){
			if($tokens[$i] == $oldId){
				$tokens[$i] = $newId;
			}
		}
		$this->linkOldReductions = implode(',', $tokens);
	}

	public function getId(){ return $this->id; }
	public function getPosition(){ return $this->position; }
	public function isNew(){ return $this->id == -1; }
	public function getName(){ return RESA_Tools::getTextByLocale($this->name,get_locale()); }
	public function getPresentation(){ return RESA_Tools::getTextByLocale($this->presentation, get_locale()); }
	public function getReductionConditionsList(){ return $this->reductionConditionsList; }
	public function getReductionApplications(){ return $this->reductionApplications; }
	public function isActivated(){ return $this->activated; }
	public function getVisibility(){ return $this->visibility; }
	public function isCombinable(){ return $this->combinable; }
	public function isOldReduction(){ return $this->oldReduction; }
	public function getLinkOldReductions(){ return $this->linkOldReductions; }
	public function isSameReduction($idReduction){
		$tokens = explode(',', $this->linkOldReductions);
		return $this->id == $idReduction || in_array($idReduction, $tokens);
	}



	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
		foreach($this->reductionConditionsList as $reductionConditions){
			$reductionConditions->setNew();
		}
		foreach($this->reductionApplications as $reductionApplications){
			$reductionApplications->setNew();
		}
	}
	public function setName($name){ $this->name = $name; }
	public function setPresentation($presentation){ $this->presentation = $presentation; }
	public function setReductionConditionsList($reductionConditionsList){ $this->reductionConditionsList = $reductionConditionsList; }
	public function setReductionApplications($reductionApplications){ $this->reductionApplications = $reductionApplications; }
	public function setActivated($activated){ $this->activated = $activated;}
	public function setVisibility($visibility){ $this->visibility = $visibility;}
	public function setCombinable($combinable){ $this->combinable = $combinable;}
	public function setOldReduction($oldReduction){ $this->oldReduction = $oldReduction;}
	public function setLinkOldReductions($linkOldReductions){ $this->linkOldReductions = $linkOldReductions; }
	public function addLinkOldReduction($idReduction){
		if(!empty($this->linkOldReductions)) $this->linkOldReductions .= ',';
		$this->linkOldReductions .= $idReduction;
	}

	public function needCreateNewVersion($oldReduction){
		$reductionsApplications = $this->getReductionApplications();
		$oldReductionsApplications = $oldReduction->getReductionApplications();
		if(count($reductionsApplications) != count($oldReductionsApplications)) return true;
		for($i = 0; $i < count($reductionsApplications); $i++){
			if($oldReductionsApplications[$i]->getValue() != $reductionsApplications[$i]->getValue()){
				return true;
			}
		}
		return false;
	}
}
