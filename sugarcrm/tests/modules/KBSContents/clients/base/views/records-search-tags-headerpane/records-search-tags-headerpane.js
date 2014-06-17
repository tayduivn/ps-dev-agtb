describe('KBSContents.Base.Views.RecordsSearchTagsHeaderpane', function() {

    var app, view, sandbox, context, drawer, moduleName = 'KBSContents';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        context = app.context.getContext({
            module: moduleName
        });
        SugarTest.loadComponent(
            'base',
            'view',
            'records-search-tags-headerpane',
            moduleName
        );
        SugarTest.loadHandlebarsTemplate(
            'headerpane',
            'view',
            'base'
        );
        view = SugarTest.createView(
            'base',
            moduleName,
            'records-search-tags-headerpane',
            {
                template: 'headerpane',
                title: 'LBL_MODULE_NAME',
                buttons: [
                    {
                        name: 'cancel_button',
                        type: 'button',
                        label: 'LBL_CANCEL_BUTTON_LABEL',
                        css_class: 'btn-invisible btn-link'
                    },
                    {
                        name: 'sidebar_toggle',
                        type: 'sidebartoggle'
                    }
                ]
            },
            context,
            moduleName
        );
        drawer = app.drawer;
        app.drawer = {
            close: function() {}
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

    it('should close drawer when call cancel()', function() {
        var drawerCloseStub = sandbox.stub(app.drawer, 'close');
        view.cancel();
        expect(drawerCloseStub).toHaveBeenCalled();
    });
});
