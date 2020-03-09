<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'settings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APISettingsController', 'init'),
  ));
  register_rest_route('resa/v1', 'settings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'save'),
  ));
  register_rest_route('resa/v1', 'settingsLite/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'settingsLite'),
  ));
  register_rest_route('resa/v1', 'clearSynchronizationCustomers/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'clearSynchronizationCustomers'),
  ));
  register_rest_route('resa/v1', 'forceCaisseOnline/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'forceCaisseOnline'),
  ));
  register_rest_route('resa/v1', 'configurationFile/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'configurationFile'),
  ));
  register_rest_route('resa/v1', 'uploadCSSFile/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'uploadCSSFile'),
  ));
  register_rest_route('resa/v1', 'settingsAcceptStripeConnect/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'settingsAcceptStripeConnect'),
  ));
  register_rest_route('resa/v1', 'exportFile/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'exportFile'),
  ));
  register_rest_route('resa/v1', 'importFile/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APISettingsController', 'importFile'),
  ));
});
