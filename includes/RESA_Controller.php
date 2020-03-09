<?php if ( ! defined( 'ABSPATH' ) ) exit;

interface RESA_ControllerInterface
{
	public function getSlug();
	public function getPageName();
	public function isSettings();
	public function getClassDir();

	/**
	 * Method to call with menu.
	 */
	public function initialize();

	/**
	 * automatically call to register ajax methods.
	 */
	public function registerAjaxMethods();

	/**
	 * Return a list of needed scripts
	 */
	public function getNeededScripts();

	/**
	 * Return a list of needed styles
	 */
	public function getNeededStyles();
}

abstract class RESA_Controller implements RESA_ControllerInterface
{
	protected static $GLOBAL_SCRIPTS = array(
		'libs/sweetalert.min',
		'libs/bootstrap.min',
		'libs/angular.min',
		'libs/locales/angular-locale_fr-fr',
		'libs/angular-sanitize.min',
		'libs/angular-animate.min',
		'libs/ui-bootstrap-tpls.min',
		'resa_app',
		'filters/formatDateTime',
		'filters/formatDate',
		'filters/formatTime',
		'filters/formatPhone',
		'filters/hours',
		'filters/negative',
		'filters/positive',
		'filters/displayNewLines',
		'filters/trusted',
		'filters/htmlSpecialDecode',
		'filters/parseDate',
		'filters/formatManyDatesView'
	);

	protected static $GLOBAL_STYLES = array(
		'bootstrap',
		'ro-admin-style',
		'sweetalert',
		'angular-css'
	);

	/**
	 * Display the template with variables.
	 * \param $template the file to display
	 * \param $variables array format key => value.
	 */
	public function renderer($template, $variables = array())
	{
		include_once $this->getClassDir().'/templates/'. $template. '.php';
	}

	/**
	 * Add ajax method with method name.
	 */
	protected function addAjaxMethod($method, $noPrivate=false){
		if($noPrivate)
			add_action('wp_ajax_nopriv_'.$method.'', array($this, $method));
		add_action('wp_ajax_'.$method.'', array($this, $method));
	}

	/**
	 * Use a script
	 */
	protected function useScript($scriptName)
	{
		if(substr( $scriptName, -3) !== '.js'){
			$scriptName = $scriptName.'.js';
		}
		if(substr( $scriptName, 0, 4 ) === "http"){
			wp_enqueue_script('resa_'.$scriptName, $scriptName, array(), RESA_Variables::getVersion(), true);
		}
		else {
			wp_enqueue_script('resa_'.$scriptName, plugins_url('../controller/js/'.$scriptName, __FILE__ ), array(), RESA_Variables::getVersion(), true);
		}
	}

	/**
	 * Use a script
	 */
	protected function useStyle($cssFilename)
	{
		if(substr( $cssFilename, -4) !== '.css'){
			$cssFilename = $cssFilename.'.css';
		}
		if(substr( $cssFilename, 0, 4 ) === "http"){
			wp_register_style('resa_'.$cssFilename, $cssFilename, array(), RESA_Variables::getVersion());
		}
		else {
			wp_register_style('resa_'.$cssFilename, plugins_url('../controller/css/'.$cssFilename, __FILE__ ), array(), RESA_Variables::getVersion());
		}
		wp_enqueue_style( 'resa_'.$cssFilename);
	}

	/**
	 * automatically call to register local styles
	 */
	public function useStyles(){
		$array = $this->getNeededStyles();
		for($i = 0; $i < count($array); $i++){
			$this->useStyle($array[$i]);
		}
	}

	/**
	 * automatically call to register local styles
	 */
	public function useScripts(){
		$array = $this->getNeededScripts();
		for($i = 0; $i < count($array); $i++){
			$this->useScript($array[$i]);
		}
	}

	/**
	 * automatically call to register local scripts
	 */
	public function useScriptsAndStyles(){
		$this->useScripts();
		$this->useStyles();
	}

	public function noDisplayAdminMenu(){
		echo '<!-- no display admin menu -->
		<style type="text/css">#wpcontent, #footer{ margin: 0px; } #wpfooter, #adminmenuwrap, #adminmenuwrap, #adminmenumain, #list-tables-css,
		.notice { display:none; } .notice-resa { display:block; }</style>
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$(\'#adminmenuback, #adminmenuwrap, #wpfooter, #adminmenumain, #screen-meta\').remove();
				$(\'#list-tables-css\').remove();
				$(\'.update-nag\').remove();
			});
		</script>';
	}

	public function noSpecialDisplay(){
		echo '<!-- no display admin menu -->
		<script type="text/javascript">
			jQuery(document).ready( function($) {
				$(\'#common-css\').remove();
				$(\'#revisions-css\').remove();
				$(\'#forms-css\').remove();
				$(\'#admin-menu-css\').remove();
				$(\'#edit-css\').remove();
				$("#wp-auth-check-wrap").remove();
				$("LINK[href*=\'wp-admin/load-styles.php\']").remove();
			});
		</script>';
	}

	public function noDisplayAdminBar(){
		echo '<!-- no display admin bar --><style type="text/css">#wpadminbar{ display:none; }</style><script type="text/javascript">jQuery(document).ready( function($) { $(\'#wpadminbar\').remove(); $(\'html\').removeClass(\'wp-toolbar\'); });</script>';
	}

	/**
	 * delete another css
	 * if mine is true delete us css
	 */
	public function disabledAnotherCSSPlugin($mine = false){
		echo '<script type="text/javascript">
				jQuery(document).ready( function($) {
					$(\'#padmincss-css\').prop(\'disabled\', true);
				'
				. ($mine?'document.getElementById(\'resa_resa_admin.css-css\').disabled = true;':'') .
				'});
			</script>';
	}
}
