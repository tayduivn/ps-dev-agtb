({
    extendsFrom: 'RowactionField',
    events: {
        'click [data-action=link]': 'linkClicked',
        'click [data-action=download]': 'downloadClicked',
        'click [data-action=email]': 'emailClicked'
    },

    /**
     * PDF Template collection.
     *
     * @property
     */
    templateCollection: null,

    /**
     * Visibility property for available template links.
     *
     * @property
     */
    fetchCalled: false,

    /**
     * {@inheritDoc}
     * Create PDF Template collection in order to get available template list.
     */
    initialize: function(options) {
        this._super('initialize', [options]);
        this.templateCollection = app.data.createBeanCollection('PdfManager');
    },

    /**
     * Define proper filter for PDF template list.
     * Fetch the collection to get available template list.
     * @private
     */
    _fetchTemplate: function() {
        this.fetchCalled = true;
        var collection = this.templateCollection;
        collection.filterDef = {'$and': [{
            'base_module': this.module
        }, {
            'published': 'yes'
        }]};
        collection.fetch();
    },

    /**
     * Build download link url.
     *
     * @param {String} templateId PDF Template id.
     * @return {string} Link url.
     * @private
     */
    _buildDownloadLink: function(templateId) {
        var urlParams = $.param({
            'action': 'sugarpdf',
            'module': this.module,
            'sugarpdf': 'pdfmanager',
            'record': this.model.id,
            'pdf_template_id': templateId
        });
        return '?' + urlParams;
    },

    /**
     * Build email pdf link url.
     *
     * @param {String} templateId PDF Template id.
     * @return {string} Email pdf url.
     * @private
     */
    _buildEmailLink: function(templateId) {
        return '#' + app.bwc.buildRoute(this.module, null, 'sugarpdf', {
            'sugarpdf': 'pdfmanager',
            'record': this.model.id,
            'pdf_template_id': templateId,
            'to_email': '1'
        });
    },

    /**
     * Handle the button click event.
     * Stop event propagation in order to keep the dropdown box.
     *
     * @param {Event} evt Mouse event.
     */
    linkClicked: function(evt) {
        evt.preventDefault();
        evt.stopPropagation();
        if (this.templateCollection.dataFetched) {
            this.fetchCalled = !this.fetchCalled;
        } else {
            this._fetchTemplate();
        }
        this.render();
    },

    /**
     * Handles email pdf link.
     *
     * @param {Event} evt Mouse event.
     */
    emailClicked: function(evt) {
        var templateId = this.$(evt.currentTarget).data('id');
        app.router.navigate(this._buildEmailLink(templateId), {
            trigger: true
        });
    },

    /**
     * Handles download pdf link.
     *
     * @param {Event} evt Mouse event.
     */
    downloadClicked: function(evt) {
        var templateId = this.$(evt.currentTarget).data('id');
        app.api.fileDownload(this._buildDownloadLink(templateId), {
            error: function(data) {
                // refresh token if it has expired
                app.error.handleHttpError(data, {});
            }
        }, {iframe: this.$el});
    },

    /**
     * {@inheritDoc}
     * Bind listener for template collection.
     */
    bindDataChange: function() {
        this.templateCollection.on('reset', this.render, this);
        this._super('bindDataChange');
    },

    /**
     * {@inheritDoc}
     * Dispose safe for templateCollection listeners.
     */
    unbindData: function() {
        this.templateCollection.off(null, null, this);
        this.templateCollection = null;
        this._super('unbindData');
    },

    /**
     * {@inheritDoc}
     * Check additional access for PdfManager Module.
     */
    hasAccess: function() {
        var pdfAccess = app.acl.hasAccess('view', 'PdfManager');
        return pdfAccess && this._super('hasAccess');
    }
})
