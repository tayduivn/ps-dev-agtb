describe('View.Views.Base.QuicksearchModuleList', function() {
    var viewName = 'quicksearch-modulelist',
        view, layout;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', viewName);
        SugarTest.testMetadata.set();
        sinon.collection.stub(SugarTest.app.metadata, 'getModules', function() {
            var fakeModuleList = {
                Accounts: {globalSearchEnabled: true},
                Contacts: {globalSearchEnabled: true},
                globalSearchDisabled: {globalSearchEnabled: false},
                globalSearchNotSet: {},
                NoAccess: {globalSearchEnabled: true}
            };
            return fakeModuleList;
        });
        sinon.collection.stub(SugarTest.app.acl, 'hasAccess', function(action, module) {
            return module !== 'NoAccess';
        });
        sinon.collection.stub(SugarTest.app.api, 'isAuthenticated').returns(true);

        layout = SugarTest.app.view.createLayout({});
        view = SugarTest.createView('base', null, viewName, null, null, null, layout);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
        layout.dispose();
        layout = null;
        view = null;
    });

    describe('populateModules', function() {
        it('Should show searchable modules only', function() {
            sinon.collection.stub(view, 'render');
            view.populateModules();
            expect(view.searchModuleFilter.get('Accounts')).toBeTruthy();
            expect(view.searchModuleFilter.get('Contacts')).toBeTruthy();
            expect(view.searchModuleFilter.get('globalSearchDisabled')).toBeFalsy();
            expect(view.searchModuleFilter.get('globalSearchNotSet')).toBeFalsy();
            expect(view.searchModuleFilter.get('NoAccess')).toBeFalsy();
        });
    });
});
