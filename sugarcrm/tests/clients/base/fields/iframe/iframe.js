describe("iframe", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","iframe", "iframe", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("iframe", function() {
        it("should format the value", function() {
            expect(field.unformat("http://")).toEqual("");
            expect(field.unformat("http://www.google.com")).toEqual("http://www.google.com");
        });
    });
});
