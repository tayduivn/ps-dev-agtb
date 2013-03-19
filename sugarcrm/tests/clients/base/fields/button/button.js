describe("Base.Field.Button", function() {
    var app, field, Address;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('base', 'field', 'button');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field.model = null;
        field._loadTemplate = null;
        field = null;
        Address = null;
    });

    it('should bind custom event handlers', function() {
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };

        field = SugarTest.createField("base","button", "button", "edit", def);

        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

        field.render();
        expect(field.callback).toBeUndefined();
        field.$(".btn").trigger('click');
        expect(field.callback).toBe("stuff excuted");
        field.$(".btn").trigger('blur');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).toBe("blur excuted");
        delete field.callback;
        field.$(".btn").trigger('undefined');
        expect(field.callback).not.toBe("stuff excuted");
        expect(field.callback).not.toBe("blur excuted");
        expect(field.callback).toBeUndefined();

        delete field.callback;
        field.setDisabled(true);
        field.$(".btn").trigger('click .btn');
        expect(field.callback).not.toBe("stuff excuted");
    });

    it("should setDisabled with CSS 'disabled'", function() {
        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field._loadTemplate = function() {  this.template = function(){ return '<a class="btn" href="javascript:void(0);"></a>'}; };

        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.render();
        field.setDisabled(true);
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
        field.setDisabled(false);
        expect(field.getFieldElement().hasClass("disabled")).toBeFalsy();
        field.setDisabled();
        expect(field.getFieldElement().hasClass("disabled")).toBeTruthy();
    });

    it("should show and hide functions must trigger hide and show events, and it should change the isHidden property", function() {

        var def = {
            'events' : {
                'click .btn' : 'function() { this.callback = "stuff excuted"; }',
                'blur .btn' : 'function() { this.callback = "blur excuted"; }'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var triggers = sinon.spy(field, 'trigger');
        field.show();
        expect(triggers.calledOnce).toBe(true);
        expect(triggers.calledWithExactly('show')).toBe(true);
        expect(field.isHidden).toBe(false);
        triggers.restore();

        var triggers2 = sinon.spy(field, 'trigger');
        field.hide();
        expect(triggers2.calledOnce).toBe(true);
        expect(triggers2.calledWithExactly('hide')).toBe(true);
        expect(field.isHidden).toBe(true);
        triggers2.restore();

    });

    it('should call app.acl.hasAccessToModel if acl_module is not specified', function() {
        var def = {
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stub_hasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stub_hasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stub_hasAccess).not.toHaveBeenCalled();
        expect(stub_hasAccessToModel).toHaveBeenCalled();
        expect(access).toBeFalsy();

        stub_hasAccess.restore();
        stub_hasAccessToModel.restore();
    });

    it('should call app.acl.hasAccess if acl_module is specified', function() {
        var def = {
            'acl_module' : 'Contacts',
            'acl_action' : 'edit'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        var stub_hasAccess = sinon.stub(app.acl, "hasAccess").returns(true);
        var stub_hasAccessToModel = sinon.stub(app.acl, "hasAccessToModel").returns(false);

        var access = field.triggerBefore('render');
        expect(stub_hasAccess).toHaveBeenCalled();
        expect(stub_hasAccessToModel).not.toHaveBeenCalled();
        expect(access).toBeTruthy();

        stub_hasAccess.restore();
        stub_hasAccessToModel.restore();

    });

    it("should differenciate string routes from sidecar route object", function() {
        var def = {
            'route' : {
                'action' : 'edit'
            }
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.full_route).toBeNull();

        def = {
            'route' : 'custom/route'
        };
        field = SugarTest.createField("base","button", "button", "edit", def);
        field.render();
        expect(field.full_route).toEqual('custom/route');
    });
});
