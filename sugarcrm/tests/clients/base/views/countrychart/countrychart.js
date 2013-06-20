describe("Country Chart", function() {
    var app, view, moduleName = 'Cases', viewName = 'countrychart', layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'dashboard');
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        layout = SugarTest.createLayout("base", moduleName, "dashboard");
        view = SugarTest.createView("base", moduleName, viewName, null, null, null, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
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
            apiCallStub.restore();
        });
    });
});
