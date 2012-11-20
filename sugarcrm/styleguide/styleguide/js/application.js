/*jshint forin:true, noarg:true, noempty:true, eqeqeq:true, bitwise:true, strict:true, undef:true, unused:true, curly:true, browser:true, laxcomma:true */

!function ($) {
  'use strict';
  $(function (){

    // fix sub nav on scroll
    var $win = $(window)
      , $nav = $('.subnav')
      , navTop = $('.subnav').length && $('.subnav').offset().top - 40
      , isFixed = 0;

    processScroll();
    $win.on('scroll', processScroll);

    function processScroll() {
      var scrollTop = $win.scrollTop();
      if (scrollTop >= navTop && !isFixed) {
        isFixed = 1;
        $nav.addClass('subnav-fixed');
      } else if (scrollTop <= navTop && isFixed) {
        isFixed = 0;
        $nav.removeClass('subnav-fixed');
      }
    }

    // do this if greater than 768px page width
    if ( $(window).width() > 768) {
      // tooltip demo
      $('body').tooltip({
        selector: "[rel=tooltip]"
      });
      $('table').tooltip({
        selector: "[rel=tooltip]"
      });
      $('.thumbnail').tooltip({
        selector: "[rel=tooltip]",
        placement: "bottom"
      });
      $('.navbar, .subnav').tooltip({
        selector: "[rel=tooltip]",
        placement: "bottom"
      });
    }

    $("th:contains('Subject')").css("width","50%");
    $("th:contains('Modified'),th:contains('Created'),th:contains('Number'),th:contains('ID'),th:contains('input'),th:contains('cog')").css("width","1%");
    $("th:contains('Opportunity'),th:contains('Name')").css("width","30%");
    $(".side th:contains('Opportunity'),th:contains('Name')").css("width","70%");

    // keybinding
    $(document).keyup( function (e){
        if(e.keyCode === 27) {
          $(".alert-top .timeten").remove();
        }
    });

    // toggle all stars
    $('body').on('click', '.toggle-all-stars', function () {
      $(this).closest('table').toggleClass('active');
      return false;
    });

    // toggle all checkboxes
    $('body').on('click', '.toggle-all', function () {
      $('table').find('tr.alert').remove();
      $('table').find(':checkbox').attr('checked', this.checked);
      $(this).parent().parent().parent().parent().parent().append('<tr class="alert alert-warning"><td colspan="7" style="text-align: center;">You have selected 10 records. Do you want select <a href="" class="triggermass">select all 300</a> records.</td></tr>');
    });

    // toggle star
    $('body').on('click', '.icon-star', function () {
      $(this).parent().toggleClass('active');
      return false;
    });

    // toggle favorites
    $('body').on('click', '.icon-favorite', function () {
      $(this).toggleClass('active');
      return false;
    });

    // toggle more...less blocks
    $('body').on( 'click', '.more', function(e) {
      var link = $(this);
      //$(this).parent().prev('.extend').slideToggle('slow');
      if ( link.text().indexOf('More') !== -1 ) {
        link.html('Less &nbsp;<i class="icon-caret-up"></i>');
        link.parent().prev('.extend').removeClass('hide');
        link.mouseleave();
      } else {
        link.html('More &nbsp;<i class="icon-caret-down"></i>');
        link.parent().prev('.extend').addClass('hide');
      }
      e.preventDefault();
    });

    // editable example
    $('.dblclick').hover(
      function () {
       $(this).before('<span class="inlined"><i class="icon-pencil"></i></span>');
      },
      function () {
        $('.inlined').remove();
      }
    );

    // remove a dashlet
    $('.thumbnail').find('.remove').on('click', function () {
      $(this).parent().parent().parent().parent().parent().remove();
    });

    // remove a closed item
    $('.side').find('[data-toggle=tab]').on('click', function () {
      $('.nav-tabs').find('li').removeClass('on');
    });


    if ($('.btngroup .btn').length !== 0) {
      $('.btngroup .btn').button();
    }

    if ($('table.datatable').length !== 0) {
      $('table.datatable').dataTable({
        "bPaginate": false,
        "bFilter": true,
        "bInfo": false,
        "bAutoWidth": false
      });
    }

    if ($('.chzn-select').length !== 0) {
      // Select widget
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({allow_single_deselect:true});
    }

    if ($('#moduleActivity .form-search select').length !== 0) {
      $('#moduleActivity .form-search select').chosen();
      //$('#moduleActivity .form-search input').quicksearch('ul.results li');
    }

    //popover
    $("[rel=popover]").popover();
    $("[rel=popoverTop]").popover({placement: "top"});
    $("[rel=popoverBottom]").popover({placement: "bottom"});

    // add modal content into DOM and show modal
    $('body').on('click', '.modal-link', function(e){
        jQuery.ajax({
            url: $(this).attr('href'), // + "?r=" + new Date().getTime(),
            dataType:"text",
            async: false,
            success: function(data) {
              if(data !== undefined){
                $('#modal').replaceWith(data);
                $('#modal').modal({
                  keyboard: true,
                  backdrop: 'static',
                  show: true
                });
              }
            }
        });
      //$('#'+target).modal('show');
    });

    // load tab content into DOM and show tab
    $('body').on( 'shown', 'a[data-toggle="tab"]', function (e){
      //e.relatedTarget // previous tab
      var link = $(e.target)
        , source = link.attr('href')
        , target = link.data('target')
        , mode = link.data('mode')
        , method = link.data('method');

      loadContent(source,target,mode,method);
    });

    // load prototype framework
    if (page) {
      loadPage(page);
    }

    if (window.location.hash === '#view' || window.location.hash === '#edit') {
      var module = window.location.pathname.split('/').pop().split('.')[0].replace(/ies$/,'y').replace(/s$/,'')
        , mode = window.location.hash.substring(1)
        , source = 'partial/' + module + '/' + module +'-' + mode + '.html'
        , target = window.location.hash + ' .record';

      loadContent(source,target,mode);
    }

    // timeout the alerts
    setTimeout( function (){$('.timeten').fadeOut().remove();}, 3000);

  });

}(window.jQuery);

function throwMessage(data,status,temp) {
  var msg = '<div class="alert alert-'+status+' alert-block'+(temp?' timeten':'')+'">' +
    data +
    '<a class="close" data-dismiss="alert">×</a>' +
    '</div>';
  $('#alert').append(msg);
  setTimeout( function (){$('.timeten').fadeOut().remove();}, 3000);
}
