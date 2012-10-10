describe("Create Actions Dropdown", function() {
    var moduleName = 'Cases';

    beforeEach(function() {
        SugarTest.loadViewHandlebarsTemplate('base', 'createactions');
        SugarTest.loadComponent('base', 'view', 'createactions');

        SugarTest.app.metadata.set(fixtures.metadata, false);
    });

    describe('Render', function() {
        var view, isAuthenticatedStub;

        beforeEach(function() {
            view = SugarTest.createView("base", moduleName, "createactions", null, null);
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