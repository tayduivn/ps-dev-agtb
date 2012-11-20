
  function loadPartial(template) {
    if (!ich[template]) {
      jQuery.ajax({
        url: 'partial/'+ template + '.html',
        dataType:"text",
        async: false,
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
    jQuery.each(
      templates,
      function(i,t){
        loadPartial(t.file);
        var partial = ich[t.file];
        if (t.method==='prepend') {
          $(t.target).prepend(partial(page));
        } else if (t.method==='append') {
          $(t.target).append(partial(page));
        } else {
          $(t.target).html(partial(page));
        }
      }
    );
  }

  function loadContent(source,target,mode,method) {
    // console.log(source);
    // console.log(target);
    // console.log(mode);

    if ( typeof method === 'undefined' ) {
      method = 'replace';
    }

    if ( mode != undefined )
    {
      if ( mode !== 'edit' ) {
        $('#edit .record').empty();
      }
      if ( mode !== 'preview' ) {
        $('body').removeClass('edit view list preview').addClass(mode);//toggleClass( mode, source !== '#' );
      } else {
        $('body').toggleClass('preview',(source !== '#'));
      }
    }

    if ( source.indexOf('partial') === 0 )
    {
      source = source.replace('partial/','').replace('.html','');

      loadPartial(source);

      var partial = ich[source];

      if ( method==='prepend' ) {
        $(target).prepend(partial(page));
      } else if ( method==='append' ) {
        $(target).append(partial(page));
      } else {
        $(target).html(partial(page));
      }
    }
  }
