<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_Middleware
{
  public static function getUserByToken($token){
    $currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadUserByToken($token);
    return $currentRESAUser;
  }

}
