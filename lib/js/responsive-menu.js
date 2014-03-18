( function( window, $, undefined ) {
	'use strict';

	$( 'nav.mobile' ).before( '<button class="menu-toggle" role="button" aria-pressed="false"></button>' ); // Add toggles to menus
	$( 'nav.mobile' ).hide(); // begin with mobile menu hidden
	$( 'nav.mobile .sub-menu' ).before( '<button class="sub-menu-toggle" role="button" aria-pressed="false"></button>' ); // Add toggles to sub menus

	// Show/hide the navigation
	$( '.menu-toggle, .sub-menu-toggle' ).on( 'click', function() {
		var $this = $( this );
		$this.attr( 'aria-pressed', function( index, value ) {
			return 'false' === value ? 'true' : 'false';
		});

		$this.toggleClass( 'active' );
		$this.toggleClass( 'activated' );
		$this.next( 'nav.mobile, .sub-menu' ).slideToggle( 'fast' );

	});

})( this, jQuery );