describe("Forecasts Commit Buttons Component", function(){

    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/forecasts/metadata/base/views", "forecastsCommitButtons", "js", function(d) { return eval(d); });

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
                expect(view.checkShowCommitButton('a_user_id')).toBeTruthy();
            });
        });

        describe("should not show commit button", function() {
            it("is a user not viewing their own forecast log", function() {
                expect(view.checkShowCommitButton('a_different_user_id')).toBeFalsy();
            });
        });
    });
});