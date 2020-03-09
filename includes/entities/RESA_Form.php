<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_Form extends RESA_EntityDTO
{
	private $id;
  private $activated;
	private $name;
	private $description;
	private $deactivatedText;
	private $quotation;
	private $type;
	private $customCSS;
	private $colors;
	private $stepsTitle;
	private $displayImageService;
	private $displayImagePlace;
	private $displayImageCategory;
	private $selectedPlaceSentence;
	private $selectedServiceSentence;
	private $chooseADateTitle;
	private $chooseATimeslotTitle;
	private $selectedDateSentence;
	private $selectedTimeslotSentence;
	private $choosePricesTitle;
	private $chooseQuantityTitle;
	private $remainingEquipments;
	private $pricesSuffixByPersons;
	private $addNewDateTextButton;
	private $addNewActivityTextButton;
	private $informationsCustomerText;
	private $informationsParticipantsText;
	private $informationsPaymentText;
	private $informationsConfirmationText;
	private $recapBookingTitle;
	private $customerNotePlaceholder;
	private $checkboxPayment;
	private $checkboxTitlePayment;
	private $services;
	private $typesAccounts;

	/**
	 * return table name
	 */
	public static function getTableName()
	{
		global $wpdb;
		return $wpdb->prefix.'resa_form';
	}


	/**
	 * return the create query
	 */
	public static function getCreateQuery()
	{
		return 'CREATE TABLE IF NOT EXISTS `'.self::getTableName().'` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idForm` int(11) NOT NULL,
		  `attribute_name` text NOT NULL,
		  `attribute_value` text NOT NULL,
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
		$allData = array();
    global $wpdb;
    $results = $wpdb->get_results('SELECT idForm FROM '. self::getTableName() . ' WHERE attribute_name=\'id\'');
    if(isset($results)){
      foreach($results as $result){
        $form = new RESA_Form();
        $form->loadById($result->idForm);
        array_push($allData, $form);
      }
    }
		return $allData;
	}

	/**
	 *
	 */
	public static function updateSlugsServices($oldSlug, $newSlug){
		global $wpdb;
		$results = $wpdb->get_results('SELECT idForm as id, attribute_value as services FROM '. self::getTableName() . ' WHERE attribute_name=\'services\'');
		foreach ($results as $result) {
			$result->services = unserialize($result->services);
			for($i = 0; $i < count($result->services); $i++){
				if($result->services[$i] == $oldSlug){
					$result->services[$i] = $newSlug;
				}
			}
			$wpdb->update(self::getTableName(),
        array('attribute_value' => serialize($result->services)),
        array('idForm' => $result->id, 'attribute_name' => 'services'),
        array('%s'),
        array('%d','%s')
      );
		}
	}

	/**
	 * Return all entities of this type
	 */
	public static function getAllFormName()
	{
		$allData = array();
    global $wpdb;
    $results = $wpdb->get_results('SELECT idForm as id, attribute_value as name FROM '. self::getTableName() . ' WHERE attribute_name=\'name\'');
		if(isset($results)) $allData = $results;
		return $allData;
	}

	/**
	 *
	 */
	public static function getServicesForms(){
		global $wpdb;
		$results = $wpdb->get_results('SELECT idForm as id, attribute_value as services FROM '. self::getTableName() . ' WHERE attribute_name=\'services\'');
		if(isset($results)){
			$allData = array();
      foreach($results as $result){
				$form = $result;
				$varActivated = $wpdb->get_var('SELECT attribute_value FROM '. self::getTableName() . ' WHERE idForm=\''.$form->id.'\' AND attribute_name=\'activated\'');
				if($varActivated){
					$form->services = unserialize($form->services);
					$form->name = $wpdb->get_var('SELECT attribute_value FROM '. self::getTableName() . ' WHERE idForm=\''.$form->id.'\' AND attribute_name=\'name\'');
	        array_push($allData, $form);
				}
      }
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
		$this->activated = false;
		$this->name = '';
		$this->description = '';
		$this->deactivatedText = (object)array('fr_FR' => 'Formulaire désactivé', 'en_GB' => 'Form desactivated');
		$this->typeForm = 'RESA_newform';
		$this->quotation = false;
		$this->customCSS = '';
		$this->colors = (object)array(
			'override' => false,
			'primaryColor' => '#23C46C',
			'secondaryColor' => '#20B363'
		);
		$this->stepsTitle = (object)array(
			'fr_FR' => array('Lieu','Activité','Date','Compte client','Participants','Paiement','Confirmation'),
			'en_GB' => array('Place','Activity','Date','Customer account','Participants','Payment','Confirm')
		);
		$this->displayImageService = true;
		$this->displayImagePlace = false;
		$this->displayImageCategory = false;
		$this->selectedPlaceSentence = (object)array('fr_FR' => 'Vous avez sélectionné le lieu <b>[place]</b>', 'en_GB' => 'You have selected the place <b>[place]</b>');
		$this->selectedServiceSentence = (object)array('fr_FR' => 'Vous avez sélectionné l\'activité <b>[activity]</b>', 'en_GB' => 'You have selected the activity <b>[activity]</b>');
		$this->chooseADateTitle = (object)array('fr_FR' => '1 - Choisir une date', 'en_GB' => '1 - Choose a date');
		$this->chooseATimeslotTitle = (object)array('fr_FR' => '2 - Choisir un creneau', 'en_GB' => '2 - Choose a timeslot');
		$this->selectedDateSentence = (object)array('fr_FR' => 'Vous avez sélectionné le <b>[date]</b>', 'en_GB' => 'You have selected <b>[date]</b>');
		$this->selectedTimeslotSentence = (object)array('fr_FR' =>'Créneau <b>[timeslot]</b>', 'en_GB' =>'Timeslot <b>[timeslot]</b>');
		$this->choosePricesTitle = (object)array('fr_FR' => '3 - Choisir les tarifs', 'en_GB' => '3 - Select prices');
		$this->chooseQuantityTitle = (object)array('fr_FR' => 'Nombre de personnes', 'en_GB' => 'Number of persons');
		$this->remainingEquipments = (object)array('fr_FR' => 'Places restantes : [number]', 'en_GB' => 'Remaining capacity  : [number]');
		$this->pricesSuffixByPersons = (object)array('fr_FR' => 'par personne', 'en_GB' => 'per person');
		$this->addNewDateTextButton = (object)array('fr_FR' => 'Ajouter une autre date', 'en_GB' => 'Add an another date');
		$this->addNewActivityTextButton = (object)array('fr_FR' => 'Ajouter une autre activité', 'en_GB' => 'Add an another activity');
		$this->informationsCustomerText = new stdClass();
		$this->informationsParticipantsText = new stdClass();
		$this->informationsPaymentText = new stdClass();
		$this->informationsConfirmationText = new stdClass();
		$this->recapBookingTitle = (object)array('fr_FR' => 'Récapitulatif de votre réservation', 'en_GB' => 'Summary of your reservation');
		$this->customerNotePlaceholder = new stdClass();
		$this->checkboxPayment = true;
		$this->checkboxTitlePayment = (object)array('fr_FR' => 'J\'ai lu et j\'accepte les conditions générales', 'en_GB' => 'I have read and agree to the terms and conditions');
		$this->services = array();
		$this->typesAccounts = array();
	}

	/**
	 * Load form database by id
	 */
	public function loadById($id) {
    $this->id = $id;
		if($this->isExist('id')){
	    $this->activated = $this->getFormValue('activated', $this->activated);
	    $this->name = $this->getFormValue('name', $this->name);
	    $this->description = $this->getFormValue('description', $this->description);
	    $this->deactivatedText = unserialize($this->getFormValue('deactivatedText', serialize($this->deactivatedText)));
	    $this->customCSS = $this->getFormValue('customCSS', $this->customCSS);
	    $this->colors = unserialize($this->getFormValue('colors', serialize($this->colors)));
			$this->typeForm = $this->getFormValue('typeForm', $this->typeForm);
	    $this->quotation = $this->getFormValue('quotation', $this->quotation);
	    $this->stepsTitle = unserialize($this->getFormValue('stepsTitle', serialize($this->stepsTitle)));
			$this->displayImageService = $this->getFormValue('displayImageService', $this->displayImageService);
			$this->displayImagePlace = $this->getFormValue('displayImagePlace', $this->displayImagePlace);
			$this->displayImageCategory = $this->getFormValue('displayImageCategory', $this->displayImageCategory);
	    $this->selectedPlaceSentence = unserialize($this->getFormValue('selectedPlaceSentence', serialize($this->selectedPlaceSentence)));
	    $this->selectedServiceSentence = unserialize($this->getFormValue('selectedServiceSentence', serialize($this->selectedServiceSentence)));
	    $this->chooseADateTitle = unserialize($this->getFormValue('chooseADateTitle', serialize($this->chooseADateTitle)));
	    $this->chooseATimeslotTitle = unserialize($this->getFormValue('chooseATimeslotTitle', serialize($this->chooseATimeslotTitle)));
	    $this->selectedDateSentence = unserialize($this->getFormValue('selectedDateSentence', serialize($this->selectedDateSentence)));
	    $this->selectedTimeslotSentence = unserialize($this->getFormValue('selectedTimeslotSentence', serialize($this->selectedTimeslotSentence)));
	    $this->choosePricesTitle = unserialize($this->getFormValue('choosePricesTitle', serialize($this->choosePricesTitle)));
	    $this->chooseQuantityTitle = unserialize($this->getFormValue('chooseQuantityTitle', serialize($this->chooseQuantityTitle)));
			$this->remainingEquipments = unserialize($this->getFormValue('remainingEquipments', serialize($this->remainingEquipments)));
	    $this->pricesSuffixByPersons = unserialize($this->getFormValue('pricesSuffixByPersons', serialize($this->pricesSuffixByPersons)));
			$this->addNewDateTextButton = unserialize($this->getFormValue('addNewDateTextButton', serialize($this->addNewDateTextButton)));
	    $this->addNewActivityTextButton = unserialize($this->getFormValue('addNewActivityTextButton', serialize($this->addNewActivityTextButton)));
	    $this->informationsCustomerText = unserialize($this->getFormValue('informationsCustomerText', serialize($this->informationsCustomerText)));
	    $this->informationsParticipantsText = unserialize($this->getFormValue('informationsParticipantsText', serialize($this->informationsParticipantsText)));
	    $this->informationsPaymentText = unserialize($this->getFormValue('informationsPaymentText', serialize($this->informationsPaymentText)));
	    $this->informationsConfirmationText = unserialize($this->getFormValue('informationsConfirmationText', serialize($this->informationsConfirmationText)));
	    $this->recapBookingTitle = unserialize($this->getFormValue('recapBookingTitle', serialize($this->recapBookingTitle)));
	    $this->customerNotePlaceholder = unserialize($this->getFormValue('customerNotePlaceholder', serialize($this->customerNotePlaceholder)));
			$this->checkboxPayment = $this->getFormValue('checkboxPayment', $this->checkboxPayment);
			$this->checkboxTitlePayment = unserialize($this->getFormValue('checkboxTitlePayment', serialize($this->checkboxTitlePayment)));
	    $this->services = unserialize($this->getFormValue('services', serialize($this->services)));
	    $this->typesAccounts = unserialize($this->getFormValue('typesAccounts', serialize($this->typesAccounts)));
			$this->setLoaded(true);
		}
	}

	/**
	 * Save in database
	 */
	public function save($synchronize = true)	{
    $this->updateFormValue('id', $this->id);
    $this->updateFormValue('activated', $this->activated);
    $this->updateFormValue('name', $this->name);
    $this->updateFormValue('description', $this->description);
    $this->updateFormValue('deactivatedText', serialize($this->deactivatedText));
    $this->updateFormValue('typeForm', $this->typeForm);
    $this->updateFormValue('quotation', $this->quotation);
    $this->updateFormValue('customCSS', $this->customCSS);
    $this->updateFormValue('colors', serialize($this->colors));
    $this->updateFormValue('stepsTitle', serialize($this->stepsTitle));
    $this->updateFormValue('displayImageService', $this->displayImageService);
    $this->updateFormValue('displayImagePlace', $this->displayImagePlace);
    $this->updateFormValue('displayImageCategory', $this->displayImageCategory);
    $this->updateFormValue('selectedPlaceSentence', serialize($this->selectedPlaceSentence));
    $this->updateFormValue('selectedServiceSentence', serialize($this->selectedServiceSentence));
    $this->updateFormValue('chooseADateTitle', serialize($this->chooseADateTitle));
    $this->updateFormValue('chooseATimeslotTitle', serialize($this->chooseATimeslotTitle));
    $this->updateFormValue('selectedDateSentence', serialize($this->selectedDateSentence));
    $this->updateFormValue('selectedTimeslotSentence', serialize($this->selectedTimeslotSentence));
    $this->updateFormValue('choosePricesTitle', serialize($this->choosePricesTitle));
    $this->updateFormValue('chooseQuantityTitle', serialize($this->chooseQuantityTitle));
    $this->updateFormValue('remainingEquipments', serialize($this->remainingEquipments));
    $this->updateFormValue('pricesSuffixByPersons', serialize($this->pricesSuffixByPersons));
		$this->updateFormValue('addNewDateTextButton', serialize($this->addNewDateTextButton));
    $this->updateFormValue('addNewActivityTextButton', serialize($this->addNewActivityTextButton));
    $this->updateFormValue('informationsCustomerText', serialize($this->informationsCustomerText));
    $this->updateFormValue('informationsParticipantsText', serialize($this->informationsParticipantsText));
    $this->updateFormValue('informationsPaymentText', serialize($this->informationsPaymentText));
    $this->updateFormValue('informationsConfirmationText', serialize($this->informationsConfirmationText));
    $this->updateFormValue('recapBookingTitle', serialize($this->recapBookingTitle));
    $this->updateFormValue('customerNotePlaceholder', serialize($this->customerNotePlaceholder));
    $this->updateFormValue('checkboxPayment', $this->checkboxPayment);
    $this->updateFormValue('checkboxTitlePayment', serialize($this->checkboxTitlePayment));
    $this->updateFormValue('services', serialize($this->services));
    $this->updateFormValue('typesAccounts', serialize($this->typesAccounts));
	}

  /**
   *
   */
  public function updateFormValue($name, $value){

    if($this->isExist($name)){
      $this->linkWPDB->update(self::getTableName(),
        array('attribute_value' => $value),
        array('idForm' => $this->id, 'attribute_name' => $name),
        array('%s'),
        array('%d','%s')
      );
    }
    else {
      $this->linkWPDB->insert(self::getTableName(), array(
          'idForm' => $this->id,
          'attribute_name' => $name,
          'attribute_value' => $value
        ), array (
  				'%d',
  				'%s',
  				'%s'
      ));
    }
  }

  public function isExist($name){
    $result = $this->linkWPDB->get_var('SELECT COUNT(*) FROM '.self::getTableName().' WHERE attribute_name=\''.$name.'\' AND idForm='.$this->id);
    if(!isset($result) || $result == 0) return false;
    return true;
  }

  public function getFormValue($name, $default){
    $result = $this->linkWPDB->get_var('SELECT attribute_value FROM '.self::getTableName().' WHERE attribute_name=\''.$name.'\' AND idForm='.$this->id);
    if(!isset($result)) return $default;
    return $result;
  }



	/**
	 * Save in database
	 */
	public function deleteMe(){
    $this->linkWPDB->delete(self::getTableName(), array('idForm'=>$this->id), array ('%d'));
		$this->clearCSS();
	}


	/**
	 * Return this to JSON value
	 */
	public function toJSON($withPassword = false)
	{
     return '{
			"id":'.$this->id .',
			"name":"'.$this->name .'",
			"activated":'.RESA_Tools::toJSONBoolean($this->activated).',
			"description":"'.$this->description .'",
			"deactivatedText":'.json_encode($this->deactivatedText) .',
			"quotation":'.RESA_Tools::toJSONBoolean($this->quotation).',
			"typeForm":"'.$this->typeForm.'",
			"customCSS":"'.$this->customCSS.'",
			"colors":'.json_encode($this->colors) .',
			"stepsTitle":'.json_encode($this->stepsTitle) .',
			"displayImageService":'.RESA_Tools::toJSONBoolean($this->displayImageService).',
			"displayImagePlace":'.RESA_Tools::toJSONBoolean($this->displayImagePlace).',
			"displayImageCategory":'.RESA_Tools::toJSONBoolean($this->displayImageCategory).',
			"selectedPlaceSentence":'.json_encode($this->selectedPlaceSentence) .',
			"selectedServiceSentence":'.json_encode($this->selectedServiceSentence) .',
			"chooseADateTitle":'.json_encode($this->chooseADateTitle) .',
			"chooseATimeslotTitle":'.json_encode($this->chooseATimeslotTitle) .',
			"selectedDateSentence":'.json_encode($this->selectedDateSentence) .',
			"selectedTimeslotSentence":'.json_encode($this->selectedTimeslotSentence) .',
			"choosePricesTitle":'.json_encode($this->choosePricesTitle) .',
			"chooseQuantityTitle":'.json_encode($this->chooseQuantityTitle) .',
			"remainingEquipments":'.json_encode($this->remainingEquipments) .',
			"pricesSuffixByPersons":'.json_encode($this->pricesSuffixByPersons) .',
			"addNewDateTextButton":'.json_encode($this->addNewDateTextButton) .',
			"addNewActivityTextButton":'.json_encode($this->addNewActivityTextButton) .',
			"informationsCustomerText":'.json_encode($this->informationsCustomerText) .',
			"informationsParticipantsText":'.json_encode($this->informationsParticipantsText) .',
			"informationsPaymentText":'.json_encode($this->informationsPaymentText) .',
			"informationsConfirmationText":'.json_encode($this->informationsConfirmationText) .',
			"recapBookingTitle":'.json_encode($this->recapBookingTitle) .',
			"customerNotePlaceholder":'.json_encode($this->customerNotePlaceholder).',
			"checkboxPayment":'.RESA_Tools::toJSONBoolean($this->checkboxPayment).',
			"checkboxTitlePayment":'.json_encode($this->checkboxTitlePayment).',
			"services":'.json_encode($this->services) .',
			"typesAccounts":'.json_encode($this->typesAccounts) .'
     }';
	}

	/**
	 * load object with json
	 */
	public function fromJSON($json)
	{
    $this->id = $json->id;
    if(isset($json->activated)) $this->activated = esc_html($json->activated);
    if(isset($json->name)) $this->name = esc_html($json->name);
    if(isset($json->description)) $this->description = esc_html($json->description);
    if(isset($json->deactivatedText)) $this->deactivatedText = $json->deactivatedText;
    if(isset($json->quotation)) $this->quotation = esc_html($json->quotation);
    if(isset($json->customCSS)) $this->customCSS = esc_html($json->customCSS);
		if(isset($json->typeForm)) $this->typeForm = esc_html($json->typeForm);
    if(isset($json->colors)) $this->colors = $json->colors;
    if(isset($json->stepsTitle)) $this->stepsTitle = $json->stepsTitle;
    if(isset($json->displayImageService)) $this->displayImageService = $json->displayImageService?1:0;
    if(isset($json->displayImagePlace)) $this->displayImagePlace = $json->displayImagePlace?1:0;
    if(isset($json->displayImageCategory)) $this->displayImageCategory = $json->displayImageCategory?1:0;
    if(isset($json->selectedPlaceSentence)) $this->selectedPlaceSentence = $json->selectedPlaceSentence;
    if(isset($json->selectedServiceSentence)) $this->selectedServiceSentence = $json->selectedServiceSentence;
    if(isset($json->chooseADateTitle)) $this->chooseADateTitle = $json->chooseADateTitle;
    if(isset($json->chooseATimeslotTitle)) $this->chooseATimeslotTitle = $json->chooseATimeslotTitle;
    if(isset($json->selectedDateSentence)) $this->selectedDateSentence = $json->selectedDateSentence;
		if(isset($json->selectedTimeslotSentence)) $this->selectedTimeslotSentence = $json->selectedTimeslotSentence;
		if(isset($json->choosePricesTitle)) $this->choosePricesTitle = $json->choosePricesTitle;
		if(isset($json->chooseQuantityTitle)) $this->chooseQuantityTitle = $json->chooseQuantityTitle;
		if(isset($json->remainingEquipments)) $this->remainingEquipments = $json->remainingEquipments;
		if(isset($json->pricesSuffixByPersons)) $this->pricesSuffixByPersons = $json->pricesSuffixByPersons;
		if(isset($json->addNewDateTextButton)) $this->addNewDateTextButton = $json->addNewDateTextButton;
    if(isset($json->addNewActivityTextButton)) $this->addNewActivityTextButton = $json->addNewActivityTextButton;
    if(isset($json->informationsCustomerText)) $this->informationsCustomerText = $json->informationsCustomerText;
    if(isset($json->informationsParticipantsText)) $this->informationsParticipantsText = $json->informationsParticipantsText;
    if(isset($json->informationsPaymentText)) $this->informationsPaymentText = $json->informationsPaymentText;
    if(isset($json->informationsConfirmationText)) $this->informationsConfirmationText = $json->informationsConfirmationText;
    if(isset($json->recapBookingTitle)) $this->recapBookingTitle = $json->recapBookingTitle;
    if(isset($json->customerNotePlaceholder)) $this->customerNotePlaceholder = $json->customerNotePlaceholder;
    if(isset($json->checkboxPayment)) $this->checkboxPayment = $json->checkboxPayment;
    if(isset($json->checkboxTitlePayment)) $this->checkboxTitlePayment = $json->checkboxTitlePayment;
    if(isset($json->services)) $this->services = $json->services;
    if(isset($json->typesAccounts)) $this->typesAccounts = $json->typesAccounts;
	}

  public function isNew(){ return $this->id == -1; }
	public function getId(){ return $this->id; }
	public function isActivated(){ return $this->activated; }
	public function getName(){ return $this->name; }
	public function getDescription(){ return $this->description; }
	public function getDeactivatedText(){ return $this->deactivatedText; }
	public function isQuotation(){ return $this->quotation; }
	public function getCustomCSS(){ return $this->customCSS; }
	public function getTypeForm(){ return $this->typeForm; }
	public function getColors(){ return $this->colors; }
	public function getStepsTitle(){ return $this->stepsTitle; }
	public function isDisplayImageService(){ return $this->displayImageService; }
	public function isDisplayImagePlace(){ return $this->displayImagePlace; }
	public function isDisplayImageCategory(){ return $this->displayImageCategory; }
	public function getSelectedPlaceSentence(){ return $this->selectedPlaceSentence; }
	public function getSelectedServiceSentence(){ return $this->selectedServiceSentence; }
	public function getChooseADateTitle(){ return $this->chooseADateTitle; }
	public function getChooseATimeslotTitle(){ return $this->chooseATimeslotTitle; }
	public function getSelectedDateSentence(){ return $this->selectedDateSentence; }
	public function getSelectedTimeslotSentence(){ return $this->selectedTimeslotSentence; }
	public function getChoosePricesTitle(){ return $this->choosePricesTitle; }
	public function getChooseQuantityTitle(){ return $this->chooseQuantityTitle; }
	public function getRemainingEquipments(){ return $this->remainingEquipments; }
	public function getPricesSuffixByPersons(){ return $this->pricesSuffixByPersons; }
	public function getAddNewDateTextButton(){ return $this->addNewDateTextButton; }
	public function getAddNewActivityTextButton(){ return $this->addNewActivityTextButton; }
	public function getInformationsCustomerText(){ return $this->informationsCustomerText; }
	public function getInformationsParticipantsText(){ return $this->informationsParticipantsText; }
	public function getInformationsPaymentText(){ return $this->informationsPaymentText; }
	public function getInformationsConfirmationText(){ return $this->informationsConfirmationText; }
	public function getRecapBookingTitle(){ return $this->recapBookingTitle; }
	public function getCustomerNotePlaceholder(){ return $this->customerNotePlaceholder; }
	public function isCheckboxPayment(){ return $this->checkboxPayment; }
	public function getCheckboxTitlePayment(){ return $this->checkboxTitlePayment; }
	public function getServices(){ return $this->services; }
	public function getTypeAccounts(){ return $this->typesAccounts; }
	public function getFileCSSGenerated(){ return '__form'.$this->id.'.css'; }

  public function setNew(){ $this->ID = -1; $this->setLoaded(false); }
  public function setId($id){ $this->id = $id; }
  public function setActivated($activated){ $this->activated = $activated; }
  public function setName($name){ $this->name = $name; }
  public function setDescription($description){ $this->description = $description; }
	public function setCustomCSS($customCSS){ $this->customCSS = $customCSS; }
  public function setQuotation($quotation){ $this->quotation = $quotation; }
  public function setTypeForm($typeForm){ $this->typeForm = $typeForm; }
	public function setStepsTitle($stepsTitle){ $this->stepsTitle = $stepsTitle; }
  public function setDisplayImageService($displayImageService){ $this->displayImageService = $displayImageService; }
  public function setDisplayImagePlace($displayImagePlace){ $this->displayImagePlace = $displayImagePlace; }
  public function setDisplayImageCategory($displayImageCategory){ $this->displayImageCategory = $displayImageCategory; }
	public function setAddNewDateTextButton($addNewDateTextButton){ $this->addNewDateTextButton = $addNewDateTextButton; }
	public function setAddNewActivityTextButton($addNewActivityTextButton){ $this->addNewActivityTextButton = $addNewActivityTextButton; }
	public function setInformationsCustomerText($informationsCustomerText){ $this->informationsCustomerText = $informationsCustomerText; }
	public function setInformationsParticipantsText($informationsParticipantsText){ $this->informationsParticipantsText = $informationsParticipantsText; }
	public function setInformationsPaymentText($informationsPaymentText){ $this->informationsPaymentText = $informationsPaymentText; }
	public function setInformationsConfirmationText($informationsConfirmationText){ $this->informationsConfirmationText = $informationsConfirmationText; }
	public function setCustomerNotePlaceholder($customerNotePlaceholder){ $this->customerNotePlaceholder = $customerNotePlaceholder; }
	public function setCheckboxPayment($checkboxPayment){ $this->checkboxPayment = $checkboxPayment; }
	public function setCheckboxTitlePayment($checkboxTitlePayment){ $this->checkboxTitlePayment = $checkboxTitlePayment; }

  public function setServices($services){ $this->services = $services; }
	public function setTypeAccounts($typesAccounts){ $this->typesAccounts = $typesAccounts; }

	public function clearCSS(){
		$arrayUploadDir = wp_get_upload_dir();
		$dir = $arrayUploadDir['basedir'].'/resa_css';
		if(!is_dir($dir)){ mkdir($dir); }
		$cssFile = $dir . '/' . $this->getFileCSSGenerated();
		unlink($cssFile);
	}

	public function generateCSS(){
		$colors = unserialize(get_option('resa_settings_colors'));
		if(isset($this->getColors()->override) && $this->getColors()->override){
			$colors = $this->getColors();
		}
		$css = '/**** COLORS SCHEME*****/
		.activities-col .resa-category,
		.activities-col .resa-activity {
		    width: calc(33% - 10px);
		}

		.resa-places-cols .resa-place {
		    width: calc(33% - 10px);
		}

		.resa-datepicker tbody .btn:hover{
			border:1px solid ' . $colors->secondaryColor. ';
			background-color:' . $colors->secondaryColor. ';
		}

		.resa-datepicker tbody .btn.active{
			color:' . $colors->primaryColor. ';
			border:1px solid ' . $colors->primaryColor. ';
		}

		.resa-datepicker tbody .btn.active:hover{
			color:white;
			border:1px solid ' . $colors->secondaryColor. ';
		}


		.navigation-step.selected { /* Etape en cours */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		}

		.navigation-step.active { /* Etape cliquable */
		    color: ' . $colors->primaryColor. ';
		    background-color: white;
		    border-color: ' . $colors->primaryColor. ';
		}

		.navigation-step.active:hover { /* Etape cliquable au survol */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		}

		@media (max-width:767px) {
		    .navigation-step.selected { /* Etape en cours (responsive) */
		        color: white;
		        background-color: ' . $colors->primaryColor. ';
		        border-color: transparent;
		    }
		}

		/*  Titres et texte général  */
		#resa-form h2 { /* titre de niveau 2 */
		    color: black;
		    font-size: 36px;
		}

		#resa-form h3 { /* titre de niveau 3 */
		    color: black;
		    font-size: 28px;
		}

		#resa-form h4 { /* titre de niveau 4 */
		    color: black;
		    font-size: 24px;
		}

		#resa-form h5 { /* titre de niveau 5 */
		    color: black;
		    font-size: 20px;
		}

		#resa-steps-content p,
		#resa-steps-content div { /* texte */
		    color: black;
		    font-size: 14px;
		}

		#resa-form .text-important { /* texte important */
		    color: ' . $colors->primaryColor. ';
		}

		#resa-form a { /* lien */
		    color: ' . $colors->primaryColor. ';
		}

		#resa-form a:hover { /* lien au survol */
		    color: ' . $colors->secondaryColor. ';
		}

		::placeholder { /*  placeholder */
		    color: grey;
		}

		/**** Boutons ****/
		#resa-form .resa-btn,
		#resa-form input[type="submit"] { /* > normal */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		}

		#resa-form .resa-btn:hover,
		#resa-form input[type="submit"]:hover { /* > au survol */
		    color: white;
		    background-color: ' . $colors->secondaryColor. ';
		    border-color: ' . $colors->secondaryColor. ';
		}

		/***** calendrier et creneaux *****/
		#resa-form .resa-month-selector-content select { /* > select */
		    color: white;
		    border-color: ' . $colors->secondaryColor. ';
		    background-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-period,
		#resa-form .resa-timeslot { /* > creneau */
		    color:black;
		    background-color: white;
		    border-color: black;
		}

		#resa-form .resa-period:hover,
		#resa-form .resa-timeslot:hover { /* > creneau au survol */
		    color: white;
		    background-color: ' . $colors->secondaryColor. ';
		    border-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-period.selected,
		#resa-form .resa-timeslot.selected { /* > créneau sélectionné */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		}

		/** tarifs et extras **/
		#resa-form .resa-price h3 { /*  > tarifs - couleur du titre */
		    color: black;
		}

		#resa-form .resa-price p,
		#resa-form .resa-price .resa-price-quatity-title { /* > tarifs - couleur du texte */
		    color: black;
		}

		#resa-form .resa-price { /* > tarifs - fond - bordure */
		    background-color: transparent;
		    border-color: black;
		}

		#resa-form .resa-price .resa-price-value .text-important { /* > tarifs - couleur prix - taille prix */
		    color: ' . $colors->primaryColor. ';
		    font-size: 28px;
		}

		#resa-form .resa-price-quantity .quantity-less,
		#resa-form .resa-price-quantity .quantity-more { /* > tarifs - boutons */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		}

		#resa-form .resa-price-quantity .quantity-less:hover,
		#resa-form .resa-price-quantity .quantity-more:hover {
		    color: white;
		    background-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-prices .resa-price-quantity .quantity-value { /* > tarifs - quantité */

		}

		#resa-form .resa-extras .resa-price h3 { /* > extras - couleur du titre */
		    color: black;
		}

		#resa-form .resa-extras .resa-price p,
		#resa-form .resa-extras .resa-price .resa-price-quatity-title { /* > extras - couleur du texte */
		    color: black;
		}

		#resa-form .resa-extras .resa-price { /* > extras - fond - bordure */
		    background-color: transparent;
		    border-color: transparent;
		}

		#resa-form .resa-extras .resa-price .resa-price-value .text-important { /* > extras - couleur prix - taille prix */
		    color: ' . $colors->primaryColor. ';
		    font-size: 28px;
		}

		#resa-form .resa-extras .resa-price-quantity .quantity-less,
		#resa-form .resa-extras .resa-price-quantity .quantity-more { /* > extras - boutons  */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		}

		#resa-form .resa-extras .resa-price-quantity .quantity-less:hover,
		#resa-form .resa-extras .resa-price-quantity .quantity-more:hover { /* > extras - boutons au survol */
		   color: white;
		   background-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-extras .resa-price-quantity .quantity-value { /* > extras - quantité */

		}

		/**** champs de formulaire ****/
		/* non modifiable
		#resa-step-account .input_error {color: #E74C3C;}
		#resa-step-account .input_info {color: #FFC300;}
		*/
		#resa-form #resa-step-account input:not([type="submit"]),
		#resa-form #resa-step-account select { /* > champs de formulaire - bordure - texte - fond*/

		}

		/**** participants ****/
		#resa-form .participants-content thead td { /* > entête - texte */
		    color: black;
		}

		#resa-form input.participant-input,
		#resa-form select.participant-input { /* > participants - texte - bordure - fond */

		}

		#resa-form .helpbox_content { /* > helpbox - bordure - fond */
		    border-color: black;
		    background-color: white;
		}

		#resa-form .helpbox_content p {
		    /* > helpbox - texte */
		    color: black;
		}

		/**** panier et récapitulatif ****/
		#resa-form .cart-content { /* > panier */
		    background-color: white;
		    border-color: black;
		}

		#resa-form #resa-step-validation .cart-content { /* > récapitulatif */
		    background-color: white;
		    border-color: black;
		}

		.resa-cart-activity-title { /* > activités */
		    border-color: black;
		}

		#resa-form .action-delete { /* > bouton supprimer */
		    color: ' . $colors->primaryColor. ';
		    background-color: transparent;
		    border-color: ' . $colors->primaryColor. ';
		}

		#resa-form .action-delete:hover { /* > bouton supprimer au survol */
		    color: ' . $colors->primaryColor. ';
		    background-color: transparent;
		    border-color: ' . $colors->primaryColor. ';
		}

		.resa-lightbox { /* lightbox */
		    background: rgba(20,20,20,0.8); /* couleur de fond et opacité en rvb */
		}

		.resa-lightbox .resa-lightbox-content { /* lightbox - couleur de fond contenu*/
		    background-color: white;
		}

		/**** lieu, catégorie & activités ****/
		#resa-form .resa-place h4 { /* > lieu - titre */
		    color: white;
		    font-size: 25px;
		}

		#resa-form .resa-place p { /* > lieu - texte */
		    color: white;
		    font-size: 17px;
		}

		#resa-form .resa-place { /* > lieu - fond - bordure (taille / couleur) */
		    background-color: ' . $colors->primaryColor. ';
		    border-width: 5px;
		    border-color: ' . $colors->primaryColor. ';
		}

		#resa-form .resa-place:hover { /* > lieu - fond - bordure (taille / couleur) */
		    background-color: ' . $colors->secondaryColor. ';
		    border-width: 5px;
		    border-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-category { /* > categorie bloc - fond - bordure (taille / couleur) */
		    background-color: white;
		    border-width: 3px;
		    border-color: black;
		}

		#resa-form .resa-category h4 { /* > categorie - titre */
		    color: black;
		    font-size: 25px;
		}

		#resa-form .resa-category .resa-btn,
		#resa-form .resa-activity { /* > categorie - titre, fond - bordure - taille bordure */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		    border-width: 2px;
		}

		#resa-form .resa-activity { /* > categorie - titre, fond - bordure - taille bordure */
		    color: white;
		    background-color: ' . $colors->primaryColor. ';
		    border-color: ' . $colors->primaryColor. ';
		    border-width: 2px;
		}

		#resa-form .activity-title  {
			color: white;
		}

		#resa-form .resa-category .resa-btn:hover,
		#resa-form .resa-activity:hover { /* > categorie - titre, fond - bordure au survol */
		    color: white;
		    background-color: ' . $colors->secondaryColor. ';
		    border-color: ' . $colors->secondaryColor. ';
		}

		#resa-form .resa-btn.resa-btn-disabled {
			background-color:lightgrey;
			color:grey;
			border-color:lightgrey;
		}

		#resa-form .resa-btn.resa-btn-disabled:hover {
			background-color:lightgrey;
			color:grey;
			border-color:lightgrey;
		}

		/**** END COLORS SCHEME GENERATED *****/';

		$arrayUploadDir = wp_get_upload_dir();
		$dir = $arrayUploadDir['basedir'].'/resa_css';
		if(!is_dir($dir)){ mkdir($dir); }
		$cssFile = $dir . '/' . $this->getFileCSSGenerated();

		$fp = fopen($cssFile, 'w+');
		fwrite($fp, $css);
		fclose($fp);
	}


}
