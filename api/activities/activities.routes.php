<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'activities/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIActivitiesController', 'initActivities'),
  ));
  register_rest_route('resa/v1', 'activities/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIActivitiesController', 'saveActivities'),
  ));
});
