describe("Subpanel List View", function() {
    var app, layout, view, sinonSandbox;

    beforeEach(function () {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        app = SugarTest.app;
        layout = SugarTest.createLayout("base", "Cases", "list", null, null);
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        view = SugarTest.createView("base", 'Cases', 'subpanel-list', null, null, null, layout);
    });

    afterEach(function () {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
        layout = null;
    });

    describe('Toggle list', function() {
        var showStub, hideStub;
        beforeEach(function() {
            showStub = sinonSandbox.stub(view.$el, 'show');
            hideStub = sinonSandbox.stub(view.$el, 'hide');
        });
        it('should toggle list to show', function() {
            view.toggleList(true);
            expect(showStub).toHaveBeenCalled();
            expect(hideStub).not.toHaveBeenCalled();
        });
        it('should toggle list to hide', function() {
            view.toggleList(false);
            expect(showStub).not.toHaveBeenCalled();
            expect(hideStub).toHaveBeenCalled();
        });
    });

    describe('Subpanel metadata intiialization', function() {
        it('should return most specific subpanel view metadata if found', function() {
            var contextParentModuleStub = sinonSandbox.stub(view.options.context, "get").returns("Accounts");
            var expected = {a:1};
            var getViewStub = sinonSandbox.stub(app.metadata, 'getView').returns(expected);
            var actual = view._initializeMetadata();
            expect(actual).toEqual(expected);
            expect(getViewStub).toHaveBeenCalledThrice();
        });
    });

});
