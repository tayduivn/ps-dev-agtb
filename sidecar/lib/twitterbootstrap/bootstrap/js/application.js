!function ($) {

  $(function(){

    // make code pretty (styleguide only)
    window.prettyPrint && prettyPrint()

    // add tipsies to grid for scaffolding (styleguide only)
    if ($('#grid-system').length) {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px' }
      })
    }

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

    // do this if greater than 960px page width
    if ( $(window).width() > 768) {		

    // tooltip demo
    $('body').tooltip({
      selector: "a[rel=tooltip]"
    })
    $('table').tooltip({
			delay: { show: 500, hide: 10 },
      selector: "[rel=tooltip]"
    })
    $('.btn-group, .block, .thumbnail').tooltip({
      selector: "a[rel=tooltip]",
			placement: "bottom"
    })
    $('.navbar').tooltip({
      selector: "a[rel=tooltip]",
			placement: "bottom"
    })

    $("a[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })
    $("a[rel=popoverTop]")
      .popover({
        placement: "top"
      })
      .click(function(e) {
        e.preventDefault()
      })
      
    }
    // button state demo
    $('.loading')
      .click(function () {
        var btn = $(this)
        btn.button('loading')
        setTimeout(function () {
          btn.button('reset');
					$('.modal').modal('hide')
        }, 2000)
      })

    // javascript build logic
    var inputsComponent = $("#listed input");

		// tour
    $('#tour').on('click', function (e) {
			$('.pointsolight').prependTo('body');
    })

		// remove a close item
    $('.close').on('click', function (e) {
			$(this).parent().remove();
    })
    
    // toggle stars (needs tap logic for mobile)
  	$('.icon-star-empty, .icon-star').on('click', function () {
  	      $(this).toggleClass('icon-star-empty').addClass('icon-star');
  	      return false;
  	})
  	
    // toggle all checkboxes
    $('.toggle-all').on('click', function (e) {
      inputsComponent.attr('checked', !inputsComponent.is(':checked'))
			$('.alert').show()
    })
  })
  
  $('.datatable').dataTable({
    "bPaginate": false,
    "bFilter": true,
    "bInfo": false,
    "bAutoWidth": true
  })
  
  // toggle module search (needs tap logic for mobile)
	$('.addit').on('click', function () {
	    $(this).toggleClass('active');
	    $(this).parent().parent().parent().find('.form-addit').toggleClass('hide');
	    return false;
	})
	$('.search').on('click', function () {
	    $(this).toggleClass('active');
	    $(this).parent().parent().parent().find('.dataTables_filter').toggle();
	    $(this).parent().parent().parent().find('.form-search').toggleClass('hide');
	    return false;
	})
  $('#moduleLog.filtered input').quicksearch('#moduleLog article')
  $('#moduleRelated.filtered input').quicksearch('#moduleRelated article')
  $('#moduleActivity.filtered input').quicksearch('#moduleActivity article')


	$('.block').hover( function () {
	    $(this).find('.actions .btn').toggleClass('btn-success');
	    $(this).find('.actions .btn.btn-success').css('color','#fff');
	    return false;
	})
  
  $('.dblclick').hover( 
    function () {$(this).before('<i class="icon-pencil icon-sm"></i>');},
    function () {$('.icon-pencil').remove();}
	)
  
// Modified from the original jsonpi https://github.com/benvinegar/jquery-jsonpi
$.ajaxTransport('jsonpi', function(opts, originalOptions, jqXHR) {
  var url = opts.url;

  return {
    send: function(_, completeCallback) {
      var name = 'jQuery_iframe_' + jQuery.now()
        , iframe, form

      iframe = $('<iframe>')
        .attr('name', name)
        .appendTo('head')

      form = $('<form>')
        .attr('method', opts.type) // GET or POST
        .attr('action', url)
        .attr('target', name)

      $.each(opts.params, function(k, v) {

        $('<input>')
          .attr('type', 'hide')
          .attr('name', k)
          .attr('value', typeof v == 'string' ? v : JSON.stringify(v))
          .appendTo(form)
      })

      form.appendTo('body').submit()
    }
  }
})
}(window.jQuery)