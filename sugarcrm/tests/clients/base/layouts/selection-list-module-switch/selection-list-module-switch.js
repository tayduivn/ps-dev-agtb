describe('Base.Layout.SelectionListModuleSwitch', function() {
    var app, layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        layout = SugarTest.createLayout('base', 'Contacts', 'selection-list-module-switch');
    });

    afterEach(function() {
        layout.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
    });

    describe('_buildModuleSwitchList', function() {
        it('Should return a list of modules as objects of ID and text', function() {
            var result = layout._buildModuleSwitchList(['Accounts', 'Contacts', 'Leads']);

            expect(result).toEqual([{
                id: 'Accounts',
                text: 'LBL_MODULE_NAME'
            }, {
                id: 'Contacts',
                text: 'LBL_MODULE_NAME'
            }, {
                id: 'Leads',
                text: 'LBL_MODULE_NAME'
            }]);
        });

        it('Should only return modules that user has access to', function() {
            var result,
                hasAccessStub = sinon.stub(app.acl, 'hasAccess', function(action, module) {
                    return (module !== 'Contacts');
                });

            result = layout._buildModuleSwitchList(['Accounts', 'Contacts', 'Leads']);

            expect(result).toEqual([{
                id: 'Accounts',
                text: 'LBL_MODULE_NAME'
            }, {
                id: 'Leads',
                text: 'LBL_MODULE_NAME'
            }]);

            hasAccessStub.restore();
        });
    });
});
