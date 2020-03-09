<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'getInformationsCalendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'getInformationsCalendar'),
  ));
  register_rest_route('resa/v1', 'getInformationsCalendarMonth/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'getInformationsCalendarMonth'),
  ));
  register_rest_route('resa/v1', 'informationsCalendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'informationsCalendar'),
  ));
  register_rest_route('resa/v1', 'informationsCalendar/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[Z0-9-]+)', array(
    'methods' => 'DELETE',
    'callback' => array('RESA_APICalendarBackendController', 'deleteInformationsCalendar'),
  ));
  register_rest_route('resa/v1', 'getConstraintsCalendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'getConstraintsCalendar'),
  ));
  register_rest_route('resa/v1', 'getConstraintsCalendarMonth/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'getConstraintsCalendarMonth'),
  ));
  register_rest_route('resa/v1', 'constraintsCalendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'constraintsCalendar'),
  ));
  register_rest_route('resa/v1', 'constraintsCalendar/(?P<token>[a-zA-Z0-9-]+)/(?P<id>[Z0-9-]+)/(?P<isServiceConstraint>[Z0-1]+)', array(
    'methods' => 'DELETE',
    'callback' => array('RESA_APICalendarBackendController', 'deleteConstraintsCalendar'),
  ));
  register_rest_route('resa/v1', 'getAlertsCalendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarBackendController', 'getAlertsCalendar'),
  ));
});
