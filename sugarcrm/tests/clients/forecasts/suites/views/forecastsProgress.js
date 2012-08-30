describe("The Forecasts Progress Calculations display", function() {

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/forecasts/metadata/base/views", "forecastsProgress", "js", function(d) {return eval(d); });
    });

    describe("Rep Worksheet changes", function() {
        beforeEach(function() {
            view.selectedUser = {isManager: false, showOpps: true, id: "seed_test"};
            view.selectedTimePeriod = {id: "seed_test"};
            view.shouldRollup = false;

        });

        it("should calculate the model based on a change to the totals model", function() {
            var totals = {amount: 235000, best_case: 199900, won_amount: 95000, won_count: 3, lost_amount: 25000, lost_count: 3, included_opp_count: 5, total_opp_count: 13 };

            view.model = new Backbone.Model({amount: 0,
                                            closed_amount: 0,
                                            closed_best_above: false,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: "0",
                                            pipeline: 0,
                                            quota_amount: 246000,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});

            view.recalculateRepTotals(totals);
            var expectedModel = new Backbone.Model({amount: 0,
                                            closed_amount: 95000,
                                            closed_best_above: false,
                                            closed_best_amount: 104900,
                                            closed_best_percent: 0.4752376188094047,
                                            closed_likely_above: false,
                                            closed_likely_amount: 140000,
                                            closed_likely_percent: 0.40425531914893614,
                                            opportunities: 7,
                                            pipeline: 1.4,
                                            quota_amount: 246000,
                                            quota_best_above: false,
                                            quota_best_amount: 46100,
                                            quota_best_percent: 0.8126016260162602,
                                            quota_likely_above: false,
                                            quota_likely_amount: 11000,
                                            quota_likely_percent: 0.9552845528455285,
                                            revenue: 235000});

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
            view.likelyTotal = 167000;
            view.bestTotal = 199900;
            view.model = new Backbone.Model({amount: 0,
                                            closed_amount: 95000,
                                            closed_best_above: true,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: true,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: 7,
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: true,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: true,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 235000});

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({quota_amount: 246000});
            var expectedModel = new Backbone.Model({amount: 0,
                                            closed_amount: 95000,
                                            closed_best_above: false,
                                            closed_best_amount: 104900,
                                            closed_best_percent: 0.4752376188094047,
                                            closed_likely_above: false,
                                            closed_likely_amount: 72000,
                                            closed_likely_percent: 0.5688622754491018,
                                            opportunities: 7,
                                            pipeline: 2,
                                            quota_amount: 246000,
                                            quota_best_above: false,
                                            quota_best_amount: 46100,
                                            quota_best_percent: 0.8126016260162602,
                                            quota_likely_above: false,
                                            quota_likely_amount: 79000,
                                            quota_likely_percent: 0.6788617886178862,
                                            revenue: 235000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });
    });
    describe("Manager Worksheet changes", function() {
        beforeEach(function() {
            view.selectedUser = {isManager: true, showOpps: false, id: "seed_test"};
            view.selectedTimePeriod = {id: "seed_test"};
            view.shouldRollup = true;

        });

        it("should calculate the model based on a change to the totals model", function() {
            var totals = {amount: 600000, best_case: 500000, likely_case: 495000, likely_adjusted: 512000, best_adjusted: 565000,quota: 450000 };

            view.model = new Backbone.Model({amount: 0,
                                            closed_amount: 89000,
                                            closed_best_above: false,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: 12,
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});

            view.recalculateManagerTotals(totals);
            var expectedModel = new Backbone.Model({amount: 0,
                                            closed_amount: 89000,
                                            closed_best_above: false,
                                            closed_best_amount: 476000,
                                            closed_best_percent: 0.15752212389380532,
                                            closed_likely_above: false,
                                            closed_likely_amount: 423000,
                                            closed_likely_percent: 0.173828125,
                                            opportunities: 12,
                                            pipeline: 1.3,
                                            quota_amount: 450000,
                                            quota_best_above: true,
                                            quota_best_amount: 115000,
                                            quota_best_percent: 1.2555555555555555,
                                            quota_likely_above: true,
                                            quota_likely_amount: 62000,
                                            quota_likely_percent: 1.1377777777777778,
                                            revenue: 600000});

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
            view.likelyTotal = 512000;
            view.bestTotal = 565000;
            view.model = new Backbone.Model({amount: 0,
                                            closed_amount: 0,
                                            closed_best_above: true,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: true,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: 0,
                                            pipeline: 0,
                                            quota_amount: 450000,
                                            quota_best_above: true,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: true,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 600000});

            view.updateProgress();
            app.api.call.mostRecentCall.args[4].success({closed_amount: 89000, opportunities: 12});
            var expectedModel = new Backbone.Model({amount: 0,
                                            closed_amount: 89000,
                                            closed_best_above: false,
                                            closed_best_amount: 476000,
                                            closed_best_percent: 0.15752212389380532,
                                            closed_likely_above: false,
                                            closed_likely_amount: 423000,
                                            closed_likely_percent: 0.173828125,
                                            opportunities: 12,
                                            pipeline: 1.3,
                                            quota_amount: 450000,
                                            quota_best_above: true,
                                            quota_best_amount: 115000,
                                            quota_best_percent: 1.2555555555555555,
                                            quota_likely_above: true,
                                            quota_likely_amount: 62000,
                                            quota_likely_percent: 1.1377777777777778,
                                            revenue: 600000});

            expect(view.model.attributes).toEqual(expectedModel.attributes);
        });
    });
});