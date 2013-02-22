({
    events: {
        'click .icon-download-alt': 'download'
    },

    /**
     * {@inheritdoc}
     *
     * The vCard is always a readonly field.
     */
    initialize: function(options) {
        options.def.readonly = true;
        app.view.Field.prototype.initialize.call(this, options);
    },

    /**
     * Downloads the vCard from the Rest API.
     *
     * First we do an ajax call to the `ping` API. This will check if the token
     * hasn't expired before we append it to the URL of the VCardDownload.
     *
     * @param {Event} evt
     *   The event (expecting click event) that triggered the download action.
     */
    download: function(evt) {

        var self = this;

        app.api.call('read', app.api.buildURL('ping'), {}, {
            success: function(data) {

                var uri = app.api.buildURL('VCardDownload', 'read', {}, {
                    module: self.model.module,
                    id: self.model.id,
                    oauth_token: app.api.getOAuthToken()
                });
                if (_.isEmpty(uri)) {
                    app.logger.error('Unable to get the vCard download uri.');
                    return;
                }
                window.location.href = uri;
            },
            error: function(data) {
                app.error.handleHttpError(data, self.model);
            }
        });
    },

    /**
     * {@inheritdoc}
     *
     * Keep empty because you cannot set a value of a type `vcard`.
     */
    bindDataChange: function() {
    }
})
