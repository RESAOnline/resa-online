<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_StaticGroup extends RESA_EntityDTO
{
	private $id;
	private $idPlace;
  private $name;
	private $presentation;
	private $color;
	private $activated;
	private $idService;
	private $idServicePrices;
	private $options;
	private $max;
	private $oneByBooking;


	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_static_group';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idPlace` text NOT NULL,
		  `name` text NOT NULL,
		  `presentation` text NOT NULL,
		  `color` text NOT NULL,
		  `activated` int(1) NOT NULL,
		  `idService` int(11) NOT NULL,
		  `idServicePrices` text NOT NULL,
		  `options` text NOT NULL,
		  `max` int(11) NOT NULL,
		  `oneByBooking` int(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_idService` FOREIGN KEY (`idService`) REFERENCES `'.RESA_Service::getTableName().'` (`id`) ON DELETE CASCADE;';
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
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_StaticGroup();
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
		$this->idPlace = '';
		$this->name = '';
		$this->presentation = '';
		$this->color = '';
		$this->activated = true;
		$this->idService = -1;
		$this->idServicePrices = [];
		$this->options = [];
		$this->max = 0;
		$this->oneByBooking = false;
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
        'idPlace' => $this->idPlace,
				'name' => $this->name,
				'presentation' => $this->presentation,
				'color' => $this->color,
				'activated' => $this->activated,
				'idService' => $this->idService,
				'idServicePrices' => serialize($this->idServicePrices),
				'options' => serialize($this->options),
				'max' => $this->max,
				'oneByBooking' => $this->oneByBooking
			),
			array('id'=>$this->id),
			array (
        '%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idPlace' => $this->idPlace,
				'name' => $this->name,
				'presentation' => $this->presentation,
				'color' => $this->color,
				'activated' => $this->activated,
				'idService' => $this->idService,
				'idServicePrices' => serialize($this->idServicePrices),
				'options' => serialize($this->options),
				'max' => $this->max,
				'oneByBooking' => $this->oneByBooking
			),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
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
      "idPlace":"'.$this->idPlace.'",
  		"name":"'.$this->name .'",
  		"presentation":"'.$this->presentation .'",
  		"color":"'.$this->color .'",
			"activated":'.RESA_Tools::toJSONBoolean($this->activated).',
			"idService":'. $this->idService .',
			"idServicePrices":'. json_encode($this->idServicePrices) .',
			"options":'. json_encode($this->options) .',
			"max":'.$this->max.',
			"oneByBooking":'.RESA_Tools::toJSONBoolean($this->oneByBooking).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idPlace = $json->idPlace;
		$this->name = $json->name;
		$this->presentation = $json->presentation;
		$this->color = $json->color;
		$this->activated = $json->activated;
		$this->idService = $json->idService;
		if(isset($json->idServicePrices)) {
			if(is_array($json->idServicePrices)){
				$this->idServicePrices = $json->idServicePrices;
			}
			else if($json->idServicePrices!=false) {
				$this->idServicePrices = unserialize($json->idServicePrices);
			}
		}
		if(isset($json->options)) {
			if(is_array($json->options)){
				$this->options = $json->options;
			}
			else if($json->options!=false) {
				$this->options = unserialize($json->options);
			}
		}
		$this->max = $json->max;
		if(isset($json->oneByBooking)) $this->oneByBooking = $json->oneByBooking;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdPlace($idPlace){ $this->idPlace = $idPlace; }
	public function setName($name){ $this->name = $name; }
	public function setPresentation($presentation){ $this->presentation = $presentation; }
	public function setColor($color){ $this->color = $color; }
	public function setActivated($activated){ $this->activated = $activated; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdServicePrices($idServicePrices){ $this->idServicePrices = $idServicePrices; }
	public function setOptions($options){ $this->options = $options; }
	public function setMax($max){ $this->max = $max; }
	public function setOneByBooking($oneByBooking){ $this->oneByBooking = $oneByBooking; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdPlace(){ return $this->idPlace; }
	public function getName(){ return $this->name; }
	public function getPresentation(){ return $this->presentation; }
	public function getColor(){ return $this->color; }
	public function isActivated(){ return $this->activated; }
	public function getIdService(){ return $this->idService; }
	public function getIdServicePrices(){ return $this->idServicePrices; }
	public function getOptions(){ return $this->options; }
	public function getMax(){ return $this->max; }
	public function isOneByBooking(){ return $this->$oneByBooking; }

	public function updateService($oldService, $newService){
		if($this->idService == $oldService->getId()){
			$this->idService = $newService->getId();
			$linkOldNewPrice = array();
			foreach($oldService->getServicePrices() as $oldServicePrice){
				foreach($newService->getServicePrices() as $newServicePrice){
					if($oldServicePrice->getSlug() == $newServicePrice->getSlug()){
						array_push($linkOldNewPrice, array($oldServicePrice->getId(), $newServicePrice->getId()));
					}
				}
			}
			for($i = 0; $i < count($this->idServicePrices); $i++){
				foreach($linkOldNewPrice as $link){
					if($this->idServicePrices[$i] == $link[0]){
						$this->idServicePrices[$i] = $link[1];
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Return true if one idServicePrice are present in $idServicePrice
	 */
	public function haveOneIdServicePrices($idServicePrices){
		if(count($this->idServicePrices) == 0 || count($idServicePrices) == 0) return true;
		else {
			for($i = 0; $i < count($this->idServicePrices); $i++){
				for($j = 0; $j < count($idServicePrices); $j++){
					if($idServicePrices[$j] == $this->idServicePrices[$i]){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * to group
	 */
	public function toGroup($idBooking, $suffix){
		$group = new RESA_Group();
		$group->setIdPlace($this->idPlace);
		$group->setIdService($this->idService);
		$group->setPresentation($this->presentation);
		$group->setColor($this->color);
		$group->setOptions($this->options);
		$group->setMax($this->max);
		$group->setOneByBooking($this->oneByBooking);
		if($this->oneByBooking){
			$group->setName($this->name . $suffix);
			$group->setIdBooking($idBooking);
		}
		else {
			$group->setName($this->name);
		}
		return $group;
	}
}
