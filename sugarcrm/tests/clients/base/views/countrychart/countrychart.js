describe("Country Chart", function() {
    var app, view;
    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        // SugarTest.loadComponent('base', 'view', 'countrychart');
        app = SugarTest.app;
        view = SugarTest.createView("base","Cases", "countrychart");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var apiCallStub,
                renderStub = sinon.stub(view, 'render');

            apiCallStub = sinon.stub(app.api, 'call', function(we,dont,care, cb) {
                cb.success({});
            });

            view.loadData();
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            view.disposed = true;
            view.loadData();
            expect(renderStub).not.toHaveBeenCalled();
        });
    });
});
