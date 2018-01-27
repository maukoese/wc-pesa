<?php
/**
 * Plugin Name: WooCommerce Pesa
 * Plugin URI: https://wc-pesa.mauko.co.ke/
 * Description: This plugin extends WordPress and WooCommerce functionality to integrate Kenyan mobile payments from Safaricom M-Pesa, Airtel Money and Equitel Money.
 * Author: Mauko Maunde < hi@mauko.co.ke >
 * Version: 0.18.01
 * Author URI: https://mauko.co.ke/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ){
	exit;
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
	return;
}

define( 'PESA_DIR', plugin_dir_url( __FILE__ ) );
define( 'WC_PESA_VERSION', '0.18.01' );

add_filter( 'plugin_action_links_'.plugin_basename( __FILE__ ), 'pesa_action_links' );
add_filter( 'plugin_row_meta', 'pesa_row_meta', 10, 2 );
register_activation_hook( __FILE__, 'wc_pesa_install' );
register_uninstall_hook( __FILE__, 'wc_pesa_uninstall' );

require_once( 'transactions.php' ); 

function pesa_action_links( $links )
{
	return array_merge( $links, [ '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pesa' ).'">&nbsp;Preferences</a>' ] );
} 

function pesa_row_meta( $links, $file )
{
	$plugin = plugin_basename( __FILE__ );

	if ( $plugin == $file ) {
		$row_meta = array(
			'github'    => '<a href="' . esc_url( 'https://github.com/ModoPesa/wc-pesa/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Contribute on Github', 'woocommerce' ) . '">' . esc_html__( 'Github', 'woocommerce' ) . '</a>',
			'pro' => '<a href="' . esc_url( 'https://wc-pesa.mauko.co.ke/pro/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Get Pro Version', 'woocommerce' ) . '">' . esc_html__( 'Get pro', 'woocommerce' ) . '</a>'
		);

		return array_merge( $links, $row_meta );
	}

	return (array) $links;
}

function wc_pesa_install()
{
	global $wpdb;

	update_option( 'wc_pesa_version', WC_PESA_VERSION );

	$pesa_ipn_table = $wpdb -> prefix . "woocommerce_pesa_ipn"; 

	$charset_collate = $wpdb -> get_charset_collate();

	$sql = "CREATE TABLE $pesa_ipn_table (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`code` varchar(50) NOT NULL,
		`order_id` varchar(255) NOT NULL,
		`first_name` varchar(50) NOT NULL,
		`last_name` varchar(50) NOT NULL,
		`phone_number` varchar(50) NOT NULL,
		`telco` varchar(255) NOT NULL,
		`amount` int(11) unsigned NOT NULL,
		`paid` int(11) unsigned NOT NULL,
		`balance` varchar(50) NOT NULL,
		`status` varchar(50) NOT NULL,
		PRIMARY KEY (`id`)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	dbDelta( $sql );
}

/**
 * Cleaning up on uninstallation - remove database tables
 */
function wc_pesa_uninstall()
{
	// If uninstall not called from WordPress exit
	if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

	delete_option( 'wc_pesa_version' );

	global $wpdb;
	$pesa_ipn_table = $wpdb -> prefix . "wc_pesa_ipn";
	$wpdb -> query( "DROP TABLE IF EXISTS {$pesa_ipn_table}" );
}

add_action( 'plugins_loaded', 'wc_pesa_gateway_init', 11 );
add_filter( 'woocommerce_states', 'pesa_ke_woocommerce_counties' );
add_filter( 'woocommerce_payment_gateways', 'wc_pesa_add_to_gateways' );

//add_action( 'admin-menu', 'wc_pesa_menu');

function wc_pesa_menu()
{
	add_menu_page( 'Pesa', 'Pesa Transactions', 'manage_options', __FILE__, 'wc_pesa_transactions',
null, 90 );
	add_submenu_page( __FILE__, 'Pesa Transactions', 'Pesa Transactions', 'manage_options',
__FILE__, 'wc_pesa_transactions' );
	add_submenu_page( __FILE__, 'Pesa Preferences', 'Pesa Preferences', 'manage_options',
__FILE__, 'wc_pesa_preferences' );
}

function wc_pesa_transactions($value='')
{
	# code...
}

/**
 * Add Kenyan counties to list of woocommerce states
 */
function pesa_ke_woocommerce_counties( $counties ) 
{
	$counties['KE'] = array( 
		'BAR' => __( 'Baringo', 'woocommerce' ),
		'BMT' => __( 'Bomet', 'woocommerce' ),
		'BGM' => __( 'Bungoma', 'woocommerce' ),
		'BSA' => __( 'Busia', 'woocommerce' ),
		'EGM' => __( 'Elgeyo-Marakwet', 'woocommerce' ),
		'EBU' => __( 'Embu', 'woocommerce' ),
		'GSA' => __( 'Garissa', 'woocommerce' ),
		'HMA' => __( 'Homa Bay', 'woocommerce' ),
		'ISL' => __( 'Isiolo', 'woocommerce' ),
		'KAJ' => __( 'Kajiado', 'woocommerce' ),
		'KAK' => __( 'Kakamega', 'woocommerce' ),
		'KCO' => __( 'Kericho', 'woocommerce' ),
		'KBU' => __( 'Kiambu', 'woocommerce' ),
		'KLF' => __( 'Kilifi', 'woocommerce' ),
		'KIR' => __( 'Kirinyaga', 'woocommerce' ),
		'KSI' => __( 'Kisii', 'woocommerce' ),
		'KIS' => __( 'Kisumu', 'woocommerce' ),
		'KTU' => __( 'Kitui', 'woocommerce' ),
		'KLE' => __( 'Kwale', 'woocommerce' ),
		'LKP' => __( 'Laikipia', 'woocommerce' ),
		'LAU' => __( 'Lamu', 'woocommerce' ),
		'MCS' => __( 'Machakos', 'woocommerce' ),
		'MUE' => __( 'Makueni', 'woocommerce' ),
		'MDA' => __( 'Mandera', 'woocommerce' ),
		'MAR' => __( 'Marsabit', 'woocommerce' ),
		'MRU' => __( 'Meru', 'woocommerce' ),
		'MIG' => __( 'Migori', 'woocommerce' ),
		'MBA' => __( 'Mombasa', 'woocommerce' ),
		'MRA' => __( 'Muranga', 'woocommerce' ),
		'NBO' => __( 'Nairobi', 'woocommerce' ),
		'NKU' => __( 'Nakuru', 'woocommerce' ),
		'NDI' => __( 'Nandi', 'woocommerce' ),
		'NRK' => __( 'Narok', 'woocommerce' ),
		'NYI' => __( 'Nyamira', 'woocommerce' ),
		'NDR' => __( 'Nyandarua', 'woocommerce' ),
		'NER' => __( 'Nyeri', 'woocommerce' ),
		'SMB' => __( 'Samburu', 'woocommerce' ),
		'SYA' => __( 'Siaya', 'woocommerce' ),
		'TVT' => __( 'Taita Taveta', 'woocommerce' ),
		'TAN' => __( 'Tana River', 'woocommerce' ),
		'TNT' => __( 'Tharaka-Nithi', 'woocommerce' ),
		'TRN' => __( 'Trans-Nzoia', 'woocommerce' ),
		'TUR' => __( 'Turkana', 'woocommerce' ),
		'USG' => __( 'Uasin Gishu', 'woocommerce' ),
		'VHG' => __( 'Vihiga', 'woocommerce' ),
		'WJR' => __( 'Wajir', 'woocommerce' ),
		'PKT' => __( 'West Pokot', 'woocommerce' )
	);

	return $counties;
}

/*
 * Register our gateway with woocommerce
 */
function wc_pesa_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_PESA';
	return $gateways;
}

function wc_pesa_gateway_init() 
{
	/**
	 * @class WC_Gateway_Pesa
	 * @extends WC_Payment_Gateway
	 */
	class WC_Gateway_PESA extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() 
		{
			// Setup general properties
			$this->setup_properties();

			// Load settings
			$this -> init_form_fields();
			$this -> init_settings();

			$this -> mpesa              = $this -> get_option( 'mpesa', );
			$this -> airtel              = $this -> get_option( 'airtel' );
			$this -> equitel             = $this -> get_option( 'equitel' );

			// Get settings
			$this -> title              = $this -> get_option( 'title' );
			$this -> description        = $this -> get_option( 'description' );
			$this -> instructions       = $this -> get_option( 'instructions' );
			$this -> enable_for_methods = $this -> get_option( 'enable_for_methods', array() );
			$this -> enable_for_virtual = $this -> get_option( 'enable_for_virtual', 'yes' ) === 'yes' ? true : false;

			add_action( 'woocommerce_thankyou_' . $this -> id, array( $this, 'thankyou_page' ) );
			add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this -> id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Setup general properties for the gateway.
		 */
		protected function setup_properties()
		{
			$this -> id                 = 'pesa';
			$this -> icon               = apply_filters( 'woocommerce_pesa_icon', PESA_DIR.'pesa.png' );
			$this -> method_title       = __( 'Lipa Na Pesa', 'woocommerce' );
			$this -> method_description = __( '<h4>Receive payments securely, quickly and conveniently from Safaricom M-Pesa, Airtel Money and Equitel Money.<br>
				<a href="https://github.com/ModoPesa/wc-pesa" target="_blank" >Contribute on Github</a> | <a href="https://developer.safaricom.co.ke/docs/" target="_blank" >Pesa API Docs</a> | <a href="https://wc-pesa.mauko.co.ke/pro/" target="_blank" >Get Pro</a></h4>' );
			$this -> has_fields         = true;
		}

		/**
		 * Initialise Gateway Settings Form Fields.
		 */
		public function init_form_fields() {
			$shipping_methods = array();

			foreach ( WC() -> shipping() -> load_shipping_methods() as $method ) {
				$shipping_methods[ $method -> id ] = $method -> get_method_title();
			}

			$this -> form_fields = array( 
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'woocommerce' ),
					'label'       => __( 'Enable '.$this -> method_title, 'woocommerce' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				 ),
				'title' => array( 
					'title'       => __( 'Method Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Payment method name that the customer will see on your checkout.', 'woocommerce' ),
					'default'     => __( 'Lipa Na Pesa', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'mpesa' => array( 
					'title'       => __( 'MPesa Shortcode', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your MPesa Business Till/Paybill Number.', 'woocommerce' ),
					'default'     => __( 'MPesa Shortcode', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'airtel' => array( 
					'title'       => __( 'Airtel Money Shortcode', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Airtel Money Business Till/Paybill Number.', 'woocommerce' ),
					'default'     => __( 'Airtel Shortcode', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'equitel' => array( 
					'title'       => __( 'Equitel Money Shortcode', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Your Equitel Till/Paybill Number.', 'woocommerce' ),
					'default'     => __( 'Equitel Shortcode', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'description' => array( 
					'title'       => __( 'Method Description', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
					'default'     => __( '
<div id="payment-instructions">
<h3>' . __('Safaricom MPesa Instructions', 'woocommerce') . '</h3>
<p>
  ' . __('On your Safaricom phone go the M-PESA menu', 'woocommerce') . '</br>
  ' . __('Select Lipa Na M-PESA and then select Buy Goods and Services', 'woocommerce') . '</br>
  ' . __('Enter the Till Number <strong>' . $this -> mpesa . '</strong>', 'woocommerce') . ' </br>
  ' . __('Enter the exact amount due and follow prompts', 'woocommerce') . '</br>
  ' . __('You will receive a confirmation SMS from M-PESA with a receipt number.', 'woocommerce') . ' </br>
  ' . __('Input your mobile service provider and the receipt number that you received from M-PESA below.', 'woocommerce') . '</br>
</p>
<h3>' . __('Airtel Money Instructions', 'woocommerce') . '</h3>
<p>
  ' . __('On your Airtel phone go the Airtel Money menu', 'woocommerce') . '</br>
  ' . __('Select Buy Goods and Services', 'woocommerce') . '</br>
  ' . __('Enter the Till Number <strong>' . $this -> airtel . '</strong>', 'woocommerce') . ' </br>
  ' . __('Enter the exact amount due and follow prompts', 'woocommerce') . '</br>
  ' . __('You will receive a confirmation SMS from Airtel with a receipt number.', 'woocommerce') . ' </br>
  ' . __('Input your mobile service provider and the receipt number that you received from Airtel below.', 'woocommerce') . '</br>
</p>
<h3>' . __('Equitel Money Instructions', 'woocommerce') . '</h3>
<p>
  ' . __('On your phone go the Equitel Money menu', 'woocommerce') . '</br>
  ' . __('Select Buy Goods and Services', 'woocommerce') . '</br>
  ' . __('Enter the Till Number <strong>' . $this -> equitel . '</strong>', 'woocommerce') . ' </br>
  ' . __('Enter the exact amount due and follow prompts', 'woocommerce') . '</br>
  ' . __('You will receive a confirmation SMS from Equitel with a receipt number.', 'woocommerce') . ' </br>
  ' . __('Input your mobile service provider and the receipt number that you received from Equitel below.', 'woocommerce') . '</br>
</p>
</div>', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'instructions' => array( 
					'title'       => __( 'Instructions', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
					'default'     => __( 'Here is a summary of your order details:', 'woocommerce' ),
					'desc_tip'    => true,
				 ),
				'enable_for_methods' => array( 
					'title'             => __( 'Enable for shipping methods', 'woocommerce' ),
					'type'              => 'multiselect',
					'class'             => 'wc-enhanced-select',
					'css'               => 'width: 400px;',
					'default'           => '',
					'description'       => __( 'If Pesa is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'woocommerce' ),
					'options'           => $shipping_methods,
					'desc_tip'          => true,
					'custom_attributes' => array( 
						'data-placeholder' => __( 'Select shipping methods', 'woocommerce' ),
					 ),
				 ),
				'enable_for_virtual' => array( 
					'title'             => __( 'Accept for virtual orders', 'woocommerce' ),
					'label'             => __( 'Accept Pesa if the order is virtual', 'woocommerce' ),
					'type'              => 'checkbox',
					'default'           => 'yes',
				 ),
		   );
		}

		/**
		 * Check If The Gateway Is Available For Use.
		 *
		 * @return bool
		 */
		public function is_available() {
			$order          = null;
			$needs_shipping = false;

			// Test if shipping is needed first
			if ( WC() -> cart && WC() -> cart -> needs_shipping() ) {
				$needs_shipping = true;
			} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
				$order_id = absint( get_query_var( 'order-pay' ) );
				$order    = wc_get_order( $order_id );

				// Test if order needs shipping.
				if ( 0 < sizeof( $order -> get_items() ) ) {
					foreach ( $order -> get_items() as $item ) {
						$_product = $item -> get_product();
						if ( $_product && $_product -> needs_shipping() ) {
							$needs_shipping = true;
							break;
						}
					}
				}
			}

			$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

			// Virtual order, with virtual disabled
			if ( ! $this -> enable_for_virtual && ! $needs_shipping ) {
				return false;
			}

			// Only apply if all packages are being shipped via chosen method, or order is virtual.
			if ( ! empty( $this -> enable_for_methods ) && $needs_shipping ) {
				$chosen_shipping_methods = array();

				if ( is_object( $order ) ) {
					$chosen_shipping_methods = array_unique( array_map( 'wc_get_string_before_colon', $order -> get_shipping_methods() ) );
				} elseif ( $chosen_shipping_methods_session = WC() -> session -> get( 'chosen_shipping_methods' ) ) {
					$chosen_shipping_methods = array_unique( array_map( 'wc_get_string_before_colon', $chosen_shipping_methods_session ) );
				}

				if ( 0 < count( array_diff( $chosen_shipping_methods, $this -> enable_for_methods ) ) ) {
					return false;
				}
			}

			return parent::is_available();
		}

		public function payment_fields() {
			if ($description = $this->get_description()) {
			  echo wpautop(wptexturize($description));
			}

			$mpesa = is_numeric( $this -> get_option('mpesa') ) ? '<option value="mpesa" >Safaricom MPesa</option>' : '';
			$airtel = is_numeric( $this -> get_option('airtel') ) ? '<option value="airtel" >Airtel Money</option>' : "";
			$equitel = is_numeric( $this -> get_option('equitel') ) ? '<option value="equitel" >Equitel Money</option>' : "";

			echo '<h3>Confirm Payment</h3>
				<p class="form-row form-row form-row-wide woocommerce-validated select-2-container" id="telco_field" data-o_class="form-row form-row form-row-wide">
				<select name="telco">
				<option value="mpesa">Select Service Provider( Default: MPesa )</option>
					'.$mpesa.$airtel.$equitel.'
				</select></p>
				<p class="form-row form-row form-row-wide woocommerce-validated" id="code_field" data-o_class="form-row form-row form-row-wide">
					<label for="code" class="">Receipt Number<abbr class="required" title="required">*</abbr></label>
					<input type="text" class="input-text " name="code" id="code" placeholder="e.g LMX990KKII0" />
				</p>
				';
		}

		public function validate_fields() { 

			if ($_POST['code']) {
				$success = true;
			} else {					
				$error_message = __("The Receipt Number field is required", 'woothemes');
				wc_add_notice(__('Field error: ', 'woothemes') . $error_message, 'error');
				$success = false;
			}

			if ($_POST['telco']) {
				$success = true;
			} else {					
				$error_message = __("The Telco field is required", 'woothemes');
				wc_add_notice(__('Field error: ', 'woothemes') . $error_message, 'error');
				$success = false;
			}

			return $success;
		}


		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id )
		{
			$order = new WC_Order( $order_id );
			$total = wc_format_decimal( $order -> get_total(), 2 );
			$phone = $order -> get_billing_phone();
			$first_name = $order -> get_billing_first_name();
			$last_name = $order -> get_billing_last_name();

			// Remove the plus sign before the customer's phone number
			if ( substr( $phone, 0,1 ) == "+" ) {
				$phone = str_replace( "+", "", $phone );
			}

			// add decimal places if not there.
			if ( !strrpos( $total, "." ) ) {
				$total = "{$total}.00";
			}

			$code = trim( $_POST['code'] );
			$telcos = [ 'mpesa' => 'Safaricom MPesa', 'airtel' => 'Airtel Money', 'equitel' => 'Equitel Money' ];
			$telco = trim( $_POST['telco'] );

			//Temporarily set status as "processing", incase the Pesa API times out before processing our request
			$order -> update_status( 'on-hold', __( 'Awaiting confirmation of payment '.$code.' from '.$phone.' via '.$telcos[$telco].'.', 'woocommerce' ) );

			// Reduce stock levels
			wc_reduce_stock_levels( $order_id );

			// Remove cart
			WC() -> cart -> empty_cart();

	    	// Save transaction to db, pending confirmation
	    	global $wpdb;
	    	$pesa_ipn_table = $wpdb -> prefix . "woocommerce_pesa_ipn"; 

	    	$wpdb -> insert( 
	    		$pesa_ipn_table,
	    		[
					"code" => $code,
	    			"order_id" => $order_id,
					"first_name" => $first_name,
					"last_name" => $last_name,
					"phone_number" => $phone,
					"telco" => $telcos[$telco],
					"amount" => $total,
					"paid" => 0,
					"balance" => $total,
					"status" => "on-hold"
	    		]
	    	 );

			// Return thankyou redirect
			return array( 
				'result' 	=> 'success',
				'redirect'	=> $this -> get_return_url( $order ),
			 );
		}
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page()
		{
			if ( $this -> instructions ) {
				echo wpautop( wptexturize( $this -> instructions ) );
			}
		}

		/**
		 * Change payment complete order status to completed for Pesa orders.
		 *
		 * @since  3.1.0
		 * @param  string $status
		 * @param  int $order_id
		 * @param  WC_Order $order
		 * @return string
		 */
		public function change_payment_complete_order_status( $status, $order_id = 0, $order = false )
		{
			if ( $order && 'pesa' === $order -> get_payment_method() ) {
				$status = 'completed';
			}
			return $status;
		}

		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false )
		{
			if ( $this -> instructions && ! $sent_to_admin && $this -> id === $order -> get_payment_method() ) {
				echo wpautop( wptexturize( $this -> instructions ) ) . PHP_EOL;
			}
		}
	}
}
