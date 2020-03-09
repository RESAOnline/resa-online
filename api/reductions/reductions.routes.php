<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'reductions/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIReductionsController', 'initReductions'),
  ));
  register_rest_route('resa/v1', 'currentPromoCodes/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIReductionsController', 'currentPromoCodes'),
  ));
  register_rest_route('resa/v1', 'reductions/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIReductionsController', 'saveReductions'),
  ));
});
