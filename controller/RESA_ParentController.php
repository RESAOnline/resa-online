<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_ParentController
{

	protected $allControllers;

  public function __construct()
	{
		add_action('admin_bar_menu', array( $this, 'createAdminBarMenu'), 999);
		add_action('plugins_loaded', array($this, 'pluginLoaded'));
		add_action( 'wp_loaded', array( $this, 'globalScripts' ) );
  }

	public function pluginLoaded(){
		add_filter( 'login_redirect', function( $url, $query, $user ) {
		 	$urlAccount = get_option('resa_settings_customer_account_url');
			if(isset( $user->roles ) && is_array( $user->roles ) &&
				!empty($urlAccount) && $user->roles[0] == RESA_Variables::getCustomerRole()){
				return $urlAccount;
			}
			else if(isset( $user->roles ) && is_array( $user->roles ) &&
				($user->roles[0] == RESA_Variables::getStaffRole() || $user->roles[0] == RESA_Variables::getManagerRole())){
				if(get_option('resa_backend_v2_activated', false)) {
					return get_admin_url().'admin.php?page=resa_backend';
				}
				return get_admin_url().'admin.php?page=resa_appointments';
			}
			return $url;
		}, 10, 3 );
		if(RESA_Variables::customerIsConnected()){
			show_admin_bar(false);
		}
		RESA_GDPRManager::getInstance()->registerHooks();
	}

	public function globalScripts(){
		RESA_Crons::getInstance()->registerScheduleEvents();
		wp_enqueue_script('jquery');
	}

  public function createAdminBarMenu($wp_admin_bar){
    if(current_user_can( 'manage_options' )){
  		$prefixUrl = get_admin_url().'admin.php';
  		$args = array(
  			'id' => 'resa_bar',
  			'title' => 'RESA-Online',
  			'href' => $prefixUrl.'?page='.(get_option('resa_backend_v2_activated')?'resa_backend':'resa_appointments'),
  			'meta' => array(
  				'class' => '',
  				'title' => 'RESA-Online'
  				)
  			);
  		$wp_admin_bar->add_node($args);
			if(!get_option('resa_backend_v2_activated', false)){
	  		$args = array(
	  			'id' => 'resa_calendar',
	  			'title' => __('calendar_link_title','resa'),
	  			'href' => $prefixUrl.'?page=resa_appointments',
	  			'parent' => 'resa_bar',
	  			'meta' => array(
	  				'class' => '',
	  				'title' => __('calendar_link_title','resa')
	  				)
	  			);
	  		$wp_admin_bar->add_node($args);
	  		$args = array(
	  			'id' => 'resa_add_booking',
	  			'title' => __('add_booking_link_title','resa'),
	  			'href' => $prefixUrl.'?page=resa_appointments&view=addBooking',
	  			'parent' => 'resa_bar',
	  			'meta' => array(
	  				'class' => '',
	  				'title' => __('add_booking_link_title','resa')
	  				)
	  			);
	  		$wp_admin_bar->add_node($args);
				$args = array(
	  			'id' => 'resa_customers',
	  			'title' => __('customers_title','resa'),
	  			'href' => $prefixUrl.'?page=resa_appointments&subPage=clients',
	  			'parent' => 'resa_bar',
	  			'meta' => array(
	  				'class' => '',
	  				'title' => __('customers_title','resa')
	  				)
	  			);
		  		$wp_admin_bar->add_node($args);
			}
			else {
				$args = array(
	  			'id' => 'resa_booking',
	  			'title' => __('bookings_title','resa'),
	  			'href' => $prefixUrl.'?page=resa_backend',
	  			'parent' => 'resa_bar',
	  			'meta' => array(
	  				'class' => '',
	  				'title' => __('customers_title','resa')
	  				)
	  			);
		  	$wp_admin_bar->add_node($args);
			}
			$args = array(
  			'id' => 'resa_parameters',
  			'title' => __('parameters_title','resa'),
  			'href' => $prefixUrl.'?page=resa_parameters',
  			'parent' => 'resa_bar',
  			'meta' => array(
  				'class' => '',
  				'title' => __('parameters_title','resa')
  				)
  			);
  		$wp_admin_bar->add_node($args);
    }
	}
}
