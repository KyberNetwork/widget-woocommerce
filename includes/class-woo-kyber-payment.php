<?php

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';
use ETH\Monitor;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       developer.kyber.network
 * @since      1.0.0
 *
 * @package    Woo_Kyber_Payment
 * @subpackage Woo_Kyber_Payment/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Kyber_Payment
 * @subpackage Woo_Kyber_Payment/includes
 * @author     Hoang Ha <halink0803@gmail.com>
 */
class Woo_Kyber_Payment {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Kyber_Payment_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-kyber-payment';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->loader->add_action('plugins_loaded', $this, 'init_kyber_payment');
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Kyber_Payment_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Kyber_Payment_i18n. Defines internationalization functionality.
	 * - Woo_Kyber_Payment_Admin. Defines all hooks for the admin area.
	 * - Woo_Kyber_Payment_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-kyber-payment-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-kyber-payment-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-kyber-payment-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-kyber-payment-public.php';

		$this->loader = new Woo_Kyber_Payment_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Kyber_Payment_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Kyber_Payment_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_Kyber_Payment_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Kyber_Payment_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * @return string notice about missing required WooCommerce
	 * 
	 * @since 0.0.1
	 */
	public function missing_woocommerce_notice() {
		/* translators: warning when missing woocommerce as required plugin */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Kyber Payment requires %s to be installed and active.', 'woo-kyber-payment' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
	}

	/**
	 * Init plugin class
	 * 
	 * @since 0.0.1
	 */
	public function init_kyber_payment() {
		if ( ! class_exists("WC_Payment_Gateway") ) {
			add_action( 'admin_notices', array( $this, 'missing_woocommerce_notice' ) );
			return;
		}
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-kyber-payment-gateway.php';
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-kyber-logger.php'; 
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ),1000 );

		add_action( 'woocommerce_order_status_on-hold', array($this, 'kyber_on_order_on_hold'), 10, 2 );
		add_action( 'kyber_order_checking_cron', array( $this, 'kyber_order_checking_cron_function' ) );
		add_filter( 'cron_schedules', array( $this , 'kyber_cron_add_intervals') );
		add_action( 'wp', array( $this, 'kyber_schedule_cron') );
	}

	public function kyber_schedule_cron() {
		if ( !wp_next_scheduled( 'kyber_order_checking_cron' ) ) {
			wp_schedule_event(time(), 'custom_time', 'kyber_order_checking_cron');
		}
	}

	public function kyber_cron_add_intervals( $schedules ) {
		$schedules['custom_time'] = array(
			'interval' => 30,
			'display' => __( 'Every 30 seconds', 'woo-kyber-payment' )
		);
		return $schedules;
	}

	public function kyber_order_checking_cron_function() {
		$orders = wc_get_orders( array(
			'status' => 'on-hold',
			'payment_method' => 'kyber',
			'numberposts'=> -1
		));
		$numberOfOrders = count( $orders );
		for ( $index = 0; $index <= $numberOfOrders-1; $index++ ) {
			$this->kyber_on_order_on_hold( $orders[$index]->get_id(), $orders[$index] );
		}
	}

	/**
	 * 
	 * Add payment gateway methods to WooCommerce methods list
	 * 
	 * @param array methods list
	 * 
	 * @since 0.0.1
	 */
	function  add_payment_gateways ( $methods ) {
		$methods[] = 'WC_Kyber_Payment_Gateway';
		return $methods;
	}

	/**
	 * Check tx status when order status change to on hold
	 * 
	 * @param integer ID of order
	 * @param WC_Abstract_Order order
	 * 
	 * @return string tx status
	 * 
	 * @since 0.0.1
	 */
	function kyber_on_order_on_hold( $ID, $post) {
        $order = wc_get_order($ID);
		if ( $order->get_payment_method() != 'kyber') {
			return;
		}

		$kyber_settings= get_option( 'woocommerce_kyber_settings', 1 );
		$block_confirmation = $kyber_settings['block_confirmation'];
		if ( is_numeric( $block_confirmation ) ) {
			$block_confirmation = (int)$block_confirmation;
		} else {
			$block_confirmation = 30;
		}


		$network = $order->get_meta( 'network' );
		$receiveToken = $order->get_meta( 'receive_symbol' );

		// $useIntervalLoop = $kyber_settings['use_cron_job'] == 'true' ? false : true;
		$useIntervalLoop = false;

		$monitor = new Monitor([
			'node' => sprintf('https://%s.infura.io', $network),
			'network' => $network,
			'blockConfirm' => $block_confirmation ? $block_confirmation : 30,
			'txLostTimeout' => 15, // minutes
			'intervalRefetchTx' => 10, // seconds
			'checkPaymentValid' => true,
			'receivedAddress' => $kyber_settings['receive_addr'],
			'amount' => $order->get_meta( 'total_amount' ),
			'receivedToken' => $receiveToken,
			'useIntervalLoop' => $useIntervalLoop 
		  ]);

		  $tx = $order->get_meta( 'tx' );
		
		  $receipt = $monitor->checkStatus($tx);

		  if ( $receipt['status'] == 'SUCCESS' ) {

			  // check if payment is valid 
			  $valid = $receipt['paymentValid'];
			  if ( !$valid or is_null($valid) ) {
				  $order->update_status( 'failed',  __("Order payment failed", "woo-kyber-payment"));
				  $order->update_meta_data( 'tx_status', 'failed' );
				  $order->save();
				  return;
			  }
		      $order->update_status('processing', __("Awaiting cheque payment", "woo-kyber-payment"));
			  $order->update_meta_data( 'tx_status', 'success' );
			  $order->save();
		  } else if ( $receipt['status'] == 'FAIL' ) {
			  $order->update_status( 'failed', __("Order tx failed", "woo-kyber-payment" ) );
			  $order->update_meta_data( 'tx_status', 'failed' );
			  $order->save();
		  } else if ( $receipt['status'] == 'LOST' ) {
			  $order->update_status( 'failed', __("Order tx lost", "woo-kyber-payment") );
			  $order->update_meta_data( 'tx_status', 'lost' );
			  $order->save();
		  }
		  // if monitor time is more than 15 min then this tx consider lost
		  if ( (time() - $order->get_meta( "payment_time" )) / 60 > 15 ) {
			$order->update_status( 'failed', __("Order tx lost", "woo-kyber-payment") );
			$order->update_meta_data( 'tx_status', 'lost' );
			$order->save();	
		  }
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Kyber_Payment_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
