describe("The forecasts worksheet totals calculation test", function(){

    var app, view, context;

    beforeEach(function() {
        SugarTest.seedMetadata(true);
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../../../../clients/forecasts/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
    });


    describe("updateTotals worksheet calculation test", function() {
        beforeEach(function() {
            var model1 = new Backbone.Model({amount: 100, probability: 70, forecast: 1,  best_case : 100, likely_case : 100 });
            var model2 = new Backbone.Model({amount: 100, probability: 70, forecast: 0, best_case : 100, likely_case : 100 });
            var model3 = new Backbone.Model({amount: 100, probability: 70, forecast: 0,  best_case : 100, likely_case : 100 });
            var collection = new Backbone.Collection([model1, model2, model3]);
            view._collection = collection;
            view.totalModel = new Backbone.Model();
        });

        it("should calculate the included values based on forecast value", function() {
            context = app.context.getContext({module:'Forecasts'});
            view.context = {
                forecasts : {
                    set : function(model, updatedTotals) {
                        expect(model).toEqual('updatedTotals');
                        expect(updatedTotals.best_case).toEqual(100);
                        expect(updatedTotals.likely_case).toEqual(100);
                        expect(updatedTotals.amount).toEqual(100);
                        expect(updatedTotals.opp_count).toEqual(1);
                    }
                }
            };
            view.calculateTotals();
        });
    });

});