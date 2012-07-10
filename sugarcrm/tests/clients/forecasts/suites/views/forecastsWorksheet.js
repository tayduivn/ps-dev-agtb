describe("The forecasts worksheet", function(){

    var view, field, _renderClickToEditStub, _renderFieldStub, testMethodStub;

    beforeEach(function() {
        SugarTest.seedApp();
        app = SugarTest.app;
        view = SugarTest.loadFile("../../../../clients/forecasts/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            _renderClickToEditStub = sinon.stub(view, "_renderClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                'viewName':'worksheet',
                'def':{
                    'clickToEdit':true
                }
            };

        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
            testMethodStub.restore();
        })

        it("should render if a user is viewing their own worksheet", function() {
            testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                return true;
            });
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderClickToEditStub).toHaveBeenCalled();
        });

        it("should not render if a user is not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
            testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                return false;
            });
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderClickToEditStub).not.toHaveBeenCalled();
        });
    });

    describe("isMyWorksheet method", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function(id) {
                return 'a_user_id';
            });
        });

        afterEach(function(){
            testMethodStub.restore();
            view.selectedUser = '';
        })

        it("should return true if a user is viewing their own worksheet", function() {
            view.selectedUser = {
                id: 'a_user_id'
            };
            expect(view.isMyWorksheet()).toBeTruthy();
        });

        it("should return false if a user is not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
            view.selectedUser = {
                id: 'a_different_user_id'
            };
            expect(view.isMyWorksheet()).toBeFalsy();
        });

        it("should return false if a selectedUser is not the expected object", function() {
            view.selectedUser = 'a_user_id';
            expect(view.isMyWorksheet()).toBeFalsy();
        });
    });
});