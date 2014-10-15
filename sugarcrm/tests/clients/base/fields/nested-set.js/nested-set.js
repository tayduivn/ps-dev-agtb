describe('Base.Field.Nested-set', function() {
    var module = 'KBSContents',
        fieldDef = {
            category_root: '0',
            module_root: module
        },
        app, field, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'nested-set');
        SugarTest.loadPlugin('NestedSetCollection');
        SugarTest.loadHandlebarsTemplate('nested-set', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();

        app.data.declareModels();
        SugarTest.loadPlugin('JSTree');

        field = SugarTest.createField('base', 'nested-set', 'nested-set', 'edit', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        delete app.plugins.plugins['field']['JSTree'];
        delete app.plugins.plugins['field']['NestedSetCollection'];
        sinonSandbox.restore();
    });

    it('Should render tree and togle icon on render.', function() {
        var treeRenderSpy = sinonSandbox.stub(field, '_renderTree');
        var toggleSearchIconSpy = sinonSandbox.spy(field, 'toggleSearchIcon');

        field.render();

        expect(treeRenderSpy).toHaveBeenCalled();
        expect(toggleSearchIconSpy).toHaveBeenCalled();
    });

    it('Should render tree placeholder in edit mode only.', function() {
        // Render in detail mode.
        var fieldDetailMode = SugarTest.createField(
                'base', 'nested-set', 'nested-set', 'detail', fieldDef, module
            ),
            evt = {
                stopPropagation: sinonSandbox.spy(),
                preventDefault: sinonSandbox.spy()
            },
            deferStub = sinonSandbox.stub(_, 'defer', function() {});

        fieldDetailMode.render();
        fieldDetailMode.openDropDown(evt);

        expect(deferStub).not.toHaveBeenCalled();
    });

});
