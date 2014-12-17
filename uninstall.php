<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   WP_Discourse_SSO
 * @author    ET Cook <e@etc.io>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// @TODO: Define uninstall functionality here