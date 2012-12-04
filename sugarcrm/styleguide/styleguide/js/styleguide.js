(function(){

  $(document).ready(function () {

    loadPartials(page.templates);

    if ( 'head' in ich.templates )
    {
      $('head').append(ich.head(page));
    }
    if ( 'masthead' in ich.templates )
    {
      $('.container').prepend(ich.masthead(page));
    }
    if ( 'navbar' in ich.templates )
    {
      $('body').prepend(ich.navbar());
    }
    if ( 'libraries' in ich.templates )
    {
      $('body').append(ich.libraries());
    }

    setActiveNav();

    // make code pretty (styleguide only)
    window.prettyPrint && prettyPrint();

    $('section [href^=#]')
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

    $('.subnav').scrollspy().find('li').removeClass('active');

    //fix sub nav on scroll
    var $win = $(window)
      , $bod = $('body')
      , $nav = $('.subnav')
      , bodPad = parseInt( $bod.css('padding-top'), 10 )
      , navTop = $nav.length && $nav.offset().top - bodPad - 81;

    processScroll();
    $win.on('scroll', processScroll);

    //
    function processScroll() {
      var fixSubNav = ( $win.scrollTop() >= navTop );
      $nav.toggleClass( 'subnav-fixed', fixSubNav );
      //$con.toggleClass('subnav-fixed',fixSubNav);
      $bod.css( 'padding-top', ( fixSubNav ? $nav.outerHeight()+bodPad : bodPad ) );
    }

    if ($(".chzn-select").length) {
      $(".chzn-select").chosen({ disable_search_threshold: 5 });
      $(".chzn-select-deselect").chosen({allow_single_deselect:true});
    }

  });

  function loadPartial(template){
    jQuery.ajax({
      url: 'partial/'+ template + '.html',
      dataType:"text",
      async: false,
      cache: 'false',
      success: function(data) {
          if(data !== undefined){
            ich.addTemplate(template,data);
          }
      }
    });
  }

  function loadPartials(templates) {
    templates = templates;
    jQuery.each(templates.split(','),function(i,t){loadPartial(t);});
  }

  function setActiveNav() {
    var filename = window.location.href.split('/').pop();
    $('.navbar .nav li')
      .removeClass('active')
      .each(function(i,d){
        if ( $(this).find('a').attr('href') === filename ) {
          $(this).addClass('active');
        }
      });
  }
})();

function sectionLink(label) {
  var regx = /[\s\,]+/g;
  if (label !== undefined)
  {
    return '<li><a href="#' + label.replace(regx,'-').toLowerCase() + '">' + label + '</a></li>';
  }
  else if ( this['.'] !== undefined)
  {
    return sectionLink(this['.']);
  }
  else
  {
    var dropdown = '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">' + this.label + '<b class="caret"></b></a><ul class="dropdown-menu">';
    this.items.map(function(d){dropdown+=sectionLink(d);});
    dropdown += '</ul></li>';
    return dropdown;
  }
}
