/**
 * ajax call to retrieve the api key
 */
function get_api_key() {
	var $ = jQuery;
	var apikeyfield = '#api-key';
	
	//activate the spinner
	$( '.loading-ajax' ).css( 'visibility', '' );
	
	//hide messages from previous attempts
	$( '#success' ).hide();
	$( '#error' ).hide();
	
	//initialize our variable
	var s = {};
	s.response = 'ajax-response';
	s.type = 'POST';
	s.url = ajaxurl;
	s.data = {
			action: 'wpmon_getapikey',
			username: $( '#username' ).val(),
			password: $( '#password' ).val()
	};
	s.global = false;
	s.timeout = 30000;
	s.dataType = 'json';
	s.success = function( r ) {
		if( r.result == 'success' ) {
			$( '#success-message' ).html( r.message );
			$( apikeyfield ).val( r.data );
			$( '#success' ).fadeToggle( 'slow' );
		} else if ( r.result == 'failure' ) {
			$( '#error-message' ).html( r.message );
			$( '#error' ).fadeToggle( 'slow' );
		}
	}

	s.error = function( r, textStatus ) {
		$( '#error' ).html( wpmonSettingsl10n.errorString );
	}
	
	s.complete = function( r ){
		$( '.loading-ajax' ).css( 'visibility', 'hidden' );
	}
	// make the request
	$.ajax( s );
}

/**
 * display/hide the retrieve api form
 */
function toggle_apikey_form( obj, defaulttext, defaultaction ) {
	var $ = jQuery;
	
	if( $( obj ).val() === 'get' ) {
		//display the get api key form
		$( '#get-api-key').fadeToggle( 'slow' );
		$( obj ).html( wpmonSettingsl10n.buttonCancel );
		$( obj ).val( 'cancel' );
	} else if( $( obj ).val() === 'show' ) {
		//open a dialog box to display the key
		$( '#wpmondialog' ).html( $( '#api-key').val() );
		$( '#wpmondialog' ).dialog(
			{ 
				closeOnEscape: true,
				modal: true,
				buttons:
				{ 
					Ok: function() 
					{ 
						$( this ).dialog( "close" );
					}
				}
			}
		);
	} else if( $( obj ).val() == 'cancel' ) {
		$( obj ).html( defaulttext );
		$( obj ).val( defaultaction );
		$( '#get-api-key').fadeToggle( 'slow' );
	}
}

/**
 * used to display the chart preview
 */
function changePreview( obj ) {
	jQuery('#chart-preview').html( '<img src="' + wpmonSettingsl10n.pluginUrl + 'images/highcharts-theme-' + jQuery( obj ).val() + '.png" />' );
}
