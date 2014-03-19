/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
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
