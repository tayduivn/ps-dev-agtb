describe('Base.Field.Rowaction', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        field = SugarTest.createField("base","rowaction", "rowaction", "edit", {
            'type':'rowaction',
            'css_class':'btn',
            'tooltip':'LBL_PREVIEW',
            'event':'list:preview:fire',
            'icon':'icon-eye-open',
            'acl_action':'view'
        }, moduleName);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    //Disabling this test as its testing if a function called by calling that function directly
    //Essentially a no-op test
    xit('should render action if the user has acls', function() {
        var aclStub = sinon.stub(app.acl, "hasAccessToModel", function() {
            return true;
        });
        var stub_render = sinon.stub(app.view.fields.BaseButtonField.prototype, "_render");
        field.module = moduleName;
        field._render();
        expect(stub_render).toHaveBeenCalled();
        stub_render.restore();
        aclStub.restore();
    });

    it('should hide action if the user doesn\'t have acls', function() {
        field.model = app.data.createBean(moduleName);
        var aclStub = sinon.stub(app.acl, "hasAccessToModel", function() {
            return false;
        });
        field.render();
        expect(field.isHidden).toBeTruthy();
        aclStub.restore();
    });
});
