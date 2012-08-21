describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","int", "int", "detail", { number_group_seperator: "," });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("integer", function() {
        it("should format the value", function() {
            expect(field.format("123456.502")).toEqual("123,457");
            expect(field.unformat("123456.498")).toEqual("123456");
        });

    });
});
