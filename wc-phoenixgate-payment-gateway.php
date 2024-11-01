<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wpspins.com
 * @since             1.0.0
 * @package           Woo_Phxmn
 *
 * @wordpress-plugin
 * Plugin Name:       Payment Gateway for PhoeniXGate on WooCommerce
 * Plugin URI:        https://wpspins.com?utm-ref=wc-phoenixgate-payment-gateway
 * Description:       Phoenix’s unified e-commerce and multi-channel gateway solution for the payments industry.
 * Version:           2.2.0
 * Author:            WPSPIN LLC
 * Author URI:        https://wpspins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-phoenixgate-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Include the plugin administration functions if not already included.
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

// Check if WooCommerce is active.
if ( is_multisite() ) {
	// Check if WooCommerce is active network-wide or on the current site.
	if ( ! ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) || is_plugin_active( 'woocommerce/woocommerce.php' )) ) {
		return;
	}
} else {
	// Check if WooCommerce is active on a single site.
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
		return;
	}
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_PHXMN_VERSION', '2.2.0' );
define( 'WOO_PHXMN_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-phxmn-activator.php
 */
function activate_woo_phxmn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-phxmn-activator.php';
	Woo_Phxmn_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-phxmn-deactivator.php
 */
function deactivate_woo_phxmn() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-phxmn-deactivator.php';
	Woo_Phxmn_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_phxmn' );
register_deactivation_hook( __FILE__, 'deactivate_woo_phxmn' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-phxmn.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_phxmn() {

	$plugin = new Woo_Phxmn();
	$plugin->run();

}
run_woo_phxmn();
