describe('View.Fields.LinkAction', function() {

    var app, field, sandbox, relatedFields, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'link-action');
        field = SugarTest.createField("base", "link-action", "link-action", "edit", {
            'type':'rowaction',
            'tooltip':'Link'
        }, moduleName);

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.data, "getRelateFields", function(){
            return relatedFields;
        });
        relatedFields = [{required: false}];
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
        sandbox.restore();
    });

    it('should hide action if the user does not have access', function() {
        field.model = app.data.createBean(moduleName);
        var aclStub = sinon.stub(app.acl, "hasAccessToModel", function() {
            return false;
        });
        field.render();
        expect(field.isHidden).toBeTruthy();
        aclStub.restore();
    });

    it('should hide action if any related field is required', function() {
        field.model = app.data.createBean(moduleName);
        relatedFields = [{required: true}];
        field.render();
        expect(field.isHidden).toBeTruthy();

        relatedFields = [{required: false}, {required: true}];
        field.render();
        expect(field.isHidden).toBeTruthy();

        relatedFields = [{required: false}, {required: false}];
        field.render();
        expect(field.isHidden).toBeFalsy();
    });

});
