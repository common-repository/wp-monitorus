<?php

/**
 * This file contains the base classs for WP
 *
 * @package WP Base Classes
 * @version 0.4
 * @link https://sourceforge.net/p/wpbaseclasses
 *
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
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

namespace rtcwpbase\WPframework\v1_0 {
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\SingletonBase' ) ):
		/**
		 * A singleton class implementation
		 *
		 * @package WP Base Classes
		 * @since 0.1
		 * @abstract
		 *
		 */
		 
		abstract class SingletonBase {
			/**
			 * class version #
			 *
			 * @package WP Base Classes
 			 * @subpackage Custom Post Type Framework
 			 * @var string
			 * @since 0.1
			 */
			private static $version = '1.0';
			
			/**
			 * store __CLASS__ = (instance of class) as key => value pairs
			 * 
			 * @since 0.1
			 * @var array
			 */
			 
			private static $instance = array();
			
			/**
			 * class constructor- available to child classes
			 * 
			 * @since 0.1
			 */
			protected function __construct() {
				//allow child classes to call parent::__construct
			}
			
			/**
			 * class init- available to child classes
			 *
			 * @since 0.1
			 * @abstract
			 */
			 
			abstract protected function init();	//MUST define in child classes
			
			/**
			 * instantiate the class
			 *
			 * This function is used to instantiate the class
			 *
			 * @since 0.1
			 *
			 * @return object - the instance of the called class
			 *
			 */
			public static function get_instance( $args = null ) {
				if( version_compare(PHP_VERSION, '5.3.0') >= 0 ):
					//get the name of the class called
					$called = get_called_class();
						
					//do we already have an instance of this class stored in self::$instance?
					if( !isset( self::$instance[$called] ) ):
						//no, so create a new instance
						self::$instance[$called] = new $called( $args );
						self::$instance[$called]->init();
					endif;
					
					return( self::$instance[$called] );
				else:
					die( "This package requires PHP >= 5.3.0. Your version is " . phpversion() );
				endif;
			}
		}
	endif;
	
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\WPbase' ) ):
		/**
		 * A library of setup functions, handlers, and utility functions for commonly used WordPress functions
		 *
		 * @package WP Base Classes
		 *
		 * @since 0.1
		 * @abstract
		 */
		abstract class WPbase extends SingletonBase {
			/**
			 * version info
			 * @package WP Base Classes
			 * @since 1.0
			 * @var string
			 */
			private static $version = '1.0';
			
			/**
			 * scripts for WP admin pages
			 *
			 * This property contains the the argument handles and argument values for wp_enqueue_script. They are
			 * stored as key => value pairs, e.g.:
			 * <code>$this->admin_scripts = array( array( 'handle' => 'my-custom-script', 'src' => 'http://www.example.com/js/script.js', 'deps' => array( 'jquery' ), 'ver' => false, 'in_footer' => false ) );</code>
			 * Additionally, this property can also contain the argument 'hook', which is an array of page
			 * hooks on which to enqueue the script. This argument can contain multiple page hooks. For example,
			 * to enqueue the  jquery datepicker on post and page edit screens use the following:
			 * <code>$this->admin_scripts = array( array( 'handle' => 'jquery-ui-datepicker', 'hook' => array( 'post.php', 'page.php' ) ) );</code>
			 *
			 * @package WP Base Classes
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Parameters
			 * @var array
			 */
			protected $admin_scripts = array();
			
			/**
			 * scripts for WP frontend
			 *
			 * @package WP Base Classes
			 * @since 0.4
			 * @var array
			 */
			protected $scripts = array();
			
			/**
			 * styles for WP admin pages
			 *
			 * @package WP Base Classes
			 * @since 0.4
			 * @var array
			 */
			protected $admin_css = array();
			
			/**
			 * styles for WP frontend
			 *
			 * @package WP Base Classes
			 * @since 0.4
			 * @var array
			 */
			protected $css = array();
			
			/**
			 * plugin txtdomain
			 * 
			 * @package pkgtoken
			 * @subpackage subtoken
			 * @var string
			 * @since 1.0
			 */
			protected $txtdomain;
			
			/**
			 * plugin path relative to /wp-content/plugins/
			 * 
			 * @package pkgtoken
			 * @subpackage subtoken
			 * @var string
			 * @since 1.0
			 */
			protected $plugin_path_rel;
			
			/**
			 * custom query variables
			 *
			 * This array structure must contain the following parameters:
			 * string url the actual query variable to listen for ( e.g. set this parameter to 'myqueryvar', and
			 * 	the callback will be triggered when calling the following url: http://example.com?myqueryvar )
			 * mixed callback the function called when this query variable is present
			 *
			 * @package WP Base Classes
			 * @since 0.4
			 * @var array
			 */
			protected $query_vars = array();
			
			/**
			 * class constructor
			 * 
			 * used for add_action and add_filter calls
			 *
			 * @package WP Base Classes
			 * @since 0.1
			 * @return void 
			 */
			protected function __construct() {				
				add_action( 'init', array( &$this, 'wp_init' ) );
				add_action( 'admin_init', array( &$this, 'admin_init' ) );
				add_action( 'admin_enqueue_scripts', array( &$this, 'print_admin_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( &$this, 'print_scripts' ) );
				
				// Register our plugin query variables
				add_filter('query_vars', array( &$this, 'register_query_variables' ) );
		
				// Add the Listener callback handler. Requests will be in the format of http://example.com/?callback
				add_action( 'parse_request', array( &$this, 'listener' ) );
			}
			
			/**
			 * setup class properties
			 * 
			 * @package WP Base Classes
			 * @since 0.1
			 * 
			 * @return void
			 */
			protected function init(){}
			
			/**
			 * the callback for WordPress init action
			 *
			 * This function is implemented, but does nothing
			 *
			 * @package WP Base Classes
			 * @since 0.1
			 * 
			 * @return void
			 */
			public function wp_init() {}
			
			/**
			 * the callback for WordPress admin_init action
			 *
			 * This function is implemented, but does nothing
			 *
			 * @package WP Base Classes
			 * @since 0.1
			 * 
			 * @return void
			 */
			public function admin_init() {}
			 
			/**
			 * calback for admin_print_scripts, adds necessary scripts and CSS to admin pages
			 *
			 * This function will add the scripts and css to ALL admin pages.
			 * To enable conditional loading, create a create your own function with conditional logic
			 *
			 * @package WP Base Classes
			 * 
			 * @param string $hook the page being called ( e.g. post.php ), passed from admin_enqueue_scripts action
			 *
			 * @since 0.1
			 *
			 * @return void
			 *
			 */
			public function print_admin_scripts( $hook ) {
				if( is_admin() ) :
					$this->enqueue_scripts( $this->admin_scripts, false, $hook );
					$this->enqueue_scripts( $this->admin_css, true, $hook );
					$this->localize_scripts( $this->admin_localization_scripts, $hook );
				endif;
			}
			
			/**
			 * add script and CSS to WP front end
			 *
			 * PLEASE NOTE : this function will add js/css to ALL front end pages
			 * To enable conditional loading, create a print_scripts function in a child class and
			 * include the conditional logic there.
			 *
			 * @package WP Base Classes
			 * 
			 * @since 0.1
			 *
			 * @return void
			 *
			 */
			public function print_scripts() {
				$this->enqueue_scripts( $this->scripts );
				$this->enqueue_scripts( $this->css, true );
			}
			
			/**
			 * adds scripts and css to rendered pages
			 *
			 * @package WP Base Classes
			 * 
			 * @param array $scripts - an array or arrays containing the key/value pairs of arguments/values for enqueue functions
			 * @param bool $style (optional - required if $hook is specified) false for scripts, true for styles
			 * @param string $hook (optional) the current page hook
			 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style wp_enqueue_style() in the codex
			 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script wp_enqueue_script() in the codex
			 *
			 * @since 0.1
			 *
			 * @return void
			 *
			 */
			public static function enqueue_scripts( $scripts, $style = false, $hook = null ) {
				if( is_array( $scripts ) ):
					foreach( $scripts as $script ) {
						if( ( isset( $script['hook'] ) && in_array( $hook, $script['hook'] ) ) || !isset( $script['hook'] ) ):
							if( $script['src'] != '' ) :
								if( $style ):
									wp_enqueue_style( $script['handle'], $script['src'], $script['deps'], $script['ver'], $script['media'] );
								else:
									wp_enqueue_script( $script['handle'], $script['src'], $script['deps'], $script['ver'], $script['in_footer'] );
								endif;
							else:
								if( $style ):
									wp_enqueue_style( $script['handle'] );
								else:
									wp_enqueue_script( $script['handle'] );
								endif;
							endif;
						endif;
					}
				endif;
			}
			
			/**
			 * localizes scripts added to pages
			 *
			 * @package WP Base Classes
			 * 
			 * @param array $scripts - an array of arrays containing the key/value pairs of arguments/values for locale functions
			 * @param string $hook (optional) the current page hook
			 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style wp_localize_script() in the codex
			 *
			 * @since 1.0
			 */
			 public function localize_scripts( $scripts, $hook = '' )
			 {
			 	if( is_array( $scripts ) ):
			 		foreach( $scripts as $script ):
						if( ( isset( $script['hook'] ) && in_array( $hook, $script['hook'] ) ) || !isset( $script['hook'] ) )
							wp_localize_script( $script['handle'], $script['object_name'], $script['l10n'] );
					endforeach;
				endif;
			 }
			 
			/**
			 * registers metaboxes for the post/page edit screen
			 *
			 * @package WP Base Classes
			 * @since 0.1
			 * @link http://codex.wordpress.org/Function_Reference/add_meta_box WordPress add_meta_box() function
			 * @param array $metaboxes - an array of arrays containing the arguments for WP add_meta_box as arg_name => value pairs
			 * @return void
			 */
			public function add_meta_boxes( $post = null, $metaboxes = null ) {
				if( is_array( $metaboxes ) ):
					foreach( $metaboxes as $metabox ) {
						add_meta_box( $metabox['id'], $metabox['title'], $metabox['callback'], $metabox['post_type'], $metabox['context'], $metabox['priority'], $metabox['callback_args'] ); 
					}
				endif;
			}
			
			/**
			 * adds query variables to WordPress
			 *
			 * @package WP Base Classes
			 * 
			 * @param array $ary_vars - the registered query vars passed from query_vars filter
			 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference#Advanced_WordPress_Filters
			 *
			 * @since 0.3
			 *
			 * @return array $ary_vars
			 *
			 */
			public function register_query_variables( $ary_vars ) {
				if( is_array( $this->query_vars ) ):
					foreach( $this->query_vars as $query_var ):
						$ary_vars[] = $query_var['url'];
					endforeach;
				endif;
				
				return( $ary_vars );
			}
			
			/**
			 * Handler function to listen for our specific query string calls
			 *
			 * @package WP Base Classes
			 * @link http://codex.wordpress.org/Plugin_API/Action_Reference
			 *
			 * @param object $clsWP The WordPress WP class object passed by parse_request action
			 * @since 0.3
			 * @return void
			 */
			 
			public function listener( $clsWP ) {
				if( is_array( $this->query_vars ) ):
					foreach( $this->query_vars as $query_var ):
						if ( array_key_exists( $query_var['url'], $clsWP->query_vars ) ) :
							 call_user_func( $query_var['callback'] );
						endif;
					endforeach;
				endif;
			}

			/**
			 * prints an input field
			 *
			 * The $args array must contain the following keys and associated values:
			 * field_name, field_id, field_type (i.e. text, radio, checkbox), and value.
			 * Optionally, you can add size, maxlength, and display_before, and display_after.
			 * The paramaters display_before and display_after can accept HTML.
			 *
			 * @package WP Base Classes
			 * @param array $args the parameters for the input
			 *
			 * @return void
			 *
			 * @since 0.2
			 *
			*/
			
			public static function render_input( $args ) {
				
				printf( '%8$s<input type="%1$s" name="%2$s" id="%3$s" value="%4$s"%5$s %6$s %7$s />%9$s',
					$args['field_type'],
					$args['field_name'],
				 	$args['field_id'],
					$args['value'],
					$args['field_type'] == 'checked' && $args['value'] == true ? ' checked' : '',
					$args['size'] != '' ? sprintf( 'size="%s"', $args['size'] ) : '',
					$args['maxlength'] != '' ? sprintf( 'maxlength="%s"', $args['maxlength'] ) : '',
					$args['display_before'],
					$args['display_after']
				);
			}
			
			/*
			 * print a select field
			 *
			 * @package WP Base Classes
			 * @param array $args the arguments passed from the add_settings_field function, 
			 *
			 * @return void
			 *
			 * @since 0.2
			 *
			*/
			public function render_select( $args ) {
				printf( '<select name="%s" id="%s">', $args['field_name'], $args['field_id'] );
				foreach( $args['options'] as $option ) {
					printf( '<option value="%1$s"%3$s>%2$s</option>',
						$option['option_value'],
						$option['option_display'],
						$option['option_value'] == $args['value'] ? ' selected' : ''
					);
				}
			}
		}
	endif;
}

?>