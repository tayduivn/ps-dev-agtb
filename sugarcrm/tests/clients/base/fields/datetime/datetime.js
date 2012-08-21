describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","datetime", "datetime", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("datetime", function() {
        it("should format the value", function() {
            var unformatedValue, expectedValue, dtprefs;
            dtprefs = {
                datepref: "m/d/Y",
                timepref: "H:i"
            };
            var stub = sinon.stub(app.user, 'getUser', function(key) {
                return new Backbone.Model(dtprefs);
            });
            unformatedValue = new Date(2012, 3, 9, 9, 50, 58);
            expectedValue = "04/09/2012";
            expect(field.format(unformatedValue)).toEqual(expectedValue);
            stub.restore();
        });
    });
});
