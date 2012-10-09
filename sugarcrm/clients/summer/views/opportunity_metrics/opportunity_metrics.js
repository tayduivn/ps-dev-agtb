({
    initialize: function(options) {
        this.dataReady = false;
        this.zeroCurrency = app.currency.formatAmountLocale(0);
        this.collections = {};
        app.view.View.prototype.initialize.call(this,options);
    },

    _render: function() {
        app.view.View.prototype._render.call(this);
    },

    loadData: function() {
        var self = this;
        this.context = app.controller.context;
        this.currentModule = app.controller.layout.options.module;

        app.api.call("read", app.api.buildURL(this.currentModule + "/" + this.context.get("model").id + "/" +"opportunity_stats"), null,
        { success: function(data) {

            // parse currencies and attach the correct delimiters/symbols etc
            _.each(data, function(key, value, list) {
                data[value]['amount_usdollar'] = app.currency.formatAmountLocale(key['amount_usdollar']);
            });

            self.collections = data;
            self.dataReady = true;
            self.render();
        }});
    }
})