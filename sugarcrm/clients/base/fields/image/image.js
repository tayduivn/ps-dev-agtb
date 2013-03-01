/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    config: {
        //Default background image to show if no image has been uploaded
        profile: app.config.siteUrl + "/styleguide/assets/img/profile.png"
    },
    events: {
        "click .delete" : "delete",

        "change input[type=file]": "selectImage"
    },
    _render: function() {
        this.model.fileField = this.name;
        app.view.Field.prototype._render.call(this);

        //Define default sizes
        if (this.tplName === 'list') {
            this.width = this.height = this.$el.parent().innerHeight() || 42;
            this.def.width = this.def.height = undefined;
        } else {
            this.width = parseInt(this.def.width || this.def.height, 10) || 50;
            this.height = parseInt(this.def.height, 10) || this.width;
        }
        //Resize widget before the image is loaded
        this.resizeWidth(this.width);
        this.resizeHeight(this.height);
        this.$('.image_field').removeClass('hide');
        //Resize widget once the image is loaded
        this.$('img').addClass('hide').on('load', $.proxy(this.resizeWidget, this));
        return this;
    },
    format: function(value){
        if (value) {
            value = this.buildUrl() + "&_hash=" + value;
        }
        return value;
    },
    bindDataChange: function() {
        //Keep empty for edit because you cannot set a value of an input type `file`
        if (this.view.name != "edit" && this.view.fallbackFieldTemplate != "edit" && this.options.viewName != "edit") {
            app.view.Field.prototype.bindDataChange.call(this);
        }
    },
    bindDomChange: function() {
        //Override default behavior
    },
    selectImage: function(e) {
        var self = this,
            $input = self.$('input[type=file]');

        //Set flag to indicate we are previewing an image
        self.preview = true;

        //Remove error message
        self.clearError();

        // Upload a temporary file for preview
        self.model.uploadFile(
             self.name,
             $input,
             {
                 field: self.name,
                 //Callbacks
                 success: function(rsp) {
                     //read the guid
                     var fileId = (rsp[self.name]) ? rsp[self.name]['guid'] : null;
                     var url = app.api.buildFileURL({
                                   module: self.module,
                                   id: 'temp',
                                   field: self.name,
                                   fileId: fileId
                               });
                      // show image
                      var image = $('<img>').addClass('hide').attr('src', url).on('load', $.proxy(self.resizeWidget, self));
                      self.$('.image_preview').html(image);

                     //Trigger a change event with param "image" so the view can detect that the dom changed.
                     self.model.trigger("change", "image");
                 },
                 error: function(error) {
                     var errors = {};
                     errors[error.responseText] = {};
                     self.model.trigger('error:validation:' + this.field, errors);
                     self.model.trigger('error:validation', errors);
                     self.displayError();
                 }
              },
             { temp: true }); //for File API to understand we upload a temporary file
    },
    delete: function(e) {
        var self = this;
        //If we are previewing a file and want to cancel
        if (this.preview === true) {
            self.preview = false;
            self.clearError();
            if(!self.disposed) self.render();
        } else {
            var confirmMessage = app.lang.get('LBL_IMAGE_DELETE_CONFIRM', self.module);
            if (confirm(confirmMessage)) {
            //Otherwise delete the image
                app.api.call('delete', self.buildUrl({htmlJsonFormat: false}), {}, {
                        success: function() {
                            //Need to fire the change event twice so model.previous(self.name) is also changed.
                            self.model.unset(self.name);
                            self.model.set(self.name, null);
                            if(!self.disposed) self.render();
                        },
                        error: function(data) {
                            // refresh token if it has expired
                            app.error.handleHttpError(data, {});
                        }}
                );
            }
        }
    },
    /**
     * Build URI for File API
     */
    buildUrl: function(options) {
        return app.api.buildFileURL({
                    module: this.module,
                    id: this.model.id,
                    field: this.name
                }, options);
    },
    /**
     * Resize widget based on field defs and image size
     */
    resizeWidget: function() {
        var image = this.$('.image_preview img, .image_detail img');

        if  (!image[0]) return;

        var isDefHeight = !_.isUndefined(this.def.height) && this.def.height > 0,
            isDefWidth = !_.isUndefined(this.def.width) && this.def.width > 0;

        //set width/height defined in field defs
        if (isDefWidth) {
            image.css('width', this.width);
        }
        if (isDefHeight) {
            image.css('height', this.height);
        }

        if (!isDefHeight && !isDefWidth)
            image.css({
                'height' : this.height,
                'width' : this.width
            });

        //now resize widget
        //we resize the widget based on current image height
        this.resizeHeight(image.height());
        //if height was defined but not width, we want to resize image width to keep
        //proportionality: this.height/naturalHeight = newWidth/naturalWidth
        if (isDefHeight && !isDefWidth) {
            var newWidth = Math.floor((this.height / image[0].naturalHeight) * image[0].naturalWidth);
            image.css('width', newWidth);
            this.resizeWidth(newWidth);
        }

        image.removeClass('hide');
        this.$('.delete').remove();
        var icon = this.preview === true ? 'remove' : 'trash';
        image.closest('label, a').after('<span class="image_btn delete icon-' + icon + ' " />');
    },
    formatPX: function(size) {
        size = parseInt(size, 10);
        return size + 'px';
    },
    /**
     * Resize the elements carefully to render a pretty input[type=file]
     * @param height (in pixels)
     */
    resizeHeight: function(height) {
        var $image_field = this.$('.image_field'),
            isEditAndIcon = this.$('.icon-plus').length > 0;

        if (isEditAndIcon) {
            var $image_btn = $image_field.find('.image_btn');
            var edit_btn_height = parseInt($image_btn.css('height'), 10);

            var previewHeight = parseInt(height, 10);
            //Remove the edit button height in edit view so that the icon is centered.
            previewHeight -= edit_btn_height ? edit_btn_height : 0;
            previewHeight = this.formatPX(previewHeight);

            $image_field.find('.icon-plus').css({lineHeight:previewHeight});
        }


        var totalHeight = this.formatPX(height);
        $image_field.css({'height':totalHeight, minHeight:totalHeight, lineHeight:totalHeight});
        $image_field.find('label').css({lineHeight:totalHeight});
    },
    /**
     * Resize the elements carefully to render a pretty input[type=file]
     * @param width (in pixels)
     */
    resizeWidth: function(width) {
        var $image_field = this.$('.image_field'),
            width = this.formatPX(width),
            isInHeaderpane = $(this.el).closest('.headerpane').length > 0,
            isInRowFluid = $(this.el).closest('.row-fluid').closest('.record').length > 0;

        if(isInHeaderpane || !isInRowFluid) {
            //Need to fix width
            $image_field.css({'width':width});
        } else {
            //Width will be the biggest possible
            $image_field.css({'maxWidth':width});
        }
    },
    /**
     * Handles errors message
     * @param errors
     */
    handleValidationError: function(errors) {
        if (_.keys(errors).length > 0) {
            this.$el.closest('.control-group').addClass("error");
            this.$('.image_field').addClass('error');
        }

        // For each error add to error help block
        _.each(errors, function(errorContext, errorName) {
            this.$('.help-block')
            this.$('.help-block').append(app.error.getErrorString(errorName, errorContext));
        }, this);
    },
    displayError: function() {
        this.$('.image_preview').html('<i class="icon-remove"></i>');
        this.$('.delete').remove();
        this.$('label').after('<span class="image_btn delete icon-remove" />');
    },
    clearError: function() {
        //Remove error message
        this.$('.help-block').html('');
        this.$el.closest('.control-group').removeClass('error');
        this.$('.image_field').removeClass('error');
    }
})