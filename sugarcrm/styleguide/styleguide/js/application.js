!function ($) {
  $(function(){

    // fix sub nav on scroll
    var $win = $(window)
      , $nav = $('.subnav')
      , navTop = $('.subnav').length && $('.subnav').offset().top - 40
      , isFixed = 0

    processScroll()
    $win.on('scroll', processScroll)

    function processScroll() {
      var i, scrollTop = $win.scrollTop()
      if (scrollTop >= navTop && !isFixed) {
        isFixed = 1
        $nav.addClass('subnav-fixed')
      } else if (scrollTop <= navTop && isFixed) {
        isFixed = 0
        $nav.removeClass('subnav-fixed')
      }
    }

    // do this if greater than 768px page width
    if ( $(window).width() > 768) {		
      // tooltip demo
      $('body').tooltip({
        selector: "[rel=tooltip]"
      })
      $('table').tooltip({
        selector: "[rel=tooltip]"
      })
      $('.thumbnail').tooltip({
        selector: "[rel=tooltip]",
  			placement: "bottom"
      })
      $('.navbar, .subnav').tooltip({
        selector: "[rel=tooltip]",
  			placement: "bottom"
      })  
    }

    $("th:contains('Subject')").css("width","50%")
    $("th:contains('Modified'),th:contains('Created'),th:contains('Number'),th:contains('ID'),th:contains('input'),th:contains('cog')").css("width","1%")
    $("th:contains('Opportunity'),th:contains('Name')").css("width","30%")
    $("#folded th:contains('Opportunity'),th:contains('Name')").css("width","70%")
  
    // keybinding
    $(document).keyup(function(e){
        if(e.keyCode === 27) 
          $(".alert-top").remove();
    })

    // add tipsies to grid for scaffolding (styleguide only)
    if ($('#grid-system').length) {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px' }
      })
    }

    // toggle all stars
    $('.toggle-all-stars').on('click', function (e) {
    		$(this).closest('table').toggleClass('active'); 
    		return false;
    })

    // toggle all checkboxes
    $('.toggle-all').on('click', function (e) {
    		$('table').find(':checkbox').attr('checked', this.checked);      
    })

    // timeout the alerts
    setTimeout(function(){$('.timeten').fadeOut().remove();},9000)

    // toggle star
    $('.icon-star').on('click', function (e) {
    		$(this).parent().toggleClass('active');
    		return false;  
    })
    
    $('.comment').toggle( 
      function (e) {
  		  $(this).parent().parent().find('ul').append('<li class="acomment"><div class="control-group form-horizontal"><input placeholder="Add your comment" class="span10"> <input type="submit" class="btn btn-primary" value="Reply"></div></li>');
  		  $(this).addClass('active');
  		  return false; 
	    },
	    function (e) {
  		  $(this).parent().parent().find('.acomment').remove();
  		  $(this).removeClass('active');
  		  return false; 
	    }
    ) 

    // toggle more hide
    $('.more').toggle(
      function (e) {
    		$(this).parent().prev('.extend').removeClass('hide');
    		$(this).html('Less &nbsp;<i class="icon-caret-up"></i>');
    		return false;  
      },
      function (e) {
      		$(this).parent().prev('.extend').addClass('hide');
      		$(this).html('More &nbsp;<i class="icon-caret-down"></i>');
      		return false;  
    })
    // toggle more hide
    $('.commented .more').toggle(
      function (e) {
        $(this).parent().parent().parent().find('.comment').remove();
        $(this).parent().parent().find('.acomment').remove();
    		$(this).parent().prev('.extend').removeClass('hide');
    		$(this).html('<div class="control-group form-horizontal accoment"><input placeholder="Add your comment" class="span10"> <input type="submit" class="btn btn-primary" value="Reply"></div>');
    		return false;  
      },
      function (e) {
      		$(this).parent().prev('.extend').addClass('hide');
      		$(this).html('2 more comments...');
      		return false;  
    })

    // toggle drawer hide
    $('.drawer').toggle(
      function (e) {
    		$(this).next('.extend').removeClass('hide');
    		$(this).find('.toggle').html('<i class="icon-caret-up"></i>');
    		return false;  
      },
      function (e) {
      		$(this).next('.extend').addClass('hide');
      		$(this).find('.toggle').html('<i class="icon-caret-down"></i>');
      		return false;  
    })

    // column collapse
    $('.drawerTrig').on('click',
      function () {
       // $(this).toggleClass('pull-right').toggleClass('pull-left');
        $(this).find('i').toggleClass('icon-chevron-left').toggleClass('icon-chevron-right');
        $(this).parent("div").toggleClass("span12").toggleClass("span8");
        $('#overview').toggleClass('hide');
        $('.content').toggleClass("span12").toggleClass("span8");
        //$('.bordered').toggleClass('hide');
       // $('#charts').toggleClass('span10').toggleClass('span12');
        return false;
    })

    $('.btngroup .btn').button()

    // editable example
    $('.dblclick').hover(
      function () {
        $(this).before('<span class="span2" style="border-right: none; position: absolute; top: -2px; left: -16px; width: 15px"><i class="icon-pencil icon-sm"></i></span>');
      },
      function () { 
        $('span.span2').remove(); 
      }
    )
    
})
    
    

    $(".omnibar").toggle(
      function (e) {
   		$(this).addClass('active');
   		$(this).append('<div class="inputactions span10"><a href=""><i class="icon-tag"></i></a> <a href=""><i class="icon-paper-clip"></i></a> <input type="submit" class="pull-right btn btn-primary"><span class="pull-right">Send to <a href="">Everyone</a> &nbsp;</div>'); 
   		return false;
      },
      function (e) {
        $(this).removeClass('active');
        $('.inputactions').remove();
      		return false;  
    })

    $('.actions').find('a[data-toggle=tab]').on('click', function (e) {
      $('.nav-tabs').find('li').removeClass('on');
  		$(this).parent().parent().addClass('on');
    })

    // remove a close item
    $('#folded').find('[data-toggle=tab]').on('click', function (e) {
			$('.nav-tabs').find('li').removeClass('on');
    })

    // toggle module search (needs tap logic for mobile)
  	$('.addit').on('click', function () {
  	    $(this).toggleClass('active');
  	    $(this).parent().parent().parent().find('.form-addit').toggleClass('hide');
  	    return false;
  	})

  	$('.search').on('click', function () {
  	    $(this).toggleClass('active');
  	    $(this).parent().parent().parent().find('.dataTables_filter').toggle(
  	      function () {
  	        $(this).find('input').focus();
    	  });
    	  $(this).parent().parent().parent().find('.form-search').toggle(
  	      function () {
  	        $(this).find('input').focus();
    	  });
  	    $(this).parent().parent().parent().find('.form-search').toggleClass('hide');
  	    return false;
  	})
    
    $('table.datatable').dataTable({
      "bPaginate": false,
      "bFilter": true,
      "bInfo": false,
      "bAutoWidth": false
    })

    // Select widget
    $(".chzn-select").chosen()
    $(".chzn-select-deselect").chosen({allow_single_deselect:true})

    //popover
    $("[rel=popover]").popover()
    $("[rel=popoverTop]").popover({placement: "top"})
    $("[rel=popoverBottom]").popover({placement: "bottom"})

    $('#moduleActivity .form-search select').chosen()

    $('#moduleActivity .form-search input').quicksearch('ul.results li')

}(window.jQuery)