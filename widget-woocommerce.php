<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              developer.kyber.network
 * @since             0.1.0
 * @package           Woo_Kyber_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Kyber Payment
 * Plugin URI:        github.com/KyberNetwork/widget-woocommerce
 * Description:       Allow Woocommerce site to accept pay by token.
 * Version:           0.1.2
 * Author:            Kyber Network
 * Author URI:        developer.kyber.network
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-gateway-kyber 
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '0.1.0' );
define( 'WC_KYBER_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-kyber-payment-activator.php
 */
function activate_woo_kyber_payment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-kyber-payment-activator.php';
	Woo_Kyber_Payment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-kyber-payment-deactivator.php
 */
function deactivate_woo_kyber_payment() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-kyber-payment-deactivator.php';
	Woo_Kyber_Payment_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_kyber_payment' );
register_deactivation_hook( __FILE__, 'deactivate_woo_kyber_payment' );


/**
 * Add settings link to plugin settings page
 * 
 * @since 0.0.1
 */
function plugin_action_links( $links ) {
	$links = array_merge( array(
		'<a href="admin.php?page=wc-settings&tab=checkout&section=kyber">' . esc_html__( 'Settings', 'woocommerce-gateway-kyber' ) . '</a>',
	), $links );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'plugin_action_links' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-kyber-payment.php';
include plugin_dir_path( __FILE__ ) . 'update.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_woo_kyber_payment() {

	$plugin = new Woo_Kyber_Payment();
	$plugin->run();

}
run_woo_kyber_payment();
