$(function(){
      // make code pretty (styleguide only)
      window.prettyPrint && prettyPrint()

      // add tipsies to grid for scaffolding (styleguide only)
      if ($('#grid-system').length) {
        $('#grid-system').tooltip({
            selector: '.show-grid > div'
          , title: function () { return $(this).width() + 'px' }
        })
      }
})