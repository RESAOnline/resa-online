<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'equipments/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIEquipmentsController', 'initEquipments'),
  ));
  register_rest_route('resa/v1', 'equipmentsLite/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => array('RESA_APIEquipmentsController', 'equipmentsLite'),
  ));
  register_rest_route('resa/v1', 'equipments/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIEquipmentsController', 'saveEquipments'),
  ));
});
