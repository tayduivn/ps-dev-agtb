(function(){

  $(document).ready(function () {
    loadPartials(page.templates);
  });

  function loadPartial(template){
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

  function loadPartials(templates) {
    jQuery.each(
      templates,
      function(i,t){
        loadPartial(t.file);
        var partial = ich[t.file];
        if (t.method==='prepend') {
          $(t.target).prepend(partial(page));
        }
        else {
          $(t.target).append(partial(page));
        }
      }
    );
  }

})();
