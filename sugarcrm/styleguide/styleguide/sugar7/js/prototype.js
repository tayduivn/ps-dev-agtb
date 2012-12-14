  page.rand = function(){return Math.floor((Math.random()*100)+1);};

  function loadPartial(template) {
    //console.log('loadPartial')
    if (!ich[template]) {
      jQuery.ajax({
        url: template,
        dataType: 'html',
        cache: 'false',
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

  function loadPage(page) {
    page.module = page.title.replace(/ies$/,'y').replace(/s$/,'').toLowerCase();
    if (page.templates) {
      loadPartials(page.templates)
    }
  }

  function loadPartials(templates) {
    //console.log('loadPartials')
    jQuery.each(
      templates,
      function(i,t){

        var source = 'partial/'+ t.file + '.html';

        loadPartial(source);

        page.target_id = 'id="'+ source.replace(/\//g,'_').replace('.html','') + '"';

        var partial = ich[source];

        page.sourceid = t.file.split('/').pop() + '-' + Math.floor((Math.random()*100)+1);

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
