describe("Forecasts Utils", function(){

    var app, hbt_helper;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../clients/forecasts/lib", "ForecastsUtils", "js", function(d) { return eval(d); });
        hbt_heleper = SugarTest.loadFile("../clients/forecasts/helper","hbt-helpers", "js", function(d) { return eval(d); });
    });

    describe("test createHistoryLog function", function() {
        beforeEach(function() {
            App = app;
            newestModel = new Backbone.Model();
            oldestModel = new Backbone.Model();
        });

        afterEach(function(){
            newestModel = null;
            oldestModel = null;
        });

        describe("should show both values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 800);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BOTH_CHANGED').toBeTruthy();
            });
        });

        describe("should show best values changed", function() {
            it("should return object with text attribute best case value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 900);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_CHANGED').toBeTruthy();
            });
        });

        describe("should show likely values changed", function() {
            it("should return object with text attribute likely case value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 800);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED').toBeTruthy();
            });
        });

        describe("should show no values changed", function() {
            it("should return object with text attribute no value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 900);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_NONE_CHANGED').toBeTruthy();
            });
        });
    });
    describe("Test parseDBDate", function() {
        beforeEach(function() {
            App = app;
            newestModel = new Backbone.Model();
            oldestModel = new Backbone.Model();
        });

        afterEach(function(){
            newestModel = null;
            oldestModel = null;
        });

        describe("should parse properly formatted date string", function() {
            it("should parse properly formatted date string", function() {
                var dbDateStr = "2012-01-14 14:50:30";
                result = app.forecasts.utils.parseDBDate(dbDateStr);
                expect(_.isDate(result)).toBeTruthy();
            });

            it("should be null on improperly formatted date string", function() {
                var dbDateStr = "6516513513";
                result = app.forecasts.utils.parseDBDate(dbDateStr);
                expect(result).toBeNull();
            });
        });
    });
});
