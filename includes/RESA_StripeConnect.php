<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Routes
add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'stripeConnect/validPayment', array(
    'methods' => 'POST',
    'callback' => array('RESA_StripeConnect', 'validPayment'),
  ));
  register_rest_route('resa/v1', 'stripeConnect/refundPayment/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_StripeConnect', 'refundPayment'),
  ));
  register_rest_route('resa/v1', 'stripeConnect/deauthorize/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_StripeConnect', 'deauthorizeAction'),
  ));
});


/**
 * RESA Stripe
 */
abstract class RESA_StripeConnect
{
  private static $_URL = 'https://resa-online.pro/resa-stripe/';
  private static $_Headers = array('Content-Type' => 'application/json; charset=utf-8');

  public static function getURL(){ return self::$_URL; }

  /**
   * Get OAuth Link !
   */
  public static function getOAuthLink(){
    $result = '';
    $response = wp_remote_get(self::$_URL . 'oauthLink.php', array(
      'headers' => self::$_Headers,
    ));
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    } else {
      $jsonBody = json_decode($response['body']);
      $jsonResponse = $response['response'];
      if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
        $result = $jsonBody->link;
      }
      else if(isset($jsonResponse) && isset($jsonBody)){
        if(isset($jsonBody->message)) Logger::ERROR('Error to get oauthLink resa-stripe : ' . $jsonBody->message);
        if(isset($jsonBody->error)) Logger::ERROR('Error to get oauthLink resa-stripe : ' . $jsonBody->error);
      }
      else {
        Logger::ERROR('Error to get oauthLink resa-stripe : no response');
      }
    }
    return $result;
  }

  /**
   *
   */
  public static function checkConnection(){
    $result = false;
    $stripeConnectId = get_option('resa_settings_payment_stripe_connect_id', '');
    if(!empty($stripeConnectId)){
      $response = wp_remote_post(self::$_URL . 'checkAccount.php' , array(
        'headers' => self::$_Headers,
        'method' => 'POST',
        'timeout' => 5,
      	'body' => json_encode(array(
          'stripeConnectId' => $stripeConnectId,
          'siteUrl' => site_url()
        ))
      ));
      if ( is_wp_error( $response ) ) {
         $error_message = $response->get_error_message();
      } else {
        $jsonBody = json_decode($response['body']);
        $jsonResponse = $response['response'];
        //Logger::DEBUG(print_r($response['body'], true), true);
        if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
          $result = $jsonBody->id == $stripeConnectId;
        }
        else if(isset($jsonResponse) && isset($jsonBody)){
          if(isset($jsonBody->message)) Logger::ERROR('Error to checkConnection resa-stripe : ' . $jsonBody->message);
          if(isset($jsonBody->error)) Logger::ERROR('Error to checkConnection resa-stripe : ' . $jsonBody->error);
        }
        else {
          Logger::ERROR('Error to checkConnection resa-stripe : no response');
        }
      }
    }
    return $result;
  }

  /**
   *
   */
  public static function deauthorize(){
    $result = false;
    $stripeConnectId = get_option('resa_settings_payment_stripe_connect_id', '');
    $response = wp_remote_post(self::$_URL . 'deauthorize.php' , array(
      'headers' => self::$_Headers,
      'method' => 'POST',
      'timeout' => 5,
    	'body' => json_encode(array(
        'stripeConnectId' => $stripeConnectId,
        'siteUrl' => site_url()
      ))
    ));
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    } else {
      $jsonBody = json_decode($response['body']);
      $jsonResponse = $response['response'];
      if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
        $result = $jsonBody;
      }
      else if(isset($jsonResponse) && isset($jsonBody)){
        if(isset($jsonBody->message)) Logger::ERROR('Error to checkConnection resa-stripe : ' . $jsonBody->message);
        if(isset($jsonBody->error)) Logger::ERROR('Error to checkConnection resa-stripe : ' . $jsonBody->error);
      }
      else {
        Logger::ERROR('Error to checkConnection resa-stripe : no response');
      }
    }
    return $result;
  }

  /**
   *
   */
  public static function getAlreadyPaid($transactionId){
    $result = 0;
    $stripeConnectId = get_option('resa_settings_payment_stripe_connect_id', '');
    $response = wp_remote_post(self::$_URL . 'getCharges.php' , array(
      'headers' => self::$_Headers,
      'method' => 'POST',
      'timeout' => 5,
    	'body' => json_encode(array(
        'stripeConnectId' => $stripeConnectId,
        'transactionId' => $transactionId
      ))
    ));
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    } else {
      $jsonBody = json_decode($response['body']);
      $jsonResponse = $response['response'];
      if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
        $charges = $jsonBody;
        foreach ($charges as $payment) {
          foreach ($payment as $data) {
            if(!$data->refunded){
              $result += $data->amount/100;
              foreach ($data->refunds->data as $refund) {
                $result -= $refund->amount/100;
              }
            }
          }
        }
      }
      else if(isset($jsonResponse) && isset($jsonBody)){
        if(isset($jsonBody->message)) Logger::ERROR('Error to getAlreadyPaid resa-stripe : ' . $jsonBody->message);
        if(isset($jsonBody->error)) Logger::ERROR('Error to getAlreadyPaid resa-stripe : ' . $jsonBody->error);
      }
      else {
        Logger::ERROR('Error to getAlreadyPaid resa-stripe : no response');
      }
    }
    return $result;
  }

  /**
   *
   */
  public static function createPaymentIntent($idBooking, $amount, $customer){
    $result = false;
    $stripeConnectId = get_option('resa_settings_payment_stripe_connect_id', '');
    $response = wp_remote_post(self::$_URL . 'newPaymentIntent.php' , array(
      'headers' => self::$_Headers,
      'method' => 'POST',
      'timeout' => 5,
    	'body' => json_encode(array(
        'stripeConnectId' => $stripeConnectId,
        'amount' => $amount,
        'idBooking' => $idBooking,
        'customer' => json_decode($customer->toJSON())
      ))
    ));
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    } else {
      Logger::DEBUG(print_r($response['response'], true));
      Logger::DEBUG(print_r($response['body'], true));
      $jsonBody = json_decode($response['body']);
      $jsonResponse = $response['response'];
      if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
        $result = $jsonBody;
      }
      else if(isset($jsonResponse) && isset($jsonBody)){
        if(isset($jsonBody->message)) Logger::ERROR('Error to createPaymentIntent resa-stripe : ' . $jsonBody->message);
        if(isset($jsonBody->error)) Logger::ERROR('Error to createPaymentIntent resa-stripe : ' . $jsonBody->error);
      }
      else {
        Logger::ERROR('Error to createPaymentIntent resa-stripe : no response');
      }
    }
    return $result;
  }


  /**
   *
   */
  public static function refundPaymentIntent($transactionId, $reason, $amount){
    $result = false;
    $stripeConnectId = get_option('resa_settings_payment_stripe_connect_id', '');
    $response = wp_remote_post(self::$_URL . 'cancelCharges.php' , array(
      'headers' => self::$_Headers,
      'method' => 'POST',
      'timeout' => 5,
    	'body' => json_encode(array(
        'stripeConnectId' => $stripeConnectId,
        'transactionId' => $transactionId,
        'reason' => $reason,
        'amount' => $amount
      ))
    ));
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    } else {
      Logger::DEBUG(print_r($response['response'], true));
      Logger::DEBUG(print_r($response['body'], true));
      $jsonBody = json_decode($response['body']);
      $jsonResponse = $response['response'];
      if(isset($jsonResponse) && isset($jsonResponse['code']) && $jsonResponse['code'] == 200){
        $result = $jsonBody;
      }
      else if(isset($jsonResponse) && isset($jsonBody)){
        if(isset($jsonBody->message)) Logger::ERROR('Error to refundPaymentIntent resa-stripe : ' . $jsonBody->message);
        if(isset($jsonBody->error)) Logger::ERROR('Error to refundPaymentIntent resa-stripe : ' . $jsonBody->error);
      }
      else {
        Logger::ERROR('Error to refundPaymentIntent resa-stripe : no response');
      }
    }
    return $result;
  }



  /**
	 * \fn stripe
	 * \brief return the data to stripe form.
	 * \param the associated booking
	 * \return the data.
	 */
	public static function stripeConnect(RESA_Booking $booking,  RESA_Customer $customer, $currentUrl, $isAdvancePayment, $isForm){
		$data = '';
		$totalPrice = $booking->getNeedToPay();
		if($isForm){
			$statesParameters = unserialize(get_option('resa_settings_states_parameters'));
			$totalPrice = $booking->getTotalPriceWithAllParams($isAdvancePayment, $isForm, $statesParameters);
		}
		else if($isAdvancePayment){
			$totalPrice = $booking->calculateAdvancePayment();
		}
		else {
			$totalPrice = $booking->getNeedToPay() - self::getAlreadyPaid($booking->getTransactionId());
		}
		if($totalPrice > 0){
			//$vads_public_key = get_option( 'resa_settings_payment_stripe_public_key');
			$vads_local = !empty($customer->getLocale())?$customer->getLocale():get_locale();
			$vads_amount = $totalPrice * 100;
      $urlSuccess = $currentUrl;
      $urlError = $currentUrl;
      if(get_option('resa_settings_payment_return_url_success')!=''){
        $urlSuccess = get_option('resa_settings_payment_return_url_success');
        //$urlIPN = get_option('resa_settings_payment_return_url_success');
      }
      if(get_option('resa_settings_payment_return_url_error')!=''){
        $urlError = get_option('resa_settings_payment_return_url_error');
      }
      $textLink = '?';
      if(!is_bool(strpos($urlError, '?'))){
        $textLink = '&';
      }
      $vads_url_cancel = $urlError . $textLink . 'type=cancel';
      $vads_url_error = $urlError . $textLink . 'type=error';
      $vads_url_refused =  $urlError . $textLink . 'type=refused';
      $vads_url_success =  $urlSuccess;

      $result = self::createPaymentIntent($booking->getIdCreation(), $vads_amount, $customer);
      if($result !== false){
        if(isset($result->customer) && $customer->getIdStripe() != $result->customer){
          $customer->setIdStripe($result->customer);
          $customer->save();
        }
        $booking->setTypePaymentChosen('stripeConnect');
        $booking->addTransactionId($result->id);
        $booking->save(false);

  			//$url = RESA_CaisseOnline::getInstance()->getBaseURL() . 'stripeIPN';
        $redirectURL =  $urlSuccess;
  			$data = array(
          'type'=>'stripeConnect',
  				/*'url' => $url,*/
          'validationUrl' => site_url('/wp-json/resa/v1/stripeConnect/validPayment'),
          'redirectURL' => $redirectURL,
          'clientSecret' => $result->client_secret,
          'stripeConnectId' => get_option('resa_settings_payment_stripe_connect_id', ''),
          'pkKey' => $result->pkKey,
  				'idBooking' => $booking->getIdCreation(),
  				'externalId' => $customer->getId(),
  				'vads_local'=>$vads_local,
  				'vads_amount'=>$vads_amount,
  				'vads_url_return' => $currentUrl,
  				'vads_url_ok' => $vads_url_success,
  				'vads_url_error' => $vads_url_refused,
  				'words' => array(
            'currency' => get_option('resa_settings_payment_currency'),
            'title' => RESA_Tools::getTextByLocale(unserialize(get_option('resa_settings_payment_stripe_title')), get_locale()),
            'description' => __('stripe_description_sentence', 'resa'),
  					'company_name' => get_option('resa_settings_company_name'),
  					'payment_word' => __('Payment_word', 'resa'),
  					'cancel_word' => __('cancel_link_title', 'resa'),
  					'Redirection_word_form' => __('Redirection_2_word_form', 'resa'),
            'update_booking_word' => __('update_booking_word', 'resa'),
  					'ask_stop_payment_title_dialog' => __('ask_stop_payment_title_dialog', 'resa'),
  					'ask_stop_payment_text_dialog' => __('ask_stop_payment_text_dialog', 'resa'),
  					'ask_stop_payment_confirmButton_dialog' => __('ask_stop_payment_confirmButton_dialog', 'resa'),
  					'ask_stop_payment_cancelButton_dialog' => __('ask_stop_payment_cancelButton_dialog', 'resa'),
            'Amount_to_be_paid_words' => __('Amount_to_be_paid_words', 'resa'),
  				)
  			);
      }
      else {
        $data = array(
          'type'=>'stripeConnect',
  				'vads_url_return' => $currentUrl,
  				'vads_url_ok' => $vads_url_success,
  				'vads_url_error' => $vads_url_refused,
        );
      }
		}
		return $data;
	}

  /**
   *
   */
  public static function validPayment(WP_REST_Request $request){
		$data = $request->get_json_params();
    $response = new WP_REST_Response(array());
		if(isset($data['pid']) && isset($data['stripeConnectId'])){
      $amount = $data['amount'];
      $booking = new RESA_Booking();
      $booking->loadByTransactionId($data['pid']);
      if($booking->isLoaded()){
        $stripeModePaymentState = get_option('resa_settings_payment_stripe_mode_payment_state', 0);
        $alreadyPaid = self::getAlreadyPaid($booking->getTransactionId());
        if($stripeModePaymentState == 0) $booking->setPaymentState('complete');
        else if($stripeModePaymentState == 1){
          if($alreadyPaid == $booking->getTotalPrice()) $booking->setPaymentState('complete');
          else $booking->setPaymentState('advancePayment');
        }
        else $booking->setPaymentState('advancePayment');
        $booking->changeStatesAfterPayment(unserialize(get_option('resa_settings_states_parameters')), true);
        $booking->save(false);
        $customer = $booking->getCustomer();
        RESA_AskPayment::setExpiredToTrue($booking);
        RESA_Mailer::sentMessageCustomerPaymentBooking($customer, $booking, $amount);
        if($alreadyPaid == 0 && $booking->isOnline()) {
          RESA_Mailer::sendMessageBooking($booking, true);
        }
        $logNotification = RESA_Algorithms::generateLogNotification(9, $booking, $customer, $customer);
        $logNotification->replaceVendor('StripeConnect');
        if(isset($logNotification))	$logNotification->save();
        $response->set_data(array());
      }
  		else {
  			$response->set_status(404);
  			$response->set_data(array('error' => 'booking', 'message' => 'Booking'));
  		}
		}
		else {
			$response->set_status(401);
			$response->set_data(array('error' => 'bad_params', 'message' => 'Bad params'));
		}
		return $response;
	}


  /**
   *
   */
  public static function refundPayment(WP_REST_Request $request){
    $currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
  		$data = $request->get_json_params();
      $response = new WP_REST_Response(array());
  		if(isset($data['idBooking']) && isset($data['chargeId'])){
        $chargeId = $data['chargeId'];
        $idBooking = $data['idBooking'];
        $reason = $data['reason'];
        $amount = $data['amount'];

        $booking = new RESA_Booking();
        $booking->loadByLastIdCreation($idBooking);
        if($booking->isLoaded()){
          $result = self::refundPaymentIntent($chargeId, $reason, $amount * 100);
          $stripeModePaymentState = get_option('resa_settings_payment_stripe_mode_payment_state', 0);
          $alreadyPaid = self::getAlreadyPaid($booking->getTransactionId());
          if($alreadyPaid == 0){
            $booking->setPaymentState('noPayment');
          }
          else if($stripeModePaymentState == 1){
            if($alreadyPaid == $booking->getTotalPrice()) $booking->setPaymentState('complete');
            else $booking->setPaymentState('advancePayment');
          }
          $customer = $booking->getCustomer();
          $logNotification = RESA_Algorithms::generateLogNotification(24, $booking, $customer, $currentRESAUser);
          $logNotification->replaceVendor('StripeConnect');
          if(isset($logNotification))	$logNotification->save();
          $booking->save(false);
          $response->set_data(array(
            'idBooking' => $idBooking,
            'paymentState' => $booking->getPaymentState(),
            'idPaymentState' => RESA_Variables::calculateBookingPayment($booking->getStatus(), $booking->getPaymentState())
          ));
        }
    		else {
    			$response->set_status(404);
    			$response->set_data(array('error' => 'booking', 'message' => 'Booking'));
    		}
  		}
  		else {
  			$response->set_status(401);
  			$response->set_data(array('error' => 'bad_params', 'message' => 'Bad params'));
  		}
    }
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}


  /**
   *
   */
  public static function deauthorizeAction(WP_REST_Request $request){
    $currentRESAUser = RESA_Middleware::getUserByToken($request['token']);
		$response = new WP_REST_Response(array());
		if($currentRESAUser->isLoaded() && $currentRESAUser->canEditParameters()){
  		$data = $request->get_json_params();
      $response = new WP_REST_Response(array());
      $result = self::deauthorize();
      if($result !== false){
        $response->set_data(array('stripeConnect' => array(
					'activated' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage() && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::checkConnection():false,
					'stripeConnectId' =>get_option('resa_settings_payment_stripe_connect_id'),
					'apiStripeConnectUrl' => (class_exists('RESA_StripeConnect') && $currentRESAUser->canManage() && get_option('resa_settings_payment_stripe_connect', 0))?RESA_StripeConnect::getURL():'',
				)));
      }
      else {
        $response->set_status(404);
        $response->set_data(array('error' => 'booking', 'message' => 'Booking'));
      }
    }
		else {
			$response->set_status(201);
			$response->set_data(array('error' => 'bad_token', 'message' => 'Bad token'));
		}
		return $response;
	}
}
?>
