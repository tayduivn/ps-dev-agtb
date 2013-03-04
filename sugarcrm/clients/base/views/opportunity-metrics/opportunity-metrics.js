({
    className: 'block thumbnail widget opportunity-metrics-widget',

    metricsCollection: {},

    loadData: function() {
        var self = this;

        app.api.call('read', app.api.buildURL(this.module, 'opportunity_stats', {id: this.context.get('model').id}), null, {
            success: function(data) {
                _.each(data, function(value, key) {
                    // parse currencies and attach the correct delimiters/symbols etc
                    data[key].formattedAmount = app.currency.formatAmountLocale(value['amount_usdollar']);

                    data[key].icon = key === 'won'? 'caret-up' : (key === 'lost'? 'caret-down' : 'minus');
                    data[key].cssClass = key === 'won'? 'success' : (key === 'lost'? 'important' : 'Contacts');

                    // This ensures correct pluralization.
                    data[key].dealLabel = 'LBL_MODULE_NAME';
                    data[key].dealLabel += (value.count === 1)? '_SINGULAR' : '';

                    data[key].stageLabel = app.lang.getAppListStrings("opportunity_metrics_dom")[key];
                });

                self.metricsCollection = data;
                self.render();
            }
        });
    }
})
