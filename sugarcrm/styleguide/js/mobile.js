(function($) {
        var navbar_bottom_handler = function(e){
		e.preventDefault();
		if($(this).parent().hasClass('teaser')) {
			$(this).parent().removeClass('teaser');
		} else {
			$(this).parent().toggleClass('exposed');
		}
		return false;
	    },
	    cube_handler = function (e) {
		e.preventDefault();
		if($('body').hasClass('onL')){
		    $('#logo').trigger('swipeLeft');
		}else{
		    $('#logo').trigger('swipeRight');
		}
		return false;
	    },
	    launch_handler = function (e) {
		e.preventDefault();
		if($('body').hasClass('onR')===true){
		    $('#create').trigger('swipeRight');
		} else {
		    $('#create').trigger('swipeLeft');
		}
		return false;
            };
	$('#logo,.navbar-bottom,.thrhld,#create,#createList,#search').bind('touchmove', function (e) {
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
	$('#createList').swipeRight(function () {
		$('html').find('body').removeClass('onR');
	});
	$('#search').swipeDown(function () {
		$('body').find('#searchForm').toggleClass('hide');
	});
	$('.navbar-bottom').onpress('.thrhld',navbar_bottom_handler);
	$('.navbar-bottom .thrhld').swipeDown(function(){
	        $(this).parent().removeClass('exposed teaser');
	});
	$('.navbar-bottom .thrhld').swipeUp(function(){
	        $(this).parent().addClass('exposed').removeClass('teaser');
	});

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('body').onpress('.cube', cube_handler);

    // trigger the module menu - this could be a tap function but zepto will not honor return false
    $('body').onpress('.launch', launch_handler);

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
    $('body').onpress('article .grip', function (e) {
	e.preventDefault();
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
    $('body').onpress('#record-action .icon-phone, .btn-group .btn.call, .controls .btn.call', function () {
	$('body').append('<div class="over"><h4>Place a call</h4><p><a href="tel:605-334-2345" class="btn btn-large">Home (605)-334-2345</a></p><p><a class="btn btn-large">Mobile (605)-334-2345</a></p><p><a class="btn btn-large">Office (605)-334-2345</a></p><p><a href="javascript:return false;" class="btn btn-inverse btn-large" id="cancel">Cancel</a></p></div>');
	return false;
    });
    $('body').onpress('.over #cancel', function () {
	$(this).closest('.over').remove();
	return false;
    });
    $('body').onpress('a[title=Remove]', function () {
		$(this).closest('article').addClass('deleted').anim({ translateX: window.innerWidth + 'px', opacity: '0'}, .5, 'ease-out');
	setTimeout(function () {
	    $('.deleted').remove();
	}, 250);
	return false;
    });
    $('body').onpress('#tour', function (e) {
	e.preventDefault();
	$(this).remove();
    });
    $('body').onpress('#nomad:not(.flow) #back .back, #nomad:not(.flow) .back.btn', function(){
	if(history.length<=2) {
		window.location="./";
	}else{
	    window.history.back(-1);		
	}
    });
    $('body').onpress('.listing > article:last-child a.show_more_posts', function(e){
	e.preventDefault();
        $(this).closest('article').remove();
	inject_posts('append',$('.listing'),5);
	return false;
    });
    $('body').onpress('.listing > article:nth-child(3) a.show_more_posts', function(e){
	$(this).closest('article').remove();
	inject_posts('prepend',$('.listing'),5);
	return false;
    });
    $('body').onpress('.listing > article.nav', function(e){
	e.preventDefault();
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

    $("body").onpress('.icon-star-empty, .icon-star',function(e){
	e.preventDefault();
	var rn=Math.floor(Math.random()*100);
	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-general" style="display:block;"><strong>Loading...</strong></div>');
	setTimeout(function(ia){
            $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
        }, 3000);
    });
      $("body").onpress('a[title=Remove]',function(){
  	var rn=Math.floor(Math.random()*100);
  	$('body').append('<div id="demo-general" class="tmp-' + rn + ' alert alert-success" style="display:block;"><strong>Success!</strong> You removed an item!</div>');
  	setTimeout(function(ia){
              $('.alert.tmp-'+rn).anim({ translateY: window.innerHeight + 'px', opacity: '0'}, 3, 'ease-out', function (){ $('.alert.tmp-'+rn).remove() });
          }, 3000);
      });


    $('body').onpress('.icon-star, .icon-star-empty',function(){
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

    $('body').onpress('#nomad.flow #login_button',function(){
	fd_listing_view.show().css({opacity:'0',left:'0',right:'0'});
	fd_listing_view.anim({opacity:'1'},.75,'ease-in',function(){
	    fd_navbar.show();
	});
	fd_login_view.anim({opacity:'0'},.5,'ease-out',function(){
	    fd_login_view.css({left:'-101%',position:'absolute'});

	});
	return false;
    });

    $('body').onpress('#nomad.flow #back .back',function(){ // return to listing
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

    $('body').onpress('#nomad.flow article',function(){ // show details
	fd_detail_view.anim({ translateX: '-'+ window.innerWidth +'px', opacity: '1'}, .25, 'ease-out',function(){
	    fd_detail_view.addClass('make_static');
	    fd_detail_view.addClass('in_focus')
	    fd_listing_view.hide();
	    fd_back_btn.show();
	});
	return false;
    });

    $('body').on('#nomad .list.detail h1.nomad',function(){
	$('#nomad.flow #back .back').trigger(fd_touch_event);
	return false;
    })

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