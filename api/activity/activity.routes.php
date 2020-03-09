<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'activity/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIActivityController', 'getActivity'),
  ));
  register_rest_route('resa/v1', 'activity/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIActivityController', 'saveActivity'),
  ));
  register_rest_route('resa/v1', 'importActivityPrice/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIActivityController', 'importActivityPrice'),
  ));
  register_rest_route('resa/v1', 'activityHasChanged/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIActivityController', 'activityHasChanged'),
  ));
  register_rest_route('resa/v1', 'justOneActivity/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIActivityController', 'justOneActivity'),
  ));
});
