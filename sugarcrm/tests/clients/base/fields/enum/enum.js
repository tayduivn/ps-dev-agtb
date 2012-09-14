describe("sugarfields", function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","enum", "enum", "edit");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("enum", function() {
        it("should format the default multi select values", function() {
            var defaultString = "^option1^,^option2^,^option3^";
            var value = field.convertMultiSelectDefaultString(defaultString);
            expect(value).toEqual(["option1","option2","option3"]);
        });
    });
});
