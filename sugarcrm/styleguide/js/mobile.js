(function($) {
  
    // swipe for top nav
    $('.navbar').bind('touchmove', function (e) {e.preventDefault();} );

		$('#logo').swipeRight(function () {
      $('html').find('body').addClass('onL');
		})
		$('#logo').swipeLeft(function () {
      $('html').find('body').removeClass('onL');
		})
		
		$('#create').swipeLeft(function () {
      $('html').find('body').addClass('onR');
		})  		
		$('#create').swipeRight(function () {
      $('html').find('body').removeClass('onR');
		})
		
		$('#moduleList').swipeLeft(function () {
      $('html').find('body').removeClass('onL');
		})
		
		$('#createList').swipeRight(function () {
      $('html').find('body').removeClass('onR');
		})  		
		$('.navbar').swipeDown(function () {
      $('body').find('#searchForm').toggleClass('hide');
		})
		
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').click(function () {
      $('html').find('body').toggleClass('onL');
      return false;
    })


	$('article > .icon-star-empty, article > .icon-star').on('click', function () {
	      $(this).toggleClass('icon-star-empty').addClass('icon-star');
	})

/*
    // toggle stars (needs tap logic for mobile)
    $('article').find('[class^=icon-star]').on('click', function () {
      $(this).toggleClass('icon-star icon-star-empty');
    })
*/

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.launch').click(function () {
      $('html').find('body').toggleClass('onR');
      return false;
    })

    

		$('article').swipeLeft(function () {
		  $(this).find('.grip').addClass('on');
      $(this).find('[id^=listing-action] span').removeClass('hide').addClass('on');
		})
		
		$('article').swipeRight(function () {
      $(this).find('.grip').removeClass('on');
      $(this).find('[id^=listing-action] span').addClass('hide').removeClass('on');
		})
		
    $('article .grip').on('click', function () {
      $(this).next('.actions').toggleClass('hide');
      $(this).toggleClass('on');
    })

    // search toggle
    $('.navbar').find('#search').on('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })
    $('#searchForm').find('.cancel').on('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })

    // fake hide of message for prototype
    setTimeout(function() {
      $(".alert").fadeOut();
    }, 3600);

    // fake hide of record for prototype
    $('article').find('[class^=icon-remove]').on('click', function () {
      $(this).parent().remove();
      $('container-fluid').html('<div class="top alert alert-danger alert-block">Opportunity has been removed.</div>');
    })
    
})(window.Zepto);
