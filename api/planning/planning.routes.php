<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'planningServices/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'planningServices'),
  ));
  register_rest_route('resa/v1', 'planningMembers/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'planningMembers'),
  ));
  register_rest_route('resa/v1', 'planningGroups/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'planningGroups'),
  ));
  register_rest_route('resa/v1', 'planningSettings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIPlanningController', 'settings'),
  ));
  register_rest_route('resa/v1', 'planningTimeslots/(?P<token>[a-zA-Z0-9-]+)/(?P<idActivity>[0-9-]+)/(?P<date>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIPlanningController', 'timeslots'),
  ));
  register_rest_route('resa/v1', 'timeslot/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'timeslot'),
  ));
  register_rest_route('resa/v1', 'appointments/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'appointments'),
  ));
  register_rest_route('resa/v1', 'appointment/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIPlanningController', 'getAppointment'),
  ));
  register_rest_route('resa/v1', 'bookingEditor/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIPlanningController', 'bookingEditor'),
  ));
  register_rest_route('resa/v1', 'calculateReductions/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIPlanningController', 'calculateReductions'),
  ));
  register_rest_route('resa/v1', 'bookingEditor/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIPlanningController', 'addBookingEditor'),
  ));
});
