describe('modules.kbcontents.clients.base.fields.enum-config', function() {
    var app, field, sandbox,
        module = 'KBContents',
        fieldName = 'language',
        fieldType = 'enum-config',
        model;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('base', fieldName, fieldType, 'edit', {}, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        model = null;
        field = null;
    });

    it('should call loadEnumOptions and set items during render', function() {
        var loadEnumSpy = sandbox.spy(field, 'loadEnumOptions');
        expect(field.items).toBeNull();
        field.render();
        expect(loadEnumSpy.called).toBe(true);
        expect(field.items).toEqual({});
    });

    it('should be disabled if model is not new record', function () {
        model.set({
            id: 'test_id'
        });
        field.setMode('edit');
        expect(field.action).toEqual('disabled');
    });

});
