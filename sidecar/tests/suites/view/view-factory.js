describe("View Manager", function() {
    var app;

    beforeEach(function() {
        app = sugarApp;
        app.metadata.set(fixtures.metadata);
    });

    describe("should be able to create instances of View class which is", function() {

        it('base class', function () {
            var view = app.view.createView({
                name: "edit",
                module: "Contacts"
            });

            expect(view).toBeDefined();
            expect(view instanceof app.view.View).toBeTruthy();
        });

        it('pre-defined view class', function () {
            var view = app.view.createView({
                name: "list",
                module: "Contacts"
            });

            expect(view).toBeDefined();
            expect(view instanceof app.view.views.ListView).toBeTruthy();
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
