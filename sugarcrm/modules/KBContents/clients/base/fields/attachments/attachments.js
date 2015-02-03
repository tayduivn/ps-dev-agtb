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

({
    /**
     * {@inheritDoc}
     */
    events: {
        'click [data-action=download]': 'startDownload',
        'click [data-action=download-all]': 'startDownloadArchive'
    },

    plugins: ['DragdropAttachments'],

    /**
     * @property {Object} `Select2` object.
     */
    $node: null,

    /**
     * @property {String} Selector for `Select2` dropdown.
     */
    fieldSelector: '',

    /**
     * @property {String} Unique ID for file input.
     */
    cid: null,

    /**
     * @property {String} Selector for file input.
     */
    fileInputSelector: '',

    /**
     * @property {Object} Handlebar object.
     */
    _select2formatSelectionTemplate: null,

    /**
     * Label for `Download all`.
     */
    download_label: '',

    /**
     * {@inheritDoc}
     */
    initialize: function (opts) {
        var evt = {},
            relate,
            self = this;
        evt['change ' +  this.getFileNode().selector] = 'uploadFile';
        this.events = _.extend({}, this.events, opts.def.events, evt);

        this.fileInputSelector = opts.def.fileinput || '';
        this.fieldSelector = opts.def.field || '';
        this.cid = _.uniqueId('attachment');

        this._super('initialize', [opts]);
        this._select2formatSelectionTemplate = app.template.get('f.attachments.KBContents.selection-partial');
        /**
         * Selects attachments related module.
         */
        if (this.model.id) {
            relate = this.model.getRelatedCollection(this.def.link);
            relate.fetch({
                relate: true,
                success: function() {
                    if (self.disposed === true) {
                        return;
                    }
                    self.render();
                }
            });
        }
    },

    /**
     * {@inheritDoc}
     */
    format: function (value) {
        return _.map(value, function (item) {
            var forceDownload = !item.isImage,
                mimeType = item.isImage ? 'image' : 'application/octet-stream',
                urlOpts = {
                    module: this.def.module,
                    id: item.id,
                    field: this.def.modulefield
                };
            return _.extend(
                {},
                {
                    mimeType: mimeType,
                    url: app.api.buildFileURL(
                        urlOpts,
                        {
                            htmlJsonFormat: false,
                            passOAuthToken: false,
                            cleanCache: true,
                            forceDownload: forceDownload
                        }
                    )
                },
                item
            );
        }, this);
    },

    /**
     * {@inheritdoc}
     */
    _render: function () {
        if (this.action == 'noaccess') {
            return;
        }
        this.download_label = (this.value && this.value.length > 1) ? 'LBL_DOWNLOAD_ALL' : 'LBL_DOWNLOAD_ONE';
        // Please, do not put this._super call before acl check,
        // due to _loadTemplate function logic from sidecar/src/view.js file
        this._super('_render',[]);

        this.setSelect2Node();
        if (this.$node.length > 0) {
            this.$node.select2({
                allowClear: true,
                multiple: true,
                containerCssClass: 'select2-choices-pills-close span12 with-padding',
                tags: [],
                formatSelection: _.bind(this.formatSelection, this),
                width: 'off',
                escapeMarkup: function(m) {
                    return m;
                }
            });
            $(this.$node.data('select2').containerSelector).attr('data-attachable', true);
            this.refreshFromModel();
        }
    },

    /**
     *  Update `Select2` data from model.
     */
    refreshFromModel: function () {
        var attachments = [];
        if (this.model.has(this.name)) {
            attachments = this.model.get(this.name);
        }
        this.$node.select2('data', this.format(attachments));
    },

    /**
     * Set `$node` as `Select2` object.
     */
    setSelect2Node: function () {
        var self = this;
        if (this.$node !== null && this.$node.length > 0) {
            this.$node.off('change');
            this.$node.off('select2-opening');
        }
        this.$node = this.$(this.fieldSelector + '[data-type=attachments]');
        this.$node.on('change',
            function (evt) {
                if (!_.isEmpty(evt.removed)) {
                    self.model.set(
                        self.name,
                        _.filter(
                            self.model.get(self.name),
                            function(file) {return (file.id != evt.removed.id);}
                        )
                    );
                    /**
                     * Deletes relate attachment from server.
                     */
                    if (!_.isEmpty(self.model.id)) {
                        var relates = self.model.getRelatedCollection(self.def.link),
                            relate = relates.get(evt.removed.id);

                        if (relate) {
                            relate.destroy({relate: true});
                        }
                    }
                }
                self.render();
            });
        /**
         * Disables dropdown for `Select2`
         */
        this.$node.on('select2-opening', function (evt) {
            evt.preventDefault();
        });

    },

    /**
     * Return file input.
     * @return {Object}
     */
    getFileNode: function () {
        return this.$(this.fileInputSelector + '[data-type=fileinput]');
    },

    /**
     * {@inheritDoc}
     */
    bindDomChange: function () {
        this.setSelect2Node();
    },

    /**
     * Upload file to server.
     */
    uploadFile: function() {
        var self = this,
            $input = this.getFileNode(),
            note = app.data.createBean('Notes'),
            fieldName = 'filename';

        note.save({name: $input[0].files[0].name}, {
            success: function(model) {
                // FileApi uses one name for file key and defs.
                var $cloneInput = _.clone($input);
                $cloneInput.attr('name', fieldName);
                model.uploadFile(
                    fieldName,
                    $input,
                    {
                        success: function(rsp) {
                            var att = {};
                            att.id = rsp.record.id;
                            att.isImage = (rsp[fieldName]['content-type'].indexOf('image') !== -1);
                            att.name = rsp[fieldName].name;
                            self.model.set(self.name, _.union([], self.model.get(self.name) || [], [att]));
                            $input.val('');
                            self.render();
                        }
                    }
                );
            }
        });
    },

    /**
     * {@inheritDoc}
     * Handles drop event.
     *
     * @param {Event} event Drop event.
     */
    dropAttachment: function(event) {
        event.preventDefault();
        var self = this,
            data = new FormData(),
            fieldName = 'filename';

        _.each(event.dataTransfer.files, function(file) {
            data.append(this.name, file);

            var note = app.data.createBean('Notes');
            note.save({name: file.name}, {
                success: function(model) {
                    var url = app.api.buildFileURL({
                        module: model.module,
                        id: model.id,
                        field: 'filename'
                    }, {htmlJsonFormat: false});
                    data.append('filename', file);
                    data.append('OAuth-Token', app.api.getOAuthToken());

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function(rsp) {
                            var att = {};
                            att.id = rsp.record.id;
                            att.isImage = (rsp[fieldName]['content-type'].indexOf('image') !== -1);
                            att.name = rsp[fieldName].name;
                            self.model.set(self.name, _.union([], self.model.get(self.name) || [], [att]));
                            self.render();
                        }
                    });
                }
            });
        }, this);
    },

    /**
     * Format selection for `Select2` to display.
     * @param {Object} attachment
     * @return {String}
     */
    formatSelection: function (attachment) {
        return this._select2formatSelectionTemplate(attachment);
    },

    /**
     * Download file from server.
     * @param {Event} evt
     */
    startDownload: function (evt) {
        var uri = this.$(evt.currentTarget).data('url');
        app.api.fileDownload(
            uri,
            {
                error: function (data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }
            },
            {iframe: this.$el}
        );
    },

    /**
     * Download archived files from server.
     */
    startDownloadArchive: function () {
        var params = {
            format:'sugar-html-json',
            link_name: this.def.link,
            platform: app.config.platform
        };
        params[(new Date()).getTime()] = 1;

        // todo: change buildURL to buildFileURL when will be allowed "link" attribute
        var uri = app.api.buildURL(this.model.module, 'file', {
            module: this.model.module,
            id: this.model.id,
            field: this.def.modulefield
        }, params);

        app.api.fileDownload(
            uri,
            {
                error: function (data) {
                    // refresh token if it has expired
                    app.error.handleHttpError(data, {});
                }
            },
            {iframe: this.$el}
        );
    },

    dispose: function () {
        this.$node.off('change');
        this.$node.off('select2-opening');
        this._super('dispose');
    }
})
