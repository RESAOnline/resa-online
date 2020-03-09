<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'daily/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'daily'),
  ));

  register_rest_route('resa/v1', 'daily/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIStatictisController', 'dailySettings'),
  ));

  register_rest_route('resa/v1', 'promoCodes/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIStatictisController', 'promoCodes'),
  ));

  register_rest_route('resa/v1', 'promoCodesBookings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'promoCodesBookings'),
  ));

  register_rest_route('resa/v1', 'initMembersStats/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIStatictisController', 'initMembersStats'),
  ));

  register_rest_route('resa/v1', 'countHoursMembers/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'countHoursMembers'),
  ));

  register_rest_route('resa/v1', 'initStatisticsActivities/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIStatictisController', 'initStatisticsActivities'),
  ));

  register_rest_route('resa/v1', 'statisticsActivities/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'statisticsActivities'),
  ));

  register_rest_route('resa/v1', 'initStatisticsBookings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIStatictisController', 'initStatisticsBookings'),
  ));

  register_rest_route('resa/v1', 'statisticsBookings/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'statisticsBookings'),
  ));

  register_rest_route('resa/v1', 'search/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIStatictisController', 'search'),
  ));

});
