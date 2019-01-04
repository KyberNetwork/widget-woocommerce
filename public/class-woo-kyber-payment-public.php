<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       developer.kyber.network
 * @since      1.0.0
 *
 * @package    Woo_Kyber_Payment
 * @subpackage Woo_Kyber_Payment/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_Kyber_Payment
 * @subpackage Woo_Kyber_Payment/public
 * @author     Hoang Ha <halink0803@gmail.com>
 */
class Woo_Kyber_Payment_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Kyber_Payment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Kyber_Payment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-kyber-payment-public.css', array(), "0.0.3", 'all' );
		$kyber_settings= get_option( 'woocommerce_kyber_settings', 1 );
		$version = $kyber_settings['version'];
		$version = $version ? $version : "v0.2";
        // wp_enqueue_style( "woo-kyber-pyment-widget-css", sprintf('https://widget.kyber.network/%s/widget.css', $version), array(), "0.0.2", 'all' );
        wp_enqueue_style( "woo-kyber-payment-widget-css", 'https://widget.kyber.network/v0.4/widget.css', array(), "", 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Kyber_Payment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Kyber_Payment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$kyber_settings= get_option( 'woocommerce_kyber_settings', 1 );
		$version = $kyber_settings['version'];
		$version = $version ? $version : "v0.3";
        // wp_enqueue_script( "woo-kyber-payment-widget-js", sprintf('https://widget.kyber.network/%s/widget.js', $version), array(), $this->version, true);
        wp_enqueue_script( "woo-kyber-payment-widget-js", 'https://widget.kyber.network/v0.4/widget.js', array(), $this->version, true);
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-kyber-payment-public.js', array( 'jquery' ), "0.0.2", true);
	}

}
