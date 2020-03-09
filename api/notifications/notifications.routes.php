<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'notifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APINotificationsController', 'init'),
  ));
  register_rest_route('resa/v1', 'notifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'save'),
  ));
  register_rest_route('resa/v1', 'previewNotification/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'previewNotification'),
  ));
  register_rest_route('resa/v1', 'sendNotification/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'sendNotification'),
  ));
  register_rest_route('resa/v1', 'notificationsSettings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APINotificationsController', 'notificationsSettings'),
  ));
  register_rest_route('resa/v1', 'sendNotificationPassword/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'sendNotificationPassword'),
  ));
  register_rest_route('resa/v1', 'sendEmailToCustomer/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'sendEmailToCustomer'),
  ));
  register_rest_route('resa/v1', 'askPayment/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'askPayment'),
  ));
  register_rest_route('resa/v1', 'sendCustomEmail/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'sendCustomEmail'),
  ));
  register_rest_route('resa/v1', 'acceptQuotationBackend/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'acceptQuotationBackend'),
  ));
  register_rest_route('resa/v1', 'acceptQuotationAndAskPayment/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APINotificationsController', 'acceptQuotationAndAskPayment'),
  ));




});
