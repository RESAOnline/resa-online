<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'forms/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIFormsController', 'getForms'),
  ));
  register_rest_route('resa/v1', 'forms/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIFormsController', 'saveForms'),
  ));
  register_rest_route('resa/v1', 'pageForm/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIFormsController', 'pageForm'),
  ));
  register_rest_route('resa/v1', 'pageAskAccount/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIFormsController', 'pageAskAccount'),
  ));
});
