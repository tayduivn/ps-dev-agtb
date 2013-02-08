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

});

var cachePartials = (window.location.hostname.indexOf('nomad')!==-1)?true:false;

function loadPartial(template) {
  if (!ich[template]) {
    jQuery.ajax({
      url: 'partial/'+ template + (template.indexOf('.html')===-1?'.html':''),
      dataType: 'html',
      async: false,
      cache: cachePartials,
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
  $('#section-content').html(partial(page));
  $(window).scrollTop( 170 );
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

  if ( mode != undefined ) {
    if ( mode !== 'edit' && mode !== 'preview' ) {
      $('#edit .record').empty();
    }
    if ( mode !== 'preview' ) {
      $('body').removeClass('edit view list preview').addClass(mode);//toggleClass( mode, source !== '#' );
      if (mode === 'edit') {
        drawer(mode === 'edit');
      }
    } else {
      $('body').toggleClass('preview',(source !== '#'));
    }
    page.mode = mode;
  } else {
    page.mode = 'list';
  }

  //if ( source.indexOf('partial') === 0 ) {
    page.target_id = 'id="'+ source.replace(/\//g,'_').replace('.html','') + '"';
    page.sourceid = source.replace(/\//g,'_').replace('.html','')  + '-' + Math.floor((Math.random()*100)+1);

    loadPartial(source);
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
  //}
  if ( $(target).find('prettyprint').length) {
    // make code pretty (styleguide only)
    window.prettyPrint && prettyPrint();
  }

}
