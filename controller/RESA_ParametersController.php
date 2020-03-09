<?php if ( ! defined( 'ABSPATH' ) ) exit;


class RESA_ParametersController extends RESA_Controller
{
	public function getSlug()
	{
		return 'resa_parameters';
	}

	public function getPageName()
	{
		return __( 'settings_title', 'resa' );
	}

	public function isSettings()
	{
		return true;
	}

	public function getClassDir()
	{
		return __DIR__;
	}


	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts()
	{
		return array();
	}

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles()
	{
		return array();
	}

	/**
	 * Method to call with menu.
	 */
	public function initializeWithSubPage($currentRESAUser, $subPage = '/') {
		if(!get_option('resa_first_parameter_done', 0)) $subPage = 'installation';
		$this->noDisplayAdminMenu();
		$this->noDisplayAdminBar();
		$this->noSpecialDisplay();
		$this->disabledAnotherCSSPlugin(true);
		echo '
			<app-root></app-root>
			<script type="text/javascript">
				if(window.location.href.indexOf("#") == -1) window.location.href = window.location.href + "#' . $subPage . '?token=' . $currentRESAUser->getToken()  . '";
			</script>
		';
		$arrayCSS = array(
			'assets/bootstrap/css/bootstrap.css',
			'assets/dashboard.css',
			'assets/css/dashicons.min.css',
			'assets/css/switches.css',
			'assets/css/resa-settings.css',
			'assets/tinymce/skins/lightgray/skin.min.css',
			'assets/tinymce/skins/lightgray/content.min.css',
			'assets/tinymce/skins/lightgray/fonts/tinymce.woff',
			'assets/tinymce/skins/lightgray/fonts/tinymce.ttf',
			'styles.css'
		);

		foreach($arrayCSS as $css){
			wp_register_style('resa_' . $css, plugins_url('../ui/' . $css, __FILE__ ), array(), RESA_Variables::getVersion());
			wp_enqueue_style('resa_' . $css);
		}

		$arrayJS = array(
			'runtime.js',
			'polyfills.js',
			'scripts.js',
			'main.js',
			'assets/js/jquery.min.js',
			'assets/js/popper.js',
			'assets/js/sweetalert.min.js',
			'assets/tinymce/plugins/RESAShortcodes/plugin.js',
			'assets/bootstrap/js/bootstrap.min.js',
			'assets/js/ie10-viewport-bug-workaround.js',
		);
		foreach($arrayJS as $js){
			wp_enqueue_script('resa_' . $js, plugins_url('../ui/' . $js, __FILE__ ), array(), RESA_Variables::getVersion(), true);
		}
	}

	public function initializeBookings(){
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);
		$subPage = '';
		$filterSettings = $currentRESAUser->getFilterSettings();
		if(isset($filterSettings->favPage) && !$currentRESAUser->isRESAStaff()){
			$subPage = $filterSettings->favPage;
		}
		else if($currentRESAUser->isRESAStaff()){
			$associatedMember = RESA_Member::getAssociatedMember($currentRESAUser->getId());
			if(!$associatedMember->getPermissionDisplayBookingsTab() && RESA_Variables::isGroupsManagementActivated()){
				$subPage = 'planningGroups';
			}
			else if(!$associatedMember->getPermissionDisplayBookingsTab() && !RESA_Variables::isGroupsManagementActivated()){
				$subPage = 'planningMembers';
			}
			else {
				if(isset($filterSettings) && isset($filterSettings->favPage))
					$subPage = $filterSettings->favPage;
				else
					$subPage = 'bookingsList';
			}
		}
		$this->initializeWithSubPage($currentRESAUser, $subPage);
	}

	public function initializeParameters(){
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);
		$this->initializeWithSubPage($currentRESAUser, '/activities');
	}

	public function initialize(){
		$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser(false);
		$this->initializeWithSubPage($currentRESAUser);
	}



	public function registerAjaxMethods(){

	}
}
