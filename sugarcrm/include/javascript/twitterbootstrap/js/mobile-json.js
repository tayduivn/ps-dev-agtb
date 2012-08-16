(function($) {
    // swipe for top nav
    $('#logo').live('touchmove', function (e) {
		e.preventDefault();
	});
	$('#logo').live('swipeRight', function () {
        $('html').find('body').addClass('onL');
	});
	$('#logo').live('swipeLeft', function () {
		$('html').find('body').removeClass('onL');
	})

	$('#create').live('touchmove', function (e) {e.preventDefault();} );
	$('#create').live('swipeLeft', function () {
		$('html').find('body').addClass('onR');
	})  		
	$('#create').live('swipeRight', function () {
		$('html').find('body').removeClass('onR');
	})

	/*$('#moduleList').bind('touchmove', function (e) {e.preventDefault();} );*/
	$('#moduleList').live('swipeLeft', function () {
		$('html').find('body').removeClass('onL');
	})

	$('#createList').live('touchmove', function (e) {e.preventDefault();} );		
	$('#createList').live('swipeRight', function () {
		$('html').find('body').removeClass('onR');
	})  		
	$('#search').live('touchmove', function (e) {e.preventDefault();} );
	$('#search').live('swipeDown', function () {
		$('body').find('#searchForm').toggleClass('hide');
	})
		
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').live('click', function () {
      $('html').find('body').toggleClass('onL');
      return false;
    })

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.launch').live('click', function () {
      $('html').find('body').toggleClass('onR');
      return false;
    })

	$('article').live('swipeLeft',function () {
		  $(this).find('.grip').addClass('on');
      $(this).find('[id^=listing-action] span').removeClass('hide').addClass('on');
	})
		
	$('article').live('swipeRight',function () {
      $(this).find('.grip').removeClass('on');
      $(this).find('[id^=listing-action] span').addClass('hide').removeClass('on');
	})
		
    $('article .grip').live('click', function () {
      $(this).next('.actions').toggleClass('hide');
      $(this).toggleClass('on');
    })

    // search toggle
    $('.navbar').find('#search').live('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })
    $('#searchForm').find('.cancel').live('click', function () {
      $('body').find('#searchForm').toggleClass('hide');
      return false;
    })

    // fake phone for prototype
    $('#record-action').find('.icon-phone').live('click', function () {
      $('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
      return false;
    })
    
    $('.over').find('#cancel').live('click', function () {
      $(this).remove();
      return false;
    })

    $('a[title=Remove]').live('click', function () {
      //$(this).closest('article').hide();
      $(this).closest('article').addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
      setTimeout(rmel,250);
	  return false;
    })   

  	$('.icon-star-empty, .icon-star').live('click', function () {
  	      $(this).toggleClass('icon-star-empty').addClass('icon-star');
  	      return false;
  	})

    $('#tour').live('click', function () {
      $(this).remove();
    })

	function rmel(){
      $('.deleted').remove();
	}

})(window.Zepto);