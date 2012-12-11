describe("Create Actions Dropdown", function() {
    var moduleName = 'Cases',
        viewName = 'createactions';

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
        var view, isAuthenticatedStub, getModuleNamesStub;

        beforeEach(function() {
            view = SugarTest.createView("base", moduleName, viewName, null, null);
            isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
                return true;
            });
            getModuleNamesStub = sinon.stub(SugarTest.app.metadata, 'getModuleNames', function() {
                return [
                    'Accounts',
                    'Bugs',
                    'Calendar',
                    'Calls',
                    'Campaigns',
                    'Cases',
                    'Contacts',
                    'Forecasts',
                    'Home',
                    'Opportunities',
                    'Prospects',
                    'Reports',
                    'Tasks'
                ];
            });
        });

        afterEach(function() {
            isAuthenticatedStub.restore();
            getModuleNamesStub.restore();
        });

        it("Should display create actions for all modules", function() {
            var modules = SugarTest.app.metadata.getModuleNames();

            view.render();

            _.each(modules, function(module, key) {
                expect(view.$el.find('.' + module).length).not.toBe(0);
            });
        });
    });
});