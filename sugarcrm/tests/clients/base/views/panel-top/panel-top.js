describe("PanelTop View", function() {
    var app, view, context, sinonSandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();
        SugarTest.loadComponent('base', 'view', 'panel-top');
        var parentContext = app.context.getContext();
        parentContext.set("module", "Accounts");
        context = app.context.getContext();
        context.parent = parentContext;
        view = SugarTest.createView("base","Contacts", "panel-top", null, context);
        view.model = new Backbone.Model();
    });
    afterEach(function() {
        sinonSandbox.restore();
        app.cache.cutAll();
        app.view.reset();
        view.dispose();
        delete Handlebars.templates;
        view.model = null;
        view = null;
    });

    describe('Toggle panel', function() {
        var isADropdownElement, notADropdownElement, myTarget, parentsStub, _toggleSubpanelStub;

        beforeEach(function() {
            notADropdownElement = [];
            isADropdownElement = ['has', 'span', 'actions'],
            myTarget = {a:'b'};
            parentsStub = sinonSandbox.stub();
            _toggleSubpanelStub = sinonSandbox.stub(view, '_toggleSubpanel');
        });
        afterEach(function() {
            notADropdownElement = null,
            myTarget = null;
        });

        it('should toggle panel if clicking anywhere on panel top', function() {
            parentsStub.withArgs('span.actions').returns(notADropdownElement);
            sinonSandbox.stub(jQuery.prototype, "parents", parentsStub);
            view.togglePanel({target: myTarget});
            expect(_toggleSubpanelStub).toHaveBeenCalled();
        });
        it('should NOT toggle panel if clicking on dropdown actions', function() {
            parentsStub.withArgs('span.actions').returns(isADropdownElement);
            sinonSandbox.stub(jQuery.prototype, "parents", parentsStub);
            view.togglePanel({target: myTarget});
            expect(_toggleSubpanelStub).not.toHaveBeenCalled();
        });
    });
    describe('Create Link model', function() {
        var parentModel, createBeanStub, relateFieldStub;

        beforeEach(function() {
            parentModel = new Backbone.Model({
                id: '101-model-id',
                name: 'parent product name',
                account_id: 'abc-111-2222',
                account_name: 'parent account name',
                assigned_user_name: 'admin'
            }),
            createBeanStub = sinonSandbox.stub(app.data, 'createRelatedBean', function() {
               return new Backbone.Model();
            }),
            relateFieldStub = sinonSandbox.stub(app.data, 'getRelateFields', function() {
                return [{
                    name: 'product_template_name',
                    rname: 'name',
                    id_name: 'product_template_id',
                    populate_list: {
                        account_id: 'account_id',
                        account_name: 'account_name',
                        assigned_user_name: 'user_name'
                    }
                }];
            });
        });
        afterEach(function() {
            parentModel = null;
        });

        it('should populate related fields when it creates linked record', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel.get('product_template_id')).toBe(parentModel.get('id'));
            expect(newModel.get('product_template_name')).toBe(parentModel.get('name'));
            expect(newModel.get('account_id')).toBe(parentModel.get('account_id'));
            expect(newModel.get('account_name')).toBe(parentModel.get('account_name'));
            expect(newModel.get('user_name')).toBe(parentModel.get('assigned_user_name'));
        });
        it('should store the relate fields in default to keep the values for [Save and create new]', function() {
            var newModel = view.createLinkModel(parentModel, 'blah');
            expect(newModel._defaults['product_template_id']).toBe(parentModel.get('id'));
            expect(newModel._defaults['product_template_name']).toBe(parentModel.get('name'));
            expect(newModel._defaults['account_id']).toBe(parentModel.get('account_id'));
            expect(newModel._defaults['account_name']).toBe(parentModel.get('account_name'));
            expect(newModel._defaults['user_name']).toBe(parentModel.get('assigned_user_name'));
        });
    });
});
