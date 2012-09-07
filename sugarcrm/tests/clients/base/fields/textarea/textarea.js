describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","textarea", "textarea", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("textarea", function() {
        it("should show new lines in details view (Bug56187)", function() {
            expect(field.format("blah\nblah")).toEqual(new Handlebars.SafeString("blah<BR>blah"));
            expect(field.format("\nblah\n\n")).toEqual(new Handlebars.SafeString("<BR>blah<BR><BR>"));
            expect(field.format("blah")).toEqual(new Handlebars.SafeString("blah"));
        });

    });
});