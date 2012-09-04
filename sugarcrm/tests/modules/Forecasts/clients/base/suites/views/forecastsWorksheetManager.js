describe("The forecasts manager worksheet", function(){

    var view, field, _renderClickToEditStub, _renderFieldStub, testMethodStub;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsWorksheetManager", "forecastsWorksheetManager", "js", function(d) { return eval(d); });
        var cte = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ClickToEdit", "js", function(d) { return eval(d); });
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'forecastsWorksheetManager',
                def:{
                    clickToEdit:true
                }
            };
        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
        })

        describe("should render", function() {
            beforeEach(function() {
                view.selectedUser.id = "test_user_id";
                testMethodStub = sinon.stub(app.user, "get", function(property){
                    var user = {
                        id: "test_user_id"
                    }
                    return user[property];
                });
            });

            afterEach(function() {
                view.selectedUser.id = null;
                testMethodStub.restore();
            })

            it("has clickToEdit set to true in metadata", function() {
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).toHaveBeenCalled();
            });
        });

        describe("should not render", function() {
            it("does not contain a value for clickToEdit in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{}
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to something other than true in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{
                        clickToEdit: 'true'
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to false in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{
                        clickToEdit: false
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("is an edit view", function() {
                field = {
                    viewName:'edit',
                    def:{
                        clickToEdit: true
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });
        });

    });
});