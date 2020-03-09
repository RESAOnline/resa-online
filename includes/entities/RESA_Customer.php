<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_Customer extends RESA_EntityDTO
{
	public static $ACTION_AUTOCONNECT = 'resa-autoconnect';

	const LOCALE_META_KEY = 'locale';
	const COMPANY_META_KEY = 'company';
	const COMPANY_ACCOUNT_META_KEY = 'companyAccount';
	const PHONE_META_KEY = 'phone';
	const PHONE2_META_KEY = 'phone2';
	const PRIVATE_NOTE_META_KEY = 'privateNotes';
	const ADDRESS_META_KEY = 'address';
	const POSTAL_CODE_META_KEY = 'postalCode';
	const TOWN_META_KEY = 'town';
	const COUNTRY_META_KEY = 'country';
	const SIRET_META_KEY = 'siret';
	const LEGAL_FORM_META_KEY = 'legalForm';
	const TOKEN_META_KEY = 'token';
	const TOKEN_VALIDATION_META_KEY = 'tokenValidation';
	const TYPE_ACCOUNT = 'typeAccount';
	const ID_CAISSE_ONLINE = 'idCaisseOnline';
	const ID_STRIPE = 'idStripe';
	const SEE_SETTINGS_META_KEY = 'seeSettings';
	const LAST_RESA_NEWS_ID = 'lastRESANewsID';
	const FILTER_SETTINGS = 'filterSettings';
	const PARTICIPANTS = 'participants';
	const MODIFICATION_DATE = 'modificationDate';
	const SEND_REQUEST_FOR_OPINION = 'sendRequestForOpinion';
	const DEACTIVATE_EMAIL = 'deactivateEmail';
	const REGISTER_NEWSLETTERS = 'registerNewsletters';
	const URI_CUSTOMER = 'uri';
	const ID_FACEBOOK = 'idFacebook';

	private $ID;
	private $login;
	private $password;
	private $userNicename;
	private $displayName;
	private $firstName;
	private $lastName;
	private $email;
	private $locale;
	private $company;
	private $companyAccount;
	private $phone;
	private $phone2;
	private $address;
	private $postalCode;
	private $town;
	private $country;
	private $siret;
	private $legalForm;
	private $token;
	private $tokenValidation;
	private $typeAccount;
	private $role;
	private $privateNotes;
	private $registerDate;
	private $modificationDate;
	private $participants;
	private $wpUser;
	private $sendRequestForOpinion;
	private $askAccount;
	private $deactivateEmail;
	private $uri;
	private $idCaisseOnline;
	private $idStripe;
	private $idFacebook;
	private $registerNewsletters;
	private $filterSettings;
	private $bookings;
	private $payments;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_customer';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `ID` int(11) NOT NULL AUTO_INCREMENT,
		  `login` text NOT NULL,
		  `password` text NOT NULL,
		  `userNicename` text NOT NULL,
		  `displayName` text NOT NULL,
		  `firstName` text NOT NULL,
		  `lastName` text NOT NULL,
		  `email` text NOT NULL,
		  `locale` text NOT NULL,
		  `company` text NOT NULL,
		  `companyAccount` tinyint(1) NOT NULL,
		  `phone` text NOT NULL,
		  `phone2` text NOT NULL,
		  `address` text NOT NULL,
		  `postalCode` text NOT NULL,
		  `town` text NOT NULL,
		  `country` text NOT NULL,
		  `siret` text NOT NULL,
		  `legalForm` text NOT NULL,
		  `token` text NOT NULL,
		  `tokenValidation` datetime NOT NULL,
		  `typeAccount` text NOT NULL,
		  `role` text NOT NULL,
		  `privateNotes` text NOT NULL,
		  `registerDate` datetime NOT NULL,
		  `modificationDate` datetime NOT NULL,
		  `participants` text NOT NULL,
		  `sendRequestForOpinion` text NOT NULL,
		  `askAccount` tinyint(1) NOT NULL,
		  `deactivateEmail` tinyint(1) NOT NULL,
		  `registerNewsletters` tinyint(1) NULL,
		  `idCaisseOnline` text NOT NULL,
		  `idStripe` text NOT NULL,
		  `idFacebook` text NOT NULL,
		  `uri` text NOT NULL,
		  PRIMARY KEY (`ID`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	}

	public static function getConstraints()
	{
		return '';
	}


	public static function initializeAutoIncrement(){
		global $wpdb;
		$maxID = 0;
		$results = $wpdb->get_var('SELECT MAX(id) FROM '. self::getTableName());
		if(isset($results)) $maxID = $results;
		$results = $wpdb->get_var('SELECT MAX(ID) FROM '. $wpdb->prefix. 'users');
		if(isset($results)) $maxID = max($maxID, $results);
		self::alterAutoIncrement($maxID + 1);
	}

	/**
	 * Auto increment table
	 */
	public static function alterAutoIncrement($number){
		global $wpdb;
		LOGGER::DEBUG('alterAutoIncrement - ID : ' . $number. ' ' . $wpdb->prefix);
		$wpdb->query('alter table ' .self::getTableName(). ' AUTO_INCREMENT='.$number);
		$wpdb->query('alter table ' .$wpdb->prefix. 'users AUTO_INCREMENT='.$number);
	}

	/**
	 * return the number of all data filtered
	 */
	public static function getMaxID($data = array()){
		global $wpdb;
		$countRESACustomer = $wpdb->get_var('SELECT MAX(ID) FROM '. self::getTableName() );
		if(!isset($countRESACustomer)) $countRESACustomer = 0;
		$countWpUsers = $wpdb->get_var('SELECT MAX(ID) FROM ' .$wpdb->prefix. 'users');
		if(!isset($countWpUsers)) $countWpUsers = 0;
		return max($countRESACustomer, $countWpUsers);
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
	public static function getAllDataMoreThanModificationDate($modificationDate)
	{
		$allData = array();
		global $wpdb;

		$user_query = new WP_User_Query( array( 'meta_key' => 'modificationDate', 'meta_value' => $modificationDate, 'meta_compare' => '>' ) );
		$results = $user_query->get_results();
		foreach($results as $result){
			$entity = new RESA_Customer();
			$result = get_user_by('ID', $result->ID);
			if(isset($result) && !is_bool($result)){
				$entity->fromDB($result);
				$entity->setLoaded(true);
				$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE modificationDate>\''.$modificationDate.'\' ORDER BY ID');
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllData($data = array(), $allUsers = false, $justNotWpUser = false)
	{
		$allData = array();
		global $wpdb;
		if(!$justNotWpUser){
			if(!$allUsers){
				$results = get_users('role='.RESA_Variables::getCustomerRole());
			}
			else {
				$results = get_users();
			}
			//$results = get_users();
			foreach($results as $result){
				$entity = new RESA_Customer();
				$entity->fromDB($result);
				$entity->setLoaded(true);
				$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		$WHERE = RESA_Tools::generateWhereClause($data);
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function searchAllCustomersWithLimit($search, $page, $limit){
		$search = htmlspecialchars($search, ENT_QUOTES);
		$allData = array();
		$formatSearch = '.*';
		if(!empty($search)){
			$formatSearch = '.*' . $search . '.*';
			$formatSearch = str_replace(' ', '.*', $formatSearch);
		}

		$begin = $page * $limit;
		global $wpdb;

		$results = $wpdb->get_results('(SELECT m1.user_id as ID, CONVERT(m1.meta_value, CHAR) as uri FROM '.$wpdb->prefix.'usermeta as m1 WHERE m1.meta_key = \'uri\' AND m1.meta_value REGEXP \''.$formatSearch.'\')
		UNION ALL
		(SELECT '.RESA_Customer::getTableName().'.ID, CONVERT('.RESA_Customer::getTableName().'.uri, CHAR) FROM '.RESA_Customer::getTableName().'
		WHERE '.RESA_Customer::getTableName().'.lastName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.firstName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.company REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.email REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.phone REGEXP \''.$formatSearch.'\'
		 OR '.RESA_Customer::getTableName().'.phone2 REGEXP \''.$formatSearch.'\')
		ORDER BY uri LIMIT '.$begin.','.$limit.'');

		/*
		$results = $wpdb->get_results('(SELECT m1.user_id as ID, m1.meta_value as lastName, m2.meta_value as firstName, m3.meta_value as company, '.$wpdb->prefix.'users.user_email as email FROM '.$wpdb->prefix.'usermeta as m1
		INNER JOIN '.$wpdb->prefix.'usermeta as m2 ON m1.user_id = m2.user_id AND m2.meta_key = \'first_name\' AND m1.meta_key = \'last_name\'
		INNER JOIN '.$wpdb->prefix.'usermeta as m3 ON m1.user_id = m3.user_id AND m3.meta_key = \'company\' AND m1.meta_key = \'last_name\'
		INNER JOIN '.$wpdb->prefix.'users ON '.$wpdb->prefix.'users.ID = m1.user_id
		WHERE m1.meta_value REGEXP \''.$formatSearch.'\' OR m2.meta_value REGEXP \''.$formatSearch.'\' OR m3.meta_value REGEXP \''.$formatSearch.'\' OR '.$wpdb->prefix.'users.user_email REGEXP \''.$formatSearch.'\')
		UNION ALL
		(SELECT '.RESA_Customer::getTableName().'.ID, '.RESA_Customer::getTableName().'.lastName, '.RESA_Customer::getTableName().'.firstName, '.RESA_Customer::getTableName().'.company, '.RESA_Customer::getTableName().'.email FROM '.RESA_Customer::getTableName().'
		WHERE '.RESA_Customer::getTableName().'.lastName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.firstName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.company REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.email REGEXP \''.$formatSearch.'\')
		ORDER BY lastName, firstName LIMIT '.$begin.','.$limit.'');
		*/
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->loadByIdWithoutBookings($result->ID);
			$entity->setLoaded(true);
			$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllCustomersNotCaisseOnlineSynchronized(){
		$allData = array();
		global $wpdb;
		$results = $wpdb->get_results('(SELECT m1.user_id as ID FROM '.$wpdb->prefix.'usermeta as m1 WHERE m1.meta_key = \'idCaisseOnline\' AND m1.meta_value = \'\' GROUP BY ID)
		UNION ALL
		(SELECT '.RESA_Customer::getTableName().'.ID FROM '.RESA_Customer::getTableName().' WHERE '.RESA_Customer::getTableName().'.idCaisseOnline = \'\')');
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->loadByIdWithoutBookings($result->ID);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return the number of all customers
	 */
	public static function countAllCustomers($search){
		$search = htmlspecialchars($search, ENT_QUOTES);
		global $wpdb;
		$formatSearch = '.*';
		if(!empty($search)){
			$formatSearch = '.*' . $search . '.*';
		}

		$count = $wpdb->get_var('SELECT count(tem.ID) FROM ((SELECT m1.user_id as ID FROM '.$wpdb->prefix.'usermeta as m1 WHERE m1.meta_key = \'uri\' AND m1.meta_value REGEXP \''.$formatSearch.'\')
		UNION ALL
		(SELECT '.RESA_Customer::getTableName().'.ID FROM '.RESA_Customer::getTableName().' WHERE '.RESA_Customer::getTableName().'.lastName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.firstName REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.company REGEXP \''.$formatSearch.'\' OR '.RESA_Customer::getTableName().'.email REGEXP \''.$formatSearch.'\')) as tem');

		if(!isset($count)) $count = 0;
		return $count;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithLimit($data = array(), $allUsers = false, $isWpUser, $offset, $limit)
	{
		$allData = array();
		global $wpdb;
		if($isWpUser){
			if(!$allUsers){
				$results = get_users(array('role' => RESA_Variables::getCustomerRole(), 'offset'=>$offset, 'number' => $limit));
			}
			else {
				$results = get_users(array('offset'=>$offset, 'number' => $limit));
			}
			//$results = get_users();
			foreach($results as $result){
				$entity = new RESA_Customer();
				$entity->fromDB($result);
				$entity->setLoaded(true);
				$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		else {
			$WHERE = RESA_Tools::generateWhereClause($data);
			$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id LIMIT '.$offset.','.$limit);
			foreach($results as $result){
				$entity = new RESA_Customer();
				$entity->fromJSON($result);
				$entity->setLoaded(true);
				$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
				array_push($allData, $entity);
			}
		}
		return $allData;
	}

	/**
	 * Return all entities of resa member
	 */
	public static function getAllDataStaffUsers(){
		$allData = array();
		global $wpdb;
		$results = get_users('role='.RESA_Variables::getStaffRole());
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromDB($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}


	/**
	 * Return all entities of resa managers et administrator
	 */
	public static function getAllDataManagers(){
		$allData = array();
		global $wpdb;
		$results = get_users('role=administrator');
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromDB($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		$results = get_users('role='. RESA_Variables::getManagerRole());
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromDB($result);
			$entity->setLoaded(true);
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllDataWithBookings($data = array(), $allUsers = false)
	{
		$allData = array();
		global $wpdb;
		if(!$allUsers){
			$results = get_users('role='.RESA_Variables::getCustomerRole());
		}
		else {
			$results = get_users();
		}
		//$results = get_users();
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromDB($result);
			$entity->setLoaded(true);
			$entity->setBookings(RESA_Booking::getAllData(array('idCustomer'=>$entity->getId(), 'oldBooking'=> false)));
			$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
			array_push($allData, $entity);
		}
		$WHERE = RESA_Tools::generateWhereClause($data);
		$results = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' '.$WHERE .' ORDER BY id');
		foreach($results as $result){
			$entity = new RESA_Customer();
			$entity->fromJSON($result);
			$entity->setLoaded(true);
			$entity->setBookings(RESA_Booking::getAllData(array('idCustomer'=>$entity->getId(), 'oldBooking'=> false)));
			$entity->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$entity->getId())));
			array_push($allData, $entity);
		}
		return $allData;
	}

	/**
	 *
	 */
	public static function phoneAlreadyExist($phone, $ID = -1){
		global $wpdb;
		if(empty($phone)) return false;
		$users = get_users(array('meta_query' => array(
	      array('key' => self::PHONE_META_KEY,
          'value' => $phone,
          'compare' => '=='
	      ),
				array('key' => 'ID',
	       'value' => $ID,
	        'compare' => '!='
	      )
	    )
    ));
		if(count($users) == 0){
			$users = $wpdb->get_results('SELECT * FROM '. self::getTableName() . ' WHERE '.self::PHONE_META_KEY.'=\''.$phone.'\' AND ID!='.$ID.' ORDER BY id');
		}
		return (count($users) > 0);
	}

	public static function isOkPasswordFormat($password){
		return preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/", $password);
	}

	public static function isPaymentTypeAuthorized($customerTypeAccount, $paymentType, $typesAccounts){
		foreach($typesAccounts as $typeAccount){
			if($typeAccount->id == $customerTypeAccount){
				if(!property_exists($typeAccount,'paymentsTypeList')) return true;
				else {
					return $typeAccount->paymentsTypeList->{$paymentType};
				}
			}
		}
		return true;
	}


	/**
	 * authenticate the user
	 */
	public static function authenticate($login, $password) {
		$return = wp_authenticate($login, $password);
		if(!is_wp_error($return)){
			wp_set_current_user($return->ID, $return->login);
			wp_set_auth_cookie($return->ID);
			do_action('wp_login', $return->login);
			$currentUser = new RESA_Customer();
			$currentUser->loadCurrentUser();
			return $currentUser;
		}
		return new RESA_Customer();
	}




	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
		$this->ID = -1;
		$this->login = '';
		$this->password = '';
		$this->userNicename = '';
		$this->displayName= '';
		$this->firstName= '';
		$this->lastName= '';
		$this->company = '';
		$this->companyAccount = false;
		$this->email = '';
		$this->locale = get_locale();
		$this->phone = '';
		$this->phone2 = '';
		$this->address = '';
		$this->postalCode = '';
		$this->town = '';
		$this->country = '';
		$this->siret = '';
		$this->legalForm = '';
		$this->typeAccount = 'type_account_0';
		$this->role = RESA_Variables::getCustomerRole();
		$this->token = '';
		$this->tokenValidation = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->privateNotes = '';
		$this->registerDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp'));
		$this->participants = array();
		$this->wpUser = false;
		$this->sendRequestForOpinion = 'yes';
		$this->askAccount = false;
		$this->deactivateEmail = false;
		$this->registerNewsletters = false;
		$this->idCaisseOnline = '';
		$this->idStripe = '';
		$this->idFacebook = '';
		$this->uri = '';
		$this->bookings = array();
		$this->payments = array();
		$this->seeSettings = false;
		$this->lastRESANewsID = -1;
		$this->filterSettings = (object)array();
	}

	/**
	 * authenticate the user
	 */
	public function autoAuthenticate() {
		wp_set_current_user($this->ID, $this->login);
		wp_set_auth_cookie($this->ID);
	}

	/**
	 * Load form database by id
	 */
	public function loadById($id, $loadAllBooking = false)
	{
		if(isset($id)){
			$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE ID = \'' . $id . '\'');
			if(isset($result)){
				$this->fromJSON($result);
				$this->setLoaded(true);
			}
			else {
				$result = get_user_by('ID', $id);
				if(isset($result) && !is_bool($result)){
					$this->fromDB($result);
					$this->setLoaded(true);
				}
			}
			if($this->isLoaded()){
				$WHERE = array('idCustomer'=>$this->getId(), 'oldBooking'=> false);
				if($loadAllBooking){
					$WHERE =  array('idCustomer'=>$this->getId());
				}
				$this->setBookings(RESA_Booking::getAllData($WHERE));
				$this->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$this->getId())));
			}
		}
	}

	/**
	 * Load form database by id
	 */
	public function loadByIdWithoutBookings($id)
	{
		if(isset($id)){
			$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE ID = \'' . $id . '\'');
			if(isset($result)){
				$this->fromJSON($result);
				$this->setLoaded(true);
			}
			else {
				$result = get_user_by('ID', $id);
				if(isset($result) && !is_bool($result)){
					$this->fromDB($result);
					$this->setLoaded(true);
				}
			}
		}
	}

	/**
	 * Load by login and password
	 */
	public function loadByLoginPassword($login, $password)
	{
		$result = get_user_by('login', $login);
		if ($result && ($password == $result->user_pass || wp_check_password($password, $result->user_pass, $result->ID))){
			$this->fromDB($result);
			$this->setBookings(RESA_Booking::getAllData(array('idCustomer'=>$this->getId(), 'oldBooking'=> false)));
			$this->setLoaded(true);
		}
		else {
			$this->setLoaded(false);
		}
	}

	/**
	 * Load form database
	 */
	public function loadCurrentUser($loadBookings = true)
	{
		$result = wp_get_current_user();
		if(0 != $result->ID){
			$this->fromDB($result);
			if($loadBookings){
				$this->setBookings(RESA_Booking::getAllData(array('idCustomer'=>$this->getId(), 'oldBooking'=> false)));
			}
			$this->setLoaded(true);
		}
		else $this->setLoaded(false);
	}

	public function loadUserByToken($token)
	{
		$user_query = new WP_User_Query( array( 'meta_key' => self::TOKEN_META_KEY, 'meta_value' => $token ) );
		if ( ! empty( $user_query->results ) ) {
			$result = $user_query->results[0];
			if(0 != $result->ID){
				$this->fromDB($result);
				$actualDate = new DateTime(date('Y-m-d H:i:s', current_time('timestamp')));
				if($this->tokenValidation >= $actualDate){
					$this->setBookings(RESA_Booking::getAllData(array('idCustomer'=>$this->getId(), 'oldBooking'=> false)));
					$this->setLoaded(true);
				}else {
					Logger::ERROR('Token of '.$this->login.' has expired');
					$this->setLoaded(false);
				}
			}
		} else {
			Logger::ERROR('Token: ' . $token .' - No users found.');
			$this->setLoaded(false);
		}
	}

	/**
	 * Load by email
	 */
	public function loadByEmail($email, $loadAllBooking = false)
	{
		if(isset($email)){
			$result = $this->linkWPDB->get_row('SELECT * FROM ' . self::getTableName() . ' WHERE email = \'' . $email . '\'');
			if(isset($result)){
				$this->fromJSON($result);
				$this->setLoaded(true);
			}
			else {
				$result = get_user_by('email', $email);
				if(isset($result) && !is_bool($result)){
					$this->fromDB($result);
					$this->setLoaded(true);
				}
			}
			if($this->isLoaded()){
				$WHERE = array('idCustomer'=>$this->getId(), 'oldBooking'=> false);
				if($loadAllBooking){
					$WHERE =  array('idCustomer'=>$this->getId());
				}
				$this->setBookings(RESA_Booking::getAllData($WHERE));
				$this->setPayments(RESA_Payment::getAllData(array('idCustomer'=>$this->getId())));
			}
		}
	}


	public function create($login, $password, $email, $notify = false)
	{
		if(!username_exists($login) && !email_exists($email)){
			if(!isset($password) || empty($password)) {
				$password = wp_generate_password(12, false);
			}
			$userID = wp_create_user($login, $password, $email);
			if(!is_wp_error($userID)){
				self::initializeAutoIncrement();
				wp_new_user_notification($userID, null, 'admin');
				$this->loadById($userID);
				$this->role = RESA_Variables::getCustomerRole();
			}
			else {
				throw new Exception($userID->get_error_message());
			}
		}
	}


	/**
	 * Set the password directly in database
	 */
	public function setPasswordInDB($password)
	{
		if($this->wpUser){
			global $wpdb;
			$table = $wpdb->prefix.'users';
			$this->linkWPDB->update($table, array(
				'user_pass' => $password),
				array('ID'=>$this->ID),
				array ('%s'),
				array ('%d'));
		}
		else {
			$this->linkWPDB->update(self::getTableName(), array(
				'password' => $password),
				array('ID'=>$this->ID),
				array ('%s'),
				array ('%d'));
		}
		$this->password = $password;
	}

	/**
	 * Create wp_user account with resa_customer data
	 */
	public function createWpUserWithCustomer($login, $password, $email, $notify = false){
		if(!$this->isWpUser()){
			$oldID = $this->ID;
			$this->create($login, $password, $email, $notify);
			$newID = $this->ID;
			if($oldID != $newID){
				$oldCustomer = new RESA_Customer();
				$oldCustomer->loadById($oldID, true);
				$oldCustomer->migrateID($newID, true);
				$oldCustomer->deleteMe();
			}
		}
	}

	/**
	 * Save in database
	 */
	public function save($synchronize = true)
	{
		$this->formatPhone();
		$this->clearModificationDate();
		if(!$this->isWpUser()){
			$this->saveNotWpUser();
		}
		else {
			if(!$this->isLoaded())
			{
				$this->create($this->login, $this->password, $this->email);
			}
			//UPDATE
			if($this->isLoaded())
			{
				$this->generateURI();
				$userID = wp_update_user(array(
					'ID' => $this->ID,
					'user_email' => $this->email,
					'user_nicename' => $this->userNicename,
					'display_name' => $this->displayName,
					'first_name' => $this->firstName,
					'last_name' => strtoupper($this->lastName),
					'user_registered' => $this->registerDate,
					'role' => $this->role
				));
				if(is_wp_error($userID)){
					Logger::ERROR(print_r($userID->get_error_message(), true));
				}
				update_user_meta($this->ID, self::LOCALE_META_KEY, $this->locale);
				update_user_meta($this->ID, self::COMPANY_META_KEY, $this->company);
				update_user_meta($this->ID, self::COMPANY_ACCOUNT_META_KEY, $this->companyAccount);
				update_user_meta($this->ID, self::PHONE_META_KEY, $this->phone);
				update_user_meta($this->ID, self::PHONE2_META_KEY, $this->phone2);
				update_user_meta($this->ID, self::PRIVATE_NOTE_META_KEY, $this->privateNotes);
				update_user_meta($this->ID, self::ADDRESS_META_KEY, $this->address);
				update_user_meta($this->ID, self::POSTAL_CODE_META_KEY, $this->postalCode);
				update_user_meta($this->ID, self::TOWN_META_KEY, $this->town);
				update_user_meta($this->ID, self::COUNTRY_META_KEY, $this->country);
				update_user_meta($this->ID, self::SIRET_META_KEY, $this->siret);
				update_user_meta($this->ID, self::LEGAL_FORM_META_KEY, $this->legalForm);
				update_user_meta($this->ID, self::TOKEN_META_KEY, $this->token);
				update_user_meta($this->ID, self::TOKEN_VALIDATION_META_KEY, $this->tokenValidation);
				update_user_meta($this->ID, self::TYPE_ACCOUNT, $this->typeAccount);
				update_user_meta($this->ID, self::ID_CAISSE_ONLINE, $this->idCaisseOnline);
				update_user_meta($this->ID, self::ID_STRIPE, $this->idStripe);
				update_user_meta($this->ID, self::SEE_SETTINGS_META_KEY, $this->seeSettings);
				update_user_meta($this->ID, self::LAST_RESA_NEWS_ID, $this->lastRESANewsID);
				update_user_meta($this->ID, self::FILTER_SETTINGS, serialize($this->filterSettings));
				update_user_meta($this->ID, self::PARTICIPANTS, serialize($this->participants));
				update_user_meta($this->ID, self::MODIFICATION_DATE, $this->modificationDate);
				update_user_meta($this->ID, self::SEND_REQUEST_FOR_OPINION, $this->sendRequestForOpinion);
				update_user_meta($this->ID, self::DEACTIVATE_EMAIL, $this->deactivateEmail);
				update_user_meta($this->ID, self::REGISTER_NEWSLETTERS, $this->registerNewsletters);
				update_user_meta($this->ID, self::URI_CUSTOMER, $this->uri);
				update_user_meta($this->ID, self::ID_FACEBOOK, $this->idFacebook);
			}
		}
		if($synchronize && !$this->isAskAccount()){
			if(class_exists('RESA_CaisseOnline')){
				RESA_CaisseOnline::getInstance()->addCustomer($this);
			}
		}
	}

	/**
	 *
	 */
  public function saveNotWpUser(){
		$this->generateURI();
		if($this->isLoaded())
		{
			$this->linkWPDB->update(self::getTableName(), array(
				'login' => $this->login,
				'password' => $this->password,
				'userNicename' => $this->userNicename,
				'displayName' => $this->displayName,
				'firstName' => $this->firstName,
				'lastName' => strtoupper($this->lastName),
				'email' => $this->email,
				'locale' => $this->locale,
				'company' => $this->company,
				'companyAccount' => $this->companyAccount,
				'phone' => $this->phone,
				'phone2' => $this->phone2,
				'address' => $this->address,
				'postalCode' => $this->postalCode,
				'town' => $this->town,
				'country' => $this->country,
				'siret' => $this->siret,
				'legalForm' => $this->legalForm,
				'token' => $this->token,
				'tokenValidation' => $this->tokenValidation,
				'typeAccount' => $this->typeAccount,
				'role' => $this->role,
				'privateNotes' => $this->privateNotes,
				'registerDate' => $this->registerDate,
				'modificationDate' => $this->modificationDate,
				'participants' => serialize($this->participants),
				'sendRequestForOpinion' => $this->sendRequestForOpinion,
				'askAccount' => $this->askAccount,
				'deactivateEmail' => $this->deactivateEmail,
				'registerNewsletters' => $this->registerNewsletters,
				'uri' => $this->uri,
				'idCaisseOnline' => $this->idCaisseOnline,
				'idStripe' => $this->idStripe,
				'idFacebook' => $this->idFacebook,
			),
			array('ID'=>$this->ID),
			array (
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
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array (
			'%d'));
		}
		else
		{
			$this->linkWPDB->insert(self::getTableName(), array(
				'ID' => '',
				'login' => $this->login,
				'password' => $this->password,
				'userNicename' => $this->userNicename,
				'displayName' => $this->displayName,
				'firstName' => $this->firstName,
				'lastName' => strtoupper($this->lastName),
				'email' => $this->email,
				'locale' => $this->locale,
				'company' => $this->company,
				'companyAccount' => $this->companyAccount,
				'phone' => $this->phone,
				'phone2' => $this->phone2,
				'address' => $this->address,
				'postalCode' => $this->postalCode,
				'town' => $this->town,
				'country' => $this->country,
				'siret' => $this->siret,
				'legalForm' => $this->legalForm,
				'token' => $this->token,
				'tokenValidation' => $this->tokenValidation,
				'typeAccount' => $this->typeAccount,
				'role' => $this->role,
				'privateNotes' => $this->privateNotes,
				'modificationDate' => $this->modificationDate,
				'registerDate' => $this->registerDate,
				'participants' => serialize($this->participants),
				'sendRequestForOpinion' => $this->sendRequestForOpinion,
				'askAccount' => $this->askAccount,
				'deactivateEmail' => $this->deactivateEmail,
				'registerNewsletters' => $this->registerNewsletters,
				'uri' => $this->uri,
				'idCaisseOnline' => $this->idCaisseOnline,
				'idStripe' => $this->idStripe,
				'idFacebook' => $this->idFacebook
			),
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
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			));
			$this->ID = $this->linkWPDB->insert_id;
			self::initializeAutoIncrement();
			$this->setLoaded(true);
		}
  }

	/**
	 * Save in database
	 */
	public function deleteMe() {
		if($this->isLoaded()){
			if($this->wpUser){
				$result = wp_delete_user($this->ID);
				if($result){
					$this->linkWPDB->delete(RESA_LogNotification::getTableName(), array('idCustomer' => $this->ID), array ('%d'));
				}
			}
			else {
				$this->linkWPDB->delete(self::getTableName(), array('ID'=>$this->ID), array ('%d'));
				$this->linkWPDB->delete(RESA_LogNotification::getTableName(), array('idCustomer' => $this->ID), array ('%d'));
			}
		}
		return true;
	}

	/**
	 * format phone
	 */
	public function formatPhone(){
		$this->phone = preg_replace("/[^0-9]/", "", $this->phone);
		$this->phone2 = preg_replace("/[^0-9]/", "", $this->phone2);
	}

	public function toSimpleArray() {
		return array(
			'ID' => $this->ID,
			'company' => $this->company,
			'lastName' => $this->lastName,
			'firstName' => $this->firstName,
			'phone' => $this->phone
		);
	}

	/**
	 * Return this to JSON value
	 */
	public function toJSON($withPassword = false)
	{
		 $json = '{
			"ID":'.$this->ID .',
			"login":"'.$this->login .'",';
			if($withPassword){
				$json .= '"password":"'.$this->password .'",';
			}
			$json .= '"userNicename":"'.$this->userNicename .'",
			"displayName":"'.$this->displayName .'",
			"firstName":"'.ucfirst($this->firstName).'",
			"lastName":"'.ucfirst($this->lastName).'",
			"email":"'.$this->getEmail() .'",
			"locale":"'.$this->locale .'",
			"company":"'.$this->company .'",
			"role":"'.$this->role.'",
			"typeAccount":"'.$this->typeAccount.'",
			"companyAccount":'.RESA_Tools::toJSONBoolean($this->isCompanyAccount()).',
			"phone":"'.$this->phone .'",
			"phone2":"'.$this->phone2 .'",
			"address":"'.$this->address .'",
			"postalCode":"'.$this->postalCode .'",
			"town":"'.$this->town .'",
			"country":"'.$this->country .'",
			"siret":"'.$this->siret .'",
			"legalForm":"'.$this->legalForm .'",
			"privateNotes":"'. preg_replace('/\r\n|\r|\n|\t/', '', $this->privateNotes) .'",
			"registerDate":"'.$this->registerDate .'",
			"modificationDate":"'.$this->modificationDate.'",
			"participants":'.json_encode($this->participants).',
			"filterSettings":'.json_encode($this->filterSettings).',
			"isWpUser":'.RESA_Tools::toJSONBoolean($this->wpUser).',
			"seeSettings":'.RESA_Tools::toJSONBoolean($this->seeSettings).',
			"idCaisseOnline":"'.$this->idCaisseOnline.'",
			"idStripe":"'.$this->idStripe.'",
			"idFacebook":"'.$this->idFacebook.'",
			"sendRequestForOpinion":"'.$this->sendRequestForOpinion.'",
			"askAccount":'.RESA_Tools::toJSONBoolean($this->askAccount).',
			"deactivateEmail":'.RESA_Tools::toJSONBoolean($this->deactivateEmail).',
			"registerNewsletters":'.RESA_Tools::toJSONBoolean($this->registerNewsletters).',
			"bookings":'.RESA_Tools::formatJSONArray($this->bookings).',
			"payments":'.RESA_Tools::formatJSONArray($this->payments).'
		}';
		return $json;
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
		$this->ID = $json->ID;
		if(isset($json->login)) $this->login = esc_html($json->login);
		if(isset($json->password)) $this->password = $json->password;
		if(isset($json->userNicename)) $this->userNicename = esc_html($json->userNicename);
		if(isset($json->displayName)) $this->displayName = esc_html($json->displayName);
		$this->firstName = esc_html($json->firstName);
		$this->lastName = esc_html($json->lastName);
		$this->email = esc_html($json->email);
		if(isset($json->locale)) $this->locale = $json->locale;
		$this->company = esc_html($json->company);
		if(isset($json->typeAccount)) $this->typeAccount = esc_html($json->typeAccount);
		if(isset($json->companyAccount)) $this->companyAccount = esc_html($json->companyAccount);
		if($this->companyAccount) $this->typeAccount = 'type_account_1'; //Retrocompatibility
		$this->phone = esc_html($json->phone);
		if(isset($json->phone2)) $this->phone2 = esc_html($json->phone2);
		$this->address = esc_html($json->address);
		$this->postalCode = esc_html($json->postalCode);
		$this->town = esc_html($json->town);
		$this->country = esc_html($json->country);
		if(isset($json->siret)) $this->siret = esc_html($json->siret);
		if(isset($json->legalForm)) $this->legalForm = esc_html($json->legalForm);
		if(isset($json->privateNotes)) $this->privateNotes = esc_html($json->privateNotes);
		if(isset($json->registerDate)) $this->registerDate = $json->registerDate;
		if(isset($json->modificationDate)) $this->modificationDate = $json->modificationDate;
		if(isset($json->participants)) {
			if(is_array($json->participants)){
				$this->participants = $json->participants;
			}
			else if($json->participants!=false) {
				$this->participants = unserialize($json->participants);
			}
		}
		if(isset($json->sendRequestForOpinion)){
			$this->sendRequestForOpinion = $json->sendRequestForOpinion;
		}
		if(isset($json->askAccount)){
			$this->askAccount = $json->askAccount;
		}
		if(isset($json->deactivateEmail)){
			$this->deactivateEmail = $json->deactivateEmail;
		}
		if(isset($json->registerNewsletters)){
			$this->registerNewsletters = $json->registerNewsletters;
		}
		if(isset($json->idCaisseOnline)){ $this->idCaisseOnline = $json->idCaisseOnline; }
		if(isset($json->idStripe)){ $this->idStripe = $json->idStripe; }
		if(isset($json->idFacebook)){ $this->idFacebook = $json->idFacebook; }
		if(isset($json->seeSettings) && $this->isRESAManager()){ $this->seeSettings = $json->seeSettings; }
		$this->generateDisplayName();
	}

	/**
	 * load object with object db
	 * \see WP_User
	 */
	public function fromDB($data)
	{
		if(isset($data) && !is_bool($data)){
			$this->ID = $data->ID;
			$this->login = $data->user_login;
			$this->password = $data->user_pass;
			$this->userNicename = $data->user_nicename;
			$this->displayName= $data->display_name;
			$this->firstName = $data->__get('first_name');
			$this->lastName = $data->__get('last_name');
			$this->email = $data->user_email;
			$this->locale = $data->locale;
			$this->registerDate = $data->user_registered;
			if(isset($data->roles) && is_array($data->roles) && count($data->roles) > 0){
				$this->role = $data->roles[0];
			}
			$this->company = get_user_meta($this->ID, self::COMPANY_META_KEY, true);
			$this->companyAccount = get_user_meta($this->ID, self::COMPANY_ACCOUNT_META_KEY, true);
			$this->phone = get_user_meta($this->ID, self::PHONE_META_KEY, true);
			$this->phone2 = get_user_meta($this->ID, self::PHONE2_META_KEY, true);
			$this->privateNotes = get_user_meta($this->ID, self::PRIVATE_NOTE_META_KEY, true);
			$this->address = get_user_meta($this->ID, self::ADDRESS_META_KEY, true);
			$this->postalCode = get_user_meta($this->ID, self::POSTAL_CODE_META_KEY, true);
			$this->town = get_user_meta($this->ID, self::TOWN_META_KEY, true);
			$this->country = get_user_meta($this->ID, self::COUNTRY_META_KEY, true);
			$this->siret = get_user_meta($this->ID, self::SIRET_META_KEY, true);
			$this->legalForm = get_user_meta($this->ID, self::LEGAL_FORM_META_KEY, true);
			$this->token = get_user_meta($this->ID, self::TOKEN_META_KEY, true);
			$this->tokenValidation = get_user_meta($this->ID, self::TOKEN_VALIDATION_META_KEY, true);
			$temporary = get_user_meta($this->ID, self::TYPE_ACCOUNT, true);
			if($temporary!=false) {	$this->typeAccount = $temporary; }
			if($this->companyAccount){ $this->typeAccount = 'type_account_1'; } //Retrocompatibility

			$this->idCaisseOnline = get_user_meta($this->ID, self::ID_CAISSE_ONLINE, true);
			$this->idStripe = get_user_meta($this->ID, self::ID_STRIPE, true);
			$this->seeSettings = get_user_meta($this->ID, self::SEE_SETTINGS_META_KEY, true);
			$this->lastRESANewsID = get_user_meta($this->ID, self::LAST_RESA_NEWS_ID, true);

			$temporary = get_user_meta($this->ID, self::FILTER_SETTINGS, true);
			if($temporary!=false) {	$this->filterSettings = unserialize($temporary); }
			$temporary = get_user_meta($this->ID, self::PARTICIPANTS, true);
			if($temporary!=false) {	$this->participants = unserialize($temporary); }
			$temporary = get_user_meta($this->ID, self::MODIFICATION_DATE, true);
			if($temporary!=false) {	$this->modificationDate = $temporary;	}
			$temporary = get_user_meta($this->ID, self::SEND_REQUEST_FOR_OPINION, true);
			if($temporary!=false) {	$this->sendRequestForOpinion = $temporary;	}
			$temporary = get_user_meta($this->ID, self::DEACTIVATE_EMAIL, true);
			if($temporary!=false) {	$this->deactivateEmail = $temporary;	}
			$temporary = get_user_meta($this->ID, self::REGISTER_NEWSLETTERS, true);
			if($temporary!=false) {	$this->registerNewsletters = $temporary;	}
			$temporary = get_user_meta($this->ID, self::ID_FACEBOOK, true);
			if($temporary!=false) {	$this->idFacebook = $temporary;	}
			$this->wpUser = true;
		}
	}
	public function isNew(){ return $this->ID == -1; }
	public function getId(){ return $this->ID; }
	public function getBookings(){ return $this->bookings; }
	public function haveBooking(){ return count($this->bookings) > 0; }
	public function getBookingsUnpaid(){
		$bookingsUnPaid = [];
		foreach($this->bookings as $booking){
			if($booking->getNeedToPay() > 0){
				array_push($bookingsUnPaid, $booking);
			}
		}
		return $bookingsUnPaid;
	}
	public function getPayments(){ return $this->payments; }
	public function getLogin(){ return $this->login; }
	public function getPassword(){ return $this->password; }
	public function getUserNicename(){ return $this->userNicename; }
	public function getDisplayName(){ return $this->displayName; }
	public function getFirstName(){ return $this->firstName; }
	public function getLastName(){ return $this->lastName; }
	public function getEmail(){
		if($this->deactivateEmail) return '******';
		return $this->email;
	}
	public function getLocale(){ return $this->locale; }
	public function getRole(){ return $this->role; }
	public function isRESACustomer(){ return (!$this->isRESAManager() && !$this->isAdministrator() && !$this->isRESAStaff()) || !$this->isWpUser(); }
	public function isRESAStaff(){ return $this->role == RESA_Variables::getStaffRole(); }
	public function isRESAManager(){ return $this->role == RESA_Variables::getManagerRole(); }
	public function isAdministrator(){ return $this->role == 'administrator'; }
	public function canEditParameters(){ return $this->isAdministrator() || ($this->seeSettings && $this->isRESAManager()); }
	public function canManage(){ return $this->role == 'administrator' || $this->isRESAManager(); }
	public function getPhone(){ return $this->phone; }
	public function getPhone2(){ return $this->phone2; }
	public function getCompany(){ return $this->company; }
	public function getTypeAccount(){ return $this->typeAccount; }
	public function isCompanyAccount(){ return $this->typeAccount == 'type_account_1' || $this->companyAccount; }
	public function getPrivateNotes(){ return $this->privateNotes; }
	public function getAddress(){ return $this->address; }
	public function getPostalCode(){ return $this->postalCode; }
	public function getTown(){ return $this->town; }
	public function getCountry(){ return $this->country; }
	public function getSiret(){ return $this->siret; }
	public function getLegalForm(){ return $this->legalForm; }
	public function getToken(){ return $this->token; }
	public function getTokenValidation(){ return $this->tokenValidation; }
	public function getRegisterDate(){ return $this->registerDate; }
	public function getModificationDate(){ return $this->modificationDate; }
	public function getParticipants(){ return $this->participants; }
	public function isWpUser(){ return $this->wpUser; }
	public function getSendRequestForOpinion(){ return $this->sendRequestForOpinion; }
	public function isSendRequestForOpinion(){ return $this->sendRequestForOpinion == 'yes'; }
	public function isAskAccount(){ return $this->askAccount; }
	public function isDeactivateEmail(){ return $this->deactivateEmail == 'yes' || $this->deactivateEmail; }
	public function isRegisterNewsletters(){ return $this->registerNewsletters == 'yes' || $this->registerNewsletters; }
	public function getIdCaisseOnline(){ return $this->idCaisseOnline; }
	public function isSynchonizedCaisseOnline(){ return !empty($this->idCaisseOnline); }
	public function getIdStripe(){ return $this->idStripe; }
	public function isSynchonizedStripe(){ return !empty($this->idStripe); }
	public function getIdFacebook(){ return $this->idFacebook; }
	public function isSynchonizedFacebook(){ return !empty($this->idFacebook); }
	public function getLastRESANewsId(){ return $this->lastRESANewsID; }
	public function getFilterSettings(){ return $this->filterSettings; }
	public function isDeletable(){
		return count($this->bookings) == 0;
	}

	public function setNew(){ $this->ID = -1; $this->setLoaded(false); }
	public function setBookings($bookings){ $this->bookings = $bookings; }
	public function setPayments($payments){ $this->payments = $payments; }
	public function setLogin($login){ $this->login = $login; }
	public function setUserNicename($userNicename){ $this->userNicename = $userNicename; }
	public function setDisplayName($displayName){ $this->displayName = $displayName; }
	public function setFirstName($firstName){ $this->firstName = $firstName; }
	public function setLastName($lastName){ $this->lastName = $lastName; }
	public function setEmail($email){ $this->email = $email; }
	public function setLocale($locale){ if(!empty($this->locale)){ $this->locale = $locale; }}
	public function setRole($role){ $this->role = $role; }
	public function setTypeAccount($typeAccount){ $this->typeAccount = $typeAccount; }
	public function setCompany($company){ $this->company = $company; }
	public function setCompanyAccount($companyAccount){ $this->companyAccount = $companyAccount; }
	public function setPhone($phone){ $this->phone = $phone; }
	public function setPhone2($phone2){ $this->phone2 = $phone2; }
	public function setPrivateNotes($privateNotes){ $this->privateNotes = $privateNotes; }
	public function setAddress($address){ $this->address = $address; }
	public function setPostalCode($postalCode){ $this->postalCode = $postalCode; }
	public function setTown($town){ $this->town = $town; }
	public function setCountry($country){ $this->country = $country; }
	public function setSiret($siret){ $this->siret = $siret; }
	public function setLegalForm($legalForm){ $this->legalForm = $legalForm; }
	public function setToken($token){ $this->token = $token; }
	public function setTokenValidation($tokenValidation){ $this->tokenValidation = $tokenValidation; }
	public function setWpUser($wpUser){ $this->wpUser = $wpUser; }
	public function setModificationDate($modificationDate){ $this->modificationDate = $modificationDate; }
	public function clearModificationDate(){ $this->modificationDate = date('Y-m-d H:i:s', current_time('timestamp')); }
	public function setParticipants($participants){ $this->participants = $participants; }
	public function setSendRequestForOpinion($sendRequestForOpinion){ $this->sendRequestForOpinion = $sendRequestForOpinion; }
	public function setAskAccount($askAccount){ $this->askAccount = $askAccount; }
	public function setDeactivateEmail($deactivateEmail){ $this->deactivateEmail = $deactivateEmail; }
	public function setRegisterNewsletters($registerNewsletters){ $this->registerNewsletters = $registerNewsletters; }
	public function setIdCaisseOnline($idCaisseOnline){ $this->idCaisseOnline = $idCaisseOnline; }
	public function clearIdCaisseOnline(){ $this->idCaisseOnline = ''; }
	public function setIdStripe($idStripe){ $this->idStripe = $idStripe; }
	public function clearIdStripe(){ $this->idStripe = ''; }
	public function setIdFacebook($idFacebook){ $this->idFacebook = $idFacebook; }
	public function setSeeSettings($seeSettings){ $this->seeSettings = $seeSettings; }
	public function setLastRESANewsID($lastRESANewsID){ $this->lastRESANewsID = $lastRESANewsID; }
	public function setFilterSettings($filterSettings){ $this->filterSettings = $filterSettings; }
	public function generateDisplayName(){
		$this->displayName = $this->lastName;
		if(!empty($this->displayName) && !empty($this->firstName)){
			$this->displayName .= ' ';
		}
		$this->displayName .= $this->firstName;
	}

	public function generateNewTokenAllTimes($days){
		$actualDate = new DateTime(date('Y-m-d H:i:s', current_time('timestamp')));
		$actualDate->add(new DateInterval('P'.$days.'D'));
		if($actualDate > $this->tokenValidation){
			$this->token = bin2hex(random_bytes(64));
			$this->tokenValidation = new DateTime(date('Y-m-d H:i:s', current_time('timestamp')));
			$this->tokenValidation->add(new DateInterval('P'.$days.'D'));
			return true;
		}
		return false;
	}

	public function generateNewToken($days){
		$actualDate = new DateTime(date('Y-m-d H:i:s', current_time('timestamp')));
		if($actualDate > $this->tokenValidation){
			$this->token = bin2hex(random_bytes(64));
			$this->tokenValidation = new DateTime(date('Y-m-d H:i:s', current_time('timestamp')));
			$this->tokenValidation->add(new DateInterval('P'.$days.'D'));
			return true;
		}
		return false;
	}

	public function generateURI(){
		$this->uri = $this->lastName . '_' . $this->firstName . '_' . $this->company . '_' . $this->email. '_' . $this->phone. '_' . $this->phone2;
	}

	public function migrateID($newID, $save = false){
		foreach($this->bookings as $booking){
			$booking->setIdCustomer($newID);
			if($save){
				$booking->save();
			}
		}
		foreach($this->payments as $payment){
			$payment->setIdCustomer($newID);
			if($save){
				$payment->save();
			}
		}
		$this->linkWPDB->query('UPDATE `'.RESA_LogNotification::getTableName().'` SET idCustomer='.$newID.' WHERE idCustomer='.$this->ID.';');
		$this->linkWPDB->query('UPDATE `'.RESA_EmailCustomer::getTableName().'` SET idCustomer='.$newID.' WHERE idCustomer='.$this->ID.';');
	}

	private function indexOfParticipant($participant){
		for($i = 0; $i < count($this->participants); $i++) {
			$participantAux = $this->participants[$i];
			if(
				(isset($participantAux->lastname) && isset($participant->lastname) &&
				isset($participantAux->firstname) && isset($participant->firstname) &&
			 	$participantAux->lastname == $participant->lastname &&
				$participantAux->firstname == $participant->firstname) ||
				(isset($participantAux->lastname) && isset($participant->lastname) && !isset($participant->firstname) && $participantAux->lastname == $participant->lastname) ||
				(isset($participantAux->firstname) && isset($participant->firstname) && !isset($participant->lastname) && $participantAux->firstname == $participant->firstname)
			){
				return $i;
			}
		}
		return -1;
	}

	/**
	 * merge new participants with old participants
	 */
	public function mergeParticipants($participants){
		$toMerge = [];
		foreach($participants as $participant){
			if(isset($participant->lastname) || isset($participant->firstname)){
				$index = $this->indexOfParticipant($participant);
				if($index == -1){
					array_push($toMerge, $participant);
				}
				else {
					$participantAux = $this->participants[$index];
					if(count(get_object_vars($participantAux)) < count(get_object_vars($participant))){
						$this->participants[$index] = $participant;
					}
				}
			}
		}
		$this->participants = array_merge($this->participants, $toMerge);
	}


	public function toCSV(){
		$typesAccounts = unserialize(get_option('resa_settings_types_accounts'));
		$typeAccountName = $this->typeAccount;
		foreach($typesAccounts as $typesAccount){
			if($typesAccount->id == $this->typeAccount){
				$typeAccountName = RESA_Tools::getTextByLocale($typesAccount->name, get_locale());
			}
		}

		$lastName = htmlspecialchars_decode(ucfirst($this->lastName), ENT_QUOTES);
		$firstName = htmlspecialchars_decode(ucfirst($this->firstName), ENT_QUOTES);
		$company = htmlspecialchars_decode(ucfirst($this->company), ENT_QUOTES);
		$address = htmlspecialchars_decode(ucfirst($this->address), ENT_QUOTES);
		$town = htmlspecialchars_decode(ucfirst($this->town), ENT_QUOTES);

		$data = array(
			$lastName,
			$firstName,
			$company,
			$this->getEmail(),
			$typeAccountName,
			$this->phone,
			$this->phone2,
			$address,
			$town,
			RESA_Variables::getCountry($this->country),
			RESA_Variables::getLanguage($this->locale),
			$this->siret,
			$this->legalForm,
			$this->registerDate,
			($this->registerNewsletters?'OUI':'NON')
		);
		return implode(';', $data);
	}

}
