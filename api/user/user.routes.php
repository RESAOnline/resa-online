<?php
add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'user/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIUserController', 'getUserByToken'),
  ));
  register_rest_route('resa/v1', 'update/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIUserController', 'update'),
  ));
  register_rest_route('resa/v1', 'updateUserSettings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIUserController', 'updateUserSettings'),
  ));
  register_rest_route('resa/v1', 'seeAllRESANews/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIUserController', 'seeAllRESANews'),
  ));
});
