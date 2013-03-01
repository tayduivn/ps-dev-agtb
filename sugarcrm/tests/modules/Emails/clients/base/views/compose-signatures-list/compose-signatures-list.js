describe("Emails.Views.ComposeSignaturesList", function() {
    var app,
        context,
        view;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent("base", "view", "list");
        SugarTest.loadComponent("base", "view", "flex-list");
        SugarTest.loadComponent("base", "view", "selection-list");
        SugarTest.loadComponent("base", "view", "compose-signatures-list", "Emails");
        SugarTest.testMetadata.set();

        context = app.context.getContext();
        context.set({
            module:     "Emails",
            create:     true,
            collection: new app.BeanCollection()
        });
        context.prepare();

        view = SugarTest.createView("base", "Emails", "compose-signatures-list", null, context, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    it("should call _sync to add a custom endpoint during a sync and the custom endpoint should be used", function() {
        var apiStub = sinon.stub(app.api, "call");

        view.collection.fetch();
        expect(apiStub.lastCall.args[1]).toMatch(/.*\/Signatures\?.*/);

        apiStub.restore();
    });
});
