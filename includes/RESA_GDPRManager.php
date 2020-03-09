<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Implement the hook for GDPR
 */
class RESA_GDPRManager
{

  private static $_instance = null;

  public static function getInstance()
  {
    if(is_null(self::$_instance)) {
      self::$_instance = new RESA_GDPRManager();
    }
    return self::$_instance;
  }


  public function my_plugin_exporter( $email_address, $page = 1 ) {
    $customer = new RESA_Customer();
    $customer->loadByEmail($email_address, true);

    $data = array();
    if($customer->isLoaded()){
      $RESACustomer = array(
        'group_id' => 'RESACustomer',
        'group_label' => __('my_personal_informations', 'resa'),
        'item_id' => 'RESACustomer',
        'data' => array()
      );
      array_push($RESACustomer['data'], array('name' => __('last_name_field_title','resa'), 'value' => $customer->getLastName()));
      array_push($RESACustomer['data'], array('name' => __('first_name_field_title','resa'), 'value' => $customer->getFirstName()));
      array_push($RESACustomer['data'], array('name' => __('company_field_title','resa'), 'value' => $customer->getCompany()));
      array_push($RESACustomer['data'], array('name' => __('phone_field_title','resa'), 'value' => $customer->getPhone()));
      array_push($RESACustomer['data'], array('name' => __('address_field_title','resa'), 'value' => $customer->getAddress()));
      array_push($RESACustomer['data'], array('name' => __('postal_code_field_title','resa'), 'value' => $customer->getPostalCode()));
      array_push($RESACustomer['data'], array('name' => __('town_field_title','resa'), 'value' => $customer->getTown()));
      array_push($RESACustomer['data'], array('name' => __('country_field_title','resa'), 'value' => $customer->getCountry()));

      array_push($data, $RESACustomer);

      $index = 0;
      foreach($customer->getParticipants() as $participant){
        $RESACustomerParticipants = array(
          'group_id' => 'RESACustomerParticipants',
          'group_label' => __('participants_field_title', 'resa'),
          'item_id' => 'RESACustomerParticipants_' . $index,
          'data' => array()
        );
        foreach($participant as $key => $value){
          array_push($RESACustomerParticipants['data'], array('name' => $key, 'value' => $value));
        }
        array_push($data, $RESACustomerParticipants);
        $index++;
      }

      //Résa
      $index = 0;
      foreach($customer->getBookings() as $booking){
        $subIndex = 0;
        foreach($booking->getAppointments() as $appointment){

          $typeBooking = __('Booking_word', 'resa');
          if($booking->isQuotation()) { $typeBooking = __('quotation_word', 'resa'); }
          $status = $booking->getStatus();
          $stringStatus = '';
          if($status == 'ok'){ $stringStatus = __('booking_status_ok_word', 'resa');  }
          if($status == 'cancelled'){ $stringStatus = __('booking_status_cancelled_word', 'resa');  }
          if($status == 'abandonned'){ $stringStatus = __('booking_status_cancelled_word', 'resa');  }
          if($status == 'waiting'){ $stringStatus = __('booking_status_waiting_word', 'resa'); }
          if($status == 'pending'){ $stringStatus = __('booking_status_waiting_word', 'resa'); }

          $paymentStatus = '';
          if($booking->isPaymentStateNoPayment()) { $paymentStatus = __('payment_status_noPayment_word', 'resa'); }
          if($booking->isPaymentStateAdvancePayment()) { $paymentStatus = __('payment_status_advancePayment_word', 'resa'); }
          if($booking->isPaymentStateComplete()) { $paymentStatus = __('payment_status_noPayment_word', 'resa'); }

          $RESACustomerBookings = array(
            'group_id' => 'RESACustomerBookings_' . $index,
            'group_label' => '['.$stringStatus.'] ' . $typeBooking. ' n°' . $booking->getIdCreation() . ' - [' . $paymentStatus . ']',
            'item_id' => 'RESACustomerBookings_' . $subIndex,
            'data' => array()
          );

          $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getStartDate());
          $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $appointment->getEndDate());
          $service = new RESA_Service();
          $service->loadById($appointment->getIdService());

          array_push($RESACustomerBookings['data'], array('name' => __('date_table_header', 'resa'), 'value' => date_i18n(get_option('date_format'), $startDate->getTimestamp())));
          if(!$appointment->isNoEnd()) {
            array_push($RESACustomerBookings['data'], array('name' => __('hours_table_header', 'resa'), 'value' => $startDate->format(get_option('time_format')) . ' ' . __('to_word', 'resa') . ' '. $endDate->format(get_option('time_format'))));
          } else {
            array_push($RESACustomerBookings['data'], array('name' => __('hours_table_header', 'resa'), 'value' => __('begin_word', 'resa').' '.$startDate->format(get_option('time_format'))));
          }
          array_push($RESACustomerBookings['data'], array('name' => __('service_table_header', 'resa'), 'value' => $service->getName()));
          array_push($RESACustomerBookings['data'], array('name' => __('number_table_header', 'resa'), 'value' => $appointment->getNumbers()));
          array_push($RESACustomerBookings['data'], array('name' => __('price_table_header', 'resa'), 'value' => $appointment->getTotalPrice().''.get_option('resa_settings_payment_currency')));

          array_push($data, $RESACustomerBookings);
          $subIndex++;
        }
        $index++;
      }
    }
    return array(
      'data' => $data,
      'done' => 1
    );
  }

  function my_plugin_eraser( $email_address, $page = 1 ) {
    $customer = new RESA_Customer();
    $customer->loadByEmail($email_address, true);

    $allAppointmentsDatePassed = true;
    foreach($customer->getBookings() as $booking){
      $allAppointmentsDatePassed = $allAppointmentsDatePassed && $booking->isAllDatePassed();
    }
    $itemsRemoved = $allAppointmentsDatePassed;
    if($itemsRemoved){
      $message = __('gdpr_message_item_deleted', 'resa');
    }
    else {
      $message = __('gdpr_message_no_item_deleted', 'resa');
    }

    $customer->setLastName('');
    $customer->setFirstName('');
    $customer->setCompany('');
    $customer->setPhone('');
    $customer->setAddress('');
    $customer->setPostalCode('');
    $customer->setTown('');
    $customer->setCountry('');
    $customer->setParticipants(array());
    $customer->setDeactivateEmail(true);
    $customer->clearModificationDate();
    $customer->save();

    return array(
      'items_removed' => $itemsRemoved,
      'items_retained' => true && $itemsRemoved,
      'messages' => array($message),
      'done' => 1,
    );
  }

  public function register_my_plugin_exporter( $exporters ) {
    $exporters['my-plugin-slug'] = array(
      'exporter_friendly_name' => __( 'Comment Location Plugin' ),
      'callback' => array($this, 'my_plugin_exporter'),
    );
    return $exporters;
  }

  function register_my_plugin_eraser( $erasers ) {
    $erasers['my-plugin-slug'] = array(
      'eraser_friendly_name' => __( 'Comment Location Plugin' ),
      'callback'             => array($this, 'my_plugin_eraser'),
    );
    return $erasers;
  }


	public function registerHooks() {
    add_filter('wp_privacy_personal_data_exporters', array($this, 'register_my_plugin_exporter'), 10 );
    add_filter('wp_privacy_personal_data_erasers', array($this, 'register_my_plugin_eraser'), 10);
  }

}
