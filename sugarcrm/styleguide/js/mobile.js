(function(document,navigator,standalone) {
	// prevents links from apps from oppening in mobile safari
	// this javascript must be the first script in your <head>
	if ((standalone in navigator) && navigator[standalone]) {
		var curnode, location=document.location, stop=/^(a|html)$/i;
		document.addEventListener('click', function(e) {
			curnode=e.target;
			while (!(stop).test(curnode.nodeName)) {
				curnode=curnode.parentNode;
			}
			// Condidions to do this only on links to your own app
			// if you want all links, use if('href' in curnode) instead.
			if(
				'href' in curnode && // is a link
				(chref=curnode.href).replace(location.href,'').indexOf('#') && // is not an anchor
				(!(curnode.attributes.getNamedItem('data-remote'))) && // does not contain the data-remote attribute used by jquery-ujs
				(	!(/^[a-z\+\.\-]+:/i).test(chref) ||                       // either does not have a proper scheme (relative links)
				chref.indexOf(location.protocol+'//'+location.host)===0 ) // or is in the same protocol and domain
			) {
				e.preventDefault();
				location.href = curnode.href;
			}
			},false);
		}
})(document,window.navigator,'standalone');
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
