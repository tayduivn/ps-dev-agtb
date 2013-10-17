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
        field.view = {trigger: function(){}};
        field.layout = {trigger: function(){}};
    });

    afterEach(function() {
        field.view = null;
        field.layout = null;
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

    describe('rowActionSelect', function(){
        var e, triggerStub;

        beforeEach(function(){
            //Wire up event
            e = jQuery.Event("click");
            e.currentTarget = field.$el.get(0);
            field.$el.data('event', field.def.event);
        });

        afterEach(function(){
            triggerStub.restore();
        });

        it('should trigger event on view\'s context by default', function() {
            field.model = app.data.createBean(moduleName);
            field.view.context = {trigger: function(){}};
            triggerStub = sinon.spy(field.view.context, 'trigger');
            field.rowActionSelect(e);
            expect(triggerStub.calledOnce).toBe(true);
        });

        it('should trigger event on view\'s layout when "layout" is set as target', function() {
            field.def.target = 'layout';
            field.model = app.data.createBean(moduleName);
            field.view.layout = {trigger: function(){}};
            triggerStub = sinon.spy(field.view.layout, 'trigger');
            field.rowActionSelect(e);
            expect(triggerStub.calledOnce).toBe(true);
        });

        it('should trigger event on view when "view" is set as target', function() {
            field.def.target = 'view';
            field.model = app.data.createBean(moduleName);
            field.view.trigger =  function(){};
            triggerStub = sinon.spy(field.view, 'trigger');
            field.rowActionSelect(e);
            expect(triggerStub.calledOnce).toBe(true);
        });
    });

});
