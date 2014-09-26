describe('Base.Field.Name', function() {
    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'name', 'name', 'detail', {});
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('Render', function() {
        using('different view names', [
            {
                view: 'record',
                value: undefined,
                expected: false
            },
            {
                view: 'preview',
                value: undefined,
                expected: true
            },
            {
                view: 'preview',
                value: false,
                expected: false
            }
        ], function(options) {
            it('should set def.link appropriately on preview and record view', function() {
                field.view.name = options.view;
                field.def.link = options.value;
                field.render();
                expect(field.def.link).toEqual(options.expected);
            });
        });
    });
});
