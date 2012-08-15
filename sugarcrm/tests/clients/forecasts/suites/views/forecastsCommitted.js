describe("The forecasts log", function(){

    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../clients/forecasts/views/forecastsCommitted", "forecastsCommitted", "js", function(d) { return eval(d); });
    });

    describe("test showCommitButton", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function(id) {
                return 'a_user_id';
            });
        });

        afterEach(function(){
            testMethodStub.restore();
        });

        describe("should show commit button", function() {
            it("is a user viewing their own forecast log", function() {
                expect(view.showCommitButton('a_user_id')).toBeTruthy();
            });
        });

        describe("should not show commit button", function() {
            it("is a user not viewing their own forecast log", function() {
                expect(view.showCommitButton('a_different_user_id')).toBeFalsy();
            });
        });
    });

    describe("test createHistoryLog function", function() {
        beforeEach(function() {
            App = app;
            currentModel = new Backbone.Model();
            previousModel = new Backbone.Model();
        });

        afterEach(function(){
            currentModel = null;
            previousModel = null;
        });

        describe("should show both values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                currentModel.set('best_case', 1000);
                currentModel.set('likely_case', 900);
                currentModel.set('date_entered', '2012-07-12 18:37:36');

                previousModel.set('best_case', 900);
                previousModel.set('likely_case', 800);
                previousModel.set('date_entered', '2012-07-12 18:37:36');

                result = view.createHistoryLog(currentModel, previousModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BOTH_CHANGED').toBeTruthy();
            });
        });

        describe("should show best values changed", function() {
            it("should return object with text attribute best case value changed", function() {
                currentModel.set('best_case', 1000);
                currentModel.set('likely_case', 900);
                currentModel.set('date_entered', '2012-07-12 18:37:36');

                previousModel.set('best_case', 900);
                previousModel.set('likely_case', 900);
                previousModel.set('date_entered', '2012-07-12 18:37:36');

                result = view.createHistoryLog(currentModel, previousModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_CHANGED').toBeTruthy();
            });
        });

        describe("should show likely values changed", function() {
            it("should return object with text attribute likely case value changed", function() {
                currentModel.set('best_case', 1000);
                currentModel.set('likely_case', 900);
                currentModel.set('date_entered', '2012-07-12 18:37:36');

                previousModel.set('best_case', 1000);
                previousModel.set('likely_case', 800);
                previousModel.set('date_entered', '2012-07-12 18:37:36');

                result = view.createHistoryLog(currentModel, previousModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED').toBeTruthy();
            });
        });

        describe("should show no values changed", function() {
            it("should return object with text attribute no value changed", function() {
                currentModel.set('best_case', 1000);
                currentModel.set('likely_case', 900);
                currentModel.set('date_entered', '2012-07-12 18:37:36');

                previousModel.set('best_case', 1000);
                previousModel.set('likely_case', 900);
                previousModel.set('date_entered', '2012-07-12 18:37:36');

                result = view.createHistoryLog(currentModel, previousModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_NONE_CHANGED').toBeTruthy();
            });
        });


    });
});
