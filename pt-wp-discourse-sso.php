<?php
/**
 *
 * @package   WP_Discourse_SSO
 * @author    ET Cook <e@etc.io>
 * @license   GPL-2.0+
 * @link      http://primetimecode.com
 * @copyright 2014 PrimeTimeCode
 *
 * Plugin Name:       PrimeTime WP Discourse SSO
 * Plugin URI:        http://etc.io
 * Description:       Single Sign on between WordPress and Discourse
 * Version:           0.2.3
 * GitHub Plugin URI: https://github.com/PrimeTimeCode/pt-wp-discourse-sso
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Set some constants
define('PT_WP_DISCOURSE_SSO_VERSION', '0.2.2');
define('PT_WP_DISCOURSE_SSO_DIR', plugin_dir_path( __FILE__ ));
define('PT_WP_DISCOURSE_SSO_URL', plugins_url( '', __FILE__ ));
/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/
require_once( PT_WP_DISCOURSE_SSO_DIR . 'public/class-pt-wp-discourse-sso.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'WP_Discourse_SSO', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Discourse_SSO', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_Discourse_SSO', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-pt-wp-discourse-sso-admin.php' );
	add_action( 'plugins_loaded', array( 'WP_Discourse_SSO_Admin', 'get_instance' ) );

}
