<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'installation/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIInstallationController', 'initialize'),
  ));
  register_rest_route('resa/v1', 'installation/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIInstallationController', 'save'),
  ));
  register_rest_route('resa/v1', 'pages/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'PUT',
    'callback' => array('RESA_APIInstallationController', 'pages'),
  ));
});
