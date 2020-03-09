<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Abstract class used to create a new RESA extension
 */
abstract class RESA_Extension implements RESA_ExtensionInterface {

	public static function registerPlugin($file, $extensionName){
		if(class_exists($extensionName)){
			$extensionName::includes();
			$extensionName::registerHooks($file);
			add_filter('plugin_action_links_' .  plugin_basename($file), array($extensionName, 'settingsLink'));

			$extension = new $extensionName();
			add_action('activated_plugin', array($extension, 'loadAfterRESAOnline'));
			add_filter('pre_set_site_transient_update_plugins', array($extension, 'displayTransientUpdatePlugin'));
		}
	}

	function loadAfterRESAOnline() {
    $pluginRESAOnline = 'resa-online/main.php';
    $pluginThis = $this->getSlugName() . '/main.php';
    if ( $plugins = get_option( 'active_plugins' ) ) {
      $indexRESAOnline = array_search($pluginRESAOnline, $plugins );
      $indexMe = array_search($pluginThis, $plugins );
      if($indexRESAOnline > $indexMe){
        $tmp = $plugins[$indexRESAOnline];
        $plugins[$indexRESAOnline] = $plugins[$indexMe];
        $plugins[$indexMe] = $tmp;
        update_option( 'active_plugins', $plugins );
      }
    }
	}

	function getNextVersion(){
	  $path = $this->getRESAPluginURL();
	  $request = wp_remote_post($path, array('body' => array('action' => 'version', 'plugin' => $this->getSlugName())));
	  if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
	      return $request['body'];
	  }
	  return false;
	}

	function getRESAPluginURL(){
	  return 'https://resa-online.fr';
	}

	function calculateRESAKey($version){
		$toHash = $this->getSlugName().$version.''.floor(time()/3600);
		return hash('sha256', $toHash);
	}


	function displayTransientUpdatePlugin($transient){
	  $result = $this->getNextVersion();
	  if(!is_bool($result) && version_compare($result, $this->getVersion(), '>')){
	    $url = $this->getRESAPluginURL().'?action=update&plugin='.$this->getSlugName().'&key='. $this->calculateRESAKey($result);
	    $obj = new stdClass();
	    $obj->slug = $this->getSlugName() . '/main.php';
	    $obj->plugin_name = $this->getSlugName();
	    $obj->name = $this->getName();
	    $obj->tested = '5.1.1';
	    $obj->new_version = $result;
	    $obj->downloaded = 1;
	    $obj->sections = array(
	      'description' => '',
	      'changelog' => ''
	    );
	    $obj->url = $url;
	    $obj->package = $url;
	    $obj->download_link = $url;
	    $transient->response[$this->getSlugName().'/main.php'] = $obj;
	  }
	  return $transient;
	}
}



interface RESA_ExtensionInterface {
	public static function includes();
	public function getName();
	public function getSlugName();
	public function getVersion();
	public static function registerHooks($file);

	public static function hookActivate();
	public static function hookDeactivate();
	public static function hookUninstall();
	public static function alreadyOneActivation();
	public static function settingsLink($links);
}
