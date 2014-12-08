/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
(function(app) {
    app.events.on('app:init', function() {
        app.plugins.register('Tinymce', ['field'], {

            /**
             * File input element.
             */
            $embeddedInput: null,

            /**
             * Name of file input.
             */
            fileFieldName: null,

            /**
             * {@inheritDoc}
             */
            onAttach: function(component) {
                var self = this;
                this.fileFieldName = component.options.def.name + '_file';
                this.$embeddedInput = $('<input />', {name: this.fileFieldName, type: 'file'}).hide();
                component.on('render', function() {
                    component.$el.append(self.$embeddedInput);
                });
            },

            /**
             * {@inheritDoc}
             */
            onDetach: function(component) {
                this.$embeddedInput.remove();
            },

            /**
             * Handle embedded file upload process.
             *
             * @param {String} field_name The name (and ID) of the dialogue window's input field.
             * @param {String} url Carries the existing link URL if you modify a link.
             * @param {String} type Either 'image', 'media' or 'file'
             * (called respectively from image plugin, media plugin and link plugin insert/edit dialogs).
             * @param {Object} win A reference to the dialogue window itself.
             */
            tinyMCEFileBrowseCallback: function(field_name, url, type, win) {
                var self = this;
                this.$embeddedInput.unbind().change(function(event) {
                    var $target = $(this),
                        fileObj = $target[0].files[0],
                        url = '';

                    if (type == 'image' && fileObj.type.indexOf('image') === -1) {
                        self.clearFileInput($target);
                        win.tinyMCEPopup.alert(app.lang.get('LBL_UPLOAD_ONLY_IMAGE', 'EmbeddedFiles'));
                        return;
                    }

                    var embeddedFile = app.data.createBean('EmbeddedFiles');

                    embeddedFile.save({name: fileObj.name}, {
                        success: function(model) {
                            model.uploadFile(
                                self.fileFieldName,
                                $target,
                                {
                                    success: function(rsp) {
                                        var forceDownload = !(rsp[self.fileFieldName]['content-type'].indexOf('image') !== -1);
                                        url = app.api.buildFileURL(
                                            {
                                                module: 'EmbeddedFiles',
                                                id: rsp.record.id,
                                                field: self.fileFieldName
                                            },
                                            {
                                                htmlJsonFormat: false,
                                                passOAuthToken: false,
                                                cleanCache: true,
                                                forceDownload: forceDownload
                                            }
                                        );

                                        $(win.document).find('#' + field_name).val(url);

                                        if (type == 'image') {
                                            // We are, so update image dimensions.
                                            if (win.ImageDialog.getImageData) {
                                                win.ImageDialog.getImageData();
                                            }
                                            if (win.ImageDialog.showPreviewImage) {
                                                win.ImageDialog.showPreviewImage(url);
                                            }
                                        }

                                        self.clearFileInput($target);
                                    },
                                    error: function() {
                                        self.clearFileInput($target);
                                    }
                                }
                            );
                        }
                    });
                });

                this.$embeddedInput.trigger('click');
            },

            /**
             * Clears input file value.
             *
             * @param {Object} $field Jquery input selector.
             */
            clearFileInput: function($field) {
                $field.val('');
                // For IE.
                $field.replaceWith($field.clone(true));
            }

        });
    });
})(SUGAR.App);
