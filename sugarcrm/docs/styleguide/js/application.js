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

    //chosen
   // $(".chzn-select").chosen({no_results_text: "No results matched"}); 
   // $(".chzn-select-deselect").chosen({allow_single_deselect:true});

    // tooltip demo
    $('section').tooltip({
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
		// styleguide
    $('.tooltip-test').tooltip()
    $('.popover-test').popover()

    // popover demo 
    $("a[rel=popover]")
      .popover()
      .click(function(e) {
        e.preventDefault()
      })


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

  //$('input#bugsSearchQuery').quicksearch('table.searchable tbody tr')
  
  // toggle module search (needs tap logic for mobile)
	$('.search').on('click', function () {
	    $(this).toggleClass('active');
	    $(this).parent().parent().parent().find('.form-search').toggleClass('hide');
	    return false;
	})

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