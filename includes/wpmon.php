<?php

/**
 * The main WP Monitorus class
 *
 * @package WP Monitorus Plugin
 * @subpackage WPMon Class
 * @version 0.2
 * @since 0.1
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
 *
 */

namespace WPMon
{
	use rtcwpbase\WPframework\v1_0\WPbase;
	use rtcwpbase\WPupdater\v1_0\WPupdater;
	use WPMon\WPMon_Settings;
	use MonitorusAPI\ExternalMonitors\v0_1\ExternalMonitor;
	use MonitorusAPI\ExternalMonitors\v0_1\Snapshot;
	
	/**
	 * parent class is defined in this file
	 * @since 0.1
	 */
	require_once( WPMON_PATH . 'base/wp-framework.php' );
	/**
	 * contains functions used by this class
	 * @since 0.1
	 */
	require_once( WPMON_PATH . 'monitorus-api/monitorus-api.php' );
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\WPMon' ) ):
		/**
		 * the base plugin class
		 *
		 * @package WP Monitorus Plugin
		 * @subpackage WPMon Class
		 * @since 0.1
		 */
		class WPMon extends WPbase 
		{
			/**
			 * the apikey as retrieved from the wp_options table
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 * @var string 0.1
			 * @since
			 */
			private $id;
			
			/**
			 * plugin text domain
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 * @var string
			 * @since 0.1
			 */
			 protected $txtdomain;
			 
			/**
			 * class constructor
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */
			protected function __construct() 
			{
				global $WPMon_Settings;
				
				if( ! $WPMon_Settings instanceof WPMon_Settings )
					$WPMon_Settings = new WPMon_Settings;
					
				$this->apikey = $WPMon_Settings->get_api_key();
				$this->txtdomain = WPMON_TXTDMN;
				
				parent::__construct();
				
				//add the dashboard widget
				add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_setup' ) );
				add_action( 'wp_ajax_wpmon_snapshot', array( $this, 'wpmon_snapshot_ajax' ) );
				add_action( 'wp_ajax_wpmon_monitors', array( $this, 'wpmon_monitors_ajax' ) );
				//add the styles necessary for the admin pages
				$this->admin_css = array
				(
					array( 'handle' => 'wpmon', 'src' => WPMON_URI . 'css/wpmon.css', 'deps' => null, 'ver' => false, 'media' => 'screen', 'hook' => array( 'index.php' ) )
				);
				
				
				//add scripts for admin pages
				$this->admin_scripts = array
				(
					'wpmon_admin' => array( 'handle' => 'wpmon-admin', 'src' => WPMON_URI . 'js/wpmon.js', 'deps' => array( 'jquery' ), 'ver' => false, 'in_footer' => false, 'hook' => array( 'index.php' ) ),
					array( 'handle' => 'highcharts', 'src' => WPMON_URI . 'js/highcharts.js', 'deps' => array( 'jquery' ), 'ver' => false, 'in_footer' => false, 'hook' => array( 'index.php' ) )
				);
				
				//do we need to load a special Highcharts theme?
				if( $WPMon_Settings->get_chart_theme() != 'default')
					$this->admin_scripts['highcharts_theme'] = array( 
						'handle' => sprintf( 'highcharts-theme-%s', $WPMon_Settings->get_chart_theme() ), 'src' => WPMON_URI . sprintf( 'js/highcharts-theme-%s.js', $WPMon_Settings->get_chart_theme() ), 'deps' => array( 'highcharts' ), 'ver' => false, 'in_footer' => false, 'hook' => array( 'index.php' ) 
				);
				
				//localize scripts				
				$translations = array(
					'showGraphText' => __( 'Show Graph', $this->txtdomain ),
					'hideGraphText' => __( 'Hide Graph', $this->txtdomain )
				);
				
				$this->admin_localization_scripts = array(
					array( 'handle' => $this->admin_scripts['wpmon_admin']['handle'], 'object_name' => 'wpmonAdminl10n', 'l10n' => $translations, 'hook' => array( 'index.php' ) )
				);
					
				//add query vars
				$this->query_vars = array(
					//used for testing ajax response
					array( 'url' => 'testmonitorajax', 'callback' => array( &$this, 'wpmon_monitors_ajax' ) )
				);
			}
			
			/**
			 * callback for wp_dashboard_setup action
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */
			public function dashboard_setup()
			{
				wp_add_dashboard_widget( 'wp_monitorus_snapshot', __( "WP Monitorus Latest Snapshot", $this->txtdomain ), array( &$this, 'dashboard_widget_snapshot' ) );
				wp_add_dashboard_widget( 'wp_monitorus_monitor', __( "WP Monitorus Data Over Last 24 Hours", $this->txtdomain ), array( &$this, 'dashboard_widget_monitor' ) );
			}
			
			/**
			 * callback for Monitor widget
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */
			public function dashboard_widget_monitor()
			{
				print( "<div id='wpmon-monitors'>\r\n" );
				printf( "<p align='center'><img src='%s/images/ajax-loader.gif' /></p>", WPMON_URI );
				print( "</div><!--wpmon-monitors-->\r\n" );
			}
			
			/**
			 * monitors widget ajax callback
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */ 
			public function wpmon_monitors_ajax()
			{
					
				$monitors = ExternalMonitor::list_external_monitors( $this->apikey);
				if( !is_wp_error( $monitors ) ):
					foreach( $monitors as $monitor):
						$result = self::_render_monitor( $monitor['id'] );
						if( !$result )
							break;
					endforeach;
				else:
					self::catch_error( $monitors );
				endif;
				die();
			}
			
			/**
			 * callback for Latest Snapshot widget wp_add_dashboard_widget
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */
			public function dashboard_widget_snapshot()
			{
				print( "<div id='wpmon-snapshot'>\r\n" );
				printf( "<p align='center'><img src='%s/images/ajax-loader.gif' /></p>", WPMON_URI );
				print( "</div><!--wpmon-snapshot-->\r\n" );
			}
			
			
			/**
			 * monitors widget ajax callback
			 * 
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @return void
			 * @since 0.1
			 */
			public function wpmon_snapshot_ajax()
			{
				$latest_snapshot = Snapshot::get_latest_snapshot( $this->apikey );
				self::_render_snapshot( $latest_snapshot );
				die();
			}
			
			/**
			 * render the monitor object html
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @param string $id the id for the test
			 * @return bool true on success, false of failure
			 * @since 0.1
			 */
			private function _render_monitor( $id )
			{
				$monitor = ExternalMonitor::get_external_monitor( $this->apikey, $id );
				
				if( !is_wp_error( $monitor ) ):
					printf( "<div class='wpmon-monitor' id='wpmon-monitor-%s'>\r\n", $monitor->get_info()->get_id() );					
					printf( "<h4>%s</h4>", sprintf( '%s: %s', 
						/* translators: This is a noun, used as a column heading on a table.  */
						__( 'Monitor', $this->txtdomain ),
						$monitor->get_info()->get_name() ) 
					);
					
					//render the results summary element
					$this->_render_monitor_results_summary( $monitor->get_results(), $monitor->get_info()->get_id() );
					
					//render the graph element
					$this->_render_monitor_graph( $monitor->get_info()->get_id(), $monitor->get_results() );
					print( "</div><!--wpmon-monitor-->\r\n" );
					return( true );
				else:
					$this->catch_error( $monitor );
					return( false );
				endif;
			}
			
			/**
			 * renders the summary for a specific monitor
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @param object $results an ExternalMonitorResults object
			 * @since 0.3
			 */
			private function _render_monitor_results_summary( $results )
			{
				if( is_array( $results ) ):
					
					print( "<table class='wpmon-monitor-summary'>" );
					print( "<thead><tr>\r\n" );
					printf( "<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>",
						__( 'Location', $this->txtdomain ),
						__( 'Min (ms)', $this->txtdomain ),
						__( 'Max (ms)', $this->txtdomain ),
						__( 'Avg (ms)', $this->txtdomain ),
						__( 'OK Count', $this->txtdomain ),
						__( 'Not OK Count', $this->txtdomain )
					);
					print( "</tr></thead>" );
					
					foreach( $results as $result ):
						print( "<tr>" );
						printf( "<td>%s</td><td class='right'>%s</td><td class='right'>%s</td><td class='right'>%s</td><td class='right'>%s</td><td class='right'>%s</td class='right'>",
							$result->get_location_name(),
							$result->get_min(),
							$result->get_max(),
							$result->get_average_response_time(),
							$result->get_okcount(),
							$result->get_nokcount()
						);
						print( "</tr>" );
					endforeach;
					print( "</table>" );
				endif;
			}
			
			/**
			 * renders the graph for the specific monitor
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @param int $id the test id
			 * @param object $results an collection of ExternalMonitorData objects (the data property of the test)
			 * @since 0.3
			 */
			private function _render_monitor_graph( $id, $results )
			{
				//get the individual results and create graph data
				foreach( $results as $result ):
					
					//reset variables
					$i=0;
					
					//get the data for this test location
					$series = $result->get_data();
					
					//get the location
					$location = $result->get_location_name();
					
					//initialize the response array
					$responses[$location] = '';
					
					//create the data string for the chart
					foreach( $series as $data ):
						//set the x-axis minimum
						if( $i === 0 || strtotime( $data->get_datetime() ) < strtotime( $xmin ))
							$xmin = date( 'm/d/Y g:i A T', strtotime( $data->get_datetime() ) );
						
						//the data takes the form of [x-coordinate, y-coordinate] pairs
						$responses[$location] .= 
							sprintf( '%s[%s,%s]',
								 $i > 0 ? ',' : '', 
								 //pass back milliseconds since epoch, since this is what javascript expects
								 sprintf( "%s", strtotime( $data->get_datetime() ) * 1000 ),
								 $data->get_responsetime()
							);
						$i++;
					endforeach;
					
					//store the location name for the series labels
					$locationname[] = $result->get_location_name();
					
				endforeach;						
				
				printf( "<button type='button' value='showgraph' onclick='toggleGraph(this, \"#wpmon-monitor-graph-%s\")'>%s</button>\r\n",  $id, __( 'Show Graph', $this->txtdomain ) );
				printf( "<div id='wpmon-monitor-graph-%s' class='wpmon-graph' style='height:300px; width:auto;'>\r\n", $id );
				printf( "<p>%s</p>\r\n", __( "This chart is displayed in your browser's local time.", $this->txtdomain ) );
				//generate the script
				print( "<script type='text/javascript'>\r\n" );
				print( " jQuery(document).ready(function($){\r\n" );
				print( "Highcharts.setOptions({global: {useUTC: false}});\r\n" );
				print( "var chart = new Highcharts.Chart({\r\n" );
				print( "	chart: {\r\n" );
				printf( "		renderTo: 'wpmon-monitor-graph-%s'\r\n", $id );
				print( "	},\r\n" );
				print( "	title: { text: null },\r\n" );
				print( "	xAxis:\r\n" );
				print( "	{\r\n" );
				print( "		type: 'datetime',\r\n" );
				print( "		dateTimeLabelFormats:\r\n" );
				print( "		{\r\n" );
				printf("			hour: '%s',\r\n",
					/*translators: This represents a time: e.g. '01:54 AM'. The appropriate tokens can be found at: http://php.net/manual/en/function.strftime.php */
					 __( '%I:%M %P',
					 	 $this->txtdomain )
				);
				printf( "			day: '%s'\r\n",
					/*translators: This represents a date: e.g. '10/24'. The appropriate tokens can be found at: http://php.net/manual/en/function.strftime.php */ 
					__( '%m/%d', $this->txtdomain )
				);
				print( "		}\r\n" );
				print( "	},\r\n" );
				print( "	yAxis:\r\n" );
				print( "	{\r\n" );
				printf( "		title:{ text: '%s' },\r\n", 
					/* translators: This will appear as the legend of a chart axis. */
					_x(  'Response (ms)', 'Server Response Time (in milliseconds)', $this->txtdomain )
				);
			    print( "		min: 0\r\n" );
			    print( "	},\r\n" );
			    print( " tooltip:\r\n" );
			    print( "	{\r\n" );
			    printf( "		xDateFormat: '%s',\r\n",
			    	/*translators: This represents a date time: e.g. '10/24 06:53 AM'. The appropriate tokens can be found at: http://php.net/manual/en/function.strftime.php */
			    	__( '%m/%d %I:%M %P', $this->txtdomain )
			    );
			    print( "		shared: true,\r\n" );
			    print( "		valueSuffix: 'ms'\r\n" );
			    print( "	},\r\n" );
			    print( "	series:\r\n" ); 
				print( "	[\r\n" );
				
				foreach( $responses as $key => $val):
					print( "		{\r\n");
					printf( "			name: '%s',\r\n", $key );
					printf( "			data: [%s]\r\n", $val );
					print( "		},\r\n" );
				endforeach;
				
				print( "	]\r\n" );
				print( "});\r\n" );
				printf( "jQuery( '#wpmon-monitor-graph-%s' ).slideToggle( 'fast' );\r\n", $id );
				print( "});\r\n" ); //ends jQuery(document).ready()
				print( "</script>\r\n" );
				printf( "</div><!-- wpmon-monitor-graph-%s -->", $id );
			}
			
			/**
			 * render the snapshot object html
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @param object $snapshot
			 * @return void
			 * @since 0.1
			 */
			private function _render_snapshot( $snapshot )
			{
				
				print( "<div id='wpmon-snapshot'>\r\n" );
				
				if( !is_wp_error( $snapshot ) ):
					foreach( $snapshot->get_locations() as $snapshot_location):
						print( "<div class='snapshot-location'>\r\n" );
						printf("<h4><span>%s : %s</span></h4>\r\n",
							__( "Location", $this->txtdomain ),
							$snapshot_location->get_name()
						);
						print( "<table class='wpmon-snapshot'>\r\n" );
						print( "<thead><tr>" );
						printf( "<td>%s</td><td>%s</td><td>%s</td><td>%s</td>",
							/* translators: This is a noun.  */
							_x( 'Monitor', 'Table column heading', $this->txtdomain ),
							_x( 'Last Checked', 'Table column heading', $this->txtdomain ),
							_x( 'Response (ms)', 'Table column heading', $this->txtdomain ),
							_x( 'Status', 'Table column heading', $this->txtdomain )
						);
						print( "</tr></thead>\r\n" );
						foreach( $snapshot_location->get_data() as $data ):
							printf( '<tr><td>%1$s</td><td>%2$s</td><td class="right">%3$s</td><td class="%4$s">%4$s</td></tr>',
								$data->get_name(),
								date( 'm-d-Y g:i A T', strtotime( $data->get_time() ) ),
								$data->get_response_time(),
								$data->get_status()
							);
							print( "\r\n" );
						endforeach;
						print( "</table>\r\n</div>\r\n" );
					endforeach;
				else:
					$this->catch_error( $snapshot );
				endif;
				
				print( "</div>\r\n" );
			}
			
			/**
			 * print an error message
			 *
			 * @package WP Monitorus Plugin
			 * @subpackage WPMon Class
			 *
			 * @param object $error the WP_Error object
			 * @return void
			 * @since 0.1
			 */
			private function catch_error( $error )
			{
				printf( "<p>%s</p>",
					/* translators: Leave the %s unchanged. */
					sprintf( __( "We were unable to retrieve your Monitor.us stats. Please refresh this page to try again. The error was as follows: %s", $this->txtdomain ), $error->get_error_message()
					)
				);
			}
		}
	endif;
	
	if( class_exists( '\\' . __NAMESPACE__ . '\\WPMon' ) )
		$WPMon = WPMon::get_instance();
}
 
?>