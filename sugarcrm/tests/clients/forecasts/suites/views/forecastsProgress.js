describe("The Forecasts Progress Calculations display", function() {

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../clients/forecasts/views/progress", "progress", "js", function(d) {return eval(d); });
    });

    describe("Rep Worksheet changes", function() {
        beforeEach(function() {
            var model1 = new Backbone.Model({amount: 25000, forecast: true,  best_case : 21500, likely_case : 18000});
            var model2 = new Backbone.Model({amount: 50000, forecast: true,  best_case : 42500, likely_case : 35500});
            var model3 = new Backbone.Model({amount: 75000, forecast: true,  best_case : 63500, likely_case : 53000});
            var model4 = new Backbone.Model({amount: 25000, forecast: true,  best_case : 21500, likely_case : 18000});
            var model5 = new Backbone.Model({amount: 75000, forecast: true,  best_case : 63500, likely_case : 53000});
            var collection = new Backbone.Collection([model1, model2, model3, model4, model5]);
            view.worksheetCollection = collection;
            view.selectedUser = new Backbone.Model({isManager: false, showOpps: true});

            var quota = {amount: 441000, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
            var closed = {amount: 0, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
            view.model = new Backbone.Model({'quota': quota, 'closed': closed, 'opportunities': 5, 'revenue': 250000, 'pipeline': 0.8});
            view.likelyTotal = 177500;
            view.bestTotal = 212500;

        });

        describe("calculate Base Data changes", function() {

            it("can calculate the likelyTotals", function() {
                expect(view.reduceWorksheet('likely_case')).toEqual(177500);
            });
            it("can calculate the BestTotals", function() {
                expect(view.reduceWorksheet('best_case')).toEqual(212500);
            });
            it("can calculate the revenue", function() {
                expect(view.reduceWorksheet('amount')).toEqual(250000);
            });
        });

        it("can calculate Quota:Distance To Likely", function() {
            var quota = view.model.get('quota');

            expect(quota.amount).toEqual(441000);
            expect(view.getAbsDifference(view.likelyTotal, quota.amount)).toEqual(263500);
            expect(Math.round(view.getPercent(view.likelyTotal, quota.amount)*100)).toEqual(40);
            expect(view.checkIsAbove(view.likelyTotal, quota.amount)).toBeFalsy();
        });

        it("can calculate Quota:Distance To Best", function() {
            var quota = view.model.get('quota');

            expect(quota.amount).toEqual(441000);
            expect(view.getAbsDifference(view.bestTotal, quota.amount)).toEqual(228500);
            expect(Math.round(view.getPercent(view.bestTotal, quota.amount)*100)).toEqual(48);
            expect(view.checkIsAbove(view.bestTotal, quota.amount)).toBeFalsy();
        });

        it("can calculate Closed:Distance To Likely", function() {
            var closed = view.model.get('closed');

            expect(closed.amount).toEqual(0);
            expect(view.getAbsDifference(closed.amount, view.likelyTotal)).toEqual(177500);
            expect(Math.round(view.getPercent(closed.amount, view.likelyTotal)*100)).toEqual(0);
            expect(view.checkIsAbove(closed.amount, view.likelyTotal)).toBeFalsy();
        });

        it("can calculate Closed:Distance To Best", function() {
            var closed = view.model.get('closed');

            expect(closed.amount).toEqual(0);
            expect(view.getAbsDifference(closed.amount, view.bestTotal)).toEqual(212500);
            expect(Math.round(view.getPercent(closed.amount, view.bestTotal)*100)).toEqual(0);
            expect(view.checkIsAbove(closed.amount, view.bestTotal)).toBeFalsy();
        });

        it("can calculate Pipeline Size", function() {
            expect(view.calculatePipelineSize(view.likelyTotal, view.model.get('revenue'), view.model.get('closed'))).toEqual(1.4);
        });

    });

    describe("Manager Worksheet changes", function() {
        beforeEach(function() {
            var model1 = new Backbone.Model({amount: 305000, forecast: 1,  best_case : 165200, likely_case : 165000, quota: 213500 });
            var model2 = new Backbone.Model({amount: 350000, forecast: 1,  best_case : 96100, likely_case : 95900, quota: 140000 });
            var model3 = new Backbone.Model({amount: 520000, forecast: 1,  best_case : 261600, likely_case : 261400, quota: 260000 });
            var collection = new Backbone.Collection([model1, model2, model3]);
            view.worksheetManagerCollection = collection;
            view.selectedUser = new Backbone.Model({isManager: true, showOpps: false});

            var quota = {amount: 613500, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
            var closed = {amount: 50000, best_case : {amount: 0, above: false, percent: 0.0}, likely_case : {amount: 0, above: false, percent: 0.0}};
            view.model = new Backbone.Model({'quota': quota, 'closed': closed, 'opportunities': 0, 'revenue': 1175000, 'pipeline': 0.0});
            view.likelyTotal = 522300;
            view.bestTotal = 522900;

        });

        describe("calculate Base Data changes", function() {

            it("can calculate the likelyTotals", function() {
                expect(view.reduceWorksheetManager('likely_case')).toEqual(522300);
            });
            it("can calculate the BestTotals", function() {
                expect(view.reduceWorksheetManager('best_case')).toEqual(522900);
            });
            it("can calculate the revenue", function() {
                expect(view.reduceWorksheetManager('amount')).toEqual(1175000);
            });
            it("can calculate the quota amount", function() {
                expect(view.reduceWorksheetManager('quota')).toEqual(613500);
            });
        });

        it("can calculate Quota:Distance To Likely", function() {
            var quota = view.model.get('quota');

            expect(quota.amount).toEqual(613500);
            expect(view.getAbsDifference(view.likelyTotal, quota.amount)).toEqual(91200);
            expect(Math.round(view.getPercent(view.likelyTotal, quota.amount)*100)).toEqual(85);
            expect(view.checkIsAbove(view.likelyTotal, quota.amount)).toBeFalsy();
        });

        it("can calculate Quota:Distance To Best", function() {
            var quota = view.model.get('quota');

            expect(quota.amount).toEqual(613500);
            expect(view.getAbsDifference(view.bestTotal, quota.amount)).toEqual(90600);
            expect(Math.round(view.getPercent(view.bestTotal, quota.amount)*100)).toEqual(85);
            expect(view.checkIsAbove(view.bestTotal, quota.amount)).toBeFalsy();
        });

        it("can calculate Closed:Distance To Likely", function() {
            var closed = view.model.get('closed');

            expect(closed.amount).toEqual(50000);
            expect(view.getAbsDifference(closed.amount, view.likelyTotal)).toEqual(472300);
            expect(Math.round(view.getPercent(closed.amount, view.likelyTotal)*100)).toEqual(10);
            expect(view.checkIsAbove(closed.amount, view.likelyTotal)).toBeFalsy();
        });

        it("can calculate Closed:Distance To Best", function() {
            var closed = view.model.get('closed');

            expect(closed.amount).toEqual(50000);
            expect(view.getAbsDifference(closed.amount, view.bestTotal)).toEqual(472900);
            expect(Math.round(view.getPercent(closed.amount, view.bestTotal)*100)).toEqual(10);
            expect(view.checkIsAbove(closed.amount, view.bestTotal)).toBeFalsy();
        });

        it("can calculate Pipeline Size", function() {
            expect(view.calculatePipelineSize(view.likelyTotal, view.model.get('revenue'), view.model.get('closed'))).toEqual(2.3);
        });

    });

});