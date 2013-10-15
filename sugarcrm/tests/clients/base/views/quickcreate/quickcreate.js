describe("Quick Create Dropdown", function() {
    var viewName = 'quickcreate',
        app, view, isAuthenticatedStub, getModuleNamesStub, getStringsStub, getModuleStub, testModules, testMeta;

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
        testModules = {
            Accounts: {visible:true, acl:'create'},
            Contacts: {visible:true, acl:'create'},
            Opportunities: {visible:true, acl:'create'}
        };
        getModuleNamesStub = sinon.stub(SugarTest.app.metadata, 'getModuleNames', function(visible, acl) {
            var modules = [];
            _.each(testModules, function(module, key) {
                if (module.visible === visible && module.acl === acl) {
                    modules.push(key);
                }
            });
            return modules;
        });
        getStringsStub = sinon.stub(SugarTest.app.metadata, 'getStrings', function() {
            return {
                Accounts: {}
            }
        });
        testMeta = {
            Accounts: buildQuickCreateMeta('Accounts', true, 0),
            Contacts: buildQuickCreateMeta('Contacts', true, 1),
            Opportunities: buildQuickCreateMeta('Opportunities', true, 2)
        };
        getModuleStub = sinon.stub(SugarTest.app.metadata, 'getModule', function(module) {
            return testMeta[module];
        });
    });

    var buildQuickCreateMeta = function(module, visible, order) {
        return {menu:{quickcreate:{meta:{module:module,visible:visible,order:order}}}};
    };

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        isAuthenticatedStub.restore();
        getModuleNamesStub.restore();
        getStringsStub.restore();
        getModuleStub.restore();
        view = null;
    });

    var filterMenuItemsByModule = function(menuItems, module) {
        return _.filter(menuItems, function(menuItem) {
            return menuItem.module === module;
        });
    };

    it("Should build create actions for all modules", function() {
        var expectedModules = SugarTest.app.metadata.getModuleNames(true, 'create');
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
        testModules['Foo'] = {visible:true, acl:'create'};
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Foo').length).toBe(0);
    });

    it("Should not build create action for hidden modules", function() {
        var expectedModules = ['Accounts', 'Contacts'];
        testModules.Opportunities.visible = false;
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Opportunities').length).toBe(0);
    });

    it("Should not build create action for modules user does not have create access to", function() {
        var expectedModules = ['Contacts', 'Opportunities'];
        testModules.Accounts.acl = 'view';
        view.render();

        _.each(expectedModules, function(module) {
            expect(filterMenuItemsByModule(view.createMenuItems, module).length).not.toBe(0);
        });
        expect(filterMenuItemsByModule(view.createMenuItems, 'Accounts').length).toBe(0);
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
