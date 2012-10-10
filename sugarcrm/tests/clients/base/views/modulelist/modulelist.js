describe("Module List", function() {
    var moduleName = 'Cases';

    beforeEach(function() {
        SugarTest.loadViewHandlebarsTemplate('base', 'modulelist');
        SugarTest.loadComponent('base', 'view', 'modulelist');

        SugarTest.app.metadata.set(fixtures.metadata, false);
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
                expect(view.$el.find('.' + key).length).not.toBe(0);
            });
        });

        it("Should select the current module to be active", function() {
            var getModuleStub = sinon.stub(SugarTest.app.controller.context, 'get', function() {
                    return moduleName;
                });

            view.render();

            expect(view.$el.find('.active').hasClass(moduleName)).toBe(true);

            getModuleStub.restore();
        });
    });
});