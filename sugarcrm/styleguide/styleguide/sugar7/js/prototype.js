$(document).ready(function () {

  // load tab content into DOM and show proto
  $('body').on( 'click', 'a[data-toggle="proto"]', function (e){
    //e.relatedTarget // previous tab
    e.preventDefault();
    //e.stopPropagation();
    var link = $(this)
      , source = link.attr('href')
      , target = link.data('target')
      , mode = link.data('mode')
      , method = link.data('method');
    // console.log(source);
    // console.log(target);
    // console.log(mode);
    // console.log(mode);
    // console.log('==============');
    loadContent(source,target,mode,method);
  });

  // load snippets
  ich.addTemplate('drawerTrig', '<a href="#overview" title="Collapse sidebar" class="btn btn-invisible drawerTrig"><i class="icon-double-angle-right"></i></a>');

  // load prototype framework
  if (page) {
    page.rand = function(){return Math.floor((Math.random()*100)+1);};
    loadPage(page);
  }

});


function loadPartial(template) {
  if (!ich[template]) {
    jQuery.ajax({
      url: 'partial/'+ template + '.html',
      dataType: 'html',
      async: false,
      cache: false,
      success: function(data) {
        if(data !== undefined){
          ich.addTemplate(template,data);
        }
      }
    });
  }
}

function loadPartials(templates) {
  jQuery.each(
    templates,
    function(i,t){

      var source = t.file;
      page.target_id = 'id="'+ source.replace(/\//g,'_').replace('.html','') + '"';
      page.sourceid = source.split('/').pop() + '-' + Math.floor((Math.random()*100)+1);

      loadPartial(source);
      var partial = ich[source];


      if (t.method==='prepend') {
        $(t.target).prepend(partial(page));
      } else if (t.method==='append') {
        $(t.target).append(partial(page));
      } else if (t.method==='toggle') {
        $(t.target).append(partial(page));
      } else {
        $(t.target).html(partial(page));
      }
    }
  );
}

function loadPageContent(href) {
  var source = href.split('/').pop().split('.').shift() +'/'+ href.split('#').pop(); // widgets/modals
  $('body').scrollTop(0);
  loadPartial(source);
  var partial = ich[source];
  $('#section-content').html(partial);
  $(window).scrollTop( 170 );
}

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
    var dropdown = '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">' +
                    this.label + '<b class="caret"></b></a><ul class="dropdown-menu">';
    this.items.map(function(d){
        dropdown += sectionLink(d);
    });
        dropdown += '</ul></li>';
    return dropdown;
  }
}

function loadPage(page) {
  page.module = page.title.replace(/ies$/,'y').replace(/s$/,'').toLowerCase();
  if (page.templates) {
    loadPartials(page.templates)
  }
}

function loadContent(source,target,mode,method) {
  //console.log('loadContent')
  if ( typeof method === 'undefined' ) {
    method = 'replace';
  }
  if ( mode != undefined )
  {
    if ( mode !== 'edit' && mode !== 'preview' ) {
      $('#edit .record').empty();
    }
    if ( mode !== 'preview' ) {
      $('body').removeClass('edit view list preview').addClass(mode);//toggleClass( mode, source !== '#' );
      drawer(mode === 'edit');
    } else {
      $('body').toggleClass('preview',(source !== '#'));
    }
  }

  if ( source.indexOf('partial') === 0 )
  {
    loadPartial(source);

    page.target_id = 'id="'+ source.replace(/\//g,'_').replace('.html','') + '"';
    page.sourceid = source.split('/').pop() + '-' + Math.floor((Math.random()*100)+1);

    var partial = ich[source];

    if ( method==='prepend' ) {
      $(target).prepend(partial(page));
    } else if ( method==='append' ) {
      $(target).append(partial(page));
    } else if ( method==='toggle' ) {
      $(target).append(partial(page));
    } else {
      $(target).html(partial(page));
    }
  }
}
