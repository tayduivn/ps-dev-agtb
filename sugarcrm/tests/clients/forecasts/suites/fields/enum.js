describe("forecasts commit_stage enum", function() {

    beforeEach(function() {
        app = SugarTest.app;
    });

    it("should have a save handler", function() {
        var controller = SugarFieldTest.loadSugarField('enum/enum'),
        field = SugarFieldTest.createField("foo", "enum", "forecastsWorksheet");
        field = _.extend(field, controller);
        expect(field._save).toBeDefined();
    })

    //this is not adding the stub/spy properly, skipping until fixed.
    xdescribe("when rendered in a forecastsWorksheet view", function() {
        it("change handler should be added ", function(){
            var controller = SugarFieldTest.loadSugarField('enum/enum'),
                field = SugarFieldTest.createField("foo", "enum", "forecastsWorksheet");
            field = _.extend(field, controller);
            var renderStub = sinon.stub(field.app.view.Field.prototype, "_render", function() {
                return this;
            })
            field.el = '<select name="test">' +
                '<option value="" selected></option>'+
                '<option value="50">Ommit</option>'+
                '<option value="75">Likely</option>'+
                '<option value="100">Include</option>'+
            '</select>';
            field.$el = $(field.el);
            var changeSpy = sinon.spy(field.$el, "change");
            field._render();
            expect(changeSpy).toHaveBeenCalled();
            changeSpy.restore();
        });

    });

    //this is not adding the stub/spy properly, skipping until fixed.
    xdescribe("when rendered in a view that is not a forecastsWorksheet view", function() {
        //this is not adding the spy properly, skipping until fixed.
        it("should not be added when rendered in a view that is not a forecastsWorksheet view", function(){
            var controller = SugarFieldTest.loadSugarField('enum/enum'),
                field = SugarFieldTest.createField("foo", "enum", "detail");
            field = _.extend(field, controller);
            var renderStub = sinon.stub(field.app.view.Field.prototype, "_render", function() {
                return this;
            })
            field.el = '<select name="test">' +
                '<option value="" selected></option>'+
                '<option value="50">Ommit</option>'+
                '<option value="75">Likely</option>'+
                '<option value="100">Include</option>'+
            '</select>';
            field.$el = $(field.el);
            var changeSpy = sinon.spy(field.$el, "change");
            field._render();
            expect(changeSpy).not.toHaveBeenCalled();
            changeSpy.restore();
        });
    })

});