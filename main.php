<?php if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Resa Online
Plugin URI:https://resa-online.fr
Description: RESA Online logiciel de réservation et gestion de planning
Version: 2.2.6
Author: 360-online
Author URI:https://360-online.fr/
Text Domain: resa
Domain Path: /languages
*/

/*
 * Copyright 2016-2019 360-online
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * ( at your option ) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 */
include_once 'includes.php';

add_action('plugins_loaded', function () {
	load_plugin_textdomain('resa', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
	try{
		RESA_UpdatesManager::updatePlugin();
	}
	catch(Exception $e){
		Logger::ERROR($e->getMessage());
	}
});

add_action('activated_plugin', array('RESA_InstallManager', 'loadRESAOnlineBeforePlugins'));
RESA_HooksManager::registerHooks(__FILE__);
is_admin() ? new RESA_AdminMainController() : new RESA_PublicMainController();
