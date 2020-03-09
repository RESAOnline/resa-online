<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class RESA_Crons
{

  private static $_instance = null;

  public static function getInstance()
  {
    if(is_null(self::$_instance)) {
      self::$_instance = new RESA_Crons();
    }
    return self::$_instance;
  }

  public static function registerScheduleEvents(){
    add_filter( 'cron_schedules', 'resa_cron_add_10min' );
    function resa_cron_add_10min( $schedules ) {
       $schedules['10min'] = array(
           'interval' => 600,
           'display' => __( 'Once every ten minutes' )
       );
       return $schedules;
    }
    if (!wp_next_scheduled ('resa_check_timeout_bookings')) {
      wp_schedule_event(time(), '10min', 'resa_check_timeout_bookings');
    }
    if (!wp_next_scheduled ('resa_check_emails_event')) {
      wp_schedule_event(time(), '10min', 'resa_check_emails_event');
    }
    if (!wp_next_scheduled ('resa_notification_booking_event')) {
      wp_schedule_event(time(), 'hourly', 'resa_notification_booking_event');
    }
    if (!wp_next_scheduled ('resa_notification_payment_expired_event')) {
      wp_schedule_event(time(), 'hourly', 'resa_notification_payment_expired_event');
    }
    if (!wp_next_scheduled ('resa_check_no_response_quotation_event')) {
      wp_schedule_event(time(), 'twicedaily', 'resa_check_no_response_quotation_event');
    }
    add_action('resa_check_timeout_bookings', array(RESA_Crons::getInstance(), 'timeoutBookings'));
    add_action('resa_check_emails_event', array(RESA_ImapManager::getInstance(), 'processEmails'));
    add_action('resa_notification_booking_event', array(RESA_Crons::getInstance(), 'checkNotificationsBooking'));
    add_action('resa_notification_payment_expired_event', array(RESA_Crons::getInstance(), 'checkNotificationPaymentExpired'));
    add_action('resa_check_no_response_quotation_event', array(RESA_Crons::getInstance(), 'checkNotificationNoResponseQuotation'));
  }

  public static function clearRESACheckEmailsEvent(){
    wp_unschedule_event(wp_next_scheduled( 'resa_check_emails_event' ), 'resa_check_emails_event');
    wp_clear_scheduled_hook('resa_check_emails_event');
  }


  public function timeoutBookings(){
    $statesParameters = unserialize(get_option('resa_settings_states_parameters'));
    $idsParameters = array();
    for($i = 0; $i < count($statesParameters); $i++){
      $statesParameter = $statesParameters[$i];
      if($statesParameter->paiement && $statesParameter->expired){
        $idBookings = RESA_Appointment::getAppointmentsExpired($statesParameter);
        foreach($idBookings as $idBooking){
          $booking = new RESA_Booking();
          $booking->loadById($idBooking);
          if($booking->isLoaded()){
      			$customer = $booking->getCustomer();
      			$logNotification = RESA_Algorithms::generateLogNotification(22, $booking, $customer, $customer);
      			if(isset($logNotification))	$logNotification->save();
          }
        }
      }
    }
  }

  /**
   * check notification booking (same as name of function ok)
   */
  public function checkNotificationsBooking(){
    $this->checkNotificationBeforeBooking();
    $this->checkNotificationAfterBooking();
  }

  public function checkNotificationBeforeBooking(){
    Logger::DEBUG('begin');
    $beforeAppointmentDays = 2;
    if(is_string(get_option('resa_settings_notifications_email_notification_before_appointment_days'))){
      $beforeAppointmentDays = intval(get_option('resa_settings_notifications_email_notification_before_appointment_days'));
    }
    $startDate = new DateTime();
    $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $startDate->format('Y-m-d') . ' 23:59:59');
    $startDate->add(new DateInterval('P'.($beforeAppointmentDays).'D'));

    $currentDate = new DateTime();
    $array = array('startDate' => array('AND', array(array($startDate->format('Y-m-d H:i:s'), '<='), array($currentDate->format('Y-m-d H:i:s'), '>='))), 'state' => 'ok', 'beforeAppointmentNotified' => 0);
    $appointments = RESA_Appointment::getAllDataWithLimit($array, 5);
    foreach($appointments as $appointment){
      $booking = new RESA_Booking();
			$booking->loadById($appointment->getIdBooking());
      $appointment->setBeforeAppointmentNotified(true);
      if($booking->isLoaded() && $booking->isFirstAppointment($appointment)){
        RESA_Mailer::sendMessageBeforeAppointment($booking);
      }
      $appointment->save(false);
    }
  }

  public function checkNotificationAfterBooking(){
    Logger::DEBUG('begin');
    $afterAppointmentDays = 5;
    if(is_string(get_option('resa_settings_notifications_email_notification_after_appointment_days'))){
      $afterAppointmentDays = intval(get_option('resa_settings_notifications_email_notification_after_appointment_days'));
    }
    $endDate = new DateTime();
    $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $endDate->format('Y-m-d') . ' 23:59:59');
    $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $endDate->format('Y-m-d') . ' 00:00:00');
    $startDate->sub(new DateInterval('P'.($afterAppointmentDays+2).'D'));
		$endDate->sub(new DateInterval('P'.$afterAppointmentDays.'D'));
    $array = array('startDate' => array($startDate->format('Y-m-d H:i:s'), '>='),
                  'endDate' => array($endDate->format('Y-m-d H:i:s'), '<='),
                  'state' => 'ok',
                  'afterAppointmentNotified' => 0);
    $appointments = RESA_Appointment::getAllDataWithLimit($array, 5);
    foreach($appointments as $appointment){
      $booking = new RESA_Booking();
			$booking->loadById($appointment->getIdBooking());
      $appointment->setAfterAppointmentNotified(true);
      if($booking->isLoaded() && $booking->isLastAppointment($appointment)){
        RESA_Mailer::sendMessageAfterAppointment($booking);
      }
      $appointment->save(false);
    }
  }


  public function checkNotificationPaymentExpired(){
    $allAskPayments = RESA_AskPayment::getAllDataExpiredButNotNotified();
    foreach($allAskPayments as $askPayment){
      $booking = new RESA_Booking();
      $booking->loadById($askPayment->getIdBooking());
      if($booking->isLoaded()){
        $booking->changeExpirationState(unserialize(get_option('resa_settings_states_parameters')));
        $booking->clearModificationDate();
        $booking->save(false);

        $customer = $booking->getCustomer();
        $logNotification = RESA_Algorithms::generateLogNotification(21, $booking, $customer, $customer);
        if(isset($logNotification))	$logNotification->save();
        $askPayment->setExpired(true);
        $askPayment->save();
      }
    }
  }

  public function checkNotificationNoResponseQuotation(){
    $noResponseQuotationDays = 5;
    if(is_string(get_option('resa_settings_notifications_email_notification_no_response_quotation_days'))){
      $noResponseQuotationDays = intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_days'));
    }
    $noResponseQuotationTimes = 1;
    if(is_string(get_option('resa_settings_notifications_email_notification_no_response_quotation_times'))){
      $noResponseQuotationTimes = intval(get_option('resa_settings_notifications_email_notification_no_response_quotation_times'));
    }
    $endDate = new DateTime();
    $lastDateSentEmailQuotation = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', current_time('timestamp')));
		$lastDateSentEmailQuotation->sub(new DateInterval('P'.$noResponseQuotationDays.'D'));
    $array = array('lastDateSentEmailQuotation' => array($lastDateSentEmailQuotation->format('Y-m-d H:i:s'), '<='),
                    'quotation' => 1,
                    'quotationRequest' => 0,
                    'oldBooking' => false);
    $bookings = RESA_Booking::getAllData($array);
    foreach($bookings as $booking){
      if(!$booking->isCancelled() && !$booking->isAbandonned() && !$booking->isDatePassed() && !$booking->isMaxNumberSentEmailQuotation($noResponseQuotationTimes)){
        RESA_Mailer::sendMessageNoResponseQuotation($booking);
        $booking->clearLastDateSentEmailQuotation();
        $booking->setNumberSentEmailQuotation($booking->getNumberSentEmailQuotation() + 1);
        $booking->save(false);
        $customer = $booking->getCustomer();
  			$logNotification = RESA_Algorithms::generateLogNotification(18, $booking, $customer, $customer);
  			if(isset($logNotification))	$logNotification->save();
      }
      else if(!$booking->isCancelled() && !$booking->isAbandonned() && !$booking->isDatePassed() && ($booking->getNumberSentEmailQuotation() == $noResponseQuotationTimes)){
        $customer = $booking->getCustomer();
				$logNotification = RESA_Algorithms::generateLogNotification(11, $booking, $customer, $customer);
				if(isset($logNotification))	$logNotification->save();
        $booking->setNumberSentEmailQuotation($booking->getNumberSentEmailQuotation() + 1);
        $booking->save(false);
      }
    }
  }




  private function __construct()
	{

  }
}
