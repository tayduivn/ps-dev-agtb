(function($) {
	// swipe for top nav
	$('#logo').bind('touchmove', function (e) {
		e.preventDefault();} 
	);
	$('#logo').swipeRight(function () {
		$('html').find('body').addClass('onL');
	});
	$('#logo').swipeLeft(function () {
		$('html').find('body').removeClassClass('onL');
	});
	$('.cube').swipeLeft(function () {
	      $('html').find('body').toggleClass('onL');
	      return false;
	});
	$('#create').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#create').swipeLeft(function () {
		$('html').find('body').addClass('onR');
	});
	$('#create').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#moduleList').swipeLeft(function () {
		$('html').find('body').removeClass('onL');
	});
	$('#createList').bind('touchmove', function (e) {
		e.preventDefault();}
	);
	$('#createList').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#search').bind('touchmove', function (e) {
		e.preventDefault();
	});
	$('#search').swipeDown(function () {
		$('body').find('#searchForm').toggleClass('hide');
	});	
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').live('click', function () {
	$('#logo').trigger('swipeRight');
	return false;
    });
    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.launch').live('click', function () {
	if($('body').hasClass('onR')===true){
	    $('#create').trigger('swipeRight');
	} else {
	    $('#create').trigger('swipeLeft');
	}
	return false;
    });
	$('article').live('swipeLeft',function () {
		var anchor=$(this);
		anchor.closest('#listing').find("article span[id^=listing-action] .grip.on").closest('article').trigger('swipeRight');
		anchor.find('.grip').addClass('on');
		anchor.find('[id^=listing-action] span').removeClass('hide').addClass('on');
	});	
	$('article').live('swipeRight',function () {
		$(this).find('.grip').removeClass('on');
		$(this).find('[id^=listing-action] span').addClass('hide').removeClass('on');
	});	
    $('article .grip').live('click', function () {
	if($(this).hasClass('on')===false){
	    $(this).closest('article').trigger('swipeLeft');
	}else{
	    $(this).closest('article').trigger('swipeRight');
	}
    })
    // search toggle
    $('.navbar').find('#search').on('click', function () {
    	$('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    $('#searchForm').find('.cancel').on('click', function () {
	    $('body').find('#searchForm').toggleClass('hide');
	    return false;
    });
    // fake phone for prototype
    $('#record-action').find('.icon-phone').on('click', function () {
	    $('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
	    return false;
    });
    $('.over').find('#cancel').on('click', function () {
		$(this).remove();
		return false;
    });
    $('a[title=Remove]').live('click', function () {
		$(this).closest('article').addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
		setTimeout(function () {
		    $('.deleted').remove();
		}, 250);
		return false;
    });
    $('#tour').on('click', function () {
      $(this).remove();
    });
    $('#back .back').on('click', function(){	
		if(history.length<=2) {
			window.location="./";
		}else{
		    window.history.back(-1);		
		}
    });

    $('#listing > article:last-child a.show_more_posts').live('click', function(e){
        $(this).closest('article').remove();
	inject_posts('append',$('#listing'),5);
	return false;
    });

    $('#listing > article:nth-child(2) a.show_more_posts').live('click', function(e){
	$(this).closest('article').remove();
	inject_posts('prepend',$('#listing'),5);
	return false;
    });

    if($('.alert').size()){
	setTimeout(function(ia){
            $('.alert').anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert').remove() });
        }, 3000);
    }

    var post = '<article><div title="Perkin Kleiners"><a href="perkin_kleiners.html">Perkin Kleiners</a> is a <a href="100seat.html">100 seat plan</a> of 75K closing in 20 days at <a href="">quality</a> stage  </div><span id="listing-action-item1"><i class="grip">|||</i><span class="hide actions"><a href="" title="Log"><i class="icon-share icon-md"></i><br>Reply</a><a href="" title="Remove"><i class="icon-trash icon-md"></i><br>Remove</a></span></span></article>',
	more_posts_link = '<article class="nav"><div><a class="show_more_posts" href="">Show more posts...</a></div></article>';

    function inject_posts(order,anchor,numberofrecords){
	var posts = '',
	    topspacer = '<i></i>',
	    domtopspacer = $('#listing > i');
        for(i=0;i<numberofrecords;i++){
		if(i===0 && order==='prepend') {
		    posts = topspacer + more_posts_link + posts;
		}
		posts = posts + post;
		if(i===numberofrecords-1 && order==='append') {
		    posts = posts+more_posts_link;
		}
	}
	if(order==='prepend' || order==='update'){
	    anchor.children('i').remove();
	    anchor.prepend(posts);
	    if(anchor.find('article:not(.nav)').size() > 25) {
	            anchor.find('article:not(.nav)').slice(20,25).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
	    }
        } else if(order==="append") {
	    anchor.append(posts);
	    if(anchor.find('article:not(.nav)').size() > 25) {
                if(anchor.find('article:nth-child(2n)').hasClass('nav')===false) {
		    anchor.children('i').remove();
	            anchor.prepend(topspacer + more_posts_link);
                }
	        anchor.find('article:not(.nav)').slice(0,5).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
	    }
	}
	setTimeout(function () {
	    $('.deleted').remove();
	}, 250);
    }
})(window.Zepto);