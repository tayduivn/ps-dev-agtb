describe("Leads ConvertButton", function() {
    var app, field, context;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();

        var def = {'name':'record-convert','type':'convertbutton', 'view':'detail'};
        var Lead = Backbone.Model.extend({});
        var model = new Lead({
            id: 'aaa',
            name: 'boo',
            module: 'Leads'
        });
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField("../modules/Leads/clients/base", 'record-convert', "convertbutton", "detail", def, 'Leads', model, context);

    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field = null;
        context = null;
    });

    it('should show if not converted', function() {
        field.model.set('converted', false);
        field._render();
        expect(field.isHidden).toBeFalsy();
    });

    it('should be hidden if converted', function() {
        field.model.set('converted', true);
        field._render();
        expect(field.isHidden).toBeTruthy();
    });
});
