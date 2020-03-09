<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ReductionService extends RESA_EntityDTO
{
	private $id;
	private $idReduction;
	private $idService;
	private $idPrice;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_reduction_service';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idReduction` int(11) NOT NULL,
		  `idService` int(11) NOT NULL,
		  `idPrice` int(11) NOT NULL,
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
			$entity = new RESA_ReductionService();
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
		$this->idReduction = -1;
		$this->idService =  -1;
		$this->idPrice = -1;
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
				'idReduction' => $this->idReduction,
				'idService' => $this->idService,
				'idPrice' => $this->idPrice
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
			),
			array (
			'%d'));

		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idReduction' => $this->idReduction,
				'idService' => $this->idService,
				'idPrice' => $this->idPrice
			),
			array (
				'%d',
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
	public function toJSON()
	{
		return '{
			"id":'. $this->id .',
			"idReduction":'. $this->idReduction .',
			"idService":'.$this->idService.',
			"idPrice":'.$this->idPrice.'
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idReduction = $json->idReduction;
		$this->idService = $json->idService;
		$this->idPrice = $json->idPrice;
		if($this->id != -1)	$this->setLoaded(true);
	}

	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}

	public function getId(){ return $this->id; }
	public function isNew(){ return $this->id == -1; }
	public function getIdReduction(){ return $this->idReduction; }
	public function getIdService(){ return $this->idService; }
	public function getIdPrice(){ return $this->idPrice; }

	public function setIdReduction($idReduction){ $this->idReduction = $idReduction; }
	public function setIdService($idService){ $this->idService = $idService; }
	public function setIdPrice($idPrice){ $this->idPrice = $idPrice; }

}
