describe("forecast bool field", function() {

    describe("test detail (default) view", function() {
        it("should show detail (default) view", function() {
            var fieldDef = {
                "name": "forecast",
                "type": "bool",
                "view": "detail"
            };
            this.field = SugarTest.createField("forecast","checkbox", "toggle", "detail", fieldDef);
            var model = new Backbone.Model({forecast: true});
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
            this.field = SugarTest.createField("forecast","checkbox", "toggle", "edit", fieldDef);
            var model = new Backbone.Model({forecast: true});
            this.field.model = model;
            expect(this.field.def.view).toEqual(fieldDef.view);
        });
    });
});
