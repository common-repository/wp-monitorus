<?php

/**
 * The Monitor.us External Monitor API class
 *
 * @package Monitorus API
 * @subpackage External Monitors
 * @version 0.1
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
 * @link http://www.monitor.us/api/api.html
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

namespace MonitorusAPI\ExternalMonitors\v0_1
{
	use \WP_Error;
	use \MonitorusAPI\MonitorusAPI;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\ExternalMonitors' ) ):
		/**
		 * The Monitor.us External Monitors API class
		 * 
		 * @package Monitorus API
		 * @subpackage External Monitors
		 * @since 0.1
		 */
		class ExternalMonitor
		{	
			/**
			 * the monitor info
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var object EnternalMonitorInfo
			 * @since 0.1
			 */
			private $info;
			
			/**
			 * the monitor results for the last 24 hours
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var array a collection of ExternalMonitorResults objects
			 * @since 0.1
			 */
			private $results;
			
			/**
			 * class constructor
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey
			 * @param int $id the monitor id
			 * @return void
			 * @since 0.1
			 */ 
			private function __construct( $apikey, $id ) 
			{
				$this->apikey = $apikey;
				$this->id = $id;
			}
			
			
			
			/**
			 * get the list of external monitors on this account
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey the account API key
			 * @param string $output xml or json (default)
			 * @return array|object array on success, WP_Error object on failure
			 * @link http://www.monitor.us/api/api.html#getExternalMonitors
			 * @since 0.1
			 */
			public static function list_external_monitors( $apikey, $output = 'json')
			{
				$params['action'] = 'tests';
				$params['apikey'] = $apikey;
				$params['output'] = $output;
			 	
				$monitors = MonitorusAPI::make_request( $params );
				if( !is_wp_error( $monitors ) ):
					
					foreach( $monitors->testList as $monitor ):
						$result[] = array(
							'id' => $monitor->id,
							'name' => $monitor->name,
							'isSuspended' => $monitor->isSuspended,
							'type' => $monitor->type, 
						);
					endforeach;
				else:
					$result = $monitors;
				endif;
				
				return( $result );
		
			}
			
			/**
			 * creates an external monitor object
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey the account API key
			 * @param int $testid id of the test for which to get information
			 * @param int $timezone offset relative to GMT, used to show results in the timezone of the user
			 * @param string $locationids optional comma separated ids of locations for which results should be retrieved. If not specified results will be retrieved for all locations
			 * @return object ExternalMonitor object
			 * @since 0.1
			 */
			public static function get_external_monitor( $apikey, $testid, $timezone = '', $locationids = '' )
			{
				$monitor = new ExternalMonitor( $apikey, $testid );
				
				$info_result = ExternalMonitorInfo::get_external_monitor_info( $apikey, $testid, $timezone );
				
				if( is_wp_error( $info_result ) ):
					return( $info_result );
					exit();
				else:
					$monitor->info = $info_result;
				endif;
				
				$monitor_results = ExternalResults::get_external_monitor_results( $apikey, $testid, $locationids, $timezone );
				
				if( is_wp_error( $monitor_results ) ):
					return( $monitor_results );
					exit();
				else:
					$monitor->results = $monitor_results;
				endif;

				return( $monitor );
			}
			
			/**
			 * retrieve info
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_info()
			{
				return( $this->info );
			}
			
			/**
			 * retrieve the results property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_results()
			{
				return( $this->results );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\ExternalMonitorInfo' ) ):
		/**
		 * class wrapper for GetExternalMonitorInfo
		 *
		 * @package Monitorus API
		 * @subpackage External Monitors
		 *
		 * @since 0.1
		 */
		class ExternalMonitorInfo
		{
			/**
			 * test timeout in ms
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $timeout;
			 
			/**
			 * creation date of the test
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $startDate;
			
			/**
			 * one of the following test types: HTTP, HTTPS, PING, FTP, UDP, TCP, SIP, SMTP, IMAP, POP3, DNS, SSH, MySQL
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $type;
			 
			/**
			 * the id of the test
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $id;
			
			/**
			 * url of the test
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			 private $url;
			 
			/**
			 * the name of the test
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $name;
			
			
			/**
			 * class constructor
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param int $timeout
			 * @param string $start_date
			 * @param string $type
			 * @param int $id
			 * @param string $url
			 * @param string $name
			 * @return void
			 * @since 0.1
			 */
			private function __construct( $timeout, $start_date, $type, $id, $url, $name )
			{
				$this->timeout = $timeout;
				$this->start_date = $start_date;
				$this->type = $type;
				$this->id = $id;
				$this->url = $url;
				$this->name = $name;
			}
			
			/**
			 * get information regarding a specific monitor
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey
			 * @param int $testid
			 * @param int $timezone offset relative to GMT, used to show results in the timezone of the user
			 * @return object ExternalInfo for success, WP_Error object on failure
			 * @link http://www.monitor.us/api/api.html#getExternalMonitorInfo
			 * @since 0.1
			 */
			public static function get_external_monitor_info( $apikey, $testid, $timezone = '' )
			{
				$params['action'] = 'testinfo';
				$params['testId'] = $testid;
				$params['apikey'] = $apikey;
				$params['timezone'] = $timezone;
				$params['output'] = 'json';
				
				$info = MonitorusAPI::make_request( $params );

				if( !is_wp_error( $info ) ):
					//$info = ExternalMonitor::_decode_response( $info, 'json' );
					$info_result = new ExternalMonitorInfo
					(
						$info->timeout,
						$info->startDate,
						$info->type,
						$info->testId,
						$info->url,
						$info->name
					);
					
					return( $info_result );
				else:
					return( $info );
				endif;
			}
			
			/**
			 * retrieve the timeout property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_timeout()
			{
				return( $this->timeout );
			}
			
			/**
			 * retrieve the start_date property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_start_date()
			{
				return( $this->start_date );
			}
			
			/**
			 * retrieve the type property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_type()
			{
				return( $this->type );
			}
			
			/**
			 * retrieve the id
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_id()
			{
				return( $this->id );
			}
			
			/**
			 * retrieve the url property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_url()
			{
				return( $this->url );
			}
			/**
			 * retrieve the name property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_name()
			{
				return( $this->name );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\ExternalMonitorResult' ) ):
		//a collection of locations/results for a specific monitor
		
		/**
		 * the external results class
		 *
		 * This class is a collection of external results objects. Each instance contains the information
		 * regarding a monitor test from one specific location.
		 *
		 * @package Monitorus API
		 * @subpackage External Monitors
		 * @link http://www.monitor.us/api/api.html#getExternalMonitorResults
		 * @since 0.1
		 */
		class ExternalResults
		{
			/**
			 * id of the location for which results are retrieved
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $location_id;
			
			/**
			 * name of the location for which results are retrieved
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $location_name;
			
			/**
			 * the results of the monitor checks
			 * 
			 * This property is a collection(array) of ExternalResultsData objects containing the data
			 * from the last 24 hours of tests.
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $data = array();
			
			/**
			 * minimum response time
			 *
			 * min response time out of all checks performed during the specified period of time retrieved in milliseconds
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $min;
			
			/**
			 * maximum response time
			 * 
			 * max response time out of all checks performed during the specified period of time retrieved in milliseconds
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $max;
			
			/**
			 * number of checks with status OK performed during the specified period of time
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $okcount;
			
			/**
			 * number of checks with status NOK performed during the specified period of time
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $nokcount;
			
			/**
			 * sum of all response times with a status of OK(can be used to obtain average response time)
			 * retrieved in milliseconds
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $oksum;
			
			/**
			 * additional information for checks with status NOK and count of checks corresponding to this information
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $add_datas;
			
			
			/**
			 * get the results for an external monitor over the last 24 hours
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey
			 * @param int $testid
			 * @param string $locationids optional comma separated ids of locations for which results should be retrieved. If not specified results will be retrieved for all locations
			 * @param int $timezone optional offset relative to GMT, used to show results in the timezone of the user
			 * @return object ExternalResults on success, WP_Error object on failure
			 * @since 0.1
			 * @todo make the results property be a collection of objects
			 */
			public static function get_external_monitor_results( $apikey, $testid, $locationids = '', $timezone = '' )
			{
				$params['action'] = 'testresult';
				$params['apikey'] = $apikey;
				$params['testId'] = $testid;
				$params['locationIds'] = $locationids;
				$params['timezone'] = $timezone;
				$params['output'] = 'json';
				
				$results = MonitorusAPI::make_request( $params );
				
				if( !is_wp_error( $results ) ):
					//$results = ExternalMonitor::_decode_response( $results );
					
					//create individual ExternalResults objects for each location
					foreach( $results as $result ):
						//reset the array
						$resultsdata = array();
						
						//get the individual check results
						foreach( $result->data as $data ):
							$resultsdata[] = new ExternalResultsData
							(
								$data[0],
								$data[1],
								$data[2]
							);
						endforeach;

						//create the result object
						$object = new ExternalResults;
						$object->location_id = $result->id;
						$object->location_name = $result->locationName;
						$object->data = $resultsdata;
						$object->min = $result->trend->min;
						$object->max = $result->trend->max;
						$object->okcount = $result->trend->okcount;
						$object->nokcount = $result->trend->nokcount;
						$object->oksum = $result->trend->oksum;
						$object->add_datas = $result->adddatas;
						//add to the collection
						$temp[] = $object;
					endforeach;
					
					return( $temp );
				else:
					return( $results );
				endif;
			}
			
			/**
			 * get the location id
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string 
			 * @since 0.1
			 */
			public function get_location_id()
			{
				return( $this->location_id );
			}
			
			/**
			 * get the location name
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string 
			 * @since 0.1
			 */
			public function get_location_name()
			{
				return( $this->location_name );
			}
			
			/**
			 * get the data
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return array
			 * @since 0.1
			 */
			public function get_data()
			{
				return( $this->data );
			}
			
			/**
			 * get the min (response time)
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_min()
			{
				return( $this->min );
			}
			
			/**
			 * get the max (response time)
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int 
			 * @since 0.1
			 */
			public function get_max()
			{
				return( $this->max );
			}
			
			/**
			 * get the ok count
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int 
			 * @since 0.1
			 */
			public function get_okcount()
			{
				return( $this->okcount );
			}
			
			/**
			 * get the not ok count
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int 
			 * @since 0.1
			 */
			public function get_nokcount()
			{
				return( $this->nokcount );
			}
			
			/**
			 * get the ok sum
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int 
			 * @since 0.1
			 */
			public function get_oksum()
			{
				return( $this->oksum );
			}
			
			/**
			 * get the add_datas
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_add_datas()
			{
				return( $this->add_datas );
			}
			
			/**
			 * get the average response time
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int 
			 * @since 0.1
			 */
			public function get_average_response_time()
			{
				return( intval( $this->oksum/$this->okcount ) );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\ExternalResultsData' ) ):
		/**
		 * the result of one specific test from one specific location
		 *
		 * @package Monitorus API
		 * @subpackage External Monitors
		 * @since 0.1
		 */
		class ExternalResultsData
		{
			/**
			 * time of the check, response time(ms), status of the check("OK","NOK")
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $datetime;
			
			/**
			 * response time(ms)
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $responsetime;
			
			/**
			 * status of the check("OK","NOK")
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $status;
			
			/**
			 * class constructor
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $datetime the date and time of the test
			 * @param int $responsetime the response time in ms
			 * @param string $status OK / NOK
			 * @return void
			 * @since 0.1
			 */
			 public function __construct( $datetime, $responsetime, $status )
			 {
			 	$this->datetime = $datetime;
			 	$this->responsetime = $responsetime;
			 	$this->status = $status;
			 }
			 
			/**
			 * get the date/time of the test
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_datetime()
			{
				return( $this->datetime );
			}
			
			/**
			 * get the response time
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_responsetime()
			{
				return( $this->responsetime );
			}
			
			/**
			 * get the status
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_status()
			{
				return( $this->status );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\SnapshotData' ) ):
		/**
		 * the SnapshotData class
		 *
		 * This class contains the data retrieved during the last check of a specific monitor from a specific location.
		 * e.g. The last check by Monitor.us EU server to http://google.com.
		 * 
		 * @package Monitorus API
		 * @subpackage External Monitors
		 *
		 * @since 0.1
		 */
		class SnapshotData
		{
			/**
			 * the test id
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $id;
			
			/**
			 * the monitor type
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $test_type;
			/**
			 * the date/time of the last check
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $time;
			/**
			 * response time in ms
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $responsetime;
			/**
			 * the test status
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $status;
			/**
			 * tag of the test
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $tag;
			/**
			 * full name of the location the following results are received for
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $name;
			/**
			 * an undocumented property of the Monitorus API
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $frequency;
			/**
			 * test timeout
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $timeout;
			
			/**
			 * class construction
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param array $args an array containing the class properties and their values as key/value pairs
			 * @return void
			 * @since 0.1
			 */
			private function __construct( $args )
			{
				foreach( $args as $key => $val)
					$this->$key = $val;
			}
			
			/**
			 * create a snapshot data object
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param int $id the test id
			 * @param string $type the test type
			 * @param string $time the test time
			 * @param int $responsetime the test response time
			 * @param string $status the test status
			 * @param string $tag the test tag
			 * @param string $name the test name
			 * @param string $frequency the test frequency
			 * @param int $timeout the test timeout
			 *
			 * @return object the SnapshotData object
			 * @since 0.1
			 */
			public static function make_snapshot_data( $id, $type, $time, $responsetime, $status, $tag, $name, $frequency,$timeout )
			{
				$args = array
				(
					'id' => $id,
					'test_type' => $type,
					'time' => $time,
					'responsetime' => $responsetime,
					'status' => $status,
					'tag' => $tag,
					'name' => $name,
					'frequency' => $frequency,
					'timeout' => $timeout
				);
				
				$data = new SnapshotData( $args );
				
				return( $data );
			}
			
			/**
			 * get the id
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_id()
			{
				return( $this->id );
			}
			
			/**
			 * get the test type
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_test_type()
			{
				return( $this->test_type );
			}
			
			/**
			 * get the time
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_time()
			{
				return( $this->time );
			}
			
			/**
			 * get the response time in ms
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_response_time()
			{
				return( $this->responsetime );
			}
			
			/**
			 * get the test status
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_status()
			{
				return( $this->status );
			}
			
			/**
			 * get the tag
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_tag()
			{
				return( $this->tag );
			}
			
			/**
			 * get the test name
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_name()
			{
				return( $this->name );
			}
			
			/**
			 * get the frequency
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string
			 * @since 0.1
			 */
			public function get_frequency()
			{
				return( $this->frequency );
			}
			
			/**
			 * get the timeout
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return int
			 * @since 0.1
			 */
			public function get_timeout()
			{
				return( $this->timeout );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\SnapshotLocation' ) ):	
		/**
		 * the snapshot location
		 *
		 * This class is composed of information gathered by one specific location in the Monitor.us infrastructure.
		 * It contains information such as the location id, name, and the data gathered during the last check of monitors
		 * assigned to this location.
		 *
		 * @package Monitorus API
		 * @subpackage External Monitors
		 *
		 * @since 0.1
		 */
		class SnapshotLocation
		{
			/**
			 * the location id
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var int
			 * @since 0.1
			 */
			private $id;
			
			/**
			 * the location name
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private $name;
			
			/**
			 * the location snapshot data
			 * 
			 * This is an array containing a collection of SnapshotData objects
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var array
			 * @see SnapshotData
			 * @since 0.1
			 */
			private $snapshot_data;
			
			/**
			 * class constructor
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param array $args an array containing the class properties and their values as key/value pairs
			 * @return void
			 * @since 0.1
			 */
			private function __construct( $args )
			{
				foreach( $args as $key => $val )
					$this->$key = $val;
			}
			
			/**
			 * create a snapshot
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param int $id the location id
			 * @param string $name the location name
			 * @param array $data an containing a collection of SnapshotData objects
			 * @return object the SnapshotLocation object
			 * @since 0.1
			 */
			public static function make_snapshot( $id, $name, $data = '' )
			{
				$args = array( 'id' => $id, 'name' => $name, 'data' => $data );
				
				$snapshot = new SnapshotLocation( $args );
				return( $snapshot );
			}
			
			/**
			 * add a snapshot data object to the collection
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param object $data a SnapshotData object
			 * @return void
			 * @since 0.1
			 */
			public function add_data( $data )
			{
				$this->snapshot_data[] = $data;
			}
			
			/**
			 * get the location id
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string 
			 * @since 0.1
			 */
			public function get_id()
			{
				return( $this->id );
			}
			
			/**
			 * get the location name
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return string 
			 * @since 0.1
			 */
			public function get_name()
			{
				return( $this->name );
			}
			
			/**
			 * get the data
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return array 
			 * @since 0.1
			 */
			public function get_data()
			{
				return( $this->snapshot_data );
			}
		}
	endif;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\Snapshot' ) ):	
		/**
		 * a collection of SnapshotLocations
		 *
		 * @package Monitorus API
		 * @subpackage External Monitors
		 *
		 * @since 0.1
		 */
		class Snapshot
		{
			/**
			 * A collection of the different Monitor.us server snapshots
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var array containing SnapshotLocation Objects
			 * @since 0.1
			 */
			private $locations = array();
			
			/**
			 * class constructor
			 *
			 * Implemented, but does nothing
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return void
			 * @since 0.1
			 */
			public function __construct()
			{
			}
			
			/**
			 * add a snapshot to the collection
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param object $location a SnapshotLocation object
			 * @return void
			 * @since 0.1
			 */
			public function add_location( $location )
			{
				$this->locations[] = $location;
			}
			
			/**
			 * retrieve the snapshots property
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @return array containing SnapshotLocation objects
			 * @since 0.1
			 */
			public function get_locations()
			{
				return( $this->locations );
			}
			
			/**
			 * get last results of all user's External monitors
			 *
			 * @package Monitorus API
			 * @subpackage External Monitors
			 *
			 * @param string $apikey
			 * @param string $locationids optional comma separated ids of the locations for which results should be retrieved. If not specified, results will be retrieved for user's all locations.
			 * @return object Snapshot on success, WP_Error object on failure
			 * @link http://www.monitor.us/api/api.html#getExternalSnapshot
			 * @since 0.1
			 */
			 
			 public static function get_latest_snapshot( $apikey, $locationids = '' )
			 {
			 	$params['action'] = 'testsLastValues';
			 	$params['locationIds'] = $locationids;
			 	$params['apikey'] = $apikey;
			 	$params['output'] = 'xml';
			 	
			 	$results = MonitorusAPI::make_request( $params );
				
			 	if( !is_wp_error( $results ) ):
				 	
				 	$snapshots = new Snapshot;
					
				 	foreach( $results as $result ):
				 		$snapshot = SnapshotLocation::make_snapshot
				 		(
							$result->id,
							$result->name
						);
					 	foreach( $result->data as $data ):
					 		$snapshotdata = SnapshotData::make_snapshot_data
					 		(
						 		$data->id,
						 		$data->testType,
						 		$data->time,
						 		$data->perf,
						 		$data->status,
						 		$data->tag,
						 		$data->name,
						 		$data->frequency,
						 		$data->timeout
						 	);
					 		$snapshot->add_data( $snapshotdata );
					 	endforeach;
					 	$snapshots->add_location( $snapshot );
					endforeach;
			 		return( $snapshots );
			 	else:
			 		return( $results );
			 	endif;
			 }
		}
	endif;
}
?>