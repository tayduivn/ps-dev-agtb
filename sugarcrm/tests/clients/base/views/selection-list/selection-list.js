describe("Base.View.SelectionList", function() {
    var view, app, moduleName;
    beforeEach(function() {
        moduleName = 'Accounts';
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'baselist');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.testMetadata.addViewDefinition('baselist', {
            "panels":[
                {
                    "name":"panel",
                    "fields":[
                        {
                            "name":"first_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        {
                            "name":"last_name",
                            "label":"",
                            "placeholder":"LBL_NAME"
                        },
                        "phone_work",
                        "email1",
                        "phone_office",
                        "full_name"
                    ]
                }
            ]
        }, moduleName);
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', moduleName, 'selection-list');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('Initialization', function() {
        it("Should initialize the metadata from baselist view", function() {
            var expected = (app.metadata.getView(moduleName, 'baselist')).panels[0].fields.length + 1, //+1: selection field will be added
                actual = view.meta.panels[0].fields.length;
            expect(expected).toBe(actual);
        });
        it("Should add the selection field at the first field", function(){
            var expected = 'selection',
                actual = view.meta.panels[0].fields[0].type;
            expect(expected).toBe(actual);
        });
    });
});