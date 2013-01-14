
(function(){

  $(document).ready(function () {
    // load snippets
    //ich.addTemplate('drawerTrig', '<a href="#overview" title="Collapse sidebar" class="btn btn-invisible drawerTrig"><i class="icon-double-angle-right"></i></a>');

    // load prototype framework
    if (page) {
      page.sectionLink = sectionLink;
      page.templates = [
          {"file":"head","target":"head","method":"append"}
        , {"file":"masthead","target":"#page-content","method":"prepend"}
        , {"file":"libraries","target":"body","method":"append"}
      ]
      if (typeof page.subnav === 'undefined') {
        page.subnav = true;
      }
      loadPage(page);
    }

    $('.section-link').live('click', function (e){
      loadPageContent($(this).attr('href'));//widgets.html#modals
      window.location.href = $(this).attr('href');
      setActiveNav();
    });

    // make code pretty (styleguide only)
    window.prettyPrint && prettyPrint();

    $('section a[href^=#]')
      .click(function(e) {
        e.preventDefault();
      });

    // add tipsies to grid for scaffolding (styleguide only)
    if ($('#grid-system').length) {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px' }
      });
    }

    //$('.subnav').scrollspy().find('li').removeClass('active');

    //fix sub nav on scroll
    var $win = $(window)
      , $bod = $('body')
      , $nav = $('.subnav')
      , bodPad = parseInt( $bod.css('padding-top'), 10 )
      , navTop = $nav.length && $nav.offset().top - bodPad - 108;

    processScroll();
    $win.on('scroll', processScroll);

    function processScroll() {
      var fixSubNav = ( $win.scrollTop() >= navTop );
      $nav.toggleClass( 'subnav-fixed', fixSubNav );
      //$bod.css( 'padding-top', ( fixSubNav ? $nav.outerHeight()+bodPad : bodPad ) );
    }

    if ($(".chzn-select").length) {
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({ allow_single_deselect:true });
    }

    // capture content load requests via url
    if (window.location.hash !== '#' && window.location.hash !== '') {
      loadPageContent(window.location.pathname + window.location.hash);
      setActiveNav();
    } else if (page.sections) {
      var regx = /[\s\,]+/g
        , source = window.location.pathname.split('/').pop() +'#'
        , menu = '<section id="section-menu">';

      $.each(page.sections, function(i,d) {
        menu += ((i%4===0)?'<div class="row">':'') +
          '<div class="span3"><h3 id="modals">';
        if (!d.items) {
          menu += '<a class="section-link" href="'+ source + d.label.replace(regx,'-').toLowerCase() +'">';
        }
        menu += d.label;
        if (!d.items) {
          menu += '</a>';
        }
        menu += '</h3><p>'+ d.description;
        if (d.items) {
          l = d.items.length-1;
          $.each(d.items, function (j,d2){
            menu += ' <a class="section-link" href="'+ source + d2.replace(regx,'-').toLowerCase() +'">'+ d2 +'</a>'+ (j===l?'.':', ');
          });
        }
        menu += '</p></div>' + ((i%4===3)?'</div>':'');
      });
      menu += '</section>';

      $('#section-content').html(menu);
    }

    if ($("#find_patterns").length) {
      var find = $('#find_patterns'),
        optgroup;
      $.each(pages, function(k,v){
        optgroup = $('<optgroup>').appendTo(find).attr('label',v.title);
        $.each(v.sections, function(i,d){
          if (d.items) {
            $.each(d.items, function(j,d2) {
              renderSearchOption(k,d2,optgroup);
            });
          } else {
            renderSearchOption(k,d.label,optgroup);
          }
        });
      });
      find.on('change', function(e){
        var curFilename = window.location.href.split('/').pop().split('#').shift(),
            srcFilename = $(this).val().split('/').pop().split('#').shift();
        if (curFilename === srcFilename) {
          loadPageContent($(this).val());
        }
        window.location.href = $(this).val();
        setActiveNav();
      });
      find.chosen();
      find.trigger("liszt:updated");
    }
  });

  function renderSearchOption(key,label,optgroup) {
    $('<option>')
      .appendTo(optgroup)
      //.addClass('section-link')
      .attr('value', key +'.html#'+ label.replace(/[\s\,]+/g,'-').toLowerCase())
      .text(label);
  }

  function setActiveNav() {
    var filename = window.location.href.split('/').pop();
    $('.subnav .nav li')
      .removeClass('active')
      .each(function(i,d){
        if ( $(this).find('a').attr('href') === filename ) {
          $(this).addClass('active');
        }
      });
  }

})();

function sectionLink(label) {
  if (label !== undefined)
  {
    var regx = /[\s\,]+/g
      , source = window.location.pathname.split('/').pop() +
                 '#' + label.replace(regx,'-').toLowerCase();
    return '<li><a class="section-link" href="'+ source + '">' + label + '</a></li>';
  }
  else if ( this['.'] !== undefined)
  {
    return sectionLink(this['.']);
  }
  else
  {
    if (this.items) {
      var dropdown = '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">' +
                      this.label + '<b class="caret"></b></a><ul class="dropdown-menu">';
      this.items.map(function(d){
          dropdown += sectionLink(d);
      });
          dropdown += '</ul></li>';
      return dropdown;
    } else {
      return sectionLink(this.label);
    }
  }
}
