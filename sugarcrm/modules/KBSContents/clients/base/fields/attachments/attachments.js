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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */
({
    /**
     * {@inheritDoc}
     */
    events: {
        'click [data-action=download]': 'startDownload'
    },

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
     * {@inheritDoc}
     */
    initialize: function (opts) {
        var evt = {},
            relate;
        evt['change ' +  this.getFileNode().selector] = 'uploadFile';
        this.events = _.extend({}, this.events, opts.def.events, evt);

        this.fileInputSelector = opts.def.fileinput || '';
        this.fieldSelector = opts.def.field || '';
        this.cid = _.uniqueId('attachment');

        this._super('initialize', [opts]);
        this._select2formatSelectionTemplate = app.template.get('f.attachments.KBSContents.selection-partial');
        /**
         * Selects attachments related module.
         */
        if (this.model.id) {
            relate = this.model.getRelatedCollection(this.def.link);
            relate.fetch({
                relate: true
            });
        }
    },

    /**
     * {@inheritDoc}
     */
    format: function (value) {
        return _.map(value, function (item) {
            var isImage = false,
                forceDownload = !isImage,
                mimeType = isImage ? 'image' : 'application/octet-stream',
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
        var result = this._super('_render',[]);

        this.setSelect2Node();
        if (this.$node.length > 0) {
            this.$node.select2({
                allowClear: true,
                multiple: true,
                containerCssClass: 'select2-choices-pills-close span12 select2-choices-pills-square with-padding',
                tags: [],
                formatSelection: _.bind(this.formatSelection, this),
                width: 'off',
                escapeMarkup: function(m) {
                    return m;
                }
            });
            this.refreshFromModel();
        }

        return result;
    },

    /**
     *  Update `Select2` data from model.
     */
    refreshFromModel: function () {
        var attachments = [];
        if (this.model.has(this.name)) {
            attachments = this.model.get(this.name);
        }
        this.$node.select2('data', attachments);
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
                    self.model.set(self.name, _.without(self.model.get(self.name), evt.removed));
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
    uploadFile: function () {
        var self = this,
            $input = this.getFileNode();
        this.model.uploadFile(
            self.name,
            $input,
            {
                field: self.name,
                //Callbacks
                success: function (rsp) {
                    var att = {};
                    att.id = rsp.record.id;
                    att.name = rsp[self.name].guid;
                    self.model.set(self.name, _.union([], self.model.get(self.name) || [], [att]));
                    $input.val('');
                    self.render();
                }
            },
            {temp: true}  //for File API to understand we upload a temporary file
        );
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

    dispose: function () {
        this.$node.off('change');
        this.$node.off('select2-opening');
        this._super('dispose');
    }
})
