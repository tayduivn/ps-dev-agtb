describe("The forecasts worksheet", function(){

    var view, field, _renderClickToEditStub, _renderFieldStub, isMyWorksheetStub;

    describe("clickToEdit field", function() {

        beforeEach(function() {
            SugarTest.seedApp();
            app = SugarTest.app;
            view = SugarTest.loadFile("../../../../clients/forecasts/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
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
            isMyWorksheetStub.restore();
        })

        it("should render if a user is viewing their own worksheet", function() {
            isMyWorksheetStub = sinon.stub(view, "isMyWorksheet", function() {
                return true;
            });
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderClickToEditStub).toHaveBeenCalled();
        });

        it("should not render if a user is not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
            isMyWorksheetStub = sinon.stub(view, "isMyWorksheet", function() {
                return false;
            });
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderClickToEditStub).not.toHaveBeenCalled();
        });
    });
});