describe("toggle field", function() {

    describe("test detail view", function() {
        it("should show detail view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "toggle",
                "view": "detail"
            };
            var controller = SugarFieldTest.loadSugarField('toggle/toggle');
            this.field = SugarFieldTest.createField("forecast", "toggle", "detail", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field = _.extend(this.field, controller);
            this.field.def = fieldDef;
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });

    describe("test default (edit) view", function() {
        it("should show default (edit) view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "toggle",
                "view": "default"
            };
            var controller = SugarFieldTest.loadSugarField('toggle/toggle');
            this.field = SugarFieldTest.createField("forecast", "toggle", "default", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field = _.extend(this.field, controller);
            this.field.def = fieldDef;
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });
});
