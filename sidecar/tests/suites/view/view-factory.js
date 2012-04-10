describe("View Manager", function() {
    var app = SUGAR.App;

    beforeEach(function() {
        app.metadata.set(fixtures.metadata);
    });

    describe("should be able to create instances of View class which is", function() {

        it('base class', function () {
            expect(app.view.createView({
                name : "edit",
                module: "Contacts"
            })).not.toBe(null);
        });

        it("custom view class when the view has a custom controller", function () {
            var result = app.view.createView({
                name : "login",
                module: "Home"
            });

            expect(result).toBeDefined();
            expect(result.customCallback).toBeDefined();
            expect(app.view.views.HomeLoginView).toBeDefined();
        });

    });

});