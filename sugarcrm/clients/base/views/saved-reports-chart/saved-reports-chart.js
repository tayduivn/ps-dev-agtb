/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    plugins: ['Dashlet'],

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
     * ID for the autorefresh timer
     */
    timerId: undefined,

    /**
     * {@inheritDocs}
     */
    initDashlet: function (view) {
        // check if we're on the config screen
        if(this.meta.config) {
            this.meta.panels = this.dashletConfig.dashlet_config_panels;
            this.getAllSavedReports();
        } else {
            var autoRefresh = +this.settings.get("auto_refresh");
            if (autoRefresh) {
                if (this.timerId) {
                    clearInterval(this.timerId);
                }
                this.timerId = setInterval(_.bind(function () {
                    this.context.resetLoadFlag();
                    this.loadData();
                }, this), autoRefresh * 1000 * 60);
            }
        }
    },

    /**
     * {@inheritDocs}
     */
    initialize: function(options) {
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
     * {@inheritDocs}
     */
    loadData: function(options) {
        options = options || {};
        this.getSavedReportById(this.settings.get('saved_report_id'), options);
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
            success: _.bind(this.parseAllSavedReports, this)
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
    getSavedReportById: function(reportId, options) {
        var dt = this.layout.getComponent('dashlet-toolbar');
        if(dt) {
            // manually set the icon class to spiny
            this.$("[data-action=loading]").removeClass(dt.cssIconDefault).addClass(dt.cssIconRefresh);
        }

        app.api.call('create', app.api.buildURL('Reports/chart/' + reportId), null, {
            success: _.bind(function(serverData) {
                // set reportData's rawChartData to the chartData from the server
                // this will trigger chart.js' change:rawChartData and the chart will update
                this.reportData.set({rawChartData: serverData.chartData});
            }, this),
            complete: options ? options.complete : null
        });
    },

    /**
     * {@inheritDocs}
     */
    _render: function() {
        // if we're in config, or if the chartField doesn't exist yet... render
        // otherwise do not render again as this destroys and re-draws the chart and looks awful
        if(this.meta.config || _.isUndefined(this.chartField)) {
            app.view.View.prototype._render.call(this);
        }
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
