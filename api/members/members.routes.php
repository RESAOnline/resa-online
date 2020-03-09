<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'members/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIMembersController', 'initMembers'),
  ));
  register_rest_route('resa/v1', 'members/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIMembersController', 'saveMembers'),
  ));
  register_rest_route('resa/v1', 'createStaff/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIMembersController', 'createRESAMember'),
  ));
  register_rest_route('resa/v1', 'membersLite/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIMembersController', 'getMembersLite'),
  ));
});
