<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'groups/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIGroupsController', 'init'),
  ));
  register_rest_route('resa/v1', 'groups/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'save'),
  ));
  register_rest_route('resa/v1', 'openGroup/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'openGroup'),
  ));
  register_rest_route('resa/v1', 'switchParticipant/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'switchParticipant'),
  ));
  register_rest_route('resa/v1', 'switchMember/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'switchMember'),
  ));
  register_rest_route('resa/v1', 'dgroup/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'updateGroup'),
  ));
  register_rest_route('resa/v1', 'dgroup/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIGroupsController', 'addGroup'),
  ));
  register_rest_route('resa/v1', 'deletedgroup/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIGroupsController', 'deleteGroup'),
  ));
  register_rest_route('resa/v1', 'sendPlanningToMember/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIGroupsController', 'sendPlanningToMember'),
  ));
});
