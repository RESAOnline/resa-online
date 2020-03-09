<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'customer/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APICustomersController', 'getCustomer'),
  ));
  register_rest_route('resa/v1', 'customerData/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APICustomersController', 'getCustomerData'),
  ));
  register_rest_route('resa/v1', 'customers/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'getCustomers'),
  ));
  register_rest_route('resa/v1', 'customer/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'editCustomer'),
  ));
  register_rest_route('resa/v1', 'customer/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'DELETE',
    'callback' => array('RESA_APICustomersController', 'deleteCustomer'),
  ));
  register_rest_route('resa/v1', 'customersSettings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APICustomersController', 'getCustomersSettings'),
  ));
  register_rest_route('resa/v1', 'acceptAskAccount/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'acceptAskAccount'),
  ));
  register_rest_route('resa/v1', 'deleteEmailCustomer/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'DELETE',
    'callback' => array('RESA_APICustomersController', 'deleteEmailCustomer'),
  ));
  register_rest_route('resa/v1', 'forceProcessEmails/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'forceProcessEmails'),
  ));
  register_rest_route('resa/v1', 'updateCustomer/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'updateCustomer'),
  ));
  register_rest_route('resa/v1', 'exportCustomers/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICustomersController', 'exportCustomers'),
  ));
});
