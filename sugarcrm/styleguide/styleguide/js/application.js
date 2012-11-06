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
          $(".alert-top").remove();
        }
    });

    // add tipsies to grid for scaffolding (styleguide only)
    if ($('#grid-system').length) {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px'; }
      });
    }

    // toggle all stars
    $('.toggle-all-stars').on('click', function () {
      $(this).closest('table').toggleClass('active');
      return false;
    });

    // toggle all checkboxes
    $('.toggle-all').on('click', function () {
      $('table').find('tr.alert').remove();
      $('table').find(':checkbox').attr('checked', this.checked);
      $(this).parent().parent().parent().parent().parent().append('<tr class="alert alert-warning"><td colspan="7" style="text-align: center;">You have selected 10 records. Do you want select <a href="" class="triggermass">select all 300</a> records.</td></tr>');
    });


    // timeout the alerts
    setTimeout( function (){$('.timeten').fadeOut().remove();}, 9000);

    // toggle star
    $('.icon-star').on('click', function () {
      $(this).parent().toggleClass('active');
      return false;
    });

    // toggle favorites
    $('.icon-favorite').on('click', function () {
      $(this).toggleClass('active');
      return false;
    });

    // toggle more hide
    $('.more').toggle(
      function () {
        $(this).parent().parent().prev('.extend').removeClass('hide');
        $(this).html('Less &nbsp;<i class="icon-caret-up"></i>');
        return false;
      },
      function () {
        $(this).parent().parent().prev('.extend').addClass('hide');
        $(this).html('. . .');
        return false;
    });

    // toggle more hide
    $('.newfilter').toggle(
      function () {
        $(this).parent().parent().parent().parent().parent().parent().find('.filtered').removeClass('hide');
        $(this).dropdown('toggle');
      },
      function () {
        $(this).parent().parent().parent().parent().find('.filtered').addClass('hide');
    });

    // toggle more hide
    $('.edit').toggle(
      function () {
        $(this).addClass('active');
        $(this).parent().parent().parent().find('.extend').removeClass('hide');
        return false;
      },
      function () {
        $(this).removeClass('active');
        $(this).parent().parent().parent().find('.extend').addClass('hide');
        return false;
      }
    );

    $('.comment').toggle(
      function () {
        $(this).parent().parent().parent().find('.acomment').remove();
        $(this).parent().parent().find('ul').append('<li class="acomment"><div class="control-group form-horizontal"><input placeholder="Add your comment" class="reply span10"><input type="submit" class="btn btn-primary" value="Reply"></div></li>');
        $(this).addClass('active');
        return false;
      },
      function () {
        $(this).parent().parent().parent().find('.acomment').remove();
        $(this).removeClass('active');
        return false;
      }
    );

    // toggle more hide
    $('.commented .more').toggle(
      function () {
        $(this).parent().parent().parent().find('.comment').hide();
        $(this).parent().prev('.extend').removeClass('hide');
        return false;
      },
      function () {
        $(this).parent().parent().parent().find('.comment').show();
        $(this).parent().prev('.extend').addClass('hide');
        $(this).html('2 more comments...');
        return false;
      }
    );

    // toggle drawer hide
    $('.drawer').toggle(
      function () {
        $(this).next('.extend').removeClass('hide');
        $(this).find('.toggle').html('<i class="icon-caret-up"></i>');
        return false;
      },
      function () {
        $(this).next('.extend').addClass('hide');
        $(this).find('.toggle').html('<i class="icon-caret-down"></i>');
        return false;
      }
    );

    // column collapse
    $('.drawerTrig').on('click', function () {
      $(this).find('i').toggleClass('icon-chevron-left').toggleClass('icon-chevron-right');
      $('.side-pane').toggleClass('hide');
      $('.main-pane').toggleClass("span8").toggleClass("span12");
      return false;
    });

    if($('.btngroup .btn').length>0){
      $('.btngroup .btn').button();
    }

    // editable example
    $('.dblclick').hover(
      function () {
        $(this).before('<span class="editble"><i class="icon-pencil icon-sm"></i></span>');
      },
      function () {
        $('span.editble').remove();
      }
    );


    $(".omnibar").toggle(
      function () {
        $(this).addClass('active');
        $(this).append('<div class="inputactions span10"><a href=""><i class="icon-tag"></i></a> <a href=""><i class="icon-paper-clip"></i></a> <input type="submit" class="pull-right btn btn-primary"><span class="pull-right"><a href="" class="btn btn-invisible btn-link">Send to Everyone</a> &nbsp;</div>');
        $('.sayit').html('');
        return false;
      },
      function () {
        $(this).removeClass('active');
        $('.inputactions').remove();
        return false;
      }
    );
    $('.tmu').on('click',
      function () {
        $('.mu').show();
        return false;
      }
    );
    $('.addme').on('click',
      function () {
        $(this).after('<a href="" class="removeme pull-right"><i class="btn btn-invisible icon-minus"></i></a>');
        $('.removeme').on('click',
          function () {
            $(this).parent('.filtered-body').remove();
            return false;
        });
        $(this).parent().after('<article class="filtered-body"><select class="chzn-select chzn-done" id="selNXK" style="display: none; "><option>matches</option></select><div id="selNXK_chzn" class="chzn-container chzn-container-single" style="width: 220px; "><a href="javascript:void(0)" class="chzn-single"><span>matches</span><div><b></b></div></a><div class="chzn-drop" style="left: -9000px; width: 218px; top: 0px; "><div class="chzn-search" style=""><input type="text" autocomplete="off" style="width: 183px; "></div><ul class="chzn-results"><li id="selNXK_chzn_o_0" class="active-result result-selected" style="">matches</li></ul></div></div><input placeholder="Select a name..."><a href="" class="btn btn-invisible pull-right addme"><i class="icon-plus"></i></a></article>');
        $('.addme').on('click',
          function () {
            $(this).after('<a href="" class="removeme pull-right"><i class="btn btn-invisible icon-minus"></i></a>');
            $('.removeme').on('click',
              function () {
                $(this).parent('.filtered-body').remove();
                return false;
            });
            $(this).parent().after('<article class="filtered-body"><select class="chzn-select chzn-done" id="selNXK" style="display: none; "><option>matches</option></select><div id="selNXK_chzn" class="chzn-container chzn-container-single" style="width: 220px; "><a href="javascript:void(0)" class="chzn-single"><span>matches</span><div><b></b></div></a><div class="chzn-drop" style="left: -9000px; width: 218px; top: 0px; "><div class="chzn-search" style=""><input type="text" autocomplete="off" style="width: 183px; "></div><ul class="chzn-results"><li id="selNXK_chzn_o_0" class="active-result result-selected" style="">matches</li></ul></div></div><input placeholder="Select a name..."><a href="" class="btn btn-invisible pull-right addme"><i class="icon-plus"></i></a></article>');
            return false;
        });
        return false;
    });

    $('.actions').find('a[data-toggle=tab]').on('click', function () {
      $('.nav-tabs').find('li').removeClass('on');
      $(this).parent().parent().addClass('on');
    });

    $('.actions').find('a.remove').on('click', function () {
      $('.tooltip').remove();
      $(this).parent().parent().remove();
      return false;
    });

    // remove a dashlet
    $('.thumbnail').find('.remove').on('click', function () {
      $(this).parent().parent().parent().parent().parent().remove();
    });

    // remove a close item
    $('.side').find('[data-toggle=tab]').on('click', function () {
      $('.nav-tabs').find('li').removeClass('on');
    });

    // toggle module search (needs tap logic for mobile)
    $('.addit').on('click', function () {
      $(this).toggleClass('active');
      $(this).parent().parent().parent().find('.form-addit').toggleClass('hide');
      return false;
    });

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
    });

    if($(".container.welcome").length===0) {
      $('table.datatable').dataTable({
        "bPaginate": false,
        "bFilter": true,
        "bInfo": false,
        "bAutoWidth": false
      });

      // Select widget
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({allow_single_deselect:true});
      $('#moduleActivity .form-search select').chosen();
      //$('#moduleActivity .form-search input').quicksearch('ul.results li');
    }
    
    //popover
    $("[rel=popover]").popover();
    $("[rel=popoverTop]").popover({placement: "top"});
    $("[rel=popoverBottom]").popover({placement: "bottom"});
  });




  var uobj = [],
    onUploadChange = function (e) {
      var status = $(this);
      if ( this.value ) {
        var this_container = $(this).parent('.file-upload').parent('.upload-field-custom'),
          value_explode = this.value.split('\\'),
          value = value_explode[value_explode.length-1];

        if(this_container.next('.file-upload-status').length > 0){
          this_container.next('.file-upload-status').remove();
        }
        //this_container.append('<span class="file-upload-status">'+value+'</span>');
        $('<span class="file-upload-status">'+value+'</span>').insertAfter(this_container);
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
    console.log(lbl_width);
    $(this)
      .parent().find('span').css('width',lbl_width)
      .closest('.upload-field-custom').css('width',lbl_width);

    // Set current state 
    onUploadChange.call(this);      

    // Minimizes the text input part in IE
    $(this).css('width','0');
  });




}(window.jQuery);

