<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'bookings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'getBookings'),
  ));
  register_rest_route('resa/v1', 'quotations/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'getQuotations'),
  ));
  register_rest_route('resa/v1', 'appointmentsEvent/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'getAppointments'),
  ));
  register_rest_route('resa/v1', 'appointmentsEventMonths/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'getAppointmentsMonths'),
  ));
  register_rest_route('resa/v1', 'booking/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIBookingsController', 'getBooking'),
  ));
  register_rest_route('resa/v1', 'booking/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'DELETE',
    'callback' => array('RESA_APIBookingsController', 'deleteBooking'),
  ));
  register_rest_route('resa/v1', 'historic/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIBookingsController', 'getBookingHistoric'),
  ));
  register_rest_route('resa/v1', 'bookingData/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIBookingsController', 'getBookingData'),
  ));
  register_rest_route('resa/v1', 'bookingsSettings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIBookingsController', 'getBookingsSettings'),
  ));
  register_rest_route('resa/v1', 'receipt/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIBookingsController', 'receipt'),
  ));
  register_rest_route('resa/v1', 'pushBookingInCaisseOnline/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIBookingsController', 'pushBookingInCaisseOnline'),
  ));
  register_rest_route('resa/v1', 'pushBookingsInCaisseOnline/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIBookingsController', 'pushBookingsInCaisseOnline'),
  ));
  register_rest_route('resa/v1', 'changePaymentState/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'changePaymentState'),
  ));
  register_rest_route('resa/v1', 'calculateBookingPaymentState/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIBookingsController', 'calculateBookingPaymentState'),
  ));



});
