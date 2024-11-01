<?php
/**
 * the main plugin file
 *
 * @package WP Monitorus Plugin
 * @since 0.1
 */
 
/*
Plugin Name: WP-Monitor.us
Plugin URI: http://wordpress.org/extend/plugins/wp-monitorus
Description: This plugin displays information from your Monitor.us account on the WordPress dashboard. Please note: This plugin is in no way associtated with Monitor.us or Monitis. This is a third party plugin.
Version: 0.3
Author: Daryl Lozupone <dlozupone@renegadetechconsulting.com>
Text Domain: wpmonitorus
License: GPLv2 or later
*/

/*  Copyright 2012 Daryl Lozupone <dlozupone@renegadetechconsulting.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// This plugin requires PHP >= 5.3.0
if( version_compare(PHP_VERSION, '5.3.0', '>=' ) ):
		
	// Establish our constants
	/**
	 * the plugin slug
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_SLUG', 'wpmonitorus' );
	/**
	 * uri to plugin directory
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_URI', plugin_dir_url(__FILE__) );
	/**
	 * absolute path to plugin directory
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_PATH', plugin_dir_path(__FILE__) );
	/**
	 * Text domain for gettext functions
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_TXTDMN', 'wpmonitorus' );
	/**
	 * plugin version
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_VERSION', '0.2' );
	/**
	 * used for registering activation hooks
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_MAIN_FILE', plugin_basename( __FILE__ ) );
	
	/**
	 * path to plugin directory relative to /wp-content/plugins
	 * @package WP Monitorus Plugin
	 * @since 0.1
	 */
	define( 'WPMON_PATH_REL', dirname( plugin_basename( __FILE__ ) ) );
	
	load_plugin_textdomain( WPMON_TXTDMN, false, WPMON_PATH_REL . '/languages/' );
	
	require 'includes/wpmon-settings.php';
	require_once 'includes/wpmon.php';
	
else:
	add_action ( 'admin_notices', 'wpmon_php_error_notice' );
endif;



function wpmon_load_txtdomain() {
	load_plugin_textdomain( WPMON_TXTDMN, false, WPMON_PATH_REL . '/languages/' );
}

/**
 * error notice for failed php version check
 *
 * @package WP Monitorus Plugin
 *
 * @return void
 * @since 0.1
 */
function wpmon_php_error_notice() {
	printf( '<div class="error"><p>%s</p></div>', 
		sprintf( __( /*translators: leave the %s unchanged. */ 'WP Monitor.us requires PHP >= %s, and you are currently running %s. Please upgrade in order to use this plugin', WPMON_TXTDMN ), '5.3.0', PHP_VERSION ) 
	);
}
?>