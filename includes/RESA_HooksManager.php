<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

abstract class RESA_HooksManager
{
	public static function activate()
	{
		$installManager = new RESA_InstallManager();
		$installManager->install();
	}

	public static function deactivate()
	{

	}

	public static function uninstall()
	{
		$installManager = new RESA_InstallManager();
		$installManager->uninstall();
	}

	public static function registerHooks($file)
  {
		if ( is_admin() ) {
		  register_activation_hook($file,   array( __CLASS__, 'activate' ) );
		  register_deactivation_hook($file, array( __CLASS__, 'deactivate' ) );
		  register_uninstall_hook($file,    array( __CLASS__, 'uninstall' ) );
		}
  }

}
