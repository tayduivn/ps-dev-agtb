describe("View Manager", function() {
    var app, views, context;

    beforeEach(function() {
        SugarTest.seedApp(true);
        app = SugarTest.app;
        SugarTest.seedMetadata(true);
        views = app.view.views;
        context = app.context.getContext();
    });

    afterEach(function() {
        app.view.views = views;
    });

    describe("should be able to create instances of View class which is", function() {

        it('base class', function () {
            var view = app.view.createView({
                name: "edit",
                module: "Contacts",
                context: context
            });

            expect(view).toBeDefined();
            expect(view instanceof app.view.View).toBeTruthy();
            expect(view.meta).toEqual(fixtures.metadata.modules.Contacts.views.edit);
        });

        it('pre-defined view class', function () {
            var view = app.view.createView({
                name: "list",
                module: "Contacts",
                context: context
            });

            expect(view).toBeDefined();
            expect(view instanceof app.view.views.ListView).toBeTruthy();
        });

        it("custom view class when the view has a custom controller", function () {
            var result = app.view.createView({
                name : "login",
                module: "Home",
                context: context
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
                meta: testMeta,
                context: context
            });

            expect(view.meta).toEqual(testMeta);
        });

        it('custom class without metadata', function() {
            app.view.views.ToolbarView = app.view.View.extend();

            var view = app.view.createView({
                name: "toolbar",
                context: context
            });

            expect(view instanceof app.view.views.ToolbarView).toBeTruthy();
        });


    });

});
