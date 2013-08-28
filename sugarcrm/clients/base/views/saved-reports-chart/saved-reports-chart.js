({
    plugins: ['Dashlet'],

    /**
     * Holds initialization options
     */
    initOptions: undefined,

    /**
     * Holds report data from the server's endpoint once we fetch it
     */
    reportData: undefined,

    /**
     * Holds a reference to the Chart field
     */
    chartField: undefined,

    /**
     * Holds all report ID's and titles
     */
    reportOptions: undefined,

    /**
     * {@inheritDocs}
     */
    initDashlet: function (view) {
        // check if we're on the config screen
        if(this.meta.config) {
            this.meta.panels = this.dashletConfig.dashlet_config_panels;
            this.getAllSavedReports();
        } else {
            this.getSavedReportById(this.settings.get('saved_report_id'));
        }
    },

    /**
     * {@inheritDocs}
     */
    initialize: function(options) {
        this.initOptions = options;
        this.reportData = new Backbone.Model();
        app.view.View.prototype.initialize.call(this, options);
    },

    /**
     * {@inheritDocs}
     */
    bindDataChange: function() {
        if(this.meta.config) {
            this.settings.on('change:saved_report_id', function(model) {
                var reportTitle = this.reportOptions[model.get('saved_report_id')];

                this.settings.set({label: reportTitle});

                // set the title of the dashlet to the report title
                $('[name="label"]').val(reportTitle)
            }, this);
        }
    },

    /**
     * Makes a call to Reports/saved_reports to get any items stored in the saved_reports table
     */
    getAllSavedReports: function() {
        var params = {
                has_charts: true
            },
            url = app.api.buildURL('Reports/saved_reports', null, null, params);

        app.api.call('read', url, null, {
            success: _.bind(this.parseAllSavedReports, this),
            complete: this.initOptions ? this.initOptions.complete : null
        });
    },

    /**
     * Parses items passed back from Reports/saved_reports endpoint into enum options
     *
     * @param {Array} reports an array of saved reports returned from the endpoint
     */
    parseAllSavedReports: function(reports) {
        this.reportOptions = {};

        _.each(reports, function(report) {
            // build the reportOptions key/value pairs
            this.reportOptions[report.id] = report.name;
        }, this);

        // find the saved_report_id field
        var reportsField = _.find(this.fields, function(field) {
            return field.name == 'saved_report_id';
        });

        if(reportsField) {
            // set field options and render
            reportsField.items = this.reportOptions;
            reportsField._render();
        }
    },

    /**
     * Makes a call to Reports/saved_reports/:id to fetch specific saved report data
     *
     * @param {String} reportId the ID for the report we're looking for
     */
    getSavedReportById: function(reportId) {
        app.api.call('create', app.api.buildURL('Reports/chart/' + reportId), null, {
            success: _.bind(function(serverData) {
                // set reportData's rawChartData to the chartData from the server
                // this will trigger chart.js' change:rawChartData and the chart will update
                this.reportData.set({rawChartData: serverData.chartData});
            }, this),
            complete: this.initOptions ? this.initOptions.complete : null
        });
    },

    /**
     * {@inheritDocs}
     * When rendering fields, get a reference to the chart field if we don't have one yet
     */
    _renderField: function(field) {
        app.view.View.prototype._renderField.call(this, field);

        // hang on to a reference to the chart field
        if(_.isUndefined(this.chartField) && field.name == 'chart') {
            this.chartField = field;
        }
    }
})
