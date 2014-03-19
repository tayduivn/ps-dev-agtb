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

  // components dropdowns
  _renderHtml: function () {
    this._super('_renderHtml');

    /* Custom file upload overrides and avatar widget */
    var uobj = [],
        onUploadChange = function (e) {
          var status = $(this),
              opts = 'show';
          if (this.value) {
            var this_container = $(this).parent('.file-upload').parent('.upload-field-custom'),
              value_explode = this.value.split('\\'),
              value = value_explode[value_explode.length-1];

            if ($(this).closest('.upload-field-custom').hasClass('avatar')===true) { /* hide status for avatars */
              opts = "hide";
            }

            if (this_container.next('.file-upload-status').length > 0) {
              this_container.next('.file-upload-status').remove();
            }
            //this_container.append('<span class="file-upload-status">'+value+'</span>');
            this.$('<span class="file-upload-status ' + opts + ' ">' + value + '</span>').insertAfter(this_container);
          }
        },
        onUploadFocus = function () {
          $(this).parent().addClass('focus');
        },
        onUploadBlur = function () {
          $(this).parent().addClass('focus');
        };

    this.$('.upload-field-custom input[type=file]').each(function() {
      // Bind events
      $(this)
        .bind('focus', onUploadFocus)
        .bind('blur', onUploadBlur)
        .bind('change', onUploadChange);

      // Get label width so we can make button fluid, 12px default left/right padding
      var lbl_width = $(this).parent().find('span strong').width() + 24;
      $(this)
        .parent().find('span').css('width',lbl_width)
        .closest('.upload-field-custom').css('width',lbl_width);

      // Set current state
      onUploadChange.call(this);

      // Minimizes the text input part in IE
      $(this).css('width', '0');
    });

    this.$('#photoimg').on('change', function() {
      $("#preview1").html('');
      $("#preview1").html('<span class="loading">Loading...</span>');
      $("#imageform").ajaxForm({
        target: '#preview1'
      }).submit();
    });

    this.$('.preview.avatar').on('click.styleguide', function(e){
        $(this).closest('.span10').find('label.file-upload span strong').trigger('click');
    });
  },

  _dispose: function(view) {
      this.$('#photoimg').off('change');
      this.$('.preview.avatar').off('click.styleguide');
  }
})
