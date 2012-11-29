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
        var view, isAuthenticatedStub;

        beforeEach(function() {
            view = SugarTest.createView("base", moduleName, "modulelist", null, null);
            isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
                return true;
            });
        });

        afterEach(function() {
            delete view;
            isAuthenticatedStub.restore();
        });

        it("Should display all the modules in the module list metadata", function() {
            view.render();

            _.each(SugarTest.app.metadata.data.module_list, function(module, key) {
                if (key !== '_hash') {
                    expect(view.$el.find('.' + key).length).not.toBe(0);
                }
            });
        });

        it("Should select Cases module to be currently active module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                    return moduleName;
                });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isActive(view.$el.find('.' + moduleName))).toBe(true);

            getModuleStub.restore();
        });

        it("Should know that Contacts module is next to the Cases module", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                return moduleName;
            });

            view.activeModule._moduleList = view;
            view.render();

            expect(view.activeModule.isNext(view.$el.find('.Contacts'))).toBe(true);

            getModuleStub.restore();
        });
    });
});