describe("Forecasts.Views.Info", function() {
    var view, layout, moduleName = "Forecasts", sbox;
    
    beforeEach(function() {
        layout = SugarTest.createLayout("base", "ForecastWorksheets", "list", null, null);
        view = SugarTest.createView("base", moduleName, "info", null, null, true, layout, true);
    });
    
    afterEach(function() {
        view = null;
        layout = null;
    });
    
    describe("when resetSelection is called", function() {
        beforeEach(function() {
            sbox = sinon.sandbox.create();
            view.fields = [{
                        name:"selectedTimePeriod",
                        render: function(){},
                        dispose: function(){}
            }];
            sbox.spy(view.fields[0], "render");
            sbox.stub(view.tpModel, "set", function(){});
            sbox.stub(view, "dispose", function(){});
           
            view.resetSelection();
        });
        
        afterEach(function() {
            sbox.restore();
        });
        
        it("should have called render", function() {
            expect(view.fields[0].render).toHaveBeenCalled();
        });
        
        it("should have called set on tpModel", function() {
            expect(view.tpModel.set).toHaveBeenCalled();
        });
    });
});
