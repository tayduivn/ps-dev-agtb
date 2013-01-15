
(function(){

  $(document).ready(function () {
    // define snippets
    //ich.addTemplate('drawerTrig', '<a href="#overview" title="Collapse sidebar" class="btn btn-invisible drawerTrig"><i class="icon-double-angle-right"></i></a>');

    // load prototype framework
    if ( page )
    {
      page.sectionLink = sectionLink;
      page.templates = [
          {"file":"head", "target":"head", "method":"append"}
        , {"file":"masthead", "target":"#page-content", "method":"prepend"}
        , {"file":"libraries", "target":"body", "method":"append"}
      ];

      if ( typeof page.subnav === 'undefined' )
      {
        page.subnav = true;
      }

      if ( typeof page.search === 'undefined' )
      {
        page.search = true;
      }

      loadPage(page);
    }

    // capture content load requests via url
    if ( window.location.hash !== '#' && window.location.hash !== '' )
    {
      loadPageContent(window.location.pathname + window.location.hash);
      setActiveNav();
    }
    else if ( page.sections )
    {
      var source = getFileFromPath(window.location.pathname)
        , m = '<section id="section-menu">';

      $.each(page.sections, function (i,d) {
        m+= ( (i%4===0) ? '<div class="row">' : '' ) +'<div class="span3"><h3 id="modals">'+
            ( (!d.items) ? ('<a class="section-link" href="'+ source + getHashTag(d.label) +'">') : '' ) +
            d.label + ( (!d.items) ? '</a>' : '' ) +
            '</h3><p>'+ d.description;
        if (d.items)
        {
          l = d.items.length-1;
          $.each(d.items, function (j,d2) {
            m+= ' <a class="section-link" href="'+ source + getHashTag(d2) +'">'+ d2 +'</a>'+ (j===l?'.':', ');
          });
        }
        m+= '</p></div>'+ ( (i%4===3) ? '</div>' : '' );
      });
      m+= '</section>';

      $('#section-content').html(m);
    }
    else
    {
      var i = 0
        , source = ''
        , m = '<section id="section-menu">';

      $.each(pages, function (k,v) {
        source = ( (v.url) ? v.url : getFileFromLabel(k) );
        m+= ( (i%3===0) ? '<div class="row">' : '' ) +
            '<div class="span4"><h3 id="modals">'+
            '<a class="section-link" href="'+ source +'">'+
            v.title +'</a></h3><p>'+ v.description +'</p><ul>';
        if (v.sections)
        {
          $.each(v.sections, function (j,d) {
            m+= '<li ><a class="section-link" href="'+
                ( (d.url) ? d.url : source + getHashTag(d.label) ) +'">'+
                d.label +'</a></li>';
          });
        }
        m+= '</ul></div>' + ( (i%3===2) ? '</div>' : '' );
        i += 1;
      });
      m+= '</section>';

      $('#section-content').html(m);
    }

    // search for patterns
    if ($("#find_patterns").length)
    {
      var $find = $('#find_patterns')
        , $optgroup;

      $.each(pages, function (k,v) {
        if ( v.sections )
        {
          $optgroup = $('<optgroup>').appendTo($find).attr('label',v.title);
          $.each(v.sections, function (i,d) {
            if ( d.items )
            {
              $.each(d.items, function (j,d2) {
                renderSearchOption(k, d2, $optgroup);
              });
            }
            else
            {
              renderSearchOption(k, d.label, $optgroup);
            }
          });
        }
      });

      $find.on('change', function (e) {
        if ( getFileFromPath(window.location.href) === getFileFromPath( $(this).val() ) )
        {
          loadPageContent( $(this).val() );
        }
        window.location.href = $(this).val();
        setActiveNav();
      });

      $find.chosen();
      $find.trigger("liszt:updated");
    }

    $('.section-link').live('click', function (e) {
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
    if ( $('#grid-system').length )
    {
      $('#grid-system').tooltip({
          selector: '.show-grid > div'
        , title: function () { return $(this).width() + 'px' }
      });
    }

    if ( $(".chzn-select").length )
    {
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({ allow_single_deselect:true });
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

  });

  function renderSearchOption(key, label, optgroup) {
    $('<option>')
      .appendTo(optgroup)
      //.addClass('section-link')
      .attr('value', key +'.html'+ getHashTag(label))
      .text(label);
  }

  function setActiveNav() {
    var filename = window.location.href.split('/').pop();
    $('.subnav .nav li')
      .removeClass('active')
      .each( function(i,d) {
        if ( $(this).find('a').attr('href') === filename )
        {
          $(this).addClass('active');
        }
      });
  }

})();

function getHashTag(label) {
  return '#'+ label.replace(/[\s\,]+/g,'-').toLowerCase();
}
function getFileFromPath(path) {
  return path.split('/').pop().split('#').shift();
}
function getFileFromLabel(label) {
  return label.replace(/[\s\,]+/g,'-').toLowerCase() +'.html';
}

function sectionLink(label) {
  if ( label !== undefined )
  {
    return '<li><a class="section-link" href="'+
      getFileFromPath(window.location.pathname) + getHashTag(label) +'">'+
      label +'</a></li>';
  }
  else if ( this['.'] !== undefined )
  {
    return sectionLink(this['.']);
  }
  else if ( this.items )
  {
    var dd = '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">'+
            this.label +'<b class="caret"></b></a><ul class="dropdown-menu">';
    this.items.map( function(d) {
      dd+= sectionLink(d);
    });
    dd+= '</ul></li>';
    return dd;
  }
  else
  {
    return sectionLink(this.label);
  }
}
