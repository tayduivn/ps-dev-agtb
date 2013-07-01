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
    fieldTag: 'input[type=file]',
    events: {
        'click [data-action=download]': 'startDownload',
        'click [data-action=delete]': 'deleteFile'
    },
    fileUrl: '',
    /**
     * Handler for delete file control
     *
     * Calls api to remove attached file from the model and
     * clear value and shows input[type=file] to upload new file
     * @param {Event} e
     */
    deleteFile: function(e) {
        var self = this;
        app.alert.show('delete_file_confirmation', {
            level: 'confirmation',
            messages: app.lang.get('LBL_FILE_DELETE_CONFIRM', self.module),
            onConfirm: function() {
                var data = {
                        module: self.module,
                        id: self.model.id,
                        field: self.name
                    },
                    callbacks = {
                        success: function() {
                            self.model.set(self.name, '');
                            self.model.save({}, {
                                //Show alerts for this request
                                showAlerts: {
                                    'process': true,
                                    'success': {
                                        messages: app.lang.get('LBL_FILE_DELETED', self.module)
                                    }
                                },
                                fields: [self.name]
                            });
                            if (self.disposed) {
                                return;
                            }
                            // Because delete button is enabled in edit mode only and
                            // bindDataChange is overrided to prevent rendering field
                            // in edit mode call render method manually
                            self.render();
                        },
                        error: function(data) {
                            // refresh token if it has expired
                            app.error.handleHttpError(data, {});
                        }
                    };
                app.api.file('delete', data, null, callbacks, {htmlJsonFormat: false});
            }
        });
    },
    _render: function() {
        // This array will contain objects accessible in the view
        this.model = this.model || this.view.model;
        app.view.Field.prototype._render.call(this);
        return this;
    },
    format: function(value) {
        var attachments = [];
        // Not the same behavior either the value is a string or an array of files
        if (_.isArray(value)) {
            // If it's an array, we get the uri for each files in the response
            _.each(value, function(file) {
                var fileObj = {
                    name: file.name,
                    url: file.uri
                };
                attachments.push(fileObj);
            }, this);
        } else if (value) {
            // If it's a string, build the uri with the api library
            var fileObj = {
                name: value,
                url: app.api.buildFileURL({
                        module: this.module,
                        id: this.model.id,
                        field: this.name
                    },
                    {
                        htmlJsonFormat: false,
                        passOAuthToken: false
                    })};
            attachments.push(fileObj);
        }
        return (this.tplName === "list") ? _.first(attachments) : attachments;
    },
    startDownload: function(e) {
        var uri = self.$(e.currentTarget).data('url');

        App.api.fileDownload(uri, {
            error: function(data) {
                // refresh token if it has expired
                app.error.handleHttpError(data, {});
            }
        }, {iframe: this.$el});
    },
    /**
     * {@inheritdoc}
     *
     * Override standard method because we cannot set a value of a type `file` input
     * prevent rendering input[type=file] if it's in edit mode
     */
    bindDataChange: function() {
        if (!this.model) {
            return;
        }
        this.model.on('change:' + this.name, function() {
            if (_.isUndefined(this.options.viewName) || this.options.viewName !== 'edit') {
                this.render();
            }
        }, this);
    },

    /**
     * {@inheritdoc}
     *
     * Because input file uses full local path to file as value,
     * value can contains directory names.
     * Unformat value to have file name only in it.
     */
    unformat: function (value) {
        return value.split('/').pop().split('\\').pop();
    }
})
