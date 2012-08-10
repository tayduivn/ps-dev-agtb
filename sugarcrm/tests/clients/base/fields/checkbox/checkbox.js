describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","checkbox", "bool", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("checkbox", function() {
        it("should format the value", function() {
            expect(field.format("0")).toEqual(false);
            expect(field.format("1")).toEqual(true);
        });
    });
});

