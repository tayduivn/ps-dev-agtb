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
        /**
         * Adds ability to override TinyMCE default file upload functionality via the EmbeddedFiles module and fileAPI.
         * Just override the TinyMCE file_browser_callback function with the tinyMCEFileBrowseCallback method.
         * Once attached the plugin creates a hidden input to upload files.
         */
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
                }, this);
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
             * This callaback creates new EmbeddedFile object, so this module should present in SugarCRM.
             * If there is no EmbeddedFile module, this method does nothing.
             *
             * To activate possibility usage of embeded files in tinymce you need specify 'file_browser_callback'.
             * @see modules/KBContents/clients/base/fields/htmleditable_tinymce/htmleditable_tinymce.js
             * @see http://www.tinymce.com/wiki.php/Configuration:file_browser_callback
             *
             * Example:
             *
             * config.file_browser_callback = _.bind(this.tinyMCEFileBrowseCallback, this);
             *
             * @param {String} fieldName The name (and ID) of the dialogue window's input field.
             * @param {String} url Carries the existing link URL if you modify a link.
             * @param {String} type Either 'image', 'media' or 'file'.
             * (called respectively from image plugin, media plugin and link plugin insert/edit dialogs).
             * @param {Object} win A reference to the dialogue window itself.
             */
            tinyMCEFileBrowseCallback: function(fieldName, url, type, win) {

                if (_.isUndefined(app.metadata.getModule('EmbeddedFiles'))) {
                    return;
                }

                var attributes = {
                    fieldName: fieldName,
                    url: url,
                    type: type,
                    win: win
                };

                this.$embeddedInput.unbind().change(_.bind(this._onEmbededFile, this, attributes));
                this.$embeddedInput.trigger('click');
            },

            /**
             * Handler called when user choose file to upload.
             *
             * @param {Object} attributes
             * @param {string} attributes.fieldName The name (and ID) of the dialogue window's input field.
             * @param {string} attributes.url Carries the existing link URL if you modify a link.
             * @param {string} attributes.type Either 'image', 'media' or 'file'
             * @param {string} attributes.win A reference to the dialogue window itself.
             * @param {Event} event Dom event.
             * @private
             */
            _onEmbededFile: function(attributes, event) {
                var $target = $(event.target),
                    fileObj = $target[0].files[0],
                    url = '';

                if (attributes.type === 'image' && fileObj.type.indexOf('image') === -1) {
                    this.clearFileInput($target);
                    attributes.win.tinyMCEPopup.alert(app.lang.get('LBL_UPLOAD_ONLY_IMAGE', 'EmbeddedFiles'));
                    return;
                }

                attributes.target = $target;

                var embeddedFile = app.data.createBean('EmbeddedFiles');
                embeddedFile.save({name: fileObj.name}, {
                    success: _.bind(this._saveEmbededFile, this, attributes)
                });
            },

            /**
             * Handler to save new embeded file.
             *
             * @param {Object} attributes
             * @param {string} attributes.fieldName The name (and ID) of the dialogue window's input field.
             * @param {string} attributes.url Carries the existing link URL if you modify a link.
             * @param {string} attributes.type Either 'image', 'media' or 'file'
             * @param {string} attributes.win A reference to the dialogue window itself.
             * @param {EmbeddedFile} model Model to save.
             * @private
             */
            _saveEmbededFile: function(attributes, model) {
                model.uploadFile(
                    this.fileFieldName,
                    attributes.target,
                    {
                        success: _.bind(function(rsp) {
                            var forceDownload = !(rsp[this.fileFieldName]['content-type'].indexOf('image') !== -1);
                            url = app.api.buildFileURL(
                                {
                                    module: 'EmbeddedFiles',
                                    id: rsp.record.id,
                                    field: this.fileFieldName
                                },
                                {
                                    htmlJsonFormat: false,
                                    passOAuthToken: false,
                                    cleanCache: true,
                                    forceDownload: forceDownload
                                }
                            );

                            $(attributes.win.document).find('#' + attributes.fieldName).val(url);

                            if (attributes.type === 'image') {
                                // We are, so update image dimensions.
                                if (_.isFunction(attributes.win.ImageDialog.getImageData)) {
                                    attributes.win.ImageDialog.getImageData();
                                }
                                if (_.isFunction(attributes.win.ImageDialog.showPreviewImage)) {
                                    attributes.win.ImageDialog.showPreviewImage(url);
                                }
                            }

                            this.clearFileInput(attributes.target);
                        }, this),
                        error: _.bind(function() {
                            app.alert.show('upload-error', {
                                level: 'error',
                                messages: 'ERROR_UPLOAD_FAILED'
                            });
                            this.clearFileInput(attributes.target);
                        }, this)
                    }
                );
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
