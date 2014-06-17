describe('KBSContents.Base.Views.ConfigButtons', function() {

    var app, view, sandbox, context, drawer, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        SugarTest.loadComponent('base', 'view', 'config-buttons', moduleName);
        SugarTest.loadHandlebarsTemplate(
            'config-buttons',
            'view',
            'base',
            null,
            moduleName
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'config-buttons',
            null,
            context,
            moduleName
        );
        drawer = app.drawer;
        app.drawer = {
            close: $.noop
        };
    });

    afterEach(function() {
        sandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        Handlebars.templates = {};
        view = null;
        app.drawer = drawer;
    });

    describe('cancelClicked()', function() {
        var drawerCloseStub, disposeStub;

        beforeEach(function() {
            drawerCloseStub = sandbox.stub(app.drawer, 'close');
            disposeStub = sandbox.stub(view, 'dispose');
        });

        it('should close drawer when cancelClicked()', function() {
            view.cancelClicked();
            expect(drawerCloseStub).toHaveBeenCalled();
        });

        it('should dispose when cancelClicked()', function() {
            view.cancelClicked();
            expect(disposeStub).toHaveBeenCalled();
        });
    });
});
