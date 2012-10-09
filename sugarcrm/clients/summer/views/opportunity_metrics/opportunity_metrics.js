({
    initialize: function(options) {
        var zeroLocale = app.currency.formatAmountLocale(0);
        this.collections = {
            "active": {
                "amount_usdollar": zeroLocale,
                "count": 0
            },
            "lost": {
                "amount_usdollar": zeroLocale,
                "count": 0
            },
            "won": {
                "amount_usdollar": zeroLocale,
                "count": 0
            }
        };

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
                _.each(data, function(key, value) {
                    data[value]['amount_usdollar'] = app.currency.formatAmountLocale(key['amount_usdollar']);
                });

                self.collections = data;
                self.render();
            }});
    }
})