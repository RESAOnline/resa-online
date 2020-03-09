<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Group extends RESA_EntityDTO
{
	private $id;
	private $idService;
	private $idPlace;
  private $name;
	private $presentation;
	private $color;
	private $startDate;
	private $endDate;
	private $lastModificationDate;
	private $idMembers;
	private $idParticipants;
	private $options;
	private $max;
	private $oneByBooking;
	private $idBooking;


	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_group';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idService` int(11) NOT NULL,
		  `idPlace` text NOT NULL,
		  `name` text NOT NULL,
		  `presentation` text NOT NULL,
		  `color` text NOT NULL,
		  `startDate` datetime NOT NULL,
		  `endDate` datetime NOT NULL,
      `lastModificationDate` datetime NOT NULL,
		  `idMembers` text NOT NULL,
		  `idParticipants` text NOT NULL,
		  `options` text NOT NULL,
		  `max` int(11) NOT NULL,
		  `oneByBooking` int(1) NOT NULL,
		  `idBooking` int(11) NOT NULL,
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
	 *
	 */
	public static function updateIdServices($oldIdService, $idService) {
		global $wpdb;
		$lastModificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$results = $wpdb->query('UPDATE `'.self::getTableName().'` SET idService='.$idService.',lastModificationDate=\''.$lastModificationDate.'\' WHERE idService='.$oldIdService.';');
		Logger::DEBUG('COucou :' . print_r($results, true));
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY startDate');
		foreach($results as $result){
			$entity = new RESA_Group();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataInterval($startDate, $endDate)
	{
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . '
			WHERE (startDate <= \''.$startDate.'\' AND endDate > \''.$startDate.'\') OR
				(startDate < \''.$endDate.'\' AND endDate > \''.$endDate.'\') OR
				(startDate >= \''.$startDate.'\' AND endDate <= \''.$endDate.'\') ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Group();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 *
	 */
	public static function getGroupNameParticipantURI($idPlace, $idService, $startDate, $endDate, $participantURI){
		global $wpdb;
		$participantURI = str_replace('\'','\'\'', $participantURI);
		$result = $wpdb->get_var('SELECT name FROM '. self::getTableName() . ' WHERE idPlace=\''.$idPlace.'\' AND idService='.$idService.' AND startDate=\''.$startDate.'\' AND endDate=\''.$endDate.'\' AND idParticipants LIKE \'%'.$participantURI.'%\'');
		if(isset($result)) return $result;
		return '';
	}

	/**
	 * return group name formated
	 */
	public static function formatedName($customer, $name){
		if(strlen($customer->getLastName()) > 0){
			$displayName = $customer->getLastName();
		}
		else if(strlen($customer->getCompany()) > 0){
			$displayName = $customer->getCompany();
		}
		return preg_replace('/\(#.+\)/i',' - ' . $displayName, $name);
	}


	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idService = -1;
		$this->idPlace = '';
		$this->name = '';
		$this->presentation = '';
		$this->color = '';
		$this->startDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->endDate = date('Y-m-d H:i:s', current_time('timestamp'));
    $this->lastModificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->idMembers = [];
		$this->idParticipants = [];
		$this->options = [];
		$this->max = 0;
		$this->oneByBooking = false;
		$this->idBooking = -1;
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
				'idService' => $this->idService,
				'idPlace' => $this->idPlace,
				'name' => $this->name,
				'presentation' => $this->presentation,
				'color' => $this->color,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'lastModificationDate' => $this->lastModificationDate,
				'idMembers' => serialize($this->idMembers),
				'idParticipants' => serialize($this->idParticipants),
				'options' => serialize($this->options),
				'max' => $this->max,
				'oneByBooking' => $this->oneByBooking,
				'idBooking' => $this->idBooking
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
        'idService' => $this->idService,
				'idPlace' => $this->idPlace,
				'name' => $this->name,
				'presentation' => $this->presentation,
				'color' => $this->color,
				'startDate' => $this->startDate,
				'endDate' => $this->endDate,
				'lastModificationDate' => $this->lastModificationDate,
				'idMembers' => serialize($this->idMembers),
				'idParticipants' => serialize($this->idParticipants),
				'options' => serialize($this->options),
				'max' => $this->max,
				'oneByBooking' => $this->oneByBooking,
				'idBooking' => $this->idBooking
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
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
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
	public function toJSON($loadCustomer =  false){
		$JSON = '{
			"id":'. $this->id .',
			"idService":'. $this->idService .',
      "idPlace":"'.$this->idPlace.'",
  		"name":"'.$this->name .'",
  		"presentation":"'.$this->presentation .'",
  		"color":"'.$this->color .'",
  		"startDate":"'.$this->startDate .'",
  		"endDate":"'.$this->endDate .'",
  		"lastModificationDate":"'.$this->lastModificationDate .'",
  		"idMembers":'.json_encode($this->idMembers) .',
  		"idParticipants":'.json_encode($this->idParticipants) .',
  		"options":'.json_encode($this->options) .',
			"max":'.$this->max.',
			"type":"group",
			"oneByBooking":'.RESA_Tools::toJSONBoolean($this->oneByBooking).',
			"idBooking":'.$this->idBooking;
		$displayName = $this->name;
		if($this->oneByBooking && $this->idBooking > -1){
			$customer = new RESA_Customer();
			$customer->loadByIdWithoutBookings(RESA_Booking::getIdCustomerByIdBooking($this->idBooking));
			if($customer->isLoaded()){
				$JSON .= ', "customer":'.$customer->toJSON();
				$displayName = self::formatedName($customer, $this->name);
			}
		}
		$JSON .= ', "displayName":"'.$displayName.'"';
		$JSON .= '}';
		return $JSON;
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idService = $json->idService;
		$this->idPlace = $json->idPlace;
		$this->name = $json->name;
		$this->presentation = RESA_Tools::echapEolTab($json->presentation);
		$this->color = $json->color;
		$this->startDate = $json->startDate;
		$this->endDate = $json->endDate;
		$this->lastModificationDate = $json->lastModificationDate;
    if(isset($json->idMembers)) {
			if(is_array($json->idMembers)){
				$this->idMembers = $json->idMembers;
			}
			else if($json->idMembers!=false) {
				$this->idMembers = unserialize($json->idMembers);
			}
		}
		if(isset($json->idParticipants)) {
			if(is_array($json->idParticipants)){
				$this->idParticipants = $json->idParticipants;
			}
			else if($json->idParticipants!=false) {
				$this->idParticipants = unserialize($json->idParticipants);
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
		if(isset($json->idBooking)) $this->idBooking = $json->idBooking;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdPlace($idPlace){ $this->idPlace = $idPlace; }
	public function setName($name){ $this->name = $name; }
	public function setPresentation($presentation){ $this->presentation = $presentation; }
	public function setColor($color){ $this->color = $color; }
	public function setStartDate($startDate){ $this->startDate = $startDate; }
	public function setEndDate($endDate){ $this->endDate = $endDate; }
	public function setLastModificationDate($lastModificationDate){ $this->lastModificationDate = $lastModificationDate; }
	public function clearLastModificationDate(){ $this->lastModificationDate = date('Y-m-d H:i:s', current_time('timestamp')); }
	public function setIdMembers($idMembers){ $this->idMembers = $idMembers; }
	public function addIdMember($idMember){
		if(!$this->haveIdMember($idMember)){
			array_push($this->idMembers, $idMember);
			$this->clearLastModificationDate();
		}
	}
	public function removeIdMember($idMember){
		if($this->haveIdMember($idMember)){
			array_splice($this->idMembers, array_search($idMember, $this->idMembers), 1);
			$this->clearLastModificationDate();
		}
	}

	public function haveIdMember($idMember){
		return in_array($idMember, $this->idMembers) || in_array($idMember.'', $this->idMembers);
	}


	public function setIdParticipants($idParticipants){ $this->idParticipants = $idParticipants; }
	public function addIdParticipant($idParticipant){
		if(!$this->haveParticipant($idParticipant)){
			array_push($this->idParticipants, $idParticipant);
			$this->clearLastModificationDate();
		}
	}
	public function removeIdParticipant($idParticipant){
		if($this->haveParticipant($idParticipant)){
			array_splice($this->idParticipants, array_search($idParticipant, $this->idParticipants), 1);
			$this->clearLastModificationDate();
		}
	}

	public function haveParticipant($idParticipant){
		return in_array($idParticipant, $this->idParticipants);
	}
	public function haveOneParticipants($idParticipants){
		foreach($idParticipants as $idParticipant){
			if($this->haveParticipant($idParticipant)){
				return true;
			}
		}
		return false;
	}
	public function haveAllParticipants($idParticipants){
		foreach($idParticipants as $idParticipant){
			if(!$this->haveParticipant($idParticipant)){
				return false;
			}
		}
		return true;
	}
	public function setOptions($options){ $this->options = $options; }
	public function setMax($max){ $this->max = $max; }
	public function setOneByBooking($oneByBooking){ $this->oneByBooking = $oneByBooking; }
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdService(){ return $this->idService; }
	public function getIdPlace(){ return $this->idPlace; }
	public function getName(){ return $this->name; }
	public function getPresentation(){ return $this->presentation; }
	public function getColor(){ return $this->color; }
	public function getStartDate(){ return $this->startDate; }
	public function getEndDate(){ return $this->endDate; }

	public function getDuration(){
		$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $this->startDate);
		$endDate =  DateTime::createFromFormat('Y-m-d H:i:s', $this->endDate);
		$diff = $endDate->diff($startDate);
		return $diff->h  + ($diff->days * 24) + ($diff->i / 60);
	}


	public function getLastModificationDate(){ return $this->lastModificationDate; }
	public function getIdMembers(){	return $this->idMembers; }
	public function haveNoMemberAttribuated(){	return count($this->idMembers) == 0; }
	public function getIdParticipants(){	return $this->idParticipants; }
	public function getNbIdParticipants(){	return count($this->idParticipants); }
	public function getOptions(){	return $this->options; }
	public function getMax(){ return $this->max; }
	public function getOneByBooking(){ return $this->oneByBooking; }
	public function getIdBooking(){ return $this->idBooking; }

	public function participantIsOk($participant){
		$ok = false;
		if(count($this->options)) $ok = true;
		foreach($this->options as $option){
			$tokens = explode("=", $option);
			if(count($tokens) == 2){
				$ok = $ok && isset($participant->{$tokens[0]}) && strtolower($participant->{$tokens[0]}) == strtolower($tokens[1]);
			}
			else $ok = false;
		}
		return $ok;
	}
}
