({
  className: 'container-fluid',

  // forms editable
  _renderHtml: function () {
      this._super('_renderHtml');

      this.$('.url-editable-trigger').on('click.styleguide',function(){
        var uefield = $(this).next();
        uefield
          .html(uefield.text())
          .editable(
            function(value, settings) {
                var nvprep = '<a href="'+value+'">',
                    nvapp = '</a>',
                    value = nvprep.concat(value);
               return(value);
            },
            {onblur:'submit'}
          )
          .trigger('click.styleguide');
      });

      this.$('.text-editable-trigger').on('click.styleguide',function(){
        var uefield = $(this).next();
        uefield
          .html(uefield.text())
          .editable()
          .trigger('click.styleguide');
      });

      this.$('.urleditable-field > a').each(function(){
        if(isEllipsis($(this))===true) {
          $(this).attr({'data-original-title':$(this).text(),'rel':'tooltip','class':'longUrl'});
        }
      });

      function isEllipsis(e) { // check if ellipsis is present on el, add tooltip if so
        return (e[0].offsetWidth < e[0].scrollWidth);
      }

      this.$('.longUrl[rel=tooltip]').tooltip({placement:'top'});
  }
})
