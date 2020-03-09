<?php

add_action('rest_api_init', function(){
  register_rest_route('resa/v1', 'timeslots/(?P<token>[a-zA-Z0-9-]+)', array(
    'methods' => 'POST',
    'callback' => array('RESA_APIAlgorithmsController', 'getTimeslots'),
  ));

});
