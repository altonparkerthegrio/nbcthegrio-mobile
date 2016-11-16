jQuery( document ).ready( function( $ ) {
	var count = 0;

	//Starting slide, set from URL if hash is present
	var startingSlide = 0;
	var hash = window.location.hash;
	if( hash.length > 3 ) {
		var fragment = hash.substr( 3 );
		fragment = '.thegrio-gallery .cycle-wrapper #' + fragment;
		var $fragment = $(fragment);

		if($fragment.length > 0) {
			var possibleStartingSlide = $( fragment ).index();
			// Slides are zero indexed, so checking if possibleStartingSlide is less than totalSlides is sufficient
			var totalSlides = $( '.thegrio-gallery .cycle-wrapper > div' ).length;

			if( typeof possibleStartingSlide == 'number' && possibleStartingSlide > 0 && possibleStartingSlide < totalSlides ) {
				startingSlide = possibleStartingSlide;
			}
		}
	}

	var isMobile = $('body').hasClass('mobile');
	function beforeSlide( currSlideElement, nextSlideElement, options, forwardFlag ) {
		//URL hash
		var id = $( nextSlideElement ).attr( 'id' );
		window.location.hash = 's:' + id;
		console.log(id);

		//Count
		var slideIndex = $( nextSlideElement ).index();
		$( '.thegrio-gallery div.count span' ).text( slideIndex + 1 );

		if( !isMobile ) {
			//Width
			$( '.thegrio-gallery .cycle-wrapper' ).css( 'width', '650px' );
		}

			//Height
			var new_height = $( nextSlideElement ).height();
			$( '.thegrio-gallery .cycle-wrapper' ).animate( { height: new_height }, 300 );

			//Ad refresh
			$( '.ad iframe.ad-target' ).each( function( index ) {

				var current_iframe = $(this);

				if( count > 0 ) {
					var src = current_iframe.attr( 'src' );
					current_iframe.attr( 'src', src );
				}

			//Reset the iFrame init height to zero
			current_iframe.height( '0' );
		} );

		count = count + 1;
	}

	function afterSlide( currSlideElement, nextSlideElement, options, forwardFlag ) {
		if( !isMobile ) {
			//Width
			$( '.thegrio-gallery .cycle-wrapper' ).css( 'width', '650px' );
		}

		// We only want to show ads after the first load of cycle to prevent double-loading
		var loaded = false;
		$( 'iframe.ad-target' ).each( function( index ) {
			var current_iframe = $(this);
			if( current_iframe.hasClass('loaded') ) {
				loaded = true;
			}
		} );

		if( loaded ) {
			refreshAds();
		} else {
			$( 'iframe.ad-target' ).addClass('loaded');
		}
	}

	var cycleOptions = {
		fx: 'fade',
		prev: '.thegrio-gallery .thegrio-gallery-pager .previous',
		next: '.thegrio-gallery .thegrio-gallery-pager .next',
		fit: true,
		before: beforeSlide,
		after: afterSlide,
		startingSlide: startingSlide
	}

	if( !isMobile ) {
		cycleOptions.width = "650px";
	}
	//Cycle setup and pause
	$( '.thegrio-gallery .cycle-wrapper' ).cycle( cycleOptions ).cycle( 'pause' );

	//Reveal slideshow
	$( '.thegrio-gallery .cycle-wrapper' ).css( 'visibility', 'visible' );
	$( '.thegrio-gallery' ).css( {
		position: 'relative'
	} );
} );

function refreshAds() {
	var count = 0;
	var $ = jQuery;
	//Ad refresh
	$( 'iframe.ad-target' ).each( function( index ) {
		var current_iframe = $(this);
		var src = current_iframe.attr( 'src' );
		current_iframe.attr( 'src', src );
		count++;

		current_iframe.addClass('loaded');
	} );

}

function resizeAds() {
	var $ = jQuery;
	$( '.ad iframe.ad-target' ).each( function( index ) {
		resizeThisAd( current_iframe );
	} );
}

function resizeThisAd( current_iframe ){
		var $ = jQuery;
		current_iframe.iframeAutoHeight( {
			//debug: true,
			callback: function( callbackObject ) {
				if ( callbackObject.newFrameHeight === 0 ) {
					current_iframe.parent( '.ad' ).hide();
				}
			}
		} );
}

