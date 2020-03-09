<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'calendars/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APICalendarsController', 'init'),
  ));
  register_rest_route('resa/v1', 'calendars/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarsController', 'save'),
  ));
  register_rest_route('resa/v1', 'calendar/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APICalendarsController', 'saveCalendar'),
  ));
});
