<?php

/**
 * The Monitorus API for WordPress Class
 *
 * This class contains basic helper functions for the Monitorus API. It utilizes the WordPress HTTP API to
 * process remote requests.
 *
 * @package Monitorus API
 * @version 0.1
 * @since 0.1
 * @author Daryl Lozupone <dlozupone@renegadetechconsulting.com>
 *
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

namespace MonitorusAPI
{
	use \WP_Error;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\MonitorusAPI' ) ):
		/**
		 * helper function class
		 *
		 * @package Monitorus API
		 * @since 0.1
		 */
		class MonitorusAPI
		{
			/**
			 * the API endpoint URL
			 * 
			 * @package Monitorus API
			 * @subpackage External Monitors
			 * @var string
			 * @since 0.1
			 */
			private static $api_url = "http://monitor.us/api";
		
 			/**
			 * issue the remote url request
			 *
			 * @package Monitorus API
			 *
			 * @param array $params the url query variables to generate the API request
			 * @return object the response object on success, WP_error on failure
			 * @since 0.1
			 */
			public static function make_request( $params )
			{
				//if( $params['output'] != 'json' && $params['output'] != 'xml' )
					$params['output'] = 'json';
					
				//generate the request URL
				$url = self::$api_url . '?version=2';
				foreach( $params as $key => $val ):
					if($val != '' )
						$url .= "&".$key."=".$val;
				endforeach;
				
				//make the request
				$request = wp_remote_get( $url, array( 'timeout' => '10' ) );
				
				//did WP encounter an error retrieving the remote url?
				if( is_wp_error( $request ) ):
					return( $request );
				else:
					//was the API call successfully processed?
					if( wp_remote_retrieve_response_code( $request ) == '200' ):
						return( json_decode( wp_remote_retrieve_body( $request ) ) );
					else:
						$errormessage = json_decode( wp_remote_retrieve_body( $request ) );
						//generate an WP_Error
						return( new WP_Error
							(
								'monitis_api_error',
								$errormessage->error,
								$url
							)
						);
					endif;
				endif; 
			}
			
			/**
			 * decode a response object encoded in either xml or json
			 *
			 * @package Monitorus API
			 *
			 * @param string $output the response body
			 * @param string $format xml or json (default)
			 * @return object the decoded response body as a standard object
			 * @since 0.1
			 * @todo add code to decode xml
			 */
			public static function decode_response( $output, $format = 'json' )
			{
				if( $format == 'json' ):
					return( json_decode( $output ) );
				else:
					print_r(new \SimpleXMLElement( $output ));
					$results =  new \SimpleXMLElement( $output );
					//strip off the top level element
					foreach( $results as $result ):
						$object = new \stdClass;
						foreach( $result as $key => $val ):
							$object->$key = $val;
						endforeach;
					endforeach;

					return( $object );
				endif;
			}
		}
	endif;
	
	require_once( 'user.php' );
	require_once( 'external-monitors.php' );
}
?>