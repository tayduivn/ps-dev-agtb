//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("The Forecasts Progress Calculations display", function() {

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsProgress", "forecastsProgress", "js", function(d) {return eval(d); });
    });

    describe("Rep Worksheet changes", function() {
        beforeEach(function() {
            view.selectedUser = {isManager: false, showOpps: true, id: "seed_test"};
            view.selectedTimePeriod = {id: "seed_test"};
            view.shouldRollup = false;
            //reset base model
            view.model = new Backbone.Model({closed_amount: 0,
                                            closed_best_above: false,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            closed_worst_above: false,
                                            closed_worst_amount: 0,
                                            closed_worst_percent: 0,
                                            opportunities: "0",
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_worst_above: false,
                                            quota_worst_amount: 0,
                                            quota_worst_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});

        });

        it("should calculate the model based on a change to the totals model based on rep test case 1", function() {
            var totals = {amount: 60000, worst_case: 35000, best_case: 66000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: totals.won_amount,
                                            closed_best_above: totals.best_case > totals.won_amount,
                                            closed_best_amount: Math.abs(totals.best_case-totals.won_amount),
                                            closed_best_percent: totals.best_case/totals.won_amount,
                                            closed_worst_above: totals.worst_case > totals.won_amount,
                                            closed_worst_amount: Math.abs(totals.worst_case-totals.won_amount),
                                            closed_worst_percent: totals.worst_case/totals.won_amount,
                                            closed_likely_above: totals.amount > totals.won_amount,
                                            closed_likely_amount: Math.abs(totals.amount-totals.won_amount),
                                            closed_likely_percent: totals.amount/totals.won_amount,
                                            opportunities: totals.total_opp_count - totals.won_count - totals.lost_count,
                                            pipeline: Math.round(((totals.overall_amount - totals.won_amount - totals.lost_amount)/totals.amount)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: totals.best_case > 65000,
                                            quota_best_amount: Math.abs(totals.best_case-65000),
                                            quota_best_percent:totals.best_case/65000,
                                            quota_worst_above: totals.worst_case > 65000,
                                            quota_worst_amount: Math.abs(totals.worst_case-65000),
                                            quota_worst_percent:totals.worst_case/65000,
                                            quota_likely_above: totals.amount > 65000,
                                            quota_likely_amount: Math.abs(totals.amount-65000),
                                            quota_likely_percent: totals.amount/65000,
                                            revenue: totals.overall_amount - totals.won_amount - totals.lost_amount});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 2", function() {
            var totals = {amount: 35000, worst_case: 25000, best_case: 38000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 1, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: totals.won_amount,
                                            closed_best_above: totals.best_case > totals.won_amount,
                                            closed_best_amount: Math.abs(totals.best_case-totals.won_amount),
                                            closed_best_percent: totals.best_case/totals.won_amount,
                                            closed_worst_above: totals.worst_case > totals.won_amount,
                                            closed_worst_amount: Math.abs(totals.worst_case-totals.won_amount),
                                            closed_worst_percent: totals.worst_case/totals.won_amount,
                                            closed_likely_above: totals.amount > totals.won_amount,
                                            closed_likely_amount: Math.abs(totals.amount-totals.won_amount),
                                            closed_likely_percent: totals.amount/totals.won_amount,
                                            opportunities: totals.total_opp_count - totals.won_count - totals.lost_count,
                                            pipeline: Math.round(((totals.overall_amount - totals.won_amount - totals.lost_amount)/totals.amount)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: totals.best_case > 65000,
                                            quota_best_amount: Math.abs(totals.best_case-65000),
                                            quota_best_percent:totals.best_case/65000,
                                            quota_worst_above: totals.worst_case > 65000,
                                            quota_worst_amount: Math.abs(totals.worst_case-65000),
                                            quota_worst_percent:totals.worst_case/65000,
                                            quota_likely_above: totals.amount > 65000,
                                            quota_likely_amount: Math.abs(totals.amount-65000),
                                            quota_likely_percent: totals.amount/65000,
                                            revenue: totals.overall_amount - totals.won_amount - totals.lost_amount});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 3", function() {
            var totals = {amount: 120000, worst_case: 84000,  best_case: 129000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 3, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: totals.won_amount,
                                            closed_best_above: totals.best_case > totals.won_amount,
                                            closed_best_amount: Math.abs(totals.best_case-totals.won_amount),
                                            closed_best_percent: totals.best_case/totals.won_amount,
                                            closed_worst_above: totals.worst_case > totals.won_amount,
                                            closed_worst_amount: Math.abs(totals.worst_case-totals.won_amount),
                                            closed_worst_percent: totals.worst_case/totals.won_amount,
                                            closed_likely_above: totals.amount > totals.won_amount,
                                            closed_likely_amount: Math.abs(totals.amount-totals.won_amount),
                                            closed_likely_percent: totals.amount/totals.won_amount,
                                            opportunities: totals.total_opp_count - totals.won_count - totals.lost_count,
                                            pipeline: Math.round(((totals.overall_amount - totals.won_amount - totals.lost_amount)/totals.amount)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: totals.best_case > 65000,
                                            quota_best_amount: Math.abs(totals.best_case-65000),
                                            quota_best_percent:totals.best_case/65000,
                                            quota_worst_above: totals.worst_case > 65000,
                                            quota_worst_amount: Math.abs(totals.worst_case-65000),
                                            quota_worst_percent:totals.worst_case/65000,
                                            quota_likely_above: totals.amount > 65000,
                                            quota_likely_amount: Math.abs(totals.amount-65000),
                                            quota_likely_percent: totals.amount/65000,
                                            revenue: totals.overall_amount - totals.won_amount - totals.lost_amount});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 4", function() {
            var totals = {amount: 122000, worst_case: 90000, best_case: 135000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: totals.won_amount,
                                            closed_best_above: totals.best_case > totals.won_amount,
                                            closed_best_amount: Math.abs(totals.best_case-totals.won_amount),
                                            closed_best_percent: totals.best_case/totals.won_amount,
                                            closed_worst_above: totals.worst_case > totals.won_amount,
                                            closed_worst_amount: Math.abs(totals.worst_case-totals.won_amount),
                                            closed_worst_percent: totals.worst_case/totals.won_amount,
                                            closed_likely_above: totals.amount > totals.won_amount,
                                            closed_likely_amount: Math.abs(totals.amount-totals.won_amount),
                                            closed_likely_percent: totals.amount/totals.won_amount,
                                            opportunities: totals.total_opp_count - totals.won_count - totals.lost_count,
                                            pipeline: Math.round(((totals.overall_amount - totals.won_amount - totals.lost_amount)/totals.amount)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: totals.best_case > 65000,
                                            quota_best_amount: Math.abs(totals.best_case-65000),
                                            quota_best_percent:totals.best_case/65000,
                                            quota_worst_above: totals.worst_case > 65000,
                                            quota_worst_amount: Math.abs(totals.worst_case-65000),
                                            quota_worst_percent:totals.worst_case/65000,
                                            quota_likely_above: totals.amount > 65000,
                                            quota_likely_amount: Math.abs(totals.amount-65000),
                                            quota_likely_percent: totals.amount/65000,
                                            revenue: totals.overall_amount - totals.won_amount - totals.lost_amount});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 5", function() {
            var totals = {amount: 182000, worst_case: 139000, best_case: 198000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: totals.won_amount,
                                            closed_best_above: totals.best_case > totals.won_amount,
                                            closed_best_amount: Math.abs(totals.best_case-totals.won_amount),
                                            closed_best_percent: totals.best_case/totals.won_amount,
                                            closed_likely_above: totals.amount > totals.won_amount,
                                            closed_likely_amount: Math.abs(totals.amount-totals.won_amount),
                                            closed_likely_percent: totals.amount/totals.won_amount,
                                            closed_worst_above: totals.worst_case > totals.won_amount,
                                            closed_worst_amount: Math.abs(totals.worst_case-totals.won_amount),
                                            closed_worst_percent: totals.worst_case/totals.won_amount,
                                            opportunities: totals.total_opp_count - totals.won_count - totals.lost_count,
                                            pipeline: Math.round(((totals.overall_amount - totals.won_amount - totals.lost_amount)/totals.amount)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: totals.best_case > 65000,
                                            quota_best_amount: Math.abs(totals.best_case-65000),
                                            quota_best_percent:totals.best_case/65000,
                                            quota_worst_above: totals.worst_case > 65000,
                                            quota_worst_amount: Math.abs(totals.worst_case-65000),
                                            quota_worst_percent:totals.worst_case/65000,
                                            quota_likely_above: totals.amount > 65000,
                                            quota_likely_amount: Math.abs(totals.amount-65000),
                                            quota_likely_percent: totals.amount/65000,
                                            revenue: totals.overall_amount - totals.won_amount - totals.lost_amount});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate an ABS difference for a X to Y case", function() {
            expect(view.getAbsDifference(167000, 95000)).toEqual(72000);
        });

        it("should calculate an % for a X to Y case", function() {
            expect(Math.round(view.getPercent(95000, 167000)*100)).toEqual(57);
        });

        it("should determine if a number is above another for a X to Y case", function() {
            expect(view.checkIsAbove(95000, 167000)).toBeFalsy();
        });

        it("should calculate the model based on a change brought in from the api endpoint", function() {
            spyOn(app.api, 'call');
            view.likelyTotal = 60000;
            view.bestTotal = 66000;
            view.worstTotal = 35000,
            view.model.set({
                closed_amount: 60000,
                opportunities: 2,
                revenue: 60000
            });

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({quota_amount: 65000});
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: view.model.get('closed_amount'),
                                            closed_best_above: view.bestTotal > view.model.get('closed_amount'),
                                            closed_best_amount: Math.abs(view.bestTotal-view.model.get('closed_amount')),
                                            closed_best_percent: view.bestTotal/view.model.get('closed_amount'),
                                            closed_worst_above: view.worstTotal > view.model.get('closed_amount'),
                                            closed_worst_amount: Math.abs(view.worstTotal-view.model.get('closed_amount')),
                                            closed_worst_percent: view.worstTotal/view.model.get('closed_amount'),
                                            closed_likely_above: view.likelyTotal > view.model.get('closed_amount'),
                                            closed_likely_amount: Math.abs(view.likelyTotal-view.model.get('closed_amount')),
                                            closed_likely_percent: view.likelyTotal/view.model.get('closed_amount'),
                                            opportunities: view.model.get('opportunities'),
                                            pipeline: Math.round((view.model.get('revenue')/view.likelyTotal)*10)/10 ,
                                            quota_amount: 65000,
                                            quota_best_above: view.bestTotal > 65000,
                                            quota_best_amount: Math.abs(view.bestTotal-65000),
                                            quota_best_percent:view.bestTotal/65000,
                                            quota_worst_above: view.worstTotal > 65000,
                                            quota_worst_amount: Math.abs(view.worstTotal-65000),
                                            quota_worst_percent:view.worstTotal/65000,
                                            quota_likely_above: view.likelyTotal > 65000,
                                            quota_likely_amount: Math.abs(view.likelyTotal-65000),
                                            quota_likely_percent: view.likelyTotal/65000,
                                            revenue: view.model.get('revenue')});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });
    });


    describe("Manager Worksheet changes", function() {
        beforeEach(function() {
            view.selectedUser = {isManager: true, showOpps: false, id: "seed_test"};
            view.selectedTimePeriod = {id: "seed_test"};
            view.shouldRollup = true;
            //reset base model
            view.model = new Backbone.Model({closed_amount: 0,
                                            closed_best_above: false,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_worst_above: false,
                                            closed_worst_amount: 0,
                                            closed_worst_percent: 0,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: "0",
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_worst_above: false,
                                            quota_worst_amount: 0,
                                            quota_worst_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});
        });

        it("should calculate the model based on a change to the totals model", function() {
            var totals = {amount: 202000, worst_case: 152000, worst_adjusted: 160500, best_case: 190500, likely_case: 173900, likely_adjusted: 167900, best_adjusted: 184800,quota: 223000 };

            view.model.set({
                closed_amount: 123000,
                opportunities: 14,
                revenue: 195000
            });

            view.recalculateManagerTotals(totals);
            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: 123000,
                                            closed_best_above: totals.best_adjusted > 123000,
                                            closed_best_amount: Math.abs(totals.best_adjusted-123000),
                                            closed_best_percent: totals.best_adjusted/123000,
                                            closed_worst_above: totals.worst_adjusted > 123000,
                                            closed_worst_amount: Math.abs(totals.worst_adjusted-123000),
                                            closed_worst_percent: totals.worst_adjusted/123000,
                                            closed_likely_above: totals.likely_adjusted > 123000,
                                            closed_likely_amount: Math.abs(totals.likely_adjusted-123000),
                                            closed_likely_percent: totals.likely_adjusted/123000,
                                            opportunities: 14,
                                            pipeline: Math.round((195000/totals.likely_adjusted)*10)/10 ,
                                            quota_amount: totals.quota,
                                            quota_best_above: totals.best_adjusted > totals.quota,
                                            quota_best_amount: Math.abs(totals.best_adjusted-totals.quota),
                                            quota_best_percent:totals.best_adjusted/totals.quota,
                                            quota_worst_above: totals.worst_adjusted > totals.quota,
                                            quota_worst_amount: Math.abs(totals.worst_adjusted-totals.quota),
                                            quota_worst_percent:totals.worst_adjusted/totals.quota,
                                            quota_likely_above: totals.likely_adjusted > totals.quota,
                                            quota_likely_amount: Math.abs(totals.likely_adjusted-totals.quota),
                                            quota_likely_percent: totals.likely_adjusted/totals.quota,
                                            revenue: 195000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate an ABS difference for a X to Y case", function() {
            expect(view.getAbsDifference(512000, 450000)).toEqual(62000);
        });

        it("should calculate an % for a X to Y case", function() {
            expect(Math.round(view.getPercent(512000, 450000)*100)).toEqual(114);
        });

        it("should determine if a number is above another for a X to Y case", function() {
            expect(view.checkIsAbove(512000, 450000)).toBeTruthy();
        });


        it("should calculate the model based on a change brought in from the api endpoint", function() {
            spyOn(app.api, 'call');
            view.likelyTotal = 167900;
            view.bestTotal = 184800;
            view.worstTotal = 160500;

            view.model.set({
                quota_amount: 223000
            });

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({closed_amount: 123000, opportunities: 14, pipeline_revenue: 195000});

            //build model to mimic js calculation expectations
            var expectedModel = new Backbone.Model({closed_amount: 123000,
                                            closed_best_above: view.bestTotal > 123000,
                                            closed_best_amount: Math.abs(view.bestTotal-123000),
                                            closed_best_percent: view.bestTotal/123000,
                                            closed_worst_above: view.worstTotal > 123000,
                                            closed_worst_amount: Math.abs(view.worstTotal-123000),
                                            closed_worst_percent: view.worstTotal/123000,
                                            closed_likely_above: view.likelyTotal > 123000,
                                            closed_likely_amount: Math.abs(view.likelyTotal-123000),
                                            closed_likely_percent: view.likelyTotal/123000,
                                            opportunities: 14,
                                            pipeline: Math.round((195000/view.likelyTotal)*10)/10 ,
                                            quota_amount: view.model.get('quota_amount'),
                                            quota_best_above: view.bestTotal > view.model.get('quota_amount'),
                                            quota_best_amount: Math.abs(view.bestTotal-view.model.get('quota_amount')),
                                            quota_best_percent:view.bestTotal/view.model.get('quota_amount'),
                                            quota_worst_above: view.worstTotal > view.model.get('quota_amount'),
                                            quota_worst_amount: Math.abs(view.worstTotal-view.model.get('quota_amount')),
                                            quota_worst_percent:view.worstTotal/view.model.get('quota_amount'),
                                            quota_likely_above: view.likelyTotal > view.model.get('quota_amount'),
                                            quota_likely_amount: Math.abs(view.likelyTotal-view.model.get('quota_amount')),
                                            quota_likely_percent: view.likelyTotal/view.model.get('quota_amount'),
                                            revenue: 195000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });
    });
});