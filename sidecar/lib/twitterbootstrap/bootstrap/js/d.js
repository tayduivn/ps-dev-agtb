!function( $ ){
  "use strict"
  $(function () {
		if ( $(window).width() < 960) {
			$('.cube').click(function () {
	      $('html').find('body').toggleClass('onL');
	    		return false;
			})
		}
  })
}( window.jQuery )
