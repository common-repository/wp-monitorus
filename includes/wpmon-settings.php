<?php

/**
 * Settings for the WP-Monitor.us plugin
 *
 * @package WP Monitorus Plugin
 * @subpackage WPMon Settings
 * @version 0.1
 * @since 0.1
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
 *
 */

namespace WPMon {
	require_once( WPMON_PATH . 'base/settings-api-framework.php' );
	use \rtcwpbase\WPSettingsAPI\v1_0\WPSettingsAPI;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\WPMon_Settings' ) ):
		/**
		 * the settings class
		 *
		 * @package WP Monitorus Plugin
		 * @subpackage WPMon Settings
		 * @since 0.1
		 */
		class  WPMon_Settings extends WPSettingsAPI
		{
			/**
			 * the options page(s)
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 * @var array
			 * @since 0.1
			 */
			protected $pages = array();
			
			/**
			 * the options groups
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 * @var array
			 * @since 0.1
			 */
			protected $options = array();
			
			/**
			 * the settings sections
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 * @var array
			 * @since 0.1
			 */
			protected $sections = array();
			
			/**
			 * the settings fields
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 * @var array
			 * @since 0.1
			 */
			protected $fields = array();
			
			/**
			 * class constructor
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @return void
			 * @since 0.1
			 */
			public function __construct()
			{
				parent::__construct();
				$this->init();
				register_uninstall_hook( WPMON_MAIN_FILE, array( &$this, 'delete_options' ) );
				
				//register our ajax calls
				add_action( 'wp_ajax_wpmon_getapikey', array( $this, 'get_api_key_ajax' ) );
			}
			
			/**
			 * set up class properties
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @return void
			 * @since
			 */
			public function init()
			{
				$this->slug = WPMON_SLUG;
				$this->txtdomain = WPMON_TXTDMN;
				$this->path = WPMON_PATH;
				$this->uri = WPMON_URI;
				$this->plugin_path_rel = WPMON_PATH_REL;
				
				//set up our pages
				$this->pages = array
				(
					'main' => array( 'page_title' => __( "WP-Monitor.us Options", $this->txtdomain ), 'menu_title' => __( 'WP Monitor.us', $this->txtdomain ), 'capability' => 'manage_options', 'menu_slug' => $this->slug, 'callback' => array( &$this, 'wpmon_options_page' ) )
				);
				
				//set up our options groups
				$this->options = array(
					'main' => array( 'option_group' => "wpmonitorus-options", 'option_name' => "wp_monitorus_settings", 'sanitize_callback' => array( &$this, 'sanitize_input' ) ),
				);
				
				//set up our settings sections
				$this->sections = array(
					'basic' => array( 'id' => 'basic-settings', 'title' => __( 'Basic Settings', $this->txtdomain ), 'callback' => array( &$this, 'sanitize_input' ), 'page' => $this->slug ),
				);
				
				//set up the settings fields
				$this->fields = array(
					'api_key' => array( 'id' => 'api-key', 'title' => __( 'API Key', $this->txtdomain ), 'callback' => array( &$this, 'api_key_html' ), 'page' => $this->pages['main']['menu_slug'], 'section' => $this->sections['basic']['id'], 'group' => $this->options['main']['option_name'] ),
					'chart_theme' => array( 'id' => 'chart-theme', 'title' => __( 'Chart Theme', $this->txtdomain ), 'callback' => array( &$this, 'chart_theme_html' ), 'page' => $this->pages['main']['menu_slug'], 'section' => $this->sections['basic']['id'], 'group' => $this->options['main']['option_name'] )
				);
						
				//get the current settings
				$this->option_settings = $this->get_options();
				
				//if the apikey setting is empty, display an error notice
				if( $this->option_settings[$this->options['main']['option_name']][$this->fields['api_key']['id']] == '' )
					add_action( 'admin_notices', array( &$this, 'admin_notice' ) );
				
				
				$this->admin_scripts = array
				(
					'wpmon-settings' => array( 'handle' => 'wpmon-settings', 'src' => $this->uri . 'js/wpmon-settings-page.js', 'deps' => array( 'jquery-ui-dialog' ), 'ver' => false , 'in_footer' => true, 'hook' => array( 'settings_page_' . $this->pages['main']['menu_slug'] ) )
				);
				
				//localize scripts
				$translations = array(
					'errorString' => __( 'There was an error processing your request. Please try again.', $this->txtdomain ),
					'buttonCancel' => _x( 'Cancel', 'text label for an action button', $this->txtdomain ),
					'pluginUrl' => $this->uri
				);
				
				$this->admin_localization_scripts = array(
					array( 'handle' => $this->admin_scripts['wpmon-settings']['handle'], 'object_name' => 'wpmonSettingsl10n', 'l10n' => $translations, 'hook' => array( 'settings_page_' . $this->pages['main']['menu_slug'] ) )
				);
				
				$this->admin_css = array
				(
					array( 'handle' => 'wpmon-settings', 'src' => $this->uri . 'css/wpmon-settings-page.css', 'deps' => null, 'ver' => false, 'media' => 'all', 'hook' => array( 'settings_page_' . $this->pages['main']['menu_slug'] ) ),
					array( 'handle' => 'jquery-ui-custom', 'src' => $this->uri . '/css/ui-darkness/jquery-ui-1.8.18.custom.css', 'deps' => null, 'ver' => false, 'media' => 'all', 'hook' => array( 'settings_page_' . $this->pages['main']['menu_slug'] ) )
				);
			}
			
			/**
			 * print a notice if the settings do not exist in the WP db
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @return void
			 * @since 0.1
			 */	
			function admin_notice ()
			{
				printf( "<div class='error'><p>%s</p></div>\r\n", __( "You must configure WP Monitor.us in order for it to function properly.", $this->txtdomain ) );
			}
			
			/**
			 * render the api key field html
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @return void
			 * @since 0.1
			 */
			public function api_key_html()
			{
				$field_value = $this->option_settings[$this->options['main']['option_name']][$this->fields['api_key']['id']];
				printf( '<p><input type="%s" name="%s" id="%s" size="30" value="%s" />',
					//if the value is already present, show a password field type
					$field_value == '' ? 'text' : 'password',
					$this->_make_field_name( $this->fields['api_key'] ),
					$this->fields['api_key']['id'],
					esc_attr( $field_value )
				);
				
				printf('<button type="button" onclick="toggle_apikey_form( this, \'%1$s\', \'%2$s\' )" value="%2$s">%1$s</button></p></a>',
					$field_value == '' ? __( 'Get API Key', $this->txtdomain ) :  __( 'Show API Key', $this->txtdomain ),
					$field_value == '' ? 'get' : 'show'
				);
				if( $field_value !=  '' )
					printf( '<button type="button" onclick="toggle_apikey_form( this, \'%1$s\', \'%2$s\' )" value="%2$s">%1$s</button>', __( 'Reset API Key', $this->txtdomain ), 'get' );
				
				print( "<div id='get-api-key'>\r\n" );
				printf( "<h3>%s</h3>\r\n",
			 		__( 'Retrieve Your API Key', $this->txtdomain )
			 	);
			 	print( __( 'If you already have a key stored, this will replace the existing key with the new one.', $this->txtdomain ) );
			 	print( "<table><tr>\r\n" );
			 	printf( "<td>%s:</td>\r\n",
			 		__( 'Your Monitor.us username', $this->txtdomain )
			 	);
			 	print( "<td><input type='text' id='username' name='username' /></td>\r\n" );
			 	print( "</tr>\r\n<tr>\r\n" );
			 	printf( "<td>%s:</td>\r\n",
			 		__( 'Your Monitor.us password', $this->txtdomain )
			 	);
			 	print( "<td><input type='password' id='password' name='password' /></td>\r\n" );
			 	print( "</tr>\r\n");
			 	printf( "<tr><td colspan='2'>%s</td></tr>\r\n", __( 'Your username and password  will not be stored!', $this->txtdomain ) );
			 	print( "</table>\r\n" );
			 	printf( "<button type='button' onclick='get_api_key()'>%s</button>\r\n",
			 		 __( 'Get key', $this->txtdomain )
			 	);
			 	printf( '<img src="%simages/wpspin_light.gif" class="loading-ajax" id="loading-ajax" alt="" style="visibility:hidden;" />', admin_url() );
			 	print( "</div>\r\n" );
			 	print( "<div id='success' class='updated'><p id='success-message'></p></div>\r\n" );
			 	print( "<div id='error' class='error'><p id='error-message'></p></div>\r\n" );
			 	printf( "<div id='wpmondialog' title='%s'></div>\r\n", __( 'Your API key is:', $this->txtdomain ) );
			}
			
			/**
			 * render the chart theme field
			 *
			 * @package pkgtoken
			 * @subpackage subtoken
			 * @since0.3
			 */
			 public function chart_theme_html() {
			 	$themes = array( 
			 		//translators: this is an option in a select dropdown
			 		'default' => __( 'Default', $this->txtdomain ),
			 		//translators: this is an option in a select dropdown
			 		'gray' => __( 'Gray', $this->txtdomain ),
			 		//translators: this is an option in a select dropdown
			 		'grid' => __( 'Grid', $this->txtdomain ),
			 		//translators: this is an option in a select dropdown
			 		'darkblue' => __( 'Dark Blue', $this->txtdomain ),
			 		//translators: this is an option in a select dropdown
			 		'darkgreen' => __( 'Dark Green', $this->txtdomain )
			 	);
			 	
			 	$field_value = $this->option_settings[$this->options['main']['option_name']][$this->fields['chart_theme']['id']];
			 	printf( "<select name='%s' id='%s' onChange='changePreview( this )'>\r\n",
			 		$this->_make_field_name( $this->fields['chart_theme'] ),
			 		$this->fields['chart_theme']['id']
			 	);
			 	foreach( $themes as $val => $display ):
				 	printf( "	<option value='%s'%s>%s</option>",
				 		$val,
				 		$field_value == $val ? 'selected' : '',
						$display
				 	);
				 endforeach;
				 
			 	printf( "</select>\r\n" );
			 	printf( "<div id='chart-preview'><img src='%s/images/highcharts-theme-%s.png' /></div>",
			 		$this->uri,
			 		$field_value
			 	);
			 }
			 
			/**
			 * render the options page
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @todo add the ability to test the api key from the settins page
			 * @return void
			 * @since 0.1
			 */
			public function wpmon_options_page()
			{	
				self::options_page( $this->pages['main'], $this->options );
			}
			
			/**
			 * make a field name in the format option_group[field-name]
			 *
			 * Use this function to format field names for the HTML form
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage subtoken
			 *
			 * @param object $field a field object from $this->fields 
			 * @return string
			 * @since 0.1
			 */
			private function _make_field_name( $field )
			{
				return sprintf( '%s[%s]',
					$field['group'],
					$field['id']
				);
			}
			
			/**
			 * sanitize the settings form input
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @param object $input passed by WP
			 * @return object $input the sanitized input
			 * @since 0,1
			 */
			public function sanitize_input( $input )
			{
				if( isset( $input[$this->fields['api_key']['id']] ) )
					$input[$this->fields['api_key']['id']] = esc_attr( $input[$this->fields['api_key']['id']] );
					
				if( isset( $input[$this->fields['api_key']['id']] ) ):
					if( !in_array($input[$this->fields['chart_theme']['id']], array( 'default', 'gray', 'grid', 'darkblue', 'darkgreen' ) ) )
						$input[$this->fields['chart_theme']['id']] = 'default';
				endif;
				
				return( $input );
			}
			
			/**
			 * retrieve the api key
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage subtoken
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_api_key()
			{
				return( $this->option_settings[$this->options['main']['option_name']][$this->fields['api_key']['id']] );
			}
			
			/**
			 * retrieve the chart theme
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage subtoken
			 *
			 * @return string
			 * @since 0.4
			 */
			public function get_chart_theme() {
				return( $this->option_settings[$this->options['main']['option_name']][$this->fields['chart_theme']['id']] );
			}
			/**
			 * delete the settings, called when plugin is uninstalled
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Settings
			 *
			 * @return void
			 * @since 0.1
			 */
			 public function delete_options() {
			 	if( !isset( $WPMon_Settings ) )
			 		$WPMon_Settings = new WPMon_Settings;
			 	
			 	foreach( $WPMon_Settings->options as $option ):
			 		delete_option( $option['option_name'] );
			 	endforeach;
			 }
			 
			 /**
			  * process the ajax request to retrieve the API from Monitor.us
			  *
			  * @package WP Monitorus Plugin
			  * @subpackage WPMon Settings 
			  * @return void
			  * @since 0.2
			  */
			  
			 public function get_api_key_ajax()
			 {
			 	$key = \MonitorusAPI\User\v0_1\User::get_api_key(
			 		esc_attr( $_POST['username'] ), esc_attr( $_POST['password'] ) );
			 	
			 	if( !is_wp_error( $key ) ):
			 		update_option( $this->options['main']['option_name'],
			 			array( $this->fields['api_key']['id'] => $key )
			 		);
					$response = array(
						"result" => "success", 
						"message" => sprintf( 
							/* translators: Leave the %s unchanged. */
							__( "Your API key is %s. It has already been saved and you can now leave this page.", $this->txtdomain ), $key ),
						"data" => $key
					);
			 	else:
					$response = array(
						'result' => 'failure',
						'message' => sprintf(
							/* translators: Leave the %s unchanged. */
							__( 'There was an error fetching your key: %s', $this->txtdomain ), $key->get_error_message() )
					);
			 	endif;
			 	
			 	echo( json_encode( $response ) );
			 	die();
			 }
		}
	endif;
			
	if( class_exists( '\\' . __NAMESPACE__ . '\\WPMon_Settings' ) )
		$WPMon_Settings =  new WPMon_Settings;
}
?>