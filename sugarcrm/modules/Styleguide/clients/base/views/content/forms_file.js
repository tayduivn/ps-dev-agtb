// components dropdowns
function _render_content(view, app) {
 /* Custom file upload overrides and avatar widget */
  var uobj = [],
      onUploadChange = function (e) {
        var status = $(this);
        if (this.value) {
          var this_container = $(this).parent('.file-upload').parent('.upload-field-custom'),
            value_explode = this.value.split('\\'),
            value = value_explode[value_explode.length-1];

          if ($(this).closest('.upload-field-custom').hasClass('avatar')===true) { /* hide status for avatars */
            var opts = "hide";
          }

          if (this_container.next('.file-upload-status').length > 0) {
            this_container.next('.file-upload-status').remove();
          }
          //this_container.append('<span class="file-upload-status">'+value+'</span>');
          view.$('<span class="file-upload-status ' + opts + ' ">' + value + '</span>').insertAfter(this_container);
        }
      },
      onUploadFocus = function () {
        $(this).parent().addClass('focus');
      },
      onUploadBlur = function () {
        $(this).parent().addClass('focus');
      };

  view.$('.upload-field-custom input[type=file]').each(function() {
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

  view.$('#photoimg').on('change', function() {
    $("#preview1").html('');
    $("#preview1").html('<span class="loading">Loading...</span>');
    $("#imageform").ajaxForm({
      target: '#preview1'
    }).submit();
  });

  view.$('.preview.avatar').on('click.styleguide', function(e){
      $(this).closest('.span10').find('label.file-upload span strong').trigger('click');
  });
}

function _dispose_content(view) {
    view.$('#photoimg').off('change');
    view.$('.preview.avatar').off('click.styleguide');
}
