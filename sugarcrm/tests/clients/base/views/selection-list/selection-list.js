describe("Base.View.SelectionList", function() {
    var view, layout, app, moduleName, viewDef;
    beforeEach(function() {
        moduleName = 'Accounts';
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
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

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('Initialization with list panel fields', function() {
        it("Should initialize the metadata from list view", function() {
            expect(view._leftActions.length).toBe(0);
            view.render();
            expect(view._leftActions.length).toBe(1);
        });
        it("Should add the selection field at the first field", function(){
            view.render();
            var expected = 'selection',
                actual = view._leftActions[0].type;
            expect(expected).toBe(actual);
        });
    });

    describe('Initialization with panels fields in viewdef', function() {
        beforeEach(function() {
            viewDef = {
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
                            }
                        ]
                    }
                ]
            };

            SugarTest.testMetadata.addViewDefinition('selection-list', viewDef, moduleName);
            SugarTest.testMetadata.set();
            view = SugarTest.createView('base', moduleName, 'selection-list');
            layout = SugarTest.createLayout('base', "Cases", "list", null, null);
            view.layout = layout;
        });

        it("Should initialize the metadata from viewdef", function() {
            expect(view._leftActions.length).toBe(0);
            view.render();
            expect(view._leftActions.length).toBe(1);
        });
        it("Should add the selection field at the first field", function(){
            view.render();
            var expected = 'selection',
                actual = view._leftActions[0].type;
            expect(expected).toBe(actual);
        });
    });
});
