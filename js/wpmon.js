jQuery(document).ready( function($){
	//load the snapshot widget html
	$.post(
		ajaxurl,
		{
			'action': 'wpmon_snapshot'
		},
		function(response){
			$('#wpmon-snapshot').html( response );
		}
	);
	
	//load the monitor widget html
	$.post(
		ajaxurl,
		{
			'action': 'wpmon_monitors'
		},
		function(response){
			$('#wpmon-monitors').html( response );
		}
	)
});

function toggleGraph( obj, graphid ) {
	if( jQuery( obj ).val() == 'showgraph' ) {
		jQuery( graphid ).slideDown( 'slow' );
		jQuery( obj ).val( 'hidegraph' );
		jQuery( obj ).html( wpmonAdminl10n.hideGraphText );
	} else {
		jQuery( graphid ).slideUp( 'slow' );
		jQuery( obj ).val( 'showgraph' );
		jQuery( obj ).html( wpmonAdminl10n.showGraphText );
	}
}