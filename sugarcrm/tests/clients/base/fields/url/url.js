describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","url", "url", "edit");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("url", function() {
        it("should add http if missing on format and leave https and http alone", function() {
            var completeURL = "http://www.google.com";
            var completeHttpsURL = "https://www.google.com";
            var incompURL = "www.google.com";
            expect(field.format(completeURL)).toEqual(completeURL);
            expect(field.format(completeHttpsURL)).toEqual(completeHttpsURL);
            expect(field.format(incompURL)).toEqual(completeURL);
        });
    });
});
