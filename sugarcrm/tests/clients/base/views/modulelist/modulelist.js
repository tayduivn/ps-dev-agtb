describe("Module List", function() {
    var moduleName = 'Cases',
        viewName = 'modulelist';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(viewName, 'view', 'base');
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe('Render', function() {
        var view, isAuthenticatedStub, getModuleNamesStub, modStrings;

        beforeEach(function() {
            view = SugarTest.createView("base", moduleName, "modulelist", null, null);
            isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
                return true;
            });
            getModuleNamesStub = sinon.stub(SugarTest.app.metadata, 'getModuleNames', function() {
                return {
                    Accounts: 'Accounts',
                    Bugs: 'Bugs',
                    Calendar: 'Calendar',
                    Calls: 'Calls',
                    Campaigns: 'Campaigns',
                    Cases: 'Cases',
                    Contacts: 'Contacts',
                    Forecasts: 'Forecasts',
                    Home: 'Home',
                    Opportunities: 'Opportunities',
                    Prospects: 'Prospects',
                    Reports: 'Reports',
                    Tasks: 'Tasks'
                }
            });
            modStrings = sinon.stub(SugarTest.app.metadata, 'getStrings', function() {
                return {
                    Accounts: {}
                }
            });
        });

        afterEach(function() {
            modStrings.restore();
            isAuthenticatedStub.restore();
            getModuleNamesStub.restore();
        });

        it("Should display all the modules in the module list metadata", function() {
            var modules = SugarTest.app.metadata.getModuleNames();

            view.render();

            _.each(modules, function(module, key) {
                expect(view.$el.find("[data-module='" + module+"']").length).not.toBe(0);
            });
        });

        it("Should select Cases module to be currently active module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                    return moduleName;
                });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isActive(view.$el.find("[data-module='" + moduleName+"']"))).toBe(true);

            getModuleStub.restore();
        });

        it("Should know that Contacts module is next to the Cases module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                return moduleName;
            });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isNext(view.$el.find("[data-module='Contacts']"))).toBe(true);

            getModuleStub.restore();
        });

        it("Should be able to filter menu items by acl", function() {
            sinon.stub(SugarTest.app.acl, 'hasAccess', function(action,module) {
                if (module == 'noAccess' || action =='edit') {
                    return false;
                } else {
                    return true;
                }
            });
            var meta = [
                {
                    label: 'blah',
                    acl_action: 'edit',
                    module:'test'
                },
                {
                    label: 'blah',
                    acl_action: 'edit',
                    module:'noAccess'
                },
                {
                    label: 'blah',
                    acl_action: 'read',
                    module:'testModule'
                }
            ];
            var result = view.filterAvailableMenuActions(meta);
            meta.shift();
            meta.shift();
            expect(result).toEqual(meta);
            SugarTest.app.acl.hasAccess.restore();
        });
        it("should trigger data event on click of action links", function() {
            var cbspy = sinon.spy();

            SugarTest.app.events.register("sugar:app:testEvent", view);
            SugarTest.app.events.on("sugar:app:testEvent", cbspy, view);
            var testEl = $('<li class="dropdown" data-module="testModule"><div><div><div><div data-event="sugar:app:testEvent"></div></div></div></div></li>');
                view.$el.append(testEl);
            var event = {
                currentTarget:testEl.find('[data-event=\'sugar:app:testEvent\']')
            };
            view.handleMenuEvent(event);
            expect(cbspy).toHaveBeenCalled();
            expect(cbspy).toHaveBeenCalledWith("testModule", event);
            SugarTest.app.events.unregister(view,"sugar:app:testEvent");
        });
        it("should route and open create drawer on app view open", function() {
            // sinon stub .restore will return a error here if router or drawer is undefined like it is below
            // so workaround by storing old values
            var oRouter = SugarTest.app.router;
            var oDrawer = SugarTest.app.drawer;
            SugarTest.app.router = sinon.stub({
                navigate: function(){

                },
                buildRoute: function(){

                }
            });
            SugarTest.app.drawer = sinon.stub({
                open: function(){

                }
            });
            view.handleCreateLink('testModule',{});
            expect(SugarTest.app.router.navigate).toHaveBeenCalled();
            expect(SugarTest.app.router.buildRoute).toHaveBeenCalled();

            SugarTest.app.router = oRouter;
            SugarTest.app.router = oDrawer;
        });
    });
});