describe("Quick Create Dropdown", function() {
    var viewName = 'quickcreate',
        app, view, isAuthenticatedStub, getModulesStub, getStringsStub, testMeta;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base",null, viewName, null, null);
        isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
            return true;
        });
        getStringsStub = sinon.stub(SugarTest.app.metadata, 'getStrings', function() {
            return {
                Accounts: {}
            };
        });
        testMeta = {
            Accounts: buildQuickCreateMeta('Accounts', true, 0),
            Contacts: buildQuickCreateMeta('Contacts', true, 1),
            Opportunities: buildQuickCreateMeta('Opportunities', true, 2)
        };
        getModulesStub = sinon.stub(SugarTest.app.metadata, 'getModules');
        getModulesStub.returns(testMeta);
    });

    var buildQuickCreateMeta = function(module, visible, order) {
        return {menu:{quickcreate:{meta:{module:module,visible:visible,order:order}}}};
    };

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        isAuthenticatedStub.restore();
        getModulesStub.restore();
        getStringsStub.restore();
        view = null;
    });

    var filterMenuItemsByModule = function(menuItems, module) {
        return _.filter(menuItems, function(menuItem) {
            return menuItem.module === module;
        });
    };

    it("Should build create actions for all modules", function() {
        var expectedModules = _.keys(SugarTest.app.metadata.getModules());
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
    });

    it("Should not build create actions even if visible meta attribute not specified", function() {
        delete testMeta.Accounts.menu.quickcreate.meta.visible;
        view.render();

        expect(filterMenuItemsByModule(view.createMenuItems, 'Accounts').length).toBe(0);
    });

    it("Should not build modules that don't have quickcreate meta", function() {
        var expectedModules = ['Accounts', 'Contacts', 'Opportunities'];
        testMeta.Foo = {};
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Foo').length).toBe(0);
    });

    it("Should not build create action for modules user does not have create access to", function() {
        var expectedModules = ['Contacts', 'Opportunities'],
            hasAccessStub = sinon.stub(SugarTest.app.acl, 'hasAccess', function(action, module) {
                // Sugar.App.acl.hasAccess is called with action=quickcreate as a part of rendering the view beyond
                // determining which modules are accessible. So we assume that TRUE should be returned for those calls
                // and to only be more scrupulous when action=create, which is expected per the
                // BaseQuickcreateView#_renderHtml call.
                if (action !== 'create') {
                    return true;
                }
                return (expectedModules.indexOf(module) > -1);
            });
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Accounts').length).toBe(0);
        hasAccessStub.restore();
    });

    it("Should not build create actions that are hidden", function() {
        var expectedModules = ['Accounts', 'Opportunities'];
        testMeta.Contacts.menu.quickcreate.meta.visible = false;
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Contacts').length).toBe(0);
    });

    it("Should build create actions based on order attribute", function() {
        view.render();

        _.each(view.createMenuItems, function(menuItem, index) {
            switch (index) {
                case 0:
                    expect(menuItem.module).toBe('Accounts');
                    break;
                case 1:
                    expect(menuItem.module).toBe('Contacts');
                    break;
                case 2:
                    expect(menuItem.module).toBe('Opportunities');
                    break;
            }
        });
    });

    it("Should change the order of create actions if it has been changed from default", function() {
        testMeta.Accounts.menu.quickcreate.meta.order = 2;
        testMeta.Contacts.menu.quickcreate.meta.order = 0;
        testMeta.Opportunities.menu.quickcreate.meta.order = 1;
        view.render();

        _.each(view.createMenuItems, function(menuItem, index) {
            switch (index) {
                case 0:
                    expect(menuItem.module).toBe('Contacts');
                    break;
                case 1:
                    expect(menuItem.module).toBe('Opportunities');
                    break;
                case 2:
                    expect(menuItem.module).toBe('Accounts');
                    break;
            }
        });
    });
});
