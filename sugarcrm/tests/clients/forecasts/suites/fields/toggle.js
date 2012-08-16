describe("toggle field", function() {

    describe("test detail view", function() {
        it("should show detail view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "toggle",
                "view": "detail"
            };
            this.field = SugarTest.createField("forecast","checkbox", "toggle", "detail", fieldDef);
            var model = new Backbone.Model({forecast: true});
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
            this.field = SugarTest.createField("forecast","checkbox", "toggle", "default", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });
});
