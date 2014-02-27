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
    plugins: ['Dashlet', 'Chart'],
    className: 'cases-summary-wrapper',

    tabData: null,
    tabClass: '',

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        this._super('initialize', [options]);

        this.chart = nv.models.pieChart()
                .x(function(d) {
                    return d.key;
                })
                .y(function(d) {
                    return d.value;
                })
                .margin({top: 10, right: 10, bottom: 15, left: 10})
                .donut(true)
                .donutLabelsOutside(true)
                .donutRatio(0.4)
                .hole(this.total)
                .showTitle(false)
                .tooltips(true)
                .showLegend(false)
                .colorData('class')
                .tooltipContent(function(key, x, y, e, graph) {
                    return '<p><b>' + key + ' ' + parseInt(y, 10) + '</b></p>';
                });
    },

    /**
     * Generic method to render chart with check for visibility and data.
     * Called by _renderHtml and loadData.
     */
    renderChart: function() {
        if (!this.isChartReady()) {
            return;
        }

        // Set value of label inside donut chart
        this.chart.hole(this.total);

        d3.select('svg#' + this.cid)
            .datum(this.chartCollection)
            .transition().duration(500)
            .call(this.chart);

        this.chart_loaded = _.isFunction(this.chart.update);
        this.displayNoData(!this.chart_loaded);
    },

    /**
     * Build content with favorite fields for content tabs
     */
    addFavs: function() {
        var self = this;
        //loop over metricsCollection
        _.each(this.tabData, function(tabGroup) {
            if (tabGroup.models && tabGroup.models.length > 0) {
                _.each(tabGroup.models, function(model) {
                    var field = app.view.createField({
                            def: {type: 'favorite'},
                            model: model,
                            meta: {view: 'detail'},
                            viewName: 'detail',
                            view: self
                        });
                    field.setElement(self.$('.favTarget.[data-model-id="' + model.id + '"]'));
                    field.render();
                });
            }
        });
    },

    /* Process data loaded from REST endpoint so that d3 chart can consume
     * and set general chart properties
     */
    evaluateResult: function(data) {
        this.total = data.models.length;

        var countClosedCases = data.where({status: 'Closed'})
                .concat(data.where({status: 'Rejected'}))
                .concat(data.where({status: 'Duplicate'})).length,
            countOpenCases = this.total - countClosedCases;

        this.chartCollection = {
            data: [],
            properties: {
                title: app.lang.getAppString('LBL_CASE_SUMMARY_CHART'),
                value: 3,
                label: this.total
            }
        };
        this.chartCollection.data.push({
            key: app.lang.getAppString('LBL_DASHLET_CASESSUMMARY_CLOSE_CASES'),
            class: 'nv-fill-green',
            value: countClosedCases
        });
        this.chartCollection.data.push({
            key: app.lang.getAppString('LBL_DASHLET_CASESSUMMARY_OPEN_CASES'),
            class: 'nv-fill-red',
            value: countOpenCases
        });

        if (!_.isEmpty(data.models)) {
            this.processCases(data);
        }
    },

    /**
     * Build tab related data and set tab class name based on number of tabs
     * @param {data} object The chart related data.
     */
    processCases: function(data) {
        this.tabData = [];

        var status2css = {
                'Rejected': 'label-success',
                'Closed': 'label-success',
                'Duplicate': 'label-success'
            },
            stati = _.uniq(data.pluck('status')),
            statusOptions = app.metadata.getModule('Cases', 'fields').status.options || 'case_status_dom';

        _.each(stati, function(status, index) {
            if (!status2css[status]) {
                this.tabData.push({
                    index: index,
                    status: status,
                    statusLabel: app.lang.getAppListStrings(statusOptions)[status],
                    models: data.where({'status': status}),
                    cssClass: status2css[status] ? status2css[status] : 'label-important'
                });
            }
        }, this);

        this.tabClass = ['one', 'two', 'three', 'four', 'five'][this.tabData.length] || 'four';
    },

    /**
     * @inheritDoc
     */
    loadData: function(options) {
        var self = this,
            oppID,
            accountBean,
            relatedCollection;
        if (this.meta.config) {
            return;
        }
        oppID = this.model.get('account_id');
        if (oppID) {
            accountBean = app.data.createBean('Accounts', {id: oppID});
        }
        relatedCollection = app.data.createRelatedCollection(accountBean || this.model, 'cases');
        relatedCollection.fetch({
            relate: true,
            success: function(data) {
                self.evaluateResult(data);
                if (!self.disposed) {
                    // we have to rerender the entire dashlet, not just the chart,
                    // because the HBS file is dependant on processCases completion
                    self.render();
                    self.addFavs();
                }
            },
            error: _.bind(function() {
                this.displayNoData(true);
            }, this),
            complete: options ? options.complete : null
        });
    }
})
