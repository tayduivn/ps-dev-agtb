describe("The Forecasts Progress Calculations display", function() {

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../clients/forecasts/views/forecastsProgress", "forecastsProgress", "js", function(d) {return eval(d); });
    });

    describe("Rep Worksheet changes", function() {
        beforeEach(function() {
            var model1 = {amount: 235000, best_case: 199900, likely_case: 167000, won_amount: 95000, won_count: 3, lost_amount: 25000, lost_count: 3, included_opp_count: 5, total_opp_count: 13 };
            view.selectedUser = {isManager: false, showOpps: true};

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
            view.calculateBases(model1);

        });

        describe("calculate Rep Base Data changes", function() {

            it("can calculate the likelyTotals", function() {
                expect(view.likelyTotal).toEqual(167000);
            });
            it("can calculate the BestTotals", function() {
                expect(view.bestTotal).toEqual(199900);
            });
        });

        it("can calculate Rep Quota:Distance To Likely", function() {
            var quota_amount = 504000;

            expect(quota_amount).toEqual(504000);
            expect(view.getAbsDifference(view.likelyTotal, quota_amount)).toEqual(337000);
            expect(Math.round(view.getPercent(view.likelyTotal, quota_amount)*100)).toEqual(33);
            expect(view.checkIsAbove(view.likelyTotal, quota_amount)).toBeFalsy();
        });

        it("can calculate Rep Quota:Distance To Best", function() {
            var quota_amount = 504000;

            expect(quota_amount).toEqual(504000);
            expect(view.getAbsDifference(view.bestTotal, quota_amount)).toEqual(304100);
            expect(Math.round(view.getPercent(view.bestTotal, quota_amount)*100)).toEqual(40);
            expect(view.checkIsAbove(view.bestTotal, quota_amount)).toBeFalsy();
        });

        it("can calculate Rep Closed:Distance To Likely", function() {
            var closed_amount = 95000;

            expect(view.getAbsDifference(closed_amount, view.likelyTotal)).toEqual(72000);
            expect(Math.round(view.getPercent(closed_amount, view.likelyTotal)*100)).toEqual(57);
            expect(view.checkIsAbove(closed_amount, view.likelyTotal)).toBeFalsy();
        });

        it("can calculate Rep Closed:Distance To Best", function() {
            var closed_amount = 95000;

            expect(view.getAbsDifference(closed_amount, view.bestTotal)).toEqual(104900);
            expect(Math.round(view.getPercent(closed_amount, view.bestTotal)*100)).toEqual(48);
            expect(view.checkIsAbove(closed_amount, view.bestTotal)).toBeFalsy();
        });

        it("can calculate Rep Pipeline Size", function() {
            expect(view.calculatePipelineSize(view.likelyTotal, 395000, view.model.get('closed_amount'))).toEqual(2.4);
        });

    });


    describe("Manager Worksheet changes", function() {
        beforeEach(function() {
            var model1 = {amount: 395000, best_adjusted: 197200, best_case: 566800, likely_adjusted: 164500, likely_case: 566800, quota: 504000 };
            view.selectedUser = {isManager: true, showOpps: false};

            view.model = new Backbone.Model({amount: 0,
                                            closed_amount: 270000,
                                            closed_best_above: false,
                                            closed_best_amount: 0,
                                            closed_best_percent: 0,
                                            closed_likely_above: false,
                                            closed_likely_amount: 0,
                                            closed_likely_percent: 0,
                                            opportunities: "7",
                                            pipeline: 0,
                                            quota_amount: 0,
                                            quota_best_above: false,
                                            quota_best_amount: 0,
                                            quota_best_percent: 0,
                                            quota_likely_above: false,
                                            quota_likely_amount: 0,
                                            quota_likely_percent: 0,
                                            revenue: 0});
            view.calculateBases(model1);
        });

        describe("calculate Base Data changes", function() {

            it("can calculate the likelyTotals", function() {
                expect(view.likelyTotal).toEqual(164500);
            });
            it("can calculate the BestTotals", function() {
                expect(view.bestTotal).toEqual(197200);
            });
        });

        it("can calculate Quota:Distance To Likely", function() {
            var quota_amount = 504000;

            expect(quota_amount).toEqual(504000);
            expect(view.getAbsDifference(view.likelyTotal, quota_amount)).toEqual(339500);
            expect(Math.round(view.getPercent(view.likelyTotal, quota_amount)*100)).toEqual(33);
            expect(view.checkIsAbove(view.likelyTotal, quota_amount)).toBeFalsy();
        });

        it("can calculate Quota:Distance To Best", function() {
            var quota_amount = 504000;

            expect(quota_amount).toEqual(504000);
            expect(view.getAbsDifference(view.bestTotal, quota_amount)).toEqual(306800);
            expect(Math.round(view.getPercent(view.bestTotal, quota_amount)*100)).toEqual(39);
            expect(view.checkIsAbove(view.bestTotal, quota_amount)).toBeFalsy();
        });

        it("can calculate Closed:Distance To Likely", function() {
            var closed_amount = view.model.get('closed_amount');

            expect(closed_amount).toEqual(270000);
            expect(view.getAbsDifference(closed_amount, view.likelyTotal)).toEqual(105500);
            expect(Math.round(view.getPercent(closed_amount, view.likelyTotal)*100)).toEqual(164);
            expect(view.checkIsAbove(closed_amount, view.likelyTotal)).toBeTruthy();
        });

        it("can calculate Closed:Distance To Best", function() {
            var closed_amount = view.model.get('closed_amount');

            expect(closed_amount).toEqual(270000);
            expect(view.getAbsDifference(closed_amount, view.bestTotal)).toEqual(72800);
            expect(Math.round(view.getPercent(closed_amount, view.bestTotal)*100)).toEqual(137);
            expect(view.checkIsAbove(closed_amount, view.bestTotal)).toBeTruthy();
        });

        it("can calculate Pipeline Size", function() {
            expect(view.calculatePipelineSize(view.likelyTotal, 395000, view.model.get('closed_amount'))).toEqual(4);
        });

    });

});