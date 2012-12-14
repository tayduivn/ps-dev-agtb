describe("Portal Login View", function() {

    var view, app;

    beforeEach(function() {
        view = SugarTest.createView("portal","Login", "login");
        view.model = new Backbone.Model();
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("login", function() {

        it("should have metadata defined", function() {
            expect(view.meta).toBeDefined();
            expect(view.meta.buttons).toBeDefined();
            expect(view.meta.panels).toBeDefined();
        });

    });
});
