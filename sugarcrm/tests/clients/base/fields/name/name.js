describe("Base.Field.Name", function() {
    var app, field, sinonSandbox, fieldName;

    beforeEach(function() {
        app = SugarTest.app;
        app.view.Field.prototype._renderHtml = function() {};
        sinonSandbox = sinon.sandbox.create();
        fieldName = "name";
        field = SugarTest.createField("base", fieldName, "name", "detail", {});
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.model = null;
        field._loadTemplate = null;
        field = null;
        sinonSandbox.restore();
    });

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
