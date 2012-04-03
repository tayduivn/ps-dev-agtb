!function ($) {

  $(function(){


    // make code pretty
    window.prettyPrint && prettyPrint()


    // add tipsies to grid for scaffolding REMOVE
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

if ( $(window).width() > 960) {		
    // tooltip demo
    $('section').tooltip({
      selector: "a[rel=tooltip]"
    })
    $('table').tooltip({
			delay: { show: 500, hide: 10 },
      selector: "[rel=tooltip]"
    })
    $('.btn-group, .block').tooltip({
      selector: "a[rel=tooltip]",
			placement: "bottom"
    })
    $('.navbar').tooltip({
      selector: "a[rel=tooltip]",
			placement: "bottom"
    })
    $('.tooltip-test').tooltip()
    $('.popover-test').popover()
	} else {
		$('.cube').click(function () {
      $('html').find('body').toggleClass('onL');
    		return false;
		})
	}

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
    // toggle stars
    $('.icon-star-empty').on('click', function (e) {
			$(this).removeClass('icon-star-empty')
			$(this).addClass('icon-star')
    })
    $('.icon-star').on('click', function (e) {
			$(this).removeClass('icon-star')
			$(this).addClass('icon-star-empty')
    })

    // toggle all checkboxes
    $('.toggle-all').on('click', function (e) {
      inputsComponent.attr('checked', !inputsComponent.is(':checked'))
			$('.alert').show()
    })

    // request built javascript
    $('.download-btn').on('click', function () {

      var css = $("#components.download input:checked")
            .map(function () { return this.value })
            .toArray()
        , js = $("#plugins.download input:checked")
            .map(function () { return this.value })
            .toArray()
        , vars = {}
        , img = ['glyphicons-halflings.png', 'glyphicons-halflings-white.png']

    $("#variables.download input")
      .each(function () {
        $(this).val() && (vars[ $(this).prev().text() ] = $(this).val())
      })

      $.ajax({
        type: 'POST'
      , url: 'http://bootstrap.herokuapp.com'
      , dataType: 'jsonpi'
      , params: {
          js: js
        , css: css
        , vars: vars
        , img: img
      }
      })
    })

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
          .attr('type', 'hidden')
          .attr('name', k)
          .attr('value', typeof v == 'string' ? v : JSON.stringify(v))
          .appendTo(form)
      })

      form.appendTo('body').submit()
    }
  }
})

}(window.jQuery)