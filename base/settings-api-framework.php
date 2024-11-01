<?php

/**
 * This file contains the base class for the WP Settings API
 *
 * @package WP Base Classes
 * @subpackage WP Settings API Framework
 *
 * @since 0.1
 * @version 1.0
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
 * @link https://sourceforge.net/p/wpbaseclasses
 */

/*
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

namespace rtcwpbase\WPSettingsAPI\v1_0 {
	use \rtcwpbase\WPframework\v1_0\WPbase;
	
	if(!class_exists( '\\' . __NAMESPACE__ . '\\WPSettingsAPI' ) ):
		require_once( 'wp-framework.php' );
		
		/**
		 * WP Settings API interface class
		 *
		 * @package WP Base Classes
		 * @subpackage WP Settings API Framework
		 * @since 0.1
		 * @abstract
		 */
		abstract class WPSettingsAPI extends WPbase {
			
			/**
			 * class version #
			 *
			 * @package WP Base Classes
 			 * @subpackage WP Settings API Framework
 			 * @var string
			 * @since 0.1
			 */
			private static $version = '1.0';
			
			/**
			 * the options pages
			 *
			 * This property is an array of arrays containing the arguments and values passed to the WP
			 * add_options_page function, stored as key => value pairs. This construct allows you to 
			 * add multiple menu pages, e.g. :
			 * <code>$this->pages = array(
					'main' => array( 'page_title' => __( "My Custom Plugin Options", $this->txtdomain ), 'menu_title' => __( 'My Custom Plugin', $this->txtdomain ), 'capability' => 'manage_options', 'menu_slug' => $this->slug, 'callback' => array( &$this, 'my_custom_plugin_options_page' ) )
					);</code>
			 * Optionally, the 'parent_slug' parameter can be included, in which case the page will be added
			 * to an exising menu item other than 'Settings'.
			 *
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/add_options_page#Parameters
			 */
			protected $pages;
			
			/**
			 * settings sections
			 *
			 * This property is an array of arrays containing the arguments and values passed to the WP
			 * add_settings_section function, stored as key => value pairs. This construct allows you to 
			 * add multiple sections, e.g. :
			 * <code>
			 * $this->sections = array(
			 *			'basic' => array( 'id' => 'basic-settings', 
			 * 				'title' => __( 'Basic Settings', $this->txtdomain ), 
			 * 				'callback' => array( &$this, 'sanitize_input' ), 'page' => $this->slug
			 *			),
			 *			'advanced' => array( 'id' => 'advanced-settings', 
			 *				'title' => __( 'Advanced Settings', $this->txtdomain ), 
			 *				'callback' => array( &$this, 'sanitize_input' ), 'page' => $this->slug )
			 * );
			 *</code>
			 *
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/add_settings_section#Parameters
			 */
			protected $sections;
			
			/**
			 * settings fields
			 *
			 * This property is an array of arrays containing the arguments and values passed to the WP
			 * add_settings_field function, stored as key => value pairs. This construct allows you to 
			 * add multiple fields, e.g. :
			 * <code>
			 * $this->fields = array(
			 * 			'option_1' => array( 'id' => 'option-1', 'title' => __( 'Text', $this->txtdomain ), 
			 * 			'callback' => array( &$this, 'option_one_html' ), 'page' => $this->slug, 
			 *			'section' => 'basic-settings'
			 *		),
			 *		'option_2' => array( 
			 *			'id' => 'option-two', 'title' => __( 'Email', $this->txtdomain ), 
			 *			'callback' => array( &$this, 'option_two_html' ), 'page' => $this->slug, 
			 *			'section' => 'basic-settings' 
			 *		)
			 * );
			 * </code>
			 *
			 *
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/add_settings_field#Parameters
			 * 
			 */
			protected $fields;
			
			/**
			 * option groups
			 *
			 * This property is an array of arrays containing the arguments and values passed to the WP
			 * add_settings_field function, stored as key => value pairs. Although non-standard in use,
			 * this construct allows you to add multiple option groups, e.g. :
			 * <code>
			 * $this->options = array(
			 *		'main' => array( 
			 *			'option_group' => "my-custom-plugin-options", 
			 *			'option_name' => "my_custom_plugin_settings", 
			 *			'sanitize_callback' => array( &$this, 'sanitize_input' )
			 *		)
			 * );
			 * </code>
			 *
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/register_setting#Parameters
			 */
			protected $options;
			
			
			/**
			 * class constructor
			 *
			 * @since 0.1
			 */
			protected function __construct() {
				parent::__construct();
				// add the appropriate stuff to WP actions
				add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
				add_action( 'admin_init', array( &$this, 'admin_init' ) );
				
				//register our ajax calls
				add_action( 'wp_ajax_wpmon_get_api_key', array( $this, 'get_api_key_ajax' ) );
			}
			
			/**
			 * set up class properties
			 *
			 * Implemented, but does nothing
			 *
			 * @since 0.1
			 */
			public function init() {}
			
			/**
			 * WP admin_menu action callback
			 *
			 * @since 0.1
			 * @return void
			 */
			public function admin_menu() {
				$this->add_options_pages( $this->pages );
			}
			
			/**
			 * WP admin_init action callback
			 *
			 * @since 0.1
			 * @return void
			 */
			public function admin_init() {
			 	
			 	$this->add_settings_sections( $this->sections );
			 	$this->add_settings_fields( $this->fields );
			 	$this->register_settings( $this->options );
			}
			 
			/**
			 * retrieve options for our theme or plugin
			 * 
			 * @param string $option name of the option to retrieve
			 *
			 * @return array $options contains the option names and values
			 *
			 * @since 0.1
			 *
			*/
			  
			public function get_options( $option = '' ) {
				if( $option === '' ) :
					if( is_array( $this->options ) ):
					 	foreach( $this->options as $option ) {
							$options[$option['option_name']] = get_option( $option['option_name'] );
					 	}
					 endif;
			 	else :
			 		$options = get_option( $option );
			 	endif;
				
			 	return( $options );
			}
			 
			 /**
			  * register the settings for our plugin or theme
			  *
			  * @package WP Base Classes
			  * @subpackage WP Settings API Framework
			  *
			  * @param array $settings contains the parameters for the WP register_setting function
			  *
			  * @return void
			  *
			  * @since 0.1
			  *
			  */
			  
			function register_settings( $settings ) {
				if( is_array( $settings ) ):
					foreach( $settings as $setting ) {
						register_setting( $setting['option_group'], $setting['option_name'], $setting['sanitize_callback'] );
					}
				endif;
			}
			
			/**
			 * add options pages to our theme or plugin
			 *
			 * This function uses either add_submenu_page or add_options_page, depending upon the presence
			 * of the 'parent_slug' array key in parameter $pages.
			 *
			 * @package WP Base Classes
			 * @subpackage WP Settings API Framework
			 *
			 * @param array $pages contains the parameters to pass to the WP add_options_page/add_submenu_page function
			 *
			 * @return void
			 *
			 * @since 0.1
			 *
			 */
			function add_options_pages( $pages ) {
				if( is_array( $pages ) ):
					foreach( $pages as $page ) {
						if( array_key_exists('parent_slug', $page ) ):
							add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'] );
						else:
							add_options_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'] );
						endif;
					}
				endif;
			}
			
			 
			 /**
			  * add setting sections to our theme or plugin options page(s)
			  *
			  * @package WP Base Classes
			  * @subpackage WP Settings API Framework
			  * 
			  * @param array $sections contains the parameters for the WP add_settings_section function
			  *
			  * @since 0.1
			  * @return void
			  *
			  */
			  
			function add_settings_sections( $sections ) {
				if( is_array( $sections ) ):
					foreach( $sections as $section ) {
						add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );
					}
				endif;
			}
			
			/**
			 * add setting fields to our theme or plugin options page(s)
			 *
			 * @package WP Base Classes
			 * @subpackage WP Settings API Framework
			 * 
			 * @param array fields contains the parameters for the WP add_settings_field function
			 *
			 * @return void
			 *
			 * @since 0.1
			 *
			 */
			  
			function add_settings_fields( $fields ) {
				if( is_array( $fields ) ):
					foreach( $fields as $field ) {
						if( !isset($field['args'] ) )
							$field['args'] = array();
						
						add_settings_field( $field['id'], $field['title'], $field['callback'], $field['page'], $field['section'], $field['args'] );
					}
				endif;
			}
			
			/**
			 * render basic options page HTML
			 *
			 * This utility function renders a very simple settings page using the Settings API.
			 * If you require a more intricate settings page, simply create your own callback function and
			 * register that function as the callback for the $pages object
			 *
			 * @param object $page  the page object from $pages property
			 * @param object $options  the option object from $options which is passed to WP settings_fields
			 *
			 * @return void
			 *
			 * @since 0.1
			 *
			*/
			 
			public function options_page( $page, $options ) {
				print( '<div class="wrap">' );
				printf( "<h2>%s</h2>", $page['page_title'] );
				
				print( '<form action="options.php" method="post">' );
				foreach( $options as $option ):			
					settings_fields( $option['option_group'] );
				endforeach;
				print( "<fieldset>" );
				do_settings_sections( $page['menu_slug'] );
				printf( "<input name='Submit' type='submit' value='%s' />", _x('Save Changes', 'text for the options page submit button', $this->txtdomain ) );
				print( "</fieldset>" );
				print( "</form>" );
				print( "</div>" );
			}
		}
	endif;
}
?>