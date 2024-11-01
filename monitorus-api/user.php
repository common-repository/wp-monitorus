<?php

/**
 * The Monitor.us User API class
 *
 * @package Monitorus API
 * @subpackage User
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

namespace MonitorusAPI\User\v0_1
{
	use \WP_Error;
	use \MonitorusAPI\MonitorusAPI;
	
	if( !class_exists( '\\' . __NAMESPACE__ . '\\User' ) ):
		class User
		{
			public static function get_api_key( $username, $password )
			{
				$params['action'] = 'apikey';
				$params['userName'] = $username;
				$params['password'] = md5( $password );
				
				$result = MonitorusAPI::make_request( $params );
				if( !is_wp_error( $result ) ):
					return( $result->apikey );
				else:
					return( $result );
				endif;	
			}
			
			public static function get_secret_key( $apikey )
			{
				$params['action'] = 'secretkey';
				$params['apikey'] = $apikey;
				
				$result = MonitorusAPI::make_request( $params );
				if( !is_wp_error( $result ) ):
					return( $result->secretkey );
				else:
					return( $result );
				endif;	
			}
		}
	endif;
}	
?>