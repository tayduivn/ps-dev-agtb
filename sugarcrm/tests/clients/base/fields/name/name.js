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

    it('should set def link true on preview and false on record view', function() {
        field.view.name = 'record';
        field.render();
        expect(field.def.link).toEqual(false);
        field.view.name = 'preview';
        field.render();
        expect(field.def.link).toEqual(true);
    });
});
