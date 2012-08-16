(function($) {
	// swipe for top nav
	$('#logo').bind('touchmove', function (e) {
		e.preventDefault();} 
	);
	$('#logo, h1.nomad').swipeRight(function () {
		closeBottomMenu();
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
	$('#create, h1.nomad').swipeLeft(function () {
		closeBottomMenu();
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

	$('.thrhld').on('click',function () {
		if($(this).parent().hasClass('teaser')) {
			$(this).parent().removeClass('teaser');
		} else {
			$(this).parent().toggleClass('exposed');
			//.css('height',window.innerWidth);
		}
	});
	$('.navbar-bottom .thrhld').swipeDown(function(){
	        $(this).parent().removeClass('exposed teaser');
	});
	$('.navbar-bottom .thrhld').swipeUp(function(){
	        $(this).parent().addClass('exposed').removeClass('teaser');
	});

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('.cube').live('click', function () {
	if($('body').hasClass('onL')){
	    $('#logo').trigger('swipeLeft');
	}else{
	    $('#logo').trigger('swipeRight');
	}
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
	anchor.closest('.listing').find("article span[id^=listing-action] .grip.on").closest('article').trigger('swipeRight');
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
    });
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
    $('#record-action .icon-phone, .btn-group .btn.call, .controls .btn.call').on('click', function () {
	
	    $('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="javascript:return false;" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
	    return false;
    });
    $('.over #cancel').live('click tap', function () {
		$(this).closest('.over').remove();
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
    $('#nomad:not(.flow) #back .back, #nomad:not(.flow) .back.btn').on('click', function(){
		if(history.length<=2) {
			window.location="./";
		}else{
		    window.history.back(-1);		
		}
    });
    $('.listing > article:last-child a.show_more_posts').live('click', function(e){
        $(this).closest('article').remove();
	inject_posts('append',$('.listing'),5);
	return false;
    });
    $('.listing > article:nth-child(3) a.show_more_posts').live('click', function(e){
	$(this).closest('article').remove();
	inject_posts('prepend',$('.listing'),5);
	return false;
    });
    $('.listing > article.nav').live('click', function(e){
        $(this).find('a').first().css('border','1px solid red').trigger('click');
    });

    var post_template = '<article><div title="Perkin Kleiners"><a href="perkin_kleiners.html">Perkin Kleiners</a> is a <a href="100seat.html">100 seat plan</a> of 75K closing in 20 days at <a href="">quality</a> stage  </div><span id="listing-action-item1"><i class="grip">|||</i><span class="hide actions"><a href="#l" title="Log"><i class="icon-share icon-md"></i><br>Reply</a><a href="#r" title="Remove"><i class="icon-trash icon-md"></i><br>Remove</a></span></span></article>',
	more_posts_link = '<article class="nav"><div><a class="show_more_posts" href="#more">Show more activity...</a></div></article>',
	listing_spacer = '<i></i>',
	posts_search_template = '\
	<section class="search topelbar">\
          <i class="icon-search"></i>\
          <div class="form-search row-fluid" action="" _lpchecked="1">\
            <input type="text" class="search-query" placeholder="       Search all activity">\
          </div>\
        </section>';

    function inject_posts(order,anchor,numberofrecords){
	var posts = '',
	    topspacer = '<i></i>',
	    domtopspacer = $('.listing > i');
        for(i=0;i<numberofrecords;i++){
		posts = posts + post_template;
		if(i===numberofrecords-1 && order==='append') {
		    posts = posts+more_posts_link;
		}
	}
	if(order==='prepend' || order==='update'){
	    anchor.children("section.search").remove();
	    anchor.children("i").remove();
	    anchor.children("article.nav").remove();
	    if(anchor.find('article:not(.nav)').size() > 25) {
	        anchor.find('article:not(.nav)').slice(20,25).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
	    }
	    anchor.prepend(listing_spacer + posts_search_template + more_posts_link + posts).append(more_posts_link);
        } else if(order==="append") {
	    anchor.find('article.nav:last-child').remove();
	    if(anchor.find('article:not(.nav)').size() > 25) {
		anchor.children("section.search").remove();
		anchor.children("i").remove();
		anchor.children("article.nav").remove();
	        anchor.find('article:not(.nav)').slice(0,5).addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
		anchor.prepend(listing_spacer + posts_search_template + more_posts_link);
	    }
	    anchor.append(posts);
	}
	setTimeout(function () {
	    $('.deleted').remove();
	}, 250);
    }

    if($('.alert').size()){
	setTimeout(function(ia){
            $('.alert').anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert').hide() });
        }, 3000);
    }

    $(".icon-star-empty, .icon-star").live('click tap',function(){
	var rn=Math.floor(Math.random()*100);
	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-general" style="display:block;"><strong>Loading...</strong></div>');
	setTimeout(function(ia){
            $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
        }, 3000);
    });
      $("a[title=Remove]").live('click tap',function(){
  	var rn=Math.floor(Math.random()*100);
  	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-success" style="display:block;"><strong>Success!</strong> You removed an item!</div>');
  	setTimeout(function(ia){
              $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
          }, 3000);
      });


    $('.icon-star, .icon-star-empty').live('click tap',function(){
        $(this).toggleClass('icon-star-empty').toggleClass('icon-star');
    });
    
    $('form input').on('focus',function(){
	if($('.navbar-bottom').hasClass('teaser')) {
	    $('.navbar-bottom').removeClass('teaser');
	}
    });



    var fd_touch_event = "tap",
        fd_detail_view = $('.layout.detail'),
	fd_listing_view = $('.layout.listing'),
	fd_login_view = $('.layout.login'),
	fd_navbar = $('.navbar'),
	fd_back_btn = $('#back');

    $('#nomad.flow #login_button').on('click',function(){
	fd_listing_view.show().css({opacity:'0',left:'0',right:'0'});
	fd_listing_view.anim({opacity:'1'},.75,'ease-in',function(){
	    fd_navbar.show();
	});
	fd_login_view.anim({opacity:'0'},.5,'ease-out',function(){
	    fd_login_view.css({left:'-101%',position:'absolute'});

	});
	return false;
    });

    $('#nomad.flow #back .back').on(fd_touch_event,function(){ // return to listing
	if(fd_detail_view.hasClass('in_focus')) {
	    fd_detail_view.removeClass('make_static');
	    fd_back_btn.hide();
	    fd_listing_view.show();
	    fd_detail_view.anim({ translateX: '+'+ window.innerWidth +'px', opacity: '1'}, .25, 'ease-out',function(){
		fd_detail_view.removeClass('in_focus')	
	    });
	}
	return false;
    });

    $('#nomad.flow article').on(fd_touch_event,function(){ // show details
	fd_detail_view.anim({ translateX: '-'+ window.innerWidth +'px', opacity: '1'}, .25, 'ease-out',function(){
	    fd_detail_view.addClass('make_static');
	    fd_detail_view.addClass('in_focus')
	    fd_listing_view.hide();
	    fd_back_btn.show();
	});
	return false;
    });

    $('#nomad .list.detail h1.nomad').on(fd_touch_event,function(){
	$('#nomad.flow #back .back').trigger(fd_touch_event);
	return false;
    })
    
//alert('trigger #back click when these are clicked: h1.nomad, h3.nomad --- because they`re overlapping back button');

//   .anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');






})(window.Zepto);

function closeBottomMenu() {
	if($('.navbar-bottom').hasClass('exposed') || $('.navbar-bottom').hasClass('teaser')) {
		$('.navbar-bottom').removeClass('exposed teaser');
	}
}

function parseQueryString(){
    var qs = location.search.substring(1);
    qs = qs.split("&");
    if(qs.length === 2){
	return qs;
    }else{
	qs=qs[0].split('=');
	return qs[1];
    }
}