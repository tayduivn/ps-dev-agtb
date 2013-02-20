({
    extendsFrom: 'RecordView',

    delegateButtonEvents: function() {
        this.context.on('button:convert_to_quote:click', this.convertToQuote, this);

        app.view.views.RecordView.prototype.delegateButtonEvents.call(this);
    },

    convertToQuote: function(e) {
        var alert = app.alert.show('info_quote', {
                        level: 'info',
                        autoClose: false,
                        closeable: false,
                        title: app.lang.get("LBL_CONVERT_TO_QUOTE_INFO", this.module) + ":",
                        messages: [app.lang.get("LBL_CONVERT_TO_QUOTE_INFO_MESSAGE", this.module)]
                    });
        // remove the close since we don't want this to be closable
        alert.$el.find('a.close').remove();

        var url = app.api.buildURL(this.model.module, 'quote', { id: this.model.id });
        var callbacks = {
            'success' : _.bind(function(resp, status, xhr) {
                app.alert.dismiss('info_quote');
                window.location.hash="#bwc/index.php?module=Quotes&action=EditView&record=" + resp.id;
            }, this)
        };
        app.api.call("create", url, null, callbacks);
    }
})
