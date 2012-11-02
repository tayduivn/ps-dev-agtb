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
            amount : 0,
            quota : 0,
            best_case : 0,
            best_adjusted : 0,
            likely_case : 0,
            likely_adjusted : 0,
            worst_case : 0,
            worst_adjusted : 0,
            show_worksheet_likely: options.context.forecasts.config.get('show_worksheet_likely'),
            show_worksheet_best: options.context.forecasts.config.get('show_worksheet_best'),
            show_worksheet_worst: options.context.forecasts.config.get('show_worksheet_worst'),

        });
    },

    bindDataChange: function() {
        var self = this;
        this.context.forecasts.on('change:updatedManagerTotals', function(context, totals){
            self.model.set( totals );
            self._render();
        });

        // re-render when the worksheet is rendered as well,
        this.context.forecasts.on('forecasts:worksheetmanager:render', function() {
            self._render();
        });

        /*
         * // TODO: tagged for 6.8 see SFA-253 for details
        //Listen for config changes
        this.context.forecasts.config.on('change:show_worksheet_likely change:show_worksheet_best change:show_worksheet_worst', function(context, value) {
            self.model.set({
                show_worksheet_likely: context.get('show_worksheet_likely') == 1,
                show_worksheet_best: context.get('show_worksheet_best') == 1,
                show_worksheet_worst: context.get('show_worksheet_worst') == 1
            });
            self._render();
        });
        */
    },

    /**
     * Special _render override that injects this model directly into the
     * forecastsWorksheetManager table/template
     * @private
     */
    _render: function() {
        // make sure forecastsWorksheetManager component is rendered first before rendering this
        if(this.context.forecasts.get('currentWorksheet') == 'worksheetmanager') {
            $('#summaryManager').html(this.template(this.model.toJSON()));
        }
    }
})

