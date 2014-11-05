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
        Handlebars.templates = {};
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

    describe('triggering the event', function() {
        var e, sandbox;

        beforeEach(function() {
            e = $.Event('click');
            e.currentTarget = field.$el.get(0);

            field.view.context = {trigger: $.noop};
            sandbox = sinon.sandbox.create();
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should trigger the event defined in metadata', function() {
            var spy = sandbox.spy(field.view.context, 'trigger');
            field.propagateEvent(e);
            expect(spy).toHaveBeenCalledWith(field.def.event);
        });

        it('should trigger the event defined in the data-event attribute', function() {
            var spy = sandbox.spy(field.view.context, 'trigger');
            field.def.event = undefined;
            $(e.currentTarget).data('event', 'foo');
            field.propagateEvent(e);
            expect(spy).toHaveBeenCalledWith('foo');
        });

        it('should not trigger an event', function() {
            var spy = sandbox.spy(field.view.context, 'trigger');
            field.def.event = undefined;
            field.propagateEvent(e);
            expect(spy).not.toHaveBeenCalled();
        });

        using('event names', [undefined, 'context', 'foo'], function(eventName) {
            it('should return the context as the target on which to trigger the event', function() {
                field.view.context.name = 'context';
                field.def.target = eventName;
                expect(field.getTarget().name).toEqual(field.view.context.name);
            });
        });

        it('should return the view as the target on which to trigger the event', function() {
            field.view.name = 'view';
            field.def.target = 'view';
            expect(field.getTarget().name).toEqual(field.view.name);
        });

        it('should return the layout as the target on which to trigger the event', function() {
            field.view = {layout: {name: 'layout'}};
            field.def.target = 'layout';
            expect(field.getTarget().name).toEqual(field.view.layout.name);
        });
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
