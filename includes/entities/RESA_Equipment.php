<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Equipment extends RESA_EntityDTO
{
	private $id;
	private $position;
	private $name;
	private $presentation;
	private $places;
	private $numbers;

	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_equipment';
	}

	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `position` int(11) NOT NULL,
		  `name` text NOT NULL,
		  `presentation` text NOT NULL,
		  `email` text NULL,
		  `places` text NOT NULL,
			`numbers` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
	}

	public static function getConstraints()
	{
		return '';
	}

	public static function getDeleteQuery()
	{
		return 'DROP TABLE IF EXISTS '.self::getTableName();
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array(), $orderBy = 'id')
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE.' ORDER BY ' .$orderBy);
		foreach($results as $result){
			$entity = new RESA_Equipment();
			$entity->fromJSON($result, true);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return the number of id equipment
	 */
	public static function getNumber($idEquipment){
		global $wpdb;
		$number = $wpdb->get_var('SELECT '. self::getTableName() . '.numbers FROM '. self::getTableName() . ' WHERE id=' . $idEquipment);
		if(!isset($number)) $number = 0;
		return $number;
	}


	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->position = -1;
		$this->name =  new stdClass();
		$this->presentation = new stdClass();
		$this->places = array();
		$this->numbers = 1;
	}

	/**
	 * Load form database
	 */
	public function loadById($id)
	{
		$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE id = \'' . $id . '\'');
		$this->fromJSON($result, true);
		$this->setLoaded(true);
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
				'position' => $this->position,
				'name' => serialize($this->name),
				'presentation' => serialize($this->presentation),
				'places' => serialize($this->places),
				'numbers' => $this->numbers
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'position' => $this->position,
				'name' => serialize($this->name),
				'presentation' => serialize($this->presentation),
				'places' => serialize($this->places),
				'numbers' => $this->numbers
			),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
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
		if($this->isLoaded() && $this->isDeletable()) {
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
			"position":'.$this->position.',
			"name":'.json_encode($this->name).',
			"presentation":'.json_encode($this->presentation).',
			"places":'.json_encode($this->places).',
			"numbers":'.$this->numbers.',
			"isDeletable":'.RESA_Tools::toJSONBoolean($this->isDeletable()).'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json, $formDatabase = false)
	{
		$this->id = $json->id;
		if(isset($json->position)) $this->position = $json->position;
		if(!$formDatabase && RESA_Tools::canUseForeach($json->name)){
			foreach($json->name as $key => $name){
				$json->name->$key = str_replace(array("\n", "\r"), '', nl2br(esc_html($name)));
			}
			$this->name = $json->name;
		}else {
			$this->name = unserialize($json->name);
		}
		if(!$formDatabase && RESA_Tools::canUseForeach($json->presentation)){
			foreach($json->presentation as $key => $presentation){
				$json->presentation->$key = str_replace(array("\n", "\r"), '', nl2br(esc_html($presentation)));
			}
			$this->presentation = $json->presentation;
		}else {
			$this->presentation = unserialize($json->presentation);
		}
		if(isset($json->places)) {
			if(is_array($json->places)){
				$this->places = $json->places;
			}
			else if($json->places!=false) {
				$this->places = unserialize($json->places);
			}
		}
		if(isset($json->numbers)) $this->numbers = $json->numbers;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/**
	 * return true if is deletable
	 */
	public function isDeletable(){
		return !RESA_ServicePrice::haveServicePriceWith($this->id);
	}


	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function isNewEquipment(){ return $this->id==-1; }
	public function getId(){ return $this->id; }
	public function getPosition(){ return $this->position; }
	public function setPosition($position){ $this->position = $position; }
	public function getName(){ return $this->name; }
	public function getCurrentName(){ return RESA_Tools::getTextByLocale($this->name, get_locale()); }
	public function getPlaces(){ return $this->places; }
	public function setPlaces($places){ $this->places = $places; }
	public function addPlace($place){ array_push($this->places,$place); }
	public function setNumbers($numbers){ $this->numbers = $numbers;}
	public function getNumbers(){ return $this->numbers; }

}
