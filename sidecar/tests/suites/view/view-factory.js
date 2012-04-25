describe("View Manager", function() {
    var app, views;

    beforeEach(function() {
        SugarTest.seedApp(true);
        app = SugarTest.app;
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
            expect(view.meta).toEqual(fixtures.metadata.modules.Contacts.views.edit);
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

        it('base class with custom metadata', function() {
            var testMeta = {
                "panels": [
                    {
                        "label": "TEST",
                        "fields": []
                    }
                ]
            };

            var view = app.view.createView({
                name: "edit",
                meta: testMeta
            });

            expect(view.meta).toEqual(testMeta);
        });

        it('custom class without metadata', function() {
            app.view.views.ToolbarView = Backbone.View.extend();

            var view = app.view.createView({
                name: "toolbar"
            });

            expect(view instanceof app.view.views.ToolbarView).toBeTruthy();
        });


    });

});
