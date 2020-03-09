<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'initLogNotificationCenter/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APILogNotificationCenterController', 'initLogNotificationCenter'),
  ));
  register_rest_route('resa/v1', 'getLogNotifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'getLogNotifications'),
  ));
  register_rest_route('resa/v1', 'clickOnLogNotifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'clickOnLogNotifications'),
  ));
  register_rest_route('resa/v1', 'unreadLogNotification/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'unreadLogNotification'),
  ));
  register_rest_route('resa/v1', 'seeAllLogNotifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'seeAllLogNotifications'),
  ));
  register_rest_route('resa/v1', 'clickAllLogNotifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'clickAllLogNotifications'),
  ));
  register_rest_route('resa/v1', 'checkUpdateLogNotifications/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APILogNotificationCenterController', 'checkUpdateLogNotifications'),
  ));
});
