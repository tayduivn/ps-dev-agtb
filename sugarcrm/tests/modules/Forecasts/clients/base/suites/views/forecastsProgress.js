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
                                            opportunities: "0",
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});

        });

        it("should calculate the model based on a change to the totals model based on rep test case 1", function() {
            var totals = {amount: 60000, best_case: 66000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: true,
                                            closed_best_amount: 6000,
                                            closed_best_percent: 1.1,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 1.00,
                                            opportunities: 2,
                                            pipeline: 1,
                                            quota_amount: 65000,
                                            quota_best_above: true,
                                            quota_best_amount: 1000,
                                            quota_best_percent: 1.0153846153846153,
                                            quota_likely_above: false,
                                            quota_likely_amount: 5000,
                                            quota_likely_percent: 0.9230769230769231,
                                            revenue: 60000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 2", function() {
            var totals = {amount: 35000, best_case: 38000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 1, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: false,
                                            closed_best_amount: 22000,
                                            closed_best_percent: 0.6333333333333333,
                                            closed_likely_above: false,
                                            closed_likely_amount: 25000,
                                            closed_likely_percent:0.5833333333333334,
                                            opportunities: 2,
                                            pipeline: 1.7,
                                            quota_amount: 65000,
                                            quota_best_above: false,
                                            quota_best_amount: 27000,
                                            quota_best_percent:0.5846153846153846,
                                            quota_likely_above: false,
                                            quota_likely_amount: 30000,
                                            quota_likely_percent:0.5384615384615384,
                                            revenue: 60000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 3", function() {
            var totals = {amount: 120000, best_case: 129000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 3, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: true,
                                            closed_best_amount: 69000,
                                            closed_best_percent: 2.15,
                                            closed_likely_above: true,
                                            closed_likely_amount: 60000,
                                            closed_likely_percent: 2.00,
                                            opportunities: 2,
                                            pipeline: 0.5,
                                            quota_amount: 65000,
                                            quota_best_above: true,
                                            quota_best_amount: 64000,
                                            quota_best_percent: 1.9846153846153847,
                                            quota_likely_above: true,
                                            quota_likely_amount: 55000,
                                            quota_likely_percent: 1.8461538461538463,
                                            revenue: 60000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 4", function() {
            var totals = {amount: 122000, best_case: 135000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: true,
                                            closed_best_amount: 75000,
                                            closed_best_percent: 2.25,
                                            closed_likely_above: true,
                                            closed_likely_amount: 62000,
                                            closed_likely_percent: 2.033333333333333,
                                            opportunities: 2,
                                            pipeline:0.5,
                                            quota_amount: 65000,
                                            quota_best_above: true,
                                            quota_best_amount: 70000,
                                            quota_best_percent: 2.076923076923077,
                                            quota_likely_above: true,
                                            quota_likely_amount: 57000,
                                            quota_likely_percent: 1.876923076923077,
                                            revenue: 60000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });

        it("should calculate the model based on a change to the totals model based on rep test case 5", function() {
            var totals = {amount: 182000, best_case: 198000, overall_amount: 182000,won_amount: 60000, won_count: 1, lost_amount: 62000, lost_count: 1, included_opp_count: 2, total_opp_count: 4 };
            // reset base quota amount for rep
            view.model.set("quota_amount", 65000);

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: true,
                                            closed_best_amount: 138000,
                                            closed_best_percent: 3.30,
                                            closed_likely_above: true,
                                            closed_likely_amount: 122000,
                                            closed_likely_percent: 3.033333333333333,
                                            opportunities: 2,
                                            pipeline:0.3,
                                            quota_amount: 65000,
                                            quota_best_above: true,
                                            quota_best_amount: 133000,
                                            quota_best_percent: 3.046153846153846,
                                            quota_likely_above: true,
                                            quota_likely_amount: 117000,
                                            quota_likely_percent: 2.80,
                                            revenue: 60000});

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
            view.model.set({
                closed_amount: 60000,
                opportunities: 2,
                revenue: 60000
            });

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({quota_amount: 65000});
            var expectedModel = new Backbone.Model({closed_amount: 60000,
                                            closed_best_above: true,
                                            closed_best_amount: 6000,
                                            closed_best_percent: 1.1,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 1,
                                            opportunities: 2,
                                            pipeline: 1,
                                            quota_amount: 65000,
                                            quota_best_above: true,
                                            quota_best_amount: 1000,
                                            quota_best_percent: 1.0153846153846153,
                                            quota_likely_above: false,
                                            quota_likely_amount: 5000,
                                            quota_likely_percent: 0.9230769230769231,
                                            revenue: 60000});

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
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: "0",
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});
        });

        it("should calculate the model based on a change to the totals model", function() {
            var totals = {amount: 202000, best_case: 190500, likely_case: 173900, likely_adjusted: 167900, best_adjusted: 184800,quota: 223000 };

            view.model.set({
                closed_amount: 123000,
                opportunities: 14,
                revenue: 195000
            });

            view.recalculateManagerTotals(totals);
            var expectedModel = new Backbone.Model({closed_amount: 123000,
                                            closed_best_above: true,
                                            closed_best_amount: 61800,
                                            closed_best_percent: 1.5,
                                            closed_likely_above: true,
                                            closed_likely_amount: 44900,
                                            closed_likely_percent: 1.37,
                                            opportunities: 14,
                                            pipeline: 1.1,
                                            quota_amount: 223000,
                                            quota_best_above: false,
                                            quota_best_amount: 38200,
                                            quota_best_percent:0.83,
                                            quota_likely_above: false,
                                            quota_likely_amount: 55100,
                                            quota_likely_percent: 0.75,
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

            view.model.set({
                quota_amount: 223000
            });

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({closed_amount: 123000, opportunities: 14, pipeline_revenue: 195000});

            var expectedModel = new Backbone.Model({closed_amount: 123000,
                                            closed_best_above: true,
                                            closed_best_amount: 61800,
                                            closed_best_percent: 1.5,
                                            closed_likely_above: true,
                                            closed_likely_amount: 44900,
                                            closed_likely_percent: 1.37,
                                            opportunities: 14,
                                            pipeline: 1.1,
                                            quota_amount: 223000,
                                            quota_best_above: false,
                                            quota_best_amount: 38200,
                                            quota_best_percent:0.83,
                                            quota_likely_above: false,
                                            quota_likely_amount: 55100,
                                            quota_likely_percent: 0.75,
                                            revenue: 195000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });
    });
});