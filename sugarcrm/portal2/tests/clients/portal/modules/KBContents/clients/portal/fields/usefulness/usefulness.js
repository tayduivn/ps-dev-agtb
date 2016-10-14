describe('modules.kbcontents.clients.portal.fields.usefulness', function() {
    var sandbox;
    var app;
    var field;
    var module = 'KBContents';
    var fieldName = 'usefulness';
    var fieldType = 'usefulness';
    var model;

    beforeEach(function() {
        sandbox = sinon.sandbox.create();
        Handlebars.templates = {};
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        model = app.data.createBean(module);
        field = SugarTest.createField('portal', fieldName, fieldType, 'detail', {}, module, model, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;
        field = null;
    });

    it('should re-render view after model is synced to show proper user vote', function() {
        var voteSpy = sandbox.spy(field, 'render');
        model.trigger('data:sync:complete', {});

        expect(voteSpy).toHaveBeenCalledOnce();
    });
});
