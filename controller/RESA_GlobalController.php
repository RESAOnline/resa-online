<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_GlobalController extends RESA_Controller
{
	public function getSlug()
	{
		return '';
	}

	public function getPageName()
	{
		return '';
	}

	public function isSettings()
	{
		return false;
	}

	public function getClassDir()
	{
		return __DIR__;
	}


	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts()
	{
		return [

		];
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		return [

		];
	}

	/**
	 * Method to call with menu.
	 */
	public function initialize()
	{

	}

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods()
	{
		$this->addAjaxMethod('getTimeslots', true);
		$this->addAjaxMethod('validFormPublic', true);
		$this->addAjaxMethod('getReductions', true);
		$this->addAjaxMethod('userConnection', true);
		$this->addAjaxMethod('userDeconnection', true);
		$this->addAjaxMethod('getBill', true);
		$this->addAjaxMethod('payBooking', true);
		$this->addAjaxMethod('acceptQuotation', true);
		$this->addAjaxMethod('refuseQuotation', true);
		$this->addAjaxMethod('modifyCustomer', true);
		$this->addAjaxMethod('forgottenPasswordCustomer', true);
		$this->addAjaxMethod('askAccountRequest', true);
	}

	private function isAdmin(){
		$currentUser = new RESA_Customer();
		$currentUser->loadCurrentUser();
		return !RESA_Variables::staffIsConnected() && $currentUser->getId()!=-1 && current_user_can('resa_view');
	}

	public function getTimeslots(){
		$service = new RESA_Service();
		if(isset($_REQUEST['service']) && isset($_REQUEST['date']) && isset($_REQUEST['servicesParameters']) && isset($_REQUEST['idBooking']) && isset($_REQUEST['allowInconsistencies']) && isset($_REQUEST['frontForm']) && isset($_REQUEST['typeAccount'])){
			$service->fromJSON(json_decode(stripslashes(wp_kses_post($_REQUEST['service']))));
			$date = stripslashes(sanitize_text_field($_REQUEST['date']));
			$date = DateTime::createFromFormat('d-m-Y H:i:s' , $date);
			$servicesParameters = json_decode(stripslashes(wp_kses_post($_REQUEST['servicesParameters'])));
			$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
			$allowInconsistencies = json_decode(stripslashes(sanitize_text_field($_REQUEST['allowInconsistencies'])));
			$frontForm = json_decode(stripslashes(sanitize_text_field($_REQUEST['frontForm'])));
			$typeAccount = json_decode(stripslashes(sanitize_text_field($_REQUEST['typeAccount'])));
			$appointments = array();
			try{
				$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters,  new RESA_Customer(), true, $idBooking, ($this->isAdmin() && !$frontForm), $typeAccount);
			}
			catch(Exception $e){
				//Logger::DEBUG($e);
			}

			$dateStart = DateTime::createFromFormat('d-m-Y H:i:s', $date->format('d-m-Y').' 00:00:00');
			$dateEnd = DateTime::createFromFormat('d-m-Y H:i:s', $date->format('d-m-Y').' 23:59:59');
			$allTimeslots = RESA_Algorithms::getTimeslots($dateStart, $dateEnd, $service, $appointments, $idBooking, ($this->isAdmin() && !$frontForm), $typeAccount);
			for($i = 0; $i < count($allTimeslots); $i++){
				$allTimeslots[$i]['members'] = json_decode(RESA_Tools::formatJSONArray($allTimeslots[$i]['members']));
			}
			$allTimeslots = json_encode($allTimeslots);
			echo $allTimeslots;
		}
		else echo '[]';
		wp_die();
	}


	public function validFormPublic(){
		try {
			if(isset($_REQUEST['customer']) && isset($_REQUEST['frontForm']) && isset($_REQUEST['idForm']) && isset($_REQUEST['typePayment']) && isset($_REQUEST['servicesParameters'])){
				$customerJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['customer'])));
				$frontForm = json_decode(stripslashes(sanitize_text_field($_REQUEST['frontForm'])));
				$idForm = json_decode(stripslashes(sanitize_text_field($_REQUEST['idForm'])));
				$typePayment = json_decode(stripslashes(sanitize_text_field($_REQUEST['typePayment'])));
				$customer = new RESA_Customer();
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser(false);
				//If Caisse online not response

				$ifOkCaisseOnline = class_exists('RESA_CaisseOnline') && RESA_CaisseOnline::getInstance()->isCaisseOnlineStatusOk();
				$ifOkStripeConnect = (class_exists('RESA_StripeConnect') && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::checkConnection():false;

				if(($frontForm || !$this->isAdmin()) && (RESA_Variables::isTypePaymentOnline($typePayment) && ($typePayment!='swikly')  && ($typePayment != 'stripeConnect' || !$ifOkStripeConnect) && ($typePayment=='swikly' || $typePayment=='stripeConnect' || !$ifOkCaisseOnline))){
					throw new Exception(__('payment_online_error', 'resa'));
				}
				if(($this->isAdmin() && !$frontForm) && isset($customerJSON->ID)){
					$customer->loadById($customerJSON->ID);
				}
				else if($frontForm && $currentUser->isLoaded()){
					$customer = $currentUser;
				}
				$typeAccount = $customerJSON->typeAccount;
				if($customer->isLoaded()){
					$typeAccount = $customer->getTypeAccount();
				}
				if(!RESA_Customer::isPaymentTypeAuthorized($typeAccount, $typePayment, unserialize(get_option('resa_settings_types_accounts')))){
						throw new Exception(__('type_payment_unauthorized_error', 'resa'));
				}

				if((!$frontForm && $customer->getId() <= 0) || ($customerJSON->ID == -1 && !$currentUser->isLoaded())){
					if(isset($customerJSON) && isset($customerJSON->password) && !empty($customerJSON->password) &&
						isset($customerJSON->lastName) && !empty($customerJSON->lastName) &&
						isset($customerJSON->email) && !empty($customerJSON->email)){
						//Create new user.
						$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
						if(!username_exists($customerJSON->login) && !email_exists($customerJSON->email) && RESA_Customer::isOkPasswordFormat($customerJSON->password)){
							$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email);
							if($customer->isLoaded()){
								$customerJSON->ID = $customer->getId();
								$customer->fromJSON($customerJSON);
								$customer->setRole(RESA_Variables::getCustomerRole());
								$customer->save();
								RESA_Mailer::sentMessageAccountCreation($customer);
								RESA_Customer::authenticate($customerJSON->login, $customerJSON->password);
							}else throw new Exception(__('authentication_or_register_error', 'resa'));
						}
						else {
							//Connection
							$customer = RESA_Customer::authenticate($customerJSON->login, $customerJSON->password);
							if(!$customer->isLoaded()){
								if(is_numeric(username_exists($customerJSON->login)) || is_numeric(email_exists($customerJSON->email))){
									if(is_numeric(email_exists($customerJSON->email))) throw new Exception(__('email_already_exist_error', 'resa'));
									else if(is_numeric(username_exists($customerJSON->login))) throw new Exception(__('login_already_exist_error', 'resa'));
									else if(!RESA_Customer::isOkPasswordFormat($customerJSON->password)) throw new Exception(__('bad_password_error', 'resa'));
								}
								else throw new Exception(__('authentication_error', 'resa'));
							}
						}
					}
					else if(isset($customerJSON) &&
						isset($customerJSON->email) && !empty($customerJSON->email) &&
						isset($customerJSON->idFacebook) && !empty($customerJSON->idFacebook) &&
						class_exists('RESA_FacebookLogin') && get_option('resa_facebook_activated', false) && RESA_FacebookLogin::isOkCustomer($customerJSON)){
						$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
						$customer->create($customerJSON->login, $customerJSON->password, $customerJSON->email);
						if($customer->isLoaded()){
							$customerJSON->ID = $customer->getId();
							$customer->fromJSON($customerJSON);
							$customer->setRole(RESA_Variables::getCustomerRole());
							$customer->save();
							RESA_Mailer::sentMessageAccountCreation($customer);
							RESA_Customer::authenticate($customer->getLogin(), $customer->getPassword());
						}else throw new Exception(__('authentication_or_register_error', 'resa'));
					}
					else throw new Exception(__('authentication_or_register_error', 'resa'));
				}
				if($customer->isNew()){
					throw new Exception(__('authentication_or_register_reload_error', 'resa'));
				}
				if($customer->isAskAccount()){
					throw new Exception(__('not_validate_account_error', 'resa'));
				}

				$idBooking = -1;
				if(isset($_REQUEST['idBooking'])) $idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$isNewBooking = ($idBooking == -1);
				$servicesParameters = json_decode(stripslashes(wp_kses_post($_REQUEST['servicesParameters'])));
				$quotation = false;
				if(isset($_REQUEST['quotation'])) $quotation = json_decode(stripslashes(sanitize_text_field($_REQUEST['quotation'])));
				if($this->isAdmin() && !$frontForm && isset($_REQUEST['allowInconsistencies'])){
					$allowInconsistencies = json_decode(stripslashes(sanitize_text_field($_REQUEST['allowInconsistencies'])));
				}
				else {
					$allowInconsistencies = false;
				}
				$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters, $customer, $allowInconsistencies, $idBooking, ($this->isAdmin() && !$frontForm), $customer->getTypeAccount());

				//reductions.
				$couponsList = array();
				if(isset($_REQUEST['couponsList'])) $couponsList = json_decode(stripslashes(wp_kses_post($_REQUEST['couponsList'])));
				$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, '', ($this->isAdmin() && !$frontForm));
				$allReductions = $result['reductions'];
				$mapIdClientObjectReduction = $result['mapIdClientObjectReduction'];
				foreach($couponsList as $coupon){
					if(!empty($coupon)){
						$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, $coupon, ($this->isAdmin() && !$frontForm));
						$allReductions = array_merge($allReductions, $result['reductions']);
						foreach($result['mapIdClientObjectReduction'] as $id => $params){
							if(!isset($mapIdClientObjectReduction[$id])){
								$mapIdClientObjectReduction[$id] = [];
							}
							$mapIdClientObjectReduction[$id] = array_merge($mapIdClientObjectReduction[$id], $params);
						}
					}
				}
				//$reductions = RESA_Algorithms::returnReductionsNotCombinables($appointments, $allReductions, $mapIdClientObjectReduction);
				$booking = RESA_Algorithms::fillBookingReductions($appointments, $allReductions, $mapIdClientObjectReduction);
				if($customer->getId() == -1){ throw new Exception(__('authentication_or_register_reload_error', 'resa')); }
				$booking->setIdCustomer($customer->getId());
				$booking->setIdUserCreator($customer->getId());
				if(($this->isAdmin() && !$frontForm) && $currentUser->getId()!=-1 && current_user_can('resa_view')) {
					$booking->setIdUserCreator($currentUser->getId());
				}
				if(!empty(get_option('resa_settings_tva'))){
					$booking->setTVA(get_option('resa_settings_tva'));
				}
				$bookingCustomReductions = array();
				$customReductions = json_decode(stripslashes($_REQUEST['customReductions']));
				foreach($customReductions as $customReduction){
					$bookingCustomReduction = new RESA_BookingCustomReduction();
					$bookingCustomReduction->fromJSON($customReduction);
					array_push($bookingCustomReductions, $bookingCustomReduction);
				}
				$booking->setBookingCustomReductions($bookingCustomReductions);
				$booking->savePrices();
				$sendMessage = json_decode(stripslashes($_REQUEST['sendEmailToCustomer']));
				if(!$isNewBooking){
					$oldBooking = new RESA_Booking();
					$oldBooking->loadById($idBooking);
					if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
						if(!$oldBooking->isOldBooking()){
							$oldBooking->setOldBooking(true);
							foreach($oldBooking->getAppointments() as $appointment){
								$appointment->setState('updated');
							}
							$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
							$booking->addLinkOldBookings($oldBooking->getId());
							$booking->setAdvancePayment($oldBooking->getAdvancePayment());
							$booking->setCreationDate($oldBooking->getCreationDate());
							$booking->setIdUserCreator($oldBooking->getIdUserCreator());
							$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
							$booking->setTransactionId($oldBooking->getTransactionId());
							$booking->setIdCreation($oldBooking->getIdCreation());
							$booking->setPaymentState($oldBooking->getPaymentState());
							$booking->setQuotationRequest($oldBooking->isQuotationRequest());
							$booking->setAlreadySentEmail($oldBooking->isAlreadySentEmail());
							$oldBooking->save(false);
						}
						else {
							throw new Exception(__('create_new_version_bookings_error', 'resa'));
						}
					}
				}

				$currentUrl = json_decode(stripslashes($_REQUEST['currentUrl']));
				$bookingNote = '';
				if(isset($_REQUEST['bookingNote'])){
					$bookingNote = RESA_Tools::formatTextaeraHTML(json_decode(stripslashes($_REQUEST['bookingNote'])));
				}
				$bookingPublicNote = '';
				if(isset($_REQUEST['bookingPublicNote'])){
					$bookingPublicNote = RESA_Tools::formatTextaeraHTML(json_decode(stripslashes($_REQUEST['bookingPublicNote'])));
				}
				$bookingStaffNote = '';
				if(isset($_REQUEST['bookingStaffNote'])){
					$bookingStaffNote = RESA_Tools::formatTextaeraHTML(json_decode(stripslashes($_REQUEST['bookingStaffNote'])));
				}
				$bookingCustomerNote = '';
				if(isset($_REQUEST['bookingCustomerNote'])){
					$bookingCustomerNote = RESA_Tools::formatTextaeraHTML(json_decode(stripslashes($_REQUEST['bookingCustomerNote'])));
				}

				//Logger::DEBUG($booking->toJSON());
				$statesParameters = unserialize(get_option('resa_settings_states_parameters'));
				if($booking->isQuotation()){ $typePayment = ''; }
				$advancePaymentSelected = false;
				if(isset($_REQUEST['advancePayment'])){
					$advancePaymentSelected = json_decode(stripslashes(sanitize_text_field($_REQUEST['advancePayment'])));
				}
				if($isNewBooking) {
					if($advancePaymentSelected){
						$booking->setAdvancePayment($booking->calculateTotalPriceForm($statesParameters, true));
					}
					$booking->setTypePaymentChosen($typePayment);
				}
				$booking->setQuotation($quotation);
				if($quotation && $frontForm){
					$booking->setQuotationRequest(true);
				}
				$booking->setAllowInconsistencies($allowInconsistencies);
				$booking->setNote($bookingNote);
				$booking->setPublicNote($bookingPublicNote);
				$booking->setStaffNote($bookingStaffNote);
				$booking->setCustomerNote($bookingCustomerNote);
				$booking->setNew();
				$booking->save(!$frontForm);
				$booking->reload();


				if(($sendMessage || $frontForm) && ($typePayment != 'systempay' && $typePayment != 'paypal' && $typePayment != 'monetico' && $typePayment != 'stripe' && $typePayment != 'stripeConnect' && $typePayment != 'paybox')){
					if(!$booking->isQuotation()){
						RESA_Mailer::sendMessageBooking($booking, true);
						$booking->setAlreadySentEmail(true);
					}
					else {
						RESA_Mailer::sendMessageQuotationCustomer($booking);
					}
				}
				else if($sendMessage && $booking->isQuotation()){
					RESA_Mailer::sendMessageQuotation($booking);
				}


				if($isNewBooking){
					if($frontForm && $booking->isNotQuotation())
						$logNotification = RESA_Algorithms::generateLogNotification(0, $booking, $customer, $currentUser);
					else if($frontForm && $booking->isQuotation())
						$logNotification = RESA_Algorithms::generateLogNotification(1, $booking, $customer, $currentUser);
					else if(!$frontForm && $booking->isNotQuotation())
						$logNotification = RESA_Algorithms::generateLogNotification(2, $booking, $customer, $currentUser);
					else
						$logNotification = RESA_Algorithms::generateLogNotification(3, $booking, $customer, $currentUser);
				}
				else {
					if(!$frontForm && $booking->isNotQuotation())
						$logNotification = RESA_Algorithms::generateLogNotification(4, $booking, $customer, $currentUser);
					else
						$logNotification = RESA_Algorithms::generateLogNotification(5, $booking, $customer, $currentUser);
				}
				if(isset($logNotification))	$logNotification->save();

				//Save participants
				$nbParticipants = 0;
				$booking->generateParticipantsUri($customer->getId());
				$participants = $booking->getParticipants();
				$nbParticipants += count($participants);
				$customer->mergeParticipants($participants);
				if($nbParticipants > 0){
					$booking->clearModificationDate();
					$booking->save(false);
					$booking->reload();
					if(RESA_Variables::isGroupsManagementActivated()){
						foreach($booking->getAppointments() as $appointment){
							RESA_Algorithms::createGroupsIfNecessary($appointment, $booking);
							RESA_Algorithms::addParticipantsInGroups($appointment);
						}
					}
					$customer->save(false);
				}
				$successUrl = get_option('resa_settings_payment_return_url_success');
				if(!get_option('resa_settings_payment_activate')){
					$successUrl = '';
				}
				if($booking->isQuotation()){
					$successUrl = get_option('resa_settings_quotation_return_url_success');
				}

				$informationConfirmationText = unserialize(get_option('resa_settings_form_informations_confirmation_text'));
				if($booking->isQuotation()){
					$informationConfirmationText = unserialize(get_option('resa_settings_form_quotation_informations_confirmation_text'));
				}
				if(!empty($idForm)){
					$form = new RESA_Form();
					$form->loadById(substr($idForm, strlen('form')));
					if($form->isLoaded()){
						$informationConfirmationText = $form->getInformationsConfirmationText();
					}
				}
				$confirmationText = RESA_Mailer::formatMessage($booking, $customer, RESA_Tools::getTextByLocale($informationConfirmationText, get_locale()), true);
				if($frontForm){
					RESA_Session::store('RESA_booking', $booking->getId());
				}

				$payment = '';
				if($typePayment == 'systempay' && class_exists('RESA_Systempay')){
					$payment = RESA_Systempay::systempay($booking, $currentUrl, $advancePaymentSelected, true);
				} else if($typePayment == 'paypal' && class_exists('RESA_Paypal')){
					$payment = RESA_Paypal::paypal($booking, $currentUrl, $advancePaymentSelected, true);
				} else if($typePayment == 'monetico' && class_exists('RESA_MoneticoPayment')){
					$payment = RESA_MoneticoPayment::monetico($booking, $customer, $currentUrl, $advancePaymentSelected, true);
				}else if($typePayment == 'stripe' && class_exists('RESA_Stripe')){
					$payment = RESA_Stripe::stripe($booking, $customer, $currentUrl, $advancePaymentSelected, true);
				}else if($typePayment == 'stripeConnect' && class_exists('RESA_StripeConnect')){
					$payment = RESA_StripeConnect::stripeConnect($booking, $customer, $currentUrl, $advancePaymentSelected, true);
				}else if($typePayment == 'paybox' && class_exists('RESA_Paybox')){
					$payment = RESA_Paybox::paybox($booking, $customer, $currentUrl, $advancePaymentSelected, true);
				}else if($typePayment == 'swikly' && class_exists('RESA_Swikly')){
					$payment = RESA_Swikly::swikly($booking, $customer, $currentUrl);
				}

				//If no payment
				if(get_option('resa_settings_payment_activate') == true && $typePayment != 'onTheSpot' && $typePayment != 'later' && $booking->isNotQuotation() && $booking->stateAskPayment($statesParameters)){
					if(($frontForm || !$this->isAdmin()) && ($payment == '' || !isset($payment) || count($payment) == 0)){
						$booking->changeStatesAfterPayment(unserialize(get_option('resa_settings_states_parameters')));
						$booking->clearModificationDate();
						$booking->save(false);
						RESA_Mailer::sendMessageBooking($booking, true);
					}
				}

				$alerts = RESA_Alert::getAllDataWithDate(
					$booking->getAppointmentFirstDate(),
					$booking->getAppointmentEndDate(),
					array('idBooking' => array(array($booking->getId()))),
					array('idAppointment' => array($booking->getAllIdAppointments()))
				);
				$result = array(
					'customer' => json_decode($customer->toJSON()),
					'booking' => json_decode($booking->toJSON()),
					'alerts' => json_decode(RESA_Tools::formatJSONArray($alerts)),
					'payment'=>$payment,
					'successUrl' => $successUrl,
					'confirmationText' => $confirmationText);
				echo json_encode($result);
			}
			else {
				throw new Exception("Error", 1);
			}
		}catch(RESA_Exception $e){
			Logger::Error($e->getMessage());
			echo $e->toJSON();
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		wp_die();
	}

	public function getReductions(){
		$allReductions = array();
		$mapIdClientObjectReduction = array();
		if(isset($_REQUEST['idCustomer']) && isset($_REQUEST['servicesParameters']) && isset($_REQUEST['idBooking']) && isset($_REQUEST['allowInconsistencies']) && isset($_REQUEST['frontForm'])){
			$servicesParameters = json_decode(stripslashes(wp_kses_post($_REQUEST['servicesParameters'])));
			$idCustomer = json_decode(stripslashes(sanitize_text_field($_REQUEST['idCustomer'])));
			$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
			$allowInconsistencies = json_decode(stripslashes(sanitize_text_field($_REQUEST['allowInconsistencies'])));
			$frontForm = json_decode(stripslashes(sanitize_text_field($_REQUEST['frontForm'])));
			$appointments = array();

			$customer = new RESA_Customer();
			$customer->loadById($idCustomer);
			try{
				$appointments = RESA_Algorithms::createAppointmentsWithServiceParameters($servicesParameters, $customer, true, $idBooking, ($this->isAdmin() && !$frontForm), $customer->getTypeAccount());
			}
			catch(Exception $e){
				Logger::DEBUG($e);
			}
			$couponsList = array();
			if(isset($_REQUEST['couponsList'])) $couponsList = json_decode(stripslashes(wp_kses_post($_REQUEST['couponsList'])));
			$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer,'', ($this->isAdmin() && !$frontForm));
			$allReductions = $result['reductions'];
			$mapIdClientObjectReduction = $result['mapIdClientObjectReduction'];

			foreach($couponsList as $coupon){
				if(!empty($coupon)){
					$result = RESA_Algorithms::getReductionsApplicable($appointments, $customer, $coupon, ($this->isAdmin() && !$frontForm));
					$allReductions = array_merge($allReductions, $result['reductions']);
					foreach($result['mapIdClientObjectReduction'] as $id => $params){
						if(!isset($mapIdClientObjectReduction[$id])){
							$mapIdClientObjectReduction[$id] = [];
						}
						$mapIdClientObjectReduction[$id] = array_merge($mapIdClientObjectReduction[$id], $params);
					}
				}
			}
			//$reductions = RESA_Algorithms::returnReductionsNotCombinables($appointments, $allReductions, $mapIdClientObjectReduction);
		}
		echo '{
			"reductions":'.RESA_Tools::formatJSONArray($allReductions).',
			"mapIdClientObjectReduction":'.json_encode($mapIdClientObjectReduction).'}';
		wp_die();
	}

	/**
	 * accept quotation
	 */
	public function acceptQuotation(){
		try {
			if(isset($_REQUEST['idBooking'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser();
				if($currentUser->getId() == $booking->getIdCustomer() && $booking->isLoaded() && $booking->isQuotation() && !$booking->isQuotationRequest()){
					$oldBooking = new RESA_Booking();
					$oldBooking->loadById($idBooking);
					if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
						if(!$oldBooking->isOldBooking()){
							$oldBooking->setOldBooking(true);
							foreach($oldBooking->getAppointments() as $appointment){
								$appointment->setState('updated');
							}
							$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
							$booking->addLinkOldBookings($oldBooking->getId());
							$booking->setAdvancePayment($oldBooking->getAdvancePayment());
							$booking->setCreationDate($oldBooking->getCreationDate());
							$booking->setIdUserCreator($oldBooking->getIdUserCreator());
							$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
							$booking->setTransactionId($oldBooking->getTransactionId());
							$booking->setIdCreation($oldBooking->getIdCreation());
							$booking->setPaymentState($oldBooking->getPaymentState());
							$booking->setQuotationRequest($oldBooking->isQuotationRequest());
							$booking->setAlreadySentEmail($oldBooking->isAlreadySentEmail());
							$oldBooking->save(false);
						}
						else {
							throw new Exception(__('create_new_version_bookings_error', 'resa'));
						}
					}
					$booking->clearModificationDate();
					$booking->setAlreadySentEmail(false);
					$booking->setQuotation(false);
					$booking->save();
					RESA_Mailer::sentMessageQuotationAnswered($currentUser, $booking);

					$logNotification = RESA_Algorithms::generateLogNotification(6, $booking, $currentUser, $currentUser);
					if(isset($logNotification))	$logNotification->save();

					echo '{
						"response":"success",
						"booking":'.$booking->toJSON().',
						"oldIdBooking":'.$idBooking.'}';
				}
				else {
					echo '{
						"response":"error",
						"message":"'.__('Error_word','resa').'",
						"idBooking":'.$idBooking.'}';
				}
			}
			else {
				throw new Exception("");
			}
		}catch(Exception $e){
			echo '{
				"response":"error",
				"message":"'.__('Error_word','resa').$e->getMessage().'",
				"idBooking":'.$idBooking.'}';
		}
		wp_die();
	}

	/**
	 * refuse quotation
	 */
	public function refuseQuotation(){
		try {
			if(isset($_REQUEST['idBooking'])){
				$idBooking = json_decode(stripslashes($_REQUEST['idBooking']));
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser();
				if($currentUser->getId() == $booking->getIdCustomer() && $booking->isLoaded() && $booking->isQuotation() && !$booking->isQuotationRequest()){
					$oldBooking = new RESA_Booking();
					$oldBooking->loadById($idBooking);
					if($oldBooking->isLoaded() && $oldBooking->getIdCustomer() == $booking->getIdCustomer()){
						if(!$oldBooking->isOldBooking()){
							$oldBooking->setOldBooking(true);
							foreach($oldBooking->getAppointments() as $appointment){
								$appointment->setState('updated');
							}
							$booking->setLinkOldBookings($oldBooking->getLinkOldBookings());
							$booking->addLinkOldBookings($oldBooking->getId());
							$booking->setAdvancePayment($oldBooking->getAdvancePayment());
							$booking->setCreationDate($oldBooking->getCreationDate());
							$booking->setIdUserCreator($oldBooking->getIdUserCreator());
							$booking->setTypePaymentChosen($oldBooking->getTypePaymentChosen());
							$booking->setTransactionId($oldBooking->getTransactionId());
							$booking->setIdCreation($oldBooking->getIdCreation());
							$booking->setPaymentState($oldBooking->getPaymentState());
							$booking->setQuotation($oldBooking->isQuotation());
							$booking->setQuotationRequest($oldBooking->isQuotationRequest());
							$booking->setAlreadySentEmail($oldBooking->isAlreadySentEmail());
							$oldBooking->save(false);
						}
						else {
							throw new Exception(__('create_new_version_bookings_error', 'resa'));
						}
					}
					$booking->clearModificationDate();
					$booking->cancelBooking();
					$booking->save();

					$logNotification = RESA_Algorithms::generateLogNotification(12, $booking, $currentUser, $currentUser);
					if(isset($logNotification))	$logNotification->save();

					echo '{
						"response":"success",
						"booking":'.$booking->toJSON().',
						"oldIdBooking":'.$idBooking.'}';
				}
				else {
					echo '{
						"response":"error",
						"message":"'.__('Error_word','resa').'",
						"idBooking":'.$idBooking.'}';
				}
			}
			else {
				throw new Exception('');

			}
		}catch(Exception $e){
			echo '{
				"response":"error",
				"message":"'.__('Error_word','resa').$e->getMessage().'",
				"idBooking":'.$idBooking.'}';
		}
		wp_die();
	}

	/**
	 * modify customer
	 */
	public function modifyCustomer(){
		try{
			if(isset($_REQUEST['changePassword']) && isset($_REQUEST['customer'])){
				$changePassword = stripslashes(sanitize_text_field($_REQUEST['changePassword']));
				$customerJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['customer'])));
				$currentUser = new RESA_Customer();
				$currentUser->loadCurrentUser();
				if($currentUser->isLoaded()){
					$phoneAlreadyExist = RESA_Customer::phoneAlreadyExist($customerJSON->phone, $currentUser->getId());
					if($phoneAlreadyExist) throw new Exception(__('phone_already_exist_error', 'resa'));
					$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
					if((!username_exists($customerJSON->login) || username_exists($customerJSON->login)==$currentUser->getId()) &&
					(!email_exists($customerJSON->email) || email_exists($customerJSON->email)==$currentUser->getId())){
						$customerJSON->login = $currentUser->getLogin();
						$customerJSON->email = $currentUser->getEmail();
						$customerJSON->privateNotes = $currentUser->getPrivateNotes();
						$currentUser->fromJSON($customerJSON);
						$currentUser->save();
						if($changePassword && isset($customerJSON->password) && RESA_Customer::isOkPasswordFormat($customerJSON->password)) {
							$currentUser->setPasswordInDB(wp_hash_password($customerJSON->password));
						}
						else if(isset($customerJSON->password) && !RESA_Customer::isOkPasswordFormat($customerJSON->password)) {
							throw new Exception(__('bad_password_error', 'resa'));
						}
						echo '{
							"response":"success",
							"customer":'.$currentUser->toJSON().'}';
					}
					else if(email_exists($customerJSON->email)) throw new Exception(__('email_already_exist_error', 'resa'));
					else throw new Exception(__('login_already_exist_error', 'resa'));
				}
				else throw new Exception(__('Error_word', 'resa'));
			}
			else {
				echo '{
					"response":"error"
				}';
			}
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo '{
				"response":"error",
				"error":"'.$e->getMessage().'"
			}';
		}
		wp_die();
	}

	/**
	 * do action of user connection
	 */
	public function userConnection(){
		if(isset($_REQUEST['login']) && isset($_REQUEST['password'])){
			$login = json_decode(stripslashes(sanitize_text_field($_REQUEST['login'])));
			$password = json_decode(stripslashes(sanitize_text_field($_REQUEST['password'])));
			$customer = RESA_Customer::authenticate($login, $password);
			if(isset($customer)){
				if($customer->isLoaded()){
					echo $customer->toJSON();
				}
				else {
					echo json_encode(__('authentication_error','resa'));
				}
			}
			else echo json_encode(__('authentication_error','resa'));
		}
		else echo json_encode(__('authentication_error','resa'));
		wp_die();
	}

	/**
	 * do action of user connection
	 */
	public function userDeconnection(){
		wp_logout();
		$customer = new RESA_Customer();
		echo $customer->toJSON();
		wp_die();
	}


	public function getBill(){
		if(isset($_REQUEST['idBooking'])){
			$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
			if($idBooking >= 0){
				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				echo RESA_Algorithms::generateHTMLBill($booking, false);
			}
			else echo __('no_bill_error', 'resa');
		}
		else echo __('no_bill_error', 'resa');
		wp_die();
	}

	public function payBooking(){
		Logger::INFO('begin');
		try {
			if(isset($_REQUEST['idBooking']) && isset($_REQUEST['typePayment']) && isset($_REQUEST['currentUrl']) && isset($_REQUEST['advancePayment'])){
				$idBooking = json_decode(stripslashes(sanitize_text_field($_REQUEST['idBooking'])));
				$typePayment = json_decode(stripslashes(sanitize_text_field($_REQUEST['typePayment'])));
				$currentUrl = json_decode(stripslashes(esc_url_raw($_REQUEST['currentUrl'])));
				$advancePaymentSelected = json_decode(stripslashes(sanitize_text_field($_REQUEST['advancePayment'])));


				$ifOkCaisseOnline = class_exists('RESA_CaisseOnline') && RESA_CaisseOnline::getInstance()->isCaisseOnlineStatusOk();
				$ifOkStripeConnect = (class_exists('RESA_StripeConnect') && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::checkConnection():false;
				if((RESA_Variables::isTypePaymentOnline($typePayment) && ($typePayment!='swikly')  && ($typePayment != 'stripeConnect' || !$ifOkStripeConnect) && ($typePayment=='swikly' || $typePayment=='stripeConnect' || !$ifOkCaisseOnline))){
					throw new Exception(__('payment_online_error', 'resa'));
				}

				$booking = new RESA_Booking();
				$booking->loadById($idBooking);
				if($booking->isLoaded()){
					$customer = $booking->getCustomer();
					if(!RESA_Customer::isPaymentTypeAuthorized($customer->getTypeAccount(), $typePayment, unserialize(get_option('resa_settings_types_accounts')))){
							throw new Exception(__('type_payment_unauthorized_error', 'resa'));
					}
					$payment = '';
					if($advancePaymentSelected){
						$booking->setAdvancePayment($booking->calculateAdvancePayment());
					}
					if($typePayment == 'transfer'){
						$payment = array('type'=>'transfer', 'text' => unserialize(get_option('resa_settings_payment_transfer_text')));
					}
					else if($typePayment == 'cheque'){
						$payment = array('type'=>'cheque', 'text' => unserialize(get_option('resa_settings_payment_cheque_text')));
					}
					else if($typePayment == 'systempay' && class_exists('RESA_Systempay')){
						$payment = RESA_Systempay::systempay($booking, $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'paypal' && class_exists('RESA_Paypal')){
						$payment = RESA_Paypal::paypal($booking, $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'monetico' && class_exists('RESA_MoneticoPayment')){
						$payment = RESA_MoneticoPayment::monetico($booking, $booking->getCustomer(), $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'stripe' && class_exists('RESA_Stripe')){
						$payment = RESA_Stripe::stripe($booking, $booking->getCustomer(), $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'stripeConnect' && class_exists('RESA_StripeConnect')){
						$payment = RESA_StripeConnect::stripeConnect($booking, $booking->getCustomer(), $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'paybox' && class_exists('RESA_Paybox')){
						$payment = RESA_Paybox::paybox($booking, $booking->getCustomer(), $currentUrl, $advancePaymentSelected, false);
					}
					else if($typePayment == 'swikly' && class_exists('RESA_Swikly')){
						$payment = RESA_Swikly::swikly($booking, $booking->getCustomer(), $currentUrl);
					}
					$booking->save();
					$result = array('payment'=>$payment);
					echo json_encode($result);
				}
				else throw new Exception('booking error');
			}
			else throw new Exception('booking error');
		} catch(Exception $e){
			Logger::Error($e->getMessage());
			echo json_encode(__('Error_word','resa').$e->getMessage());
		}
		wp_die();
	}

	public function forgottenPasswordCustomer(){
		Logger::INFO('begin');
		try{
			if(isset($_REQUEST['email'])){
				$email = stripslashes(sanitize_email($_REQUEST['email']));
				if(!empty($email)){
					$customer = new RESA_Customer();
					$customer->loadByEmail($email);
					if($customer->isLoaded()){
						$result = RESA_Mailer::sentMessagePasswordReinitialization($customer);
						if(!$result){
							throw new Exception(__('Error_word','resa'));
						}
					}
					else throw new Exception(__('customer_not_found_words','resa'));
				}
				else throw new Exception(__('customer_not_found_words','resa'));
				echo '{
					"status":"success",
					"message":"Ok"
				}';
			}
			else throw new Exception(__('customer_not_found_words','resa'));
		}catch(Exception $e){
			Logger::Error($e->getMessage());
			echo  '{
				"status":"error",
				"message":"'._('Error_word','resa').$e->getMessage().'"
			}';
		}
		wp_die();
	}

	public function askAccountRequest(){
		Logger::INFO('begin');
		try{
			if(isset($_REQUEST['customer'])){
				$customer = new RESA_Customer();
				$customerJSON = json_decode(stripslashes(wp_kses_post($_REQUEST['customer'])));
				if(isset($customerJSON) && $customerJSON->ID == -1){
					$phoneAlreadyExist = RESA_Customer::phoneAlreadyExist($customerJSON->phone);
					$customerJSON->login = RESA_Tools::generateLogin($customerJSON->firstName, $customerJSON->lastName, $customerJSON->email);
					if(!username_exists($customerJSON->login) && !email_exists($customerJSON->email) && !$phoneAlreadyExist && (RESA_Customer::isOkPasswordFormat($customerJSON->password) || (isset($customerJSON->idFacebook) && !empty($customerJSON->idFacebook) &&
					class_exists('RESA_FacebookLogin') && get_option('resa_facebook_activated', false) && RESA_FacebookLogin::isOkCustomer($customerJSON)))){
						$customer->fromJSON($customerJSON);
						$customer->setAskAccount(true);
						$customer->save(false);

						RESA_Mailer::sendMessageAskAccount($customer);
						$logNotification = RESA_Algorithms::generateLogNotification(19, new RESA_Booking(), $customer, $customer);
						if(isset($logNotification))	$logNotification->save();
					}
					else if($phoneAlreadyExist) throw new Exception(__('phone_already_exist_error', 'resa'));
					else if(email_exists($customerJSON->email)) throw new Exception(__('email_already_exist_error', 'resa'));
					else if(!RESA_Customer::isOkPasswordFormat($customerJSON->password)) throw new Exception(__('bad_password_error', 'resa'));
					else throw new Exception(__('login_already_exist_error', 'resa'));
					echo '{
						"status":"success",
						"message":"Ok"
					}';
				}
				else throw new Exception(__('register_error', 'resa'));
			}
			else throw new Exception(__('register_error', 'resa'));
		}
		catch(Exception $e){
			Logger::Error($e->getMessage());
			echo  '{
				"status":"error",
				"message":"'.__('Error_word','resa').$e->getMessage().'"
			}';
		}
		wp_die();
	}
}
