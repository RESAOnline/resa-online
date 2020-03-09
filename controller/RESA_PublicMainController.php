<?php if ( ! defined( 'ABSPATH' ) ) exit;

class RESA_PublicMainController extends RESA_ParentController
{

	public function __construct()
	{
		parent::__construct();
		add_action( 'wp_loaded', array( $this, 'actions' ) );

		$this->allControllers = array();
		$RESA_FormPublic = new RESA_FormPublicController();
		$RESA_AskAccount = new RESA_AskAccountController();
		$RESA_Account = new RESA_AccountController();
		array_push($this->allControllers, $RESA_FormPublic);
		array_push($this->allControllers, $RESA_AskAccount);
		array_push($this->allControllers, $RESA_Account);

		add_action('init', array('RESA_Session', 'start'));
		add_action('wp_logout', array('RESA_Session', 'delete'));
		add_shortcode('RESA_form', array($this, 'formShortCode'));
		add_shortcode('RESA_askAccount', array($this, 'askAccountShortCode'));
		add_shortcode('RESA_account', array($this, 'accountShortCode'));
		add_shortcode('RESA_bill', array($this, 'getResultShortcodeBill'));
		add_shortcode('RESA_price', array($this, 'getPriceShortcode'));
		add_shortcode('RESA_voucher', array($this, 'voucherShortcode'));

		$global = new RESA_GlobalController();
		$global->registerAjaxMethods(); //Seens not function
	}


	public function formShortCode($atts){
		$atts = shortcode_atts(array('form' => '', 'services'=>'', 'quotation' => false, 'typesaccounts' => ''), $atts, 'RESA_form');
		$serviceSlugs = array();
		if(!empty($atts['services'])){
			$serviceSlugs = explode(',', $atts['services']);
		}
		$typesAccounts = array();
		if(!empty($atts['typesaccounts'])){
			$typesAccounts = explode(',', $atts['typesaccounts']);
		}

		$RESA_FormPublic = $this->allControllers[0];
		$RESA_FormPublic->setForm($atts['form']);
		$RESA_FormPublic->setServiceSlugs($serviceSlugs);
		$RESA_FormPublic->setQuotation($atts['quotation']);
		$RESA_FormPublic->setTypesAccounts($typesAccounts);
		ob_start();
		echo $RESA_FormPublic->initialize();
		return ob_get_clean();
	}

	public function askAccountShortCode($atts){
		$atts = shortcode_atts(array('typeaccount' => ''), $atts, 'RESA_form');

		$RESA_AskAccount = $this->allControllers[1];
		$RESA_AskAccount->setTypeAccount($atts['typeaccount']);
		ob_start();
		echo $RESA_AskAccount->initialize();
		return ob_get_clean();
	}

	public function accountShortCode($atts){
		ob_start();
		echo $this->allControllers[2]->initialize();
		return ob_get_clean();
	}

	public function getResultShortcodeBill(){
		if(count($this->allControllers) > 0){
			$this->allControllers[0]->useScriptsAndStyles();
		}
		$booking = new RESA_Booking();
		$idBooking = RESA_Session::load('RESA_booking');
		if(isset($idBooking) && $idBooking >= 0){
			$booking->loadById($idBooking);
			return RESA_Algorithms::generateHTMLBill($booking, $booking->haveAdvancePayment());
		}
		else return __('no_bill_error', 'resa');
	}

	/**
	* Return the price correspond to params of shortcode
	*/
	public function getPriceShortcode($atts){
		$atts = shortcode_atts(array('service'=>'','price'=>''), $atts, 'RESA_price');
		if(!empty($atts['service']) && !empty($atts['price'])){
			$service = new RESA_Service();
			$service->loadBy(array('oldService'=>false, 'slug'=>$atts['service']));
			foreach($service->getServicePrices() as $servicePrice){
				if($servicePrice->getSlug() == $atts['price']){
					return $servicePrice->getPrice().''.get_option('resa_settings_payment_currency');
				}
			}
			return __('shortcode_price_error', 'resa');
		}
		else return __('shortcode_price_error', 'resa');
	}

	/**
	* Return the content of voucher shortcode
	*/
	public function voucherShortcode($atts, $content = null){
		$atts = shortcode_atts(array('voucher'=>''), $atts, 'RESA_price');
		if(isset($_GET['voucher']) && (empty($atts['voucher']) || $atts['voucher'] == $_GET['voucher'])){
			return $content;
		}
		return '
		<span id="voucherShortcode_' . $atts['voucher'] . '" style="display:none">'.$content.'</span>
		<script>
		var voucher = \'' . $atts['voucher'] . '\';
		if(localStorage[\'resa\'] != null){
			var object = JSON.parse(localStorage[\'resa\']);
			if(object.couponsList.length > 0){
				if(object.couponsList.indexOf(voucher) != -1){
					jQuery(\'span[id=voucherShortcode_' . $atts['voucher'] . ']\').show();
				}
				else {
					jQuery(\'span[id=voucherShortcode_]\').show();
				}
			}
		}
		</script>
		';
	}


	/**
	* \fn actions
	* \brief realize some actions if necessary.
	*/
	public function actions(){
		if(isset($_REQUEST['action'])) {
			if(class_exists('RESA_Swikly') && (sanitize_text_field($_REQUEST['action']) == RESA_Swikly::$SWIKLY_IPN_ACTION)){
				RESA_Swikly::swiklyIPN($_REQUEST);
			}
			else if(sanitize_text_field($_REQUEST['action']) == RESA_Customer::$ACTION_AUTOCONNECT){
				if(isset($_REQUEST['token'])){
					$customer = new RESA_Customer();
					$customer->loadUserByToken(sanitize_text_field($_REQUEST['token']));
					if($customer->isLoaded()){
						wp_set_current_user($customer->getId(), $customer->getLogin());
						wp_set_auth_cookie($customer->getId());
						do_action('wp_login', $customer->getLogin());
					}
					else {
						echo '<script>alert(\'' . __('expired_token_error_feedback', 'resa') . '\')</script>';
					}
				}
			}
		}
	}
}
