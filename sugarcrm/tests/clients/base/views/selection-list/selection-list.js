describe("Base.View.SelectionList", function () {
    var view, layout, app, moduleName, viewDef;
    beforeEach(function () {
        moduleName = 'Accounts';
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'selection-list');
        SugarTest.testMetadata.addViewDefinition('list', {
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
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    it("Should add the selection field at the first field", function () {
        expect(view.leftColumns.length).toBe(1);
        expect('selection').toBe(view.leftColumns[0].type);
    });
});
