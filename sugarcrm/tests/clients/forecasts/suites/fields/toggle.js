describe("toggle field", function() {

    beforeEach(function() {
        var fieldDef = {
            "name": "forecast",
            "type": "toggle"
        };
        var controller = SugarFieldTest.loadSugarField('toggle/toggle');
        this.field = SugarFieldTest.createField("forecast", "toggle");
        var model = new Backbone.Model({forecast: true});
        this.field = _.extend(this.field, controller);
        this.field.def = fieldDef;
        this.field.model = model;
    });

    describe("test detail view", function() {
        it("should show detail view", function() {
           var optionsDef = {viewName : 'detail'};
           this.field.options.def = optionsDef;
           var output = this.field._render();
           expect(output.options.viewName).toEqual(optionsDef.viewName);
        });
    });

    describe("test default (edit) view", function() {
        it("should show default (edit) view", function() {
           var optionsDef = {viewName : 'default'};
           this.field.options.def = optionsDef;
           var output = this.field._render();
           expect(output.options.viewName).toEqual(optionsDef.viewName);
        });
    });
});
