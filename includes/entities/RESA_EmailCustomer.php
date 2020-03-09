<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_EmailCustomer extends RESA_EntityDTO
{
	private $id;
	private $idCustomer;
	private $idBooking;
	private $idSender;
	private $externalId;
	private $mailbox;
	private $emailSender;
	private $creationDate;
	private $subject;
	private $message;
	private $attachments;
	private $seen;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_email_customer';
	}

	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idCustomer` int(11) NOT NULL,
		  `idBooking` int(11) NOT NULL,
		  `idSender` int(11) NOT NULL,
		  `externalId` int(11) NOT NULL,
		  `mailbox` TEXT NOT NULL,
		  `emailSender` TEXT NOT NULL,
		  `creationDate` datetime NOT NULL,
		  `subject` TEXT NOT NULL,
		  `message` TEXT NOT NULL,
		  `attachments` TEXT NOT NULL,
		  `seen` tinyint(1) NOT NULL,
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
	 * Return all entities of this type
	 */
	public static function getAllData($data = array())
	{
		$WHERE = RESA_Tools::generateWhereClause($data);
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id DESC');
		foreach($results as $result){
			$entity = new RESA_EmailCustomer();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return the max of externalId
	 */
	public static function getMaxExternalId($mailbox){
		global $wpdb;
		$max = $wpdb->get_var('SELECT MAX(externalId) FROM '. self::getTableName().' WHERE mailbox=\''.$mailbox.'\'');
		if(!isset($max)) $max = 0;
		return $max;
	}

	/**
	 * Return the max of externalId
	 */
	public static function getMaxDateForExternalId($mailbox){
		global $wpdb;
		$max = $wpdb->get_var('SELECT MAX(creationDate) FROM '. self::getTableName().' WHERE externalId > -1 AND mailbox=\''.$mailbox.'\'');
		if(!isset($max)) $max = 0;
		return $max;
	}

	/**
	 *
	 */
	public static function generate($idCustomer, $idBooking, $idSender, $emailSender, $subject, $message, $seen = false){
		$emailCustomer = new RESA_EmailCustomer();
		$emailCustomer->setIdCustomer($idCustomer);
		$emailCustomer->setIdBooking($idBooking);
		$emailCustomer->setIdSender($idSender);
		$emailCustomer->setEmailSender($emailSender);
		$emailCustomer->setSubject(esc_html($subject));
		$emailCustomer->setMessage(esc_html($message));
		$emailCustomer->setSeen($seen);
		return $emailCustomer;
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
		$this->idSender = -1;
		$this->externalId = -1;
		$this->mailbox = '';
		$this->emailSender = '';
		$this->creationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->subject = '';
		$this->message = '';
		$this->attachments = array();
		$this->seen = false;
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
				'idSender' => $this->idSender,
				'externalId' => $this->externalId,
				'mailbox' => $this->mailbox,
				'emailSender' => $this->emailSender,
				'creationDate' => $this->creationDate,
				'subject' => $this->subject,
				'message' => $this->message,
				'attachments' => serialize($this->attachments),
				'seen' => $this->seen
			),
			array('id'=>$this->id),
			array (
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
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
				'idCustomer' => $this->idCustomer,
				'idBooking' => $this->idBooking,
				'idSender' => $this->idSender,
				'externalId' => $this->externalId,
				'mailbox' => $this->mailbox,
				'emailSender' => $this->emailSender,
				'creationDate' => $this->creationDate,
				'subject' => $this->subject,
				'message' => $this->message,
				'attachments' => serialize($this->attachments),
				'seen' => $this->seen
			),
			array (
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
				'%s',
				'%d'
			));
			if($this->linkWPDB->insert_id != false){
				$this->setLoaded(true);
				$this->id = $this->linkWPDB->insert_id;
			}
				/*else {
				$this->linkWPDB->show_errors();
				$test = '';
				$this->linkWPDB->print_error($test);
				Logger::DEBUG(print_r($test,true));
			}*/

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
		$subject = preg_replace('/\r\n|\r|\n|\t/', ' ', $this->subject);
		$message = preg_replace('/\r\n|\r|\n|\t/', ' ', $this->message);

		$name = '';
		if($this->idSender != -1 && $this->idSender != $this->idCustomer){
			$name = get_userdata($this->idSender)->user_nicename;
		}

		return '{
			"id":'.$this->id .',
			"idCustomer":'.$this->idCustomer .',
			"idBooking":'.$this->idBooking .',
			"idSender":'.$this->idSender .',
			"name":"'.$name .'",
			"externalId":'.$this->externalId .',
			"mailbox":"'.$this->mailbox .'",
			"emailSender":"'.$this->emailSender .'",
			"creationDate":"'.$this->creationDate .'",
			"subject":"'. $subject .'",
			"message":"'. $message .'",
			"attachments":'.json_encode($this->attachments).',
			"seen":'.RESA_Tools::toJSONBoolean($this->seen).'
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
		$this->idSender = $json->idSender;
		$this->externalId = $json->externalId;
		$this->mailbox = $json->mailbox;
		$this->emailSender = $json->emailSender;
		$this->creationDate = $json->creationDate;
		$this->subject = $json->subject;
		$this->message = $json->message;
		if(isset($json->attachments)) $this->attachments = unserialize($json->attachments);
		$this->seen = $json->seen;
		if($this->id != -1)	$this->setLoaded(true);
	}

	/*********** GETTER and SETTER **************/
	public function setIdCustomer($idCustomer){ $this->idCustomer = $idCustomer; }
	public function setIdBooking($idBooking){ $this->idBooking = $idBooking; }
	public function setIdSender($idSender){ $this->idSender = $idSender; }
	public function setExternalId($externalId){ $this->externalId = $externalId; }
	public function setMailbox($mailbox){ $this->mailbox = $mailbox; }
	public function setEmailSender($emailSender){ $this->emailSender = $emailSender; }
	public function setCreationDate($creationDate){ $this->creationDate = $creationDate; }
	public function setSubject($subject){ $this->subject = $subject; }
	public function setMessage($message){ $this->message = $message; }
	public function setAttachments($attachments){ $this->attachments = $attachments; }
	public function setSeen($seen){ $this->seen = $seen; }

	public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function getIdCustomer(){ return $this->idCustomer; }
	public function getIdBooking(){ return $this->idBooking; }
	public function getIdSender(){ return $this->idSender; }
	public function getExternalId(){ return $this->externalId; }
	public function getMailbox(){ return $this->mailbox; }
	public function getEmailSender(){ return $this->emailSender; }
	public function getCreationDate(){ return $this->creationDate; }
	public function getSubject(){ return $this->subject; }
	public function getMessage(){ return $this->message; }
	public function getAttachments(){ return $this->attachments; }
	public function isSeen(){ return $this->seen; }

}
