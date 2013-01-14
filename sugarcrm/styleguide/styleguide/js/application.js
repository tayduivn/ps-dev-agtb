/*jshint forin:true, noarg:true, noempty:true, eqeqeq:true, bitwise:true, strict:true, undef:true, unused:true, curly:true, browser:true, laxcomma:true */

!function ($) {
  'use strict';
  $(function (){

    // load snippets
    ich.addTemplate('drawerTrig', '<a href="#overview" title="Collapse sidebar" class="btn btn-invisible drawerTrig"><i class="icon-double-angle-right"></i></a>');

    // load prototype framework
    if (page) {
      page.rand = function(){return Math.floor((Math.random()*100)+1);};
      loadPage(page);
    }

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

    // toggle star button
    $('body').on('click', '.fav-star', function (e) {
      e.preventDefault();
      $(this).find('.icon-favorite').toggleClass('active');
    });

    //toggle favorites in list
    $('body').on('click', '.icon-favorite', function (e) {
      e.preventDefault();
      e.stopPropagation();
      $(this).toggleClass('active');
    });

    // toggle more...less blocks
    $('body')
      .on('mouseover', '.more', function(e) {
        e.preventDefault();
        $(this).css('text-decoration', 'underline');
      })
      .on('mouseout', '.more', function(e) {
        e.preventDefault();
        $(this).css('text-decoration', 'none');
      })
      .on( 'mousedown', '.more', function(e) {
        e.preventDefault();
        var link = $(this);
        link.css('text-decoration', 'none');
        if ( link.text().indexOf('More') !== -1 ) {
          link.html(link.text().replace('More','Less')+'<i class="icon-caret-up"></i>');
          link.parent().prev('.extend').removeClass('hide');
        } else {
          link.html(link.text().replace('Less','More')+'<i class="icon-caret-down"></i>');
          link.parent().prev('.extend').addClass('hide');
        }
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

    //popover
    $("[rel=popover]").popover();
    $("[rel=popoverTop]").popover({placement: "top"});
    $("[rel=popoverBottom]").popover({placement: "bottom"});
    $("[rel=popoverHover]").popover({trigger: "hover"});

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

    // if (window.location.hash === '#view' || window.location.hash === '#edit') {
    //   var module = window.location.pathname.split('/').pop().split('.')[0].replace(/ies$/,'y').replace(/s$/,'')
    //     , mode = window.location.hash.substring(1)
    //     , source = 'partial/' + module + '/' + module +'-' + mode + '.html'
    //     , target = window.location.hash + ' .record';

    //   loadContent(source,target,mode,method);
    // }

    // timeout the alerts
    setTimeout( function (){$('.timeten').fadeOut().remove();}, 3000);


    /* Custom file upload overrides and avatar widget */
    var uobj = [],
      onUploadChange = function (e) {
        var status = $(this);
        if ( this.value ) {
          var this_container = $(this).parent('.file-upload').parent('.upload-field-custom'),
            value_explode = this.value.split('\\'),
            value = value_explode[value_explode.length-1];

          if($(this).closest('.upload-field-custom').hasClass('avatar')===true) { /* hide status for avatars */
            var opts = "hide";
          }

          if(this_container.next('.file-upload-status').length > 0){
            this_container.next('.file-upload-status').remove();
          }
          //this_container.append('<span class="file-upload-status">'+value+'</span>');
          $('<span class="file-upload-status '+opts+' ">'+value+'</span>').insertAfter(this_container);
        }
      },
      onUploadFocus = function () {
        $(this).parent().addClass('focus');
      },
      onUploadBlur = function () {
        $(this).parent().addClass('focus');
      };

    $('.upload-field-custom input[type=file]').each(function() {
      // Bind events
      $(this)
        .bind('focus',onUploadFocus)
        .bind('blur',onUploadBlur)
        .bind('change',onUploadChange);

      // Get label width so we can make button fluid, 12px default left/right padding
      var lbl_width = $(this).parent().find('span strong').width() + 24;
      //console.log(lbl_width);
      $(this)
        .parent().find('span').css('width',lbl_width)
        .closest('.upload-field-custom').css('width',lbl_width);

      // Set current state
      onUploadChange.call(this);

      // Minimizes the text input part in IE
      $(this).css('width','0');
    });

    $('#photoimg').live('change', function() {
      $("#preview1").html('');
      $("#preview1").html('<span class="loading">Loading...</span>');
      $("#imageform").ajaxForm({
        target: '#preview1'
      }).submit();
    });

    $('.preview.avatar').click(function(e){
        $(this).closest('.span10').find('label.file-upload span strong').trigger('click');
    });

    if ($('table.datatable').length !== 0) {
      $('table.datatable').dataTable({
        "bPaginate": false,
        "bFilter": true,
        "bInfo": false,
        "bAutoWidth": false
      });
    }

    // Select widget
    if ($('.chzn-select').length !== 0) {
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({allow_single_deselect:true});
    }

    if ($('#moduleActivity .form-search select').length !== 0) {
      $('#moduleActivity .form-search select').chosen();
      //$('#moduleActivity .form-search input').quicksearch('ul.results li');
    }

    //j Toggle display of side pane
    $('body')
      .off('click', '.drawerTrig')
      .on('click', '.drawerTrig', function (e) {
        $(this).find('i').toggleClass('icon-double-angle-left').toggleClass('icon-double-angle-right');
        $('.side').toggleClass('hide');
        $('.main-pane').toggleClass('span8').toggleClass('span12');
        e.preventDefault();
      });
  });
}(window.jQuery);

function throwMessage(data,status,temp) {
  var msg = '<div class="alert alert-'+status+' alert-block'+(temp?' timeten':'')+'">' +
    data +
    '<a class="close" data-dismiss="alert">×</a>' +
    '</div>';
  $('#alerts').append(msg);
  setTimeout( function (){$('.timeten').fadeOut().remove();}, 3000);
}
