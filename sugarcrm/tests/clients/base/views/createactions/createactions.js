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
        var view, isAuthenticatedStub;

        beforeEach(function() {
            view = SugarTest.createView("base", moduleName, viewName, null, null);
            isAuthenticatedStub = sinon.stub(SugarTest.app.api, 'isAuthenticated', function() {
                return true;
            });
        });

        afterEach(function() {
            delete view;
            isAuthenticatedStub.restore();
        });

        it("Should display create actions for all modules", function() {
            view.render();

            _.each(SugarTest.app.metadata.data.module_list, function(module, key) {
                if (key !== '_hash') {
                    expect(view.$el.find('.' + key).length).not.toBe(0);
                }
            });
        });
    });
});