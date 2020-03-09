<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Payment extends RESA_EntityDTO
{
	private $id;
	private $idCustomer;
	private $idBooking;
	private $paymentDate;
	private $type;
	private $value;
	private $repayment;
	private $note;
	private $state;
	private $idReference;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_payment';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idCustomer` int(11) NULL,
		  `idBooking` int(11) NULL,
		  `paymentDate` datetime NOT NULL,
		  `type` TEXT NOT NULL,
		  `value` float NOT NULL,
		  `repayment` tinyint(1) NOT NULL,
		  `note` TEXT NULL,
		  `state` enum(\'ok\',\'cancelled\',\'pending\') NOT NULL,
			`idReference` TEXT NULL,
		  PRIMARY KEY (`id`),
		  KEY `idBooking` (`idBooking`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return 'ALTER TABLE `'.self::getTableName().'` ADD CONSTRAINT `'.self::getTableName().'_IdBooking` FOREIGN KEY (`idBooking`) REFERENCES `'.RESA_Booking::getTableName().'` (`id`) ON DELETE CASCADE;';
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
			$entity = new RESA_Payment();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * delete in database
	 */
	public static function deleteByIdReference($idReference)
	{
		global $wpdb;
		$wpdb->delete(self::getTableName(), array('idReference'=>$idReference), array ('%s'));
	}

	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->id = -1;
		$this->idCustomer = -1;
		$this->idBooking = -1;
		$this->paymentDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->type = 'cash';
		$this->value = 0;
		$this->repayment = false;
		$this->note = '';
		$this->state = 'ok';
		$this->idReference = '';
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
				'idCustomer' => $this->idCustomer,
				'idBooking' => $this->idBooking,
				'paymentDate' => $this->paymentDate,
				'type' => $this->type,
				'value' => $this->value,
				'repayment' => $this->repayment,
				'note' => $this->note,
				'state' => $this->state,
				'idReference' => $this->idReference
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%s',
				'%s',
				'%f',
				'%d',
				'%s',
				'%s',
				'%s'
			),
			array ('%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'id' => '',
				'idCustomer' => $this->idCustomer,
				'idBooking' => $this->idBooking,
				'paymentDate' => $this->paymentDate,
				'type' => $this->type,
				'value' => $this->value,
				'repayment' => $this->repayment,
				'note' => $this->note,
				'state' => $this->state,
				'idReference' => $this->idReference
			),
			array (
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%f',
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
	 * delete in database
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
		$idBooking = -1;
		if(isset($this->idBooking)){
			$idBooking = $this->idBooking;
		}
		$idCustomer = -1;
		if(isset($this->idCustomer)){
			$idCustomer = $this->idCustomer;
		}
		return '{
			"id":'.$this->id .',
			"idCustomer":'. $idCustomer .',
			"idBooking":'. $idBooking .',
			"paymentDate":"'.$this->paymentDate .'",
			"type":"'.$this->type .'",
			"value":'.$this->value .',
			"repayment":'.RESA_Tools::toJSONBoolean($this->repayment) .',
			"note":"'.$this->note.'",
			"state":"'.$this->state.'",
			"isCancellable":'.RESA_Tools::toJSONBoolean($this->isCancellable()) .',
			"idReference":"'.$this->idReference.'"
		}';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->id = $json->id;
		$this->idCustomer = $json->idCustomer;
		$this->idBooking = $json->idBooking;
		$this->paymentDate = $json->paymentDate;
		$this->type = $json->type;
		$this->value = $json->value;
		$this->repayment = $json->repayment;
		$this->note = str_replace(array("\n", "\r"), '', nl2br(esc_html($json->note)));
		if(isset($json->state)) $this->state = $json->state;
		if(isset($json->idReference)) $this->idReference = $json->idReference;
		if($this->id != -1)	$this->setLoaded(true);
	}


	/*********** GETTER and SETTER **************/
	public function setNew(){
		$this->id = -1;
		$this->setLoaded(false);
	}
	public function setIdCustomer($idCustomer){ $this->idCustomer = $idCustomer; }
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setPaymentDate($paymentDate){ $this->paymentDate = $paymentDate; }
	public function setTypePayment($type){ $this->type = $type; }
	public function setValue($value){ $this->value = $value; }
	public function setRepayment($repayment){ $this->repayment = $repayment; }
	public function setNote($note){ $this->note = $note; }
	public function setIdReference($idReference){ $this->idReference = $idReference; }
	public function cancel(){ $this->state = 'cancelled'; }
	public function ok(){ $this->state = 'ok'; }
	public function pending(){ $this->state = 'pending'; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdCustomer(){ return $this->idCustomer; }
	public function getIdBooking(){ return $this->idBooking; }
	public function haveBooking(){ return isset($this->idBooking) && $this->idBooking != -1; }
	public function getPaymentDate(){ return $this->paymentDate; }
	public function getTypePayment(){ return $this->type; }
	public function getValue(){ return $this->value; }
	public function isRepayment(){ return $this->repayment; }
	public function getNote(){ return $this->note; }
	public function getIdReference(){ return $this->idReference; }
	public function isOk(){ return $this->state == 'ok'; }
	public function isCancelled(){ return $this->state == 'cancelled'; }
	public function isPending(){ return $this->state == 'pending'; }
	public function isCancellable(){
		return $this->isOk() && $this->type != 'systempay' && $this->type != 'monetico'  && $this->type != 'stripe' && $this->type != 'stripeConnect' && $this->type != 'paypal'  && $this->type != 'paybox';
	}
	public function getUnixDate(){ return strtotime($this->paymentDate); }
}
