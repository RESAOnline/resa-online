<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_AdminMainController  extends RESA_ParentController
{
	private static $parent_slug = 'resa_system';

	public function __construct()
	{
		parent::__construct();
		$this->allControllers = array();
		array_push($this->allControllers, new RESA_BackendController());
		//if(RESA_Reduction::haveOneReductions()){
			array_push($this->allControllers, new RESA_ReductionsController());
		//}
		array_push($this->allControllers, new RESA_ParametersController());
		if(class_exists('RESA_SystemInfoController')){
			array_push($this->allControllers, new RESA_SystemInfoController());
		}
		add_action('admin_menu', array( $this, 'createAdminMenu' ));
		add_action('admin_init', array( $this, 'registerScriptsStyles'), 1);
		add_action('admin_notices', array( $this, 'resaInstallationAdminNotice'));

		//User profile
		add_action('show_user_profile', array($this, 'addViewsUserCustomFields'));
		add_action('edit_user_profile', array($this, 'addViewsUserCustomFields'));
		add_action('user_new_form', array($this, 'addViewsUserCustomFields'));
		add_action('personal_options_update', array($this, 'saveUserCustomFields'));
		add_action('edit_user_profile_update', array($this, 'saveUserCustomFields'));
		add_action('user_register', array($this, 'saveUserCustomFields'));
		add_action('delete_user', array($this, 'canNotDeleteRESACustomer'));


		$globalCtrl = new RESA_GlobalController();
		$globalCtrl->registerAjaxMethods();
		(new RESA_AccountController())->registerAjaxMethods(); //A revoir
		(new RESA_FormPublicController())->registerAjaxMethods(); //A revoir

		$this->registerAjaxMethods();
	}


	public function createAdminMenu()
	{
		add_menu_page('RESA Online', 'RESA', RESA_Variables::getRoleCap(), RESA_AdminMainController::$parent_slug, '', plugins_url('controller/images/ico-resa-online.svg', dirname(__FILE__)));
		$backendV2Activated = get_option('resa_backend_v2_activated', false);

		$numberSubPages = 0;
  	$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser();
		$canViewBackendEnd = true;
		if($currentRESAUser->isRESAStaff()){
			$membersIdCustomerLinked = RESA_Member::getAllData(array('idCustomerLinked' => $currentRESAUser->getId(), 'activated' => 1));
			if(count($membersIdCustomerLinked) < 1){
				$canViewBackendEnd = false;
			}
		}
		if($currentRESAUser->canEditParameters() || $backendV2Activated){
			if($currentRESAUser->generateNewToken(7)){
				$currentRESAUser->save(false);
			}
		}
		if(!$backendV2Activated){
			foreach($this->allControllers as $controller) {
				if(!empty($controller->getSlug()) && !empty($controller->getPageName()) && ($backendV2Activated || !$controller->isSettings() || ($controller->isSettings() && $currentRESAUser->canEditParameters()) )){
					if(!$currentRESAUser->isRESAStaff() ||
						($currentRESAUser->isRESAStaff() && $canViewBackendEnd && ($backendV2Activated || $controller->getSlug() == 'resa_appointments'))){
							$numberSubPages++;
							$parentSlug = RESA_AdminMainController::$parent_slug;
							if($controller->getSlug() == 'resa_reductions'){ $parentSlug = null; }
							add_submenu_page($parentSlug, $controller->getPageName(), $controller->getPageName(),
							RESA_Variables::getRoleCap(), $controller->getSlug(), array( $controller, 'initialize' ));
					}
				}
			}
		}
		else {
			$controller = $this->allControllers[0];
			add_submenu_page(null, $controller->getPageName(), $controller->getPageName(),
			RESA_Variables::getRoleCap(), $controller->getSlug(), array( $controller, 'initialize'));
			$controller = $this->allControllers[1];
			add_submenu_page(null, $controller->getPageName(), $controller->getPageName(),
			RESA_Variables::getRoleCap(), $controller->getSlug(), array( $controller, 'initialize'));


			$controller = $this->allControllers[2];
			/*add_submenu_page(RESA_AdminMainController::$parent_slug, __('bookings_title', 'resa'), __('bookings_title', 'resa'),
			RESA_Variables::getRoleCap(), $controller->getSlug().'', array( $controller, 'initializeBookings'));*/
			add_submenu_page(RESA_AdminMainController::$parent_slug, __('bookings_title', 'resa'), __('bookings_title', 'resa'),
			RESA_Variables::getRoleCap(), 'resa_backend', array( $controller, 'initializeBookings'));
			if($currentRESAUser->canEditParameters()){
				add_submenu_page(RESA_AdminMainController::$parent_slug, $controller->getPageName(), $controller->getPageName(),
				RESA_Variables::getRoleCap(), $controller->getSlug(), array( $controller, 'initializeParameters'));
			}
			if(class_exists('RESA_SystemInfoController') && $currentRESAUser->canEditParameters()){
				$controller = $this->allControllers[3];
				add_submenu_page(RESA_AdminMainController::$parent_slug, $controller->getPageName(), $controller->getPageName(),
				RESA_Variables::getRoleCap(), $controller->getSlug(), array( $controller, 'initialize'));
			}
			$numberSubPages += 3;
		}
		//remove_submenu_page(RESA_AdminMainController::$parent_slug, $controller->getSlug());

		//Bug wordpress
		remove_submenu_page(RESA_AdminMainController::$parent_slug, RESA_AdminMainController::$parent_slug);
		if($numberSubPages == 0){
			remove_menu_page(RESA_AdminMainController::$parent_slug);
		}
	}

	/**
	 * Add views to manage the user custom fields
	 * \param wp_user
	 */
	public function addViewsUserCustomFields($user){
		$company = '';
		$companyAccount = '';
		$phone = '';
		$privateNotes = '';
		$address = '';
		$postalCode = '';
		$town = '';
		$country = '';

  	$currentRESAUser = new RESA_Customer();
		$currentRESAUser->loadCurrentUser();

		$customer = new RESA_Customer();
		if(isset($user) && is_object($user)){
			$customer->loadById($user->ID);
		}
		if($customer->isLoaded() && $customer->isRESACustomer()){
			?>
			<h2> <?php _e('resa_custom_user_data_title','resa') ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
					<th><label for="company"><?php _e('company_field_title', 'resa') ?></label></th>
					<td><input type="text" name="company" id="company" value="<?php echo $customer->getCompany(); ?>" class="regular-text" /></td>
					</tr>
					<tr>
					<th><label for="companyAccount"><?php _e('company_account_field_title', 'resa') ?></label></th>
					<td><input type="checkbox" name="companyAccount" id="companyAccount" <?php if($customer->isCompanyAccount()){ echo 'checked'; } ?> class="regular-text" /></td>
					</tr>
					<tr>
					<th><label for="phone"><?php _e('phone_field_title', 'resa') ?></label></th>
					<td><input type="text" name="phone" id="phone" value="<?php echo $customer->getPhone(); ?>" class="regular-text" /></td>
					</tr>
					<tr>
					<th><label for="privateNotes"><?php _e('private_notes_field_title', 'resa') ?></label></th>
					<td><textarea type="text" name="privateNotes" id="privateNotes"><?php echo RESA_Tools::HTMLTextareaToNl($customer->getPrivateNotes()); ?></textarea></td>
					</tr>
					<tr>
					<th><label for="address"><?php _e('address_field_title', 'resa') ?></label></th>
					<td><input type="text" name="address" id="address" value="<?php echo $customer->getAddress(); ?>" class="regular-text"/></td>
					</tr>
					<tr>
					<th><label for="postalCode"><?php _e('postal_code_field_title', 'resa') ?></label></th>
					<td><input type="number" name="postalCode" id="postalCode" value="<?php echo $customer->getPostalCode(); ?>" class="regular-text" /></td>
					</tr>
					<tr>
					<th><label for="town"><?php _e('town_field_title', 'resa') ?></label></th>
					<td><input type="text" name="town" id="town" value="<?php echo $customer->getTown(); ?>" class="regular-text" /></td>
					</tr>
					<tr>
					<th><label for="country"><?php _e('country_field_title', 'resa') ?></label></th>
					<td><input type="text" name="country" id="country" value="<?php echo $customer->getCountry(); ?>" class="regular-text" /></td>
					</tr>
				</tbody>
			</table>
			<?php
		}
		else if($customer->isLoaded() && $customer->canManage() && !$customer->isAdministrator() && !$currentRESAUser->isRESAManager()){
			?>
			<h2> <?php _e('resa_custom_user_data_title','resa') ?></h2>
			<table class="form-table">
				<tbody>
					<tr>
					<th><label for="seeSettings"><?php _e('see_settings_field_title', 'resa') ?></label></th>
					<td><input type="checkbox" name="seeSettings" id="seeSettings" <?php if($customer->canEditParameters()){ echo 'checked'; } ?> class="regular-text" /></td>
					</tr>
				</tbody>
			</table>
			<?php
		}
	}

	function resaInstallationAdminNotice() {
		if(!get_option('resa_first_parameter_done', 0)){
	    ?>
	    <div class="notice updated">
	    	<p>
					<?php _e('Pour la première installation et les réglages de RESA Online c\'est par ici', 'resa' ); ?><br />
					<a href="<?php echo RESA_Variables::getLinkParameters('installation'); ?>" class="button button-primary">Aller à la première installation</a>
				</p>
	    </div>
	    <?php
		}
		if(get_option('resa_settings_caisse_online_activated') && !class_exists('RESA_CaisseOnline')){
	    ?>
	    <div class="notice notice-error">
	    	<p>
					<?php _e('Attention, nous détectons que vous avez la synchronisation avec la caisse activée mais il vous manque le plugin correspondant pour s\'y connecter. Veuillez nous contacter via le support de RESA Online !', 'resa' ); ?><br />
				</p>
	    </div>
	    <?php
		}
		if(get_option('resa_update_customers', -1) != -1){
	    ?>
	    <div class="notice notice-error">
	    	<p>
					<?php _e('Une mise à jour est prévue pour vos données de RESA Online, rendez-vous dans les réglages avancés pour l\'effectuer', 'resa' ); ?><br />
					<a href="<?php echo RESA_Variables::getLinkParameters('settings/support'); ?>" class="button button-primary">Aller aux réglages avancés</a>
				</p>
	    </div>
	    <?php
		}
		if((get_option('resa_backend_v2_activated', false) == false) && get_option('resa_ask_backend_v2_activated', true)){
	    ?>
	    <div class="notice updated">
	    	<p>
					<?php _e('La nouvelle version de RESA Online ajoute une refonte complète de l\'interface manager, cependant vous avez toujours l\'ancienne interface d\'activée.', 'resa' ); ?><br />
					<a href="<?php echo RESA_Variables::getLinkParameters('settings/support'); ?>" class="button button-primary">Activer la nouvelle interface dans les réglages</a>
				</p>
	    </div>
	    <?php
		}
	}

	/**
	 * Hook when delete user action
	 */
	public function canNotDeleteRESACustomer($user_id){
		$customer = new RESA_Customer();
		$customer->loadById($user_id);
		if($customer->isLoaded() && $customer->haveBooking()){
			wp_die(__('can_not_delete_customer_error', 'resa'));
		}
	}

	/**
	 * Saved the user custom fields
	 * \param user_id
	 */
	public function saveUserCustomFields($user_id){
		if(!current_user_can('edit_user', $user_id)){
			return false;
		}
		$customer = new RESA_Customer();
		$customer->loadById($user_id);
		if($customer->isLoaded() &&
			$customer->isRESACustomer() &&
			(isset($_POST['company']) || isset($_POST['companyAccount']) ||
			isset($_POST['phone']) || isset($_POST['privateNotes']) || isset($_POST['address']) || isset($_POST['postalCode']) || isset($_POST['town']))){
			if(isset($_POST['company'])){
				$company = sanitize_text_field($_POST['company']);
				$customer->setCompany(esc_html($company));
			}
			if(isset($_POST['companyAccount'])){
				$customer->setCompanyAccount($_POST['companyAccount'] == 'on');
			}
			if(isset($_POST['phone'])){
				$phone = sanitize_text_field($_POST['phone']);
				$customer->setPhone(esc_html($phone));
			}
			if(isset($_POST['privateNotes'])){
				$privateNotes = RESA_Tools::formatTextaeraHTML($_POST['privateNotes']);
				$customer->setPrivateNotes($privateNotes);
			}
			if(isset($_POST['address'])){
				$address = sanitize_text_field($_POST['address']);
				$customer->setAddress(esc_html($address));
			}
			if(isset($_POST['postalCode'])){
				$postalCode = sanitize_text_field($_POST['postalCode']);
				$customer->setPostalCode(esc_html($postalCode));
			}
			if(isset($_POST['town'])){
				$town = sanitize_text_field($_POST['town']);
				$customer->setTown(esc_html($town));
			}
			if(isset($_POST['country'])){
				$country = sanitize_text_field($_POST['country']);
				$customer->setCountry(esc_html($country));
			}
			$customer->save(false);
		}
		else if($customer->isLoaded() && !$customer->isRESACustomer() && !$customer->isAdministrator()){
			if(isset($_POST['seeSettings'])){
				$customer->setSeeSettings($_POST['seeSettings'] == 'on');
			}
			else $customer->setSeeSettings(false);
			$customer->save(false);
		}
		RESA_Customer::initializeAutoIncrement();
	}

	public function registerScriptsStyles()
	{
		if(get_option('resa_redirect_setup')){
			update_option('resa_redirect_setup', 0);
			wp_safe_redirect(RESA_Variables::getLinkParameters('installation'));
			exit;
		}
		if(isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'resa') !== false){
			$this->initTinyMCE();
			foreach($this->allControllers as $controller) {
				$controller->useScriptsAndStyles();
				if($_REQUEST['page'] == 'resa_parameters'){
					$this->removeDefaultStyles();
				}
			}
		}
	}

	public function registerAjaxMethods(){
		foreach($this->allControllers as $controller) {
			$controller->registerAjaxMethods();
		}
	}

	public function initTinyMCE(){
		add_filter( 'mce_external_plugins', array($this, 'loadRESAShorcodesPlugin'));
		add_filter( 'mce_buttons_2', array($this, 'loadRESAShorcodesButtons'));
	}

	public function loadRESAShorcodesPlugin( $plugin_array ) {
		 $plugin_array['RESA_shortcodes'] = plugins_url('js/libs/tinyMCERESAShortcodes.js', __FILE__ );
		 return $plugin_array;
	}

	public function loadRESAShorcodesButtons( $buttons ) {
		array_push( $buttons, 'RESAMenuShortCodes' );
		return $buttons;
	}

	public function removeDefaultStyles() {
		$stylesToDeletre = array('forms');

		// loop over all of the registered scripts
		foreach ($stylesToDeletre as $handle)
		{
			wp_deregister_style($handle);
			wp_dequeue_style($handle);
		}
	}
}
