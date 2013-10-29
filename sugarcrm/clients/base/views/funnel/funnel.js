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
    initialize: function(options) {
        app.view.View.prototype.initialize.call(this, options);

        this.funnelCollection = app.data.createBeanCollection(this.module);
        this.funnelCollection.fetch({
            //Don't show alerts for this request
            showAlerts: false
        });
        this.guid = _.uniqueId("funnel");
    },

    _render: function() {
        var self = this;

        app.view.View.prototype._render.call(this);

        // Once the data is fetched, process it, then render it.
        this.funnelCollection.on("reset", function() {
            var day_ms = 1000*60*60*24;
            var today = new Date();
            today.setUTCHours(0,0,0,0);
            var d1 = new Date(today.getTime() + 31*day_ms);
            var data, sum;
            if(self.funnelCollection) {
                data = self.funnelCollection.filter(function(model) {
                    // Filter for 30 days from now.
                    var d2 = new Date(model.get("date_closed") || "1970-01-01");
                    return (d2-d1)/day_ms <= 30;
                });
                sum = _.reduce(data, function(memo, model) {
                    return memo + parseInt(model.get('amount_usdollar'), 10);
                }, 0);
                data = _.groupBy(data, function(m) {
                    return m.get("sales_stage");
                });
            }

            var stages = ["Prospecting", "Qualification", "Closed Lost", "Closed Won"];
            var scale = 1000;

            // Massage the values to what we want.
            // TODO: Make this more efficient.
            var root = {
                properties: {
                    scale: scale,
                    title: "Pipeline",
                    units: "$",
                    total: parseInt(sum/scale, 10)
                },
                data: []
            };

            var cumulative = 0;

            _.each(stages, function(stage, i) {
                var subtotal = 0;
                if(data && data[stage]) {
                    subtotal = _.reduce(data[stage], function(memo, model) {
                        return memo + parseInt(model.get('amount_usdollar'), 10);
                    }, 0)/scale;
                }
                root.data.push({
                    bar: true,
                    key: stage,
                    values: [{
                        series: i,
                        x: 0,
                        y: subtotal,
                        y0: cumulative
                    }]
                });
                cumulative += subtotal;
            });

            nv.addGraph(function() {
                var chart = nv.models.funnelChart();

                // chart.xAxis
                //     .tickFormat(d3.format(',f'));

                chart.yAxis
                    .tickFormat(d3.format(',.1f'));

                chart.showTitle(false);

                d3.select('#'+self.guid+' svg')
                    .datum(root)
                  .transition().duration(500).call(chart);

                nv.utils.windowResize(chart.update);

                return chart;
            });

        });
    },

    unbindData: function() {
        this.funnelCollection.off();
        this.funnelCollection = null;
        app.view.View.prototype.unbindData.call(this);
    }
})
