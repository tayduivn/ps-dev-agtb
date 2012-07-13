describe("The forecasts worksheet", function(){

    var app, view, field, _renderClickToEditStub, _renderFieldStub, testMethodStub;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../../../../clients/forecasts/views/forecastsWorksheet", "forecastsWorksheet", "js", function(d) { return eval(d); });
        var cte = SugarTest.loadFile("../../../../clients/forecasts/lib", "ClickToEdit", "js", function(d) { return eval(d); });
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'worksheet',
                def:{
                    clickToEdit:true
                }
            };
        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
            testMethodStub.restore();
        })

        describe("should render", function() {
            beforeEach(function() {
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function() {
                testMethodStub.restore();
            });


            it("has clickToEdit set to true in metadata and a user is viewing their own worksheet", function() {
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).toHaveBeenCalled();
            });
        });

        describe("should not render", function() {
            beforeEach(function(){
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function(){
                testMethodStub.restore();
            });

            it("does not contain a value for clickToEdit in metadata", function() {
                field = {
                    viewName:'worksheet',
                    def:{}
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to something other than true in metadata", function() {
                field = {
                    viewName:'worksheet',
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
                    viewName:'worksheet',
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

            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                testMethodStub.restore();
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return false;
                });
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });
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
        });

        describe("should return true", function() {
            it("is a user viewing their own worksheet", function() {
                view.selectedUser = {
                    id: 'a_user_id'
                };
                expect(view.isMyWorksheet()).toBeTruthy();
            });
        });

        describe("should return false", function() {
            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                view.selectedUser = {
                    id: 'a_different_user_id'
                };
                expect(view.isMyWorksheet()).toBeFalsy();
            });

            it("receives a selectedUser that is not the expected object", function() {
                view.selectedUser = 'a_user_id';
                expect(view.isMyWorksheet()).toBeFalsy();
            });
        });
    });

    describe("forecast column", function() {
        beforeEach(function(){
            field = [
                {
                    name: 'forecast',
                    enabled: true
                },
                {
                    name: 'commit_stage',
                    enabled: true
                }
            ]
        });

        it("should be the 'forecasts' field if showBuckets is false", function() {
            app.config.showBuckets = false;
            var unused = view._setForecastColumn(field);
            expect(unused).toEqual(field[1]);
            expect(field[0].enabled).toBeTruthy();
            expect(field[1].enabled).toBeFalsy();
        });

        it("should be the 'commit_stage' field if showBuckets is true", function() {
            app.config.showBuckets = true;
            var unused = view._setForecastColumn(field);
            expect(unused).toEqual(field[0]);
            expect(field[0].enabled).toBeFalsy();
            expect(field[1].enabled).toBeTruthy();
        });
    })
});