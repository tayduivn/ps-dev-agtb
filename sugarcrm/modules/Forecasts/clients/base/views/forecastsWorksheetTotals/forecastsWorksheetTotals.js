/**
 * View that displays totals model for the forecastsWorksheetManager view
 * @extends View.View
 */
({
    /**
     * Initialize the View
     *
     * @constructor
     * @param {Object} options
     */
    initialize:function (options) {
        app.view.View.prototype.initialize.call(this, options);
        this.model.set({
            amount: 0,
            best_case: 0,
            worst_case: 0,
            overall_amount: 0,
            overall_best: 0,
            overall_worst: 0,
            show_worksheet_likely: options.context.forecasts.config.get('show_worksheet_likely'),
            show_worksheet_best: options.context.forecasts.config.get('show_worksheet_best'),
            show_worksheet_worst: options.context.forecasts.config.get('show_worksheet_worst'),

        });
    },

    bindDataChange: function() {
        var self = this;
        this.context.forecasts.on('change:updatedTotals', function(context, totals){
            self.model.set( totals );
            self._render();
        });

        //Listen for config changes
        this.context.forecasts.config.on('change:show_worksheet_likely change:show_worksheet_best change:show_worksheet_worst', function(context, value) {
            self.model.set({
                show_worksheet_likely: context.get('show_worksheet_likely') == 1,
                show_worksheet_best: context.get('show_worksheet_best') == 1,
                show_worksheet_worst: context.get('show_worksheet_worst') == 1
            });
            self._render();
        });

        this.context.forecasts.on('forecasts:worksheet:render', function() {
            self._render();
        })
    },

    /**
     * Special _render override that injects this model directly into the
     * forecastsWorksheetManager table/template
     * @private
     */
    _render: function() {
        // make sure forecastsWorksheet component is rendered first before rendering this
        if(this.context.forecasts.get('currentWorksheet') == 'worksheet') {
            // if this template's items are already in the #summary table, remove them
            if(!_.isEmpty($('#forecastsWorksheetTotalsIncludedTotals').html())) {
                $('#forecastsWorksheetTotalsIncludedTotals').remove();
                $('#forecastsWorksheetTotalsOverallTotals').remove();
            }
            // Add forecastsWorksheetTotals.hbt to the forecastsWorksheet table footer
            $('#summary tr:last').after(this.template(this.model.toJSON()));
        }
    }
})

