describe("Create Actions Dropdown", function() {
    var viewName = 'createactions',
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
            var modules = {};
            _.each(testModules, function(module, key) {
                if (module.visible === visible && module.acl === acl) {
                    modules[key] = key;
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
            Accounts: buildCreateActionMeta('Accounts', true),
            Contacts: buildCreateActionMeta('Contacts', true),
            Opportunities: buildCreateActionMeta('Opportunities', true)
        };
        getModuleStub = sinon.stub(SugarTest.app.metadata, 'getModule', function(module) {
            return testMeta[module];
        });
    });

    var buildCreateActionMeta = function(module, visible) {
        return {menu:{createaction:{meta:{module:module,visible:visible}}}};
    };

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        isAuthenticatedStub.restore();
        getModuleNamesStub.restore();
        getStringsStub.restore();
        getModuleStub.restore();
        view = null;
    });

    it("Should display create actions for all modules", function() {
        var expectedModules = SugarTest.app.metadata.getModuleNames(true, 'create');
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
    });

    it("Should display create actions even if visible meta attribute not specified", function() {
        var expectedModules = ['Accounts', 'Contacts', 'Opportunities'];
        delete testMeta.Accounts.menu.createaction.meta.visible;
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
    });

    it("Should not display modules that don't have createaction meta", function() {
        var expectedModules = ['Accounts', 'Contacts', 'Opportunities'];
        testModules['Foo'] = {visible:true, acl:'create'};
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
        expect(view.$("[data-module='Foo']").length).toBe(0);
    });

    it("Should not display create action for hidden modules", function() {
        var expectedModules = ['Accounts', 'Contacts'];
        testModules.Opportunities.visible = false;
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
        expect(view.$("[data-module='Opportunities']").length).toBe(0);
    });

    it("Should not display create action for modules user does not have create access to", function() {
        var expectedModules = ['Contacts', 'Opportunities'];
        testModules.Accounts.acl = 'view';
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
        expect(view.$("[data-module='Accounts']").length).toBe(0);
    });

    it("Should not display create actions that are hidden", function() {
        var expectedModules = ['Accounts', 'Opportunities'];
        testMeta.Contacts.menu.createaction.meta.visible = false;
        view.render();

        _.each(expectedModules, function(module) {
            expect(view.$("[data-module='" + module+"']").length).not.toBe(0);
        });
        expect(view.$("[data-module='Contacts']").length).toBe(0);
    });

    describe("handleActionLink", function () {
        var drawerBefore, event, alertShowStub, alertConfirm, mockDrawerCount;

        beforeEach(function () {
            alertConfirm = false;
            mockDrawerCount = 0;

            alertShowStub = sinon.stub(app.alert, 'show', function(name, options) {
                if (alertConfirm) options.onConfirm();
            });

            drawerBefore = app.drawer;
            app.drawer = {
                count: function() {
                    return mockDrawerCount;
                },
                reset: sinon.stub(),
                open: sinon.stub()
            };

            event = {
                currentTarget: '<a data-module="Foo" data-layout="Bar"></a>'
            };
        });

        afterEach(function () {
            alertShowStub.restore();
            app.drawer = drawerBefore;
        });

        it('should open the drawer without confirm if no drawers open', function() {
            var drawerOptions;
            view._handleActionLink(event);
            drawerOptions = _.first(app.drawer.open.lastCall.args);

            expect(alertShowStub.callCount).toBe(0);
            expect(drawerOptions.context.module).toEqual('Foo');
            expect(drawerOptions.layout).toEqual('Bar');
        });

        it('should show confirmation when drawers are open and not open drawer if not confirmed', function() {
            alertConfirm = false;
            mockDrawerCount = 1;
            view._handleActionLink(event);

            expect(alertShowStub.callCount).toBe(1);
            expect(app.drawer.reset.callCount).toBe(0);
            expect(app.drawer.open.callCount).toBe(0);
        });

        it('should reset drawers and open new drawer if confirmed', function() {
            alertConfirm = true;
            mockDrawerCount = 2;
            view._handleActionLink(event);

            expect(alertShowStub.callCount).toBe(1);
            expect(app.drawer.reset.callCount).toBe(1);
            expect(app.drawer.open.callCount).toBe(1);
        });
    });

});
