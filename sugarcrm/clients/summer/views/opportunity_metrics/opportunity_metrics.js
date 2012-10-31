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

        app.view.View.prototype.initialize.call(this, options);
    },

    loadData: function() {
        var self = this;

        app.api.call("read", app.api.buildURL(this.module + "/" + this.context.get("model").id + "/" + "opportunity_stats"), null, {
            success: function(data) {
                // parse currencies and attach the correct delimiters/symbols etc

                _.each(data, function(key, value) {
                    data[value]['amount_usdollar'] = app.currency.formatAmountLocale(key['amount_usdollar']);
                });

                self.collections = data;
                self.render();
            }
        });
    }
})