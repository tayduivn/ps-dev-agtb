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

describe("forecasts_view_forecastsCommitted", function() {
    var app, view, context, totals,
        formatAmountLocaleStub, createHistoryLogStub, forecastsSetStub,
        stubs = [];

    beforeEach(function() {
        app = SugarTest.app;

        stubs.push(sinon.stub(app.metadata, "getModule", function() {
            return {
                show_worksheet_likely: 1,
                show_worksheet_best: 1,
                show_worksheet_worst: 0
            };
        }));

        SugarTest.loadFile("../sidecar/src/utils", "currency", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) {
            return eval(d);
        });

        context = app.context.getContext();
        context.set({"selectedUser": {"id": 'test_user'}});
        context.set({"timeperiod_id": {"id": 'timeperiod_id'}});
        context.set({"collection": new Backbone.Collection()});

        app.initData = {};
        app.defaultSelections = {
            timeperiod_id: {},
            group_by: {},
            selectedUser: {}
        };

        var layout = {
            getComponent: function() {
                return {}
            }
        };

        view = SugarTest.createView("base", "Forecasts", "forecastsCommitted", null, context, true, layout);

        formatAmountLocaleStub = sinon.stub(app.currency, "formatAmountLocale", function(value) {
            return value;
        });

        stubs.push(formatAmountLocaleStub);

        createHistoryLogStub = sinon.stub(app.utils, "createHistoryLog", function() {
            return "createHistoryLog";
        });

        stubs.push(createHistoryLogStub);

        forecastsSetStub = sinon.stub(view.context, "trigger", function() {
        });
        sinon.spy(view.context.trigger);
        stubs.push(forecastsSetStub);
    });

    afterEach(function() {
        _.each(stubs, function(stub) {
            stub.restore();
        });

        totals = null;
        delete view.selectedUser;
        delete view.totals;
        delete view.context;
        delete view.collection.models;
    });

    describe("test arrow directions for sales rep", function() {
        beforeEach(function() {
            var model1 = new Backbone.Model({date_entered: "2012-12-05T11:14:25-04:00", best_case: 100, likely_case: 90, worst_case: 80, base_rate: 1 }),
                model2 = new Backbone.Model({date_entered: "2012-10-05T11:14:25-04:00", best_case: 110, likely_case: 100, worst_case: 90, base_rate: 1 }),
                model3 = new Backbone.Model({date_entered: "2012-11-05T11:14:25-04:00", best_case: 120, likely_case: 110, worst_case: 100, base_rate: 1 });
            view.collection = new Backbone.Collection([model1, model2, model3]);
        });

        it("should show up for best, worst and likely", function() {
            totals = {
                'amount': 500,
                'best_case': 500,
                'best_adjusted': 550,
                'likely_case': 450,
                'likely_adjusted': 475,
                'worst_case': 400,
                'worst_adjusted': 425
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-up');
            expect(view.likelyCaseCls).toContain('icon-arrow-up');
            expect(view.worstCaseCls).toContain('icon-arrow-up');
            expect(view.context.trigger).toHaveBeenCalled();
        });

        it("should show down for  best, worst and likely", function() {
            totals = {
                'best_case': 1,
                'best_adjusted': 1,
                'likely_case': 1,
                'likely_adjusted': 1,
                'worst_case': 1,
                'worst_adjusted': 1
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-down');
            expect(view.likelyCaseCls).toContain('icon-arrow-down');
            expect(view.worstCaseCls).toContain('icon-arrow-down');
            expect(view.context.trigger).toHaveBeenCalled();
        });
    });

    describe("test arrow directions for manager", function() {
        beforeEach(function() {
            var model1 = new Backbone.Model({date_entered: "2012-12-05T11:14:25-04:00", best_case: 100, likely_case: 90, worst_case: 80, base_rate: 1 });
            var model2 = new Backbone.Model({date_entered: "2012-10-05T11:14:25-04:00", best_case: 110, likely_case: 100, worst_case: 90, base_rate: 1 });
            var model3 = new Backbone.Model({date_entered: "2012-11-05T11:14:25-04:00", best_case: 120, likely_case: 110, worst_case: 100, base_rate: 1 });
            view.collection = new Backbone.Collection([model1, model2, model3]);
        });

        it("should show up for  best, worst and likely", function() {
            totals = {
                'amount': 500,
                'best_case': 500,
                'best_adjusted': 550,
                'likely_case': 450,
                'likely_adjusted': 475,
                'worst_case': 400,
                'worst_adjusted': 425
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-up');
            expect(view.likelyCaseCls).toContain('icon-arrow-up');
            expect(view.worstCaseCls).toContain('icon-arrow-up');
            expect(view.context.trigger).toHaveBeenCalled();
        });

        it("should show down for  best, worst and likely", function() {
            totals = {
                'best_case': 1,
                'best_adjusted': 1,
                'likely_case': 1,
                'likely_adjusted': 1,
                'worst_case': 1,
                'worst_adjusted': 1
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-down');
            expect(view.likelyCaseCls).toContain('icon-arrow-down');
            expect(view.worstCaseCls).toContain('icon-arrow-down');
            expect(view.context.trigger).toHaveBeenCalled();
        });
    });

    describe("updateTotals function without previous commit entries", function() {
        beforeEach(function() {
            totals = {
                amount: 1000,
                best_case: 1100,
                worst_case: 900,
                included_opp_count: 1,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 1000,
                overall_best: 1100,
                overall_worst: 900,
                timeperiod_id: "abc",
                total_opp_count: 1,
                won_amount: 0,
                won_count: 0
            };

            //Simulate the view having no models in the collection
            view.collection.models = [];

            //Simulate empty totals (no previous commit history)
            view.totals = {
                amount: 0,
                best_case: 0,
                worst_case: 0,
                included_opp_count: 0,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 0,
                overall_best: 0,
                overall_worst: 0,
                timeperiod_id: null,
                total_opp_count: 0,
                won_amount: 0,
                won_count: 0
            };
        });

        describe("test updateTotals function without a previousCommit entry", function() {
            it("should correctly set the member variables used to render commit log section", function() {
                view.updateTotals(totals);
                expect(view.likelyCase).toEqual(1000);
                expect(view.likelyCaseCls).toContain("icon-arrow-up font-green");
                expect(view.bestCase).toEqual(1100);
                expect(view.bestCaseCls).toContain("icon-arrow-up font-green");
                expect(view.worstCase).toEqual(900);
                expect(view.worstCaseCls).toContain("icon-arrow-up font-green");
                expect(view.totals.amount).toEqual(1000);
                expect(view.totals.best_case).toEqual(1100);
                expect(view.totals.worst_case).toEqual(900);
                expect(view.context.trigger).toHaveBeenCalled();
            });
        });
    });

    describe("updateTotals function with previous commit entries", function() {
        beforeEach(function() {
            totals = {
                amount: 1000,
                best_case: 1100,
                worst_case: 900,
                included_opp_count: 1,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 1000,
                overall_best: 1100,
                overall_worst: 900,
                timeperiod_id: "abc",
                total_opp_count: 1,
                won_amount: 0,
                won_count: 0
            };

            //Simulate the view having one model in the collection
            view.collection.models = [new Backbone.Model({
                likely_case: 900,
                best_case: 1000,
                worst_case: 800
            })];

            //Simulate previous commit history
            view.totals = {
                amount: 900,
                best_case: 1000,
                worst_case: 800,
                included_opp_count: 1,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 900,
                overall_best: 1000,
                overall_worst: 800,
                timeperiod_id: "def",
                total_opp_count: 1,
                won_amount: 0,
                won_count: 0
            };
        });

        describe("test updateTotals function with a previousCommit entry", function() {
            it("should correctly set the member variables used to render commit log section", function() {
                view.updateTotals(totals);
                expect(view.likelyCase).toEqual(1000);
                expect(view.likelyCaseCls).toContain("icon-arrow-up font-green");
                expect(view.bestCase).toEqual(1100);
                expect(view.bestCaseCls).toContain("icon-arrow-up font-green");
                expect(view.worstCase).toEqual(900);
                expect(view.worstCaseCls).toContain("icon-arrow-up font-green");
                expect(view.totals.amount).toEqual(1000);
                expect(view.totals.best_case).toEqual(1100);
                expect(view.totals.worst_case).toEqual(900);
            });
        });
    });

    describe("non-empty savedTotal will be set to null", function() {
        beforeEach(function() {
            totals = {
                amount: 1000,
                best_case: 1100,
                worst_case: 900,
                included_opp_count: 1,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 1000,
                overall_best: 1100,
                overall_worst: 900,
                timeperiod_id: "abc",
                total_opp_count: 1,
                won_amount: 0,
                won_count: 0
            };

            //Simulate the view having one model in the collection
            view.collection.models = [new Backbone.Model({
                likely_case: 900,
                best_case: 1000,
                worst_case: 800
            })];

            //Simulate previous commit history
            view.totals = {
                amount: 900,
                best_case: 1000,
                worst_case: 800,
                included_opp_count: 1,
                lost_amount: 0,
                lost_count: 0,
                overall_amount: 900,
                overall_best: 1000,
                overall_worst: 800,
                timeperiod_id: "def",
                total_opp_count: 1,
                won_amount: 0,
                won_count: 0
            };

            view.savedTotal = view.totals;
        });

        afterEach(function() {
            delete view.savedTotal;
        });

        describe("test updateTotals function with set savedTotal", function() {
            it("should set savedTotals to null", function() {
                view.updateTotals(totals);
                expect(view.savedTotal).toBeNull();
            });
        });
    });
});
