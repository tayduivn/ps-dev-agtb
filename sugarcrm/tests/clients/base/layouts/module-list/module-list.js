describe('Module List Layout', function() {

    var moduleName = 'Cases',
        layoutName = 'module-list',
        app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base');
        SugarTest.loadHandlebarsTemplate(layoutName, 'layout', 'base', 'list');
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', moduleName, layoutName);
        layout.template = app.template.getLayout(layoutName);
    });

    afterEach(function() {
        layout.dispose();
        Handlebars.templates = {};
        SugarTest.testMetadata.dispose();
    });

    describe('Render', function() {

        beforeEach(function() {

            sinon.collection.stub(app.api, 'isAuthenticated', function() {
                return true;
            });
            sinon.collection.stub(app.metadata, 'getModuleNames', function() {
                return {
                    Home: 'Home',
                    Accounts: 'Accounts',
                    Bugs: 'Bugs',
                    Calendar: 'Calendar',
                    Calls: 'Calls',
                    Campaigns: 'Campaigns',
                    Cases: 'Cases',
                    Contacts: 'Contacts',
                    Forecasts: 'Forecasts',
                    Opportunities: 'Opportunities',
                    Prospects: 'Prospects',
                    Reports: 'Reports',
                    Tasks: 'Tasks'
                };
            });
            sinon.collection.stub(app.metadata, 'getStrings', function() {
                return {
                    Accounts: {}
                };
            });

            sinon.collection.stub(app.controller.context, 'get', function() {
                return moduleName;
            });

            layout._resetMenu();
        });

        afterEach(function() {
            layout.dispose();
            sinon.collection.restore();
        });

        it('should display all the modules in the module list metadata by order', function() {
            var modules = _.map(layout.$('[data-action="more-modules"]').prevUntil().get().reverse(), function(el) {
                return $(el).data('module');
            });
            expect(modules).toEqual(_.keys(app.metadata.getModuleNames()));
        });

        it('should select Cases module to be currently active module', function() {
            layout.layout = {
                trigger: $.noop,
                off: $.noop
            };
            var triggerStub = sinon.collection.stub(layout.layout, 'trigger');

            layout.handleViewChange();

            expect(layout.$('[data-container=module-list]').children('.active').data('module')).toBe(moduleName);
            expect(triggerStub).toHaveBeenCalledWith('header:update:route');

            expect(layout.isActiveModule(moduleName)).toBeTruthy();
        });
    });
});
