describe("Merge Duplicates", function () {

    var view, layout, app;

    beforeEach(function () {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'view', 'list');
        layout = SugarTest.createLayout('base', 'Cases', 'list');
        view = SugarTest.createView("base", "Contacts", "massupdate", null, null, null, layout);
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view.model = null;
        view = null;
        layout.dispose();
    });

    it("Should Flatten Fieldsets Properly", function() {

    });
});

