describe("forecast bool field", function() {

    describe("test detail (default) view", function() {
        it("should show detail (default) view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "bool",
                "view": "detail"
            };
            var controller = SugarFieldTest.loadSugarField('bool/bool');
            this.field = SugarFieldTest.createField("forecast", "bool", "detail", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field = _.extend(this.field, controller);
            this.field.def = fieldDef;
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });

    describe("test edit view", function() {
        it("should show edit view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "bool",
                "view": "edit"
            };
            var controller = SugarFieldTest.loadSugarField('bool/bool');
            this.field = SugarFieldTest.createField("forecast", "toggle", "edit", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field = _.extend(this.field, controller);
            this.field.def = fieldDef;
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });
});
