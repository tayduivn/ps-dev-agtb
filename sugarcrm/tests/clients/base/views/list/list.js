describe("Base.View.List", function () {
    var view, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels":[
                {
                    "name":"panel_header",
                    "header":true,
                    "fields":["name", "case_number", "type", "created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ]
        }, "Cases");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function () {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe('parseFieldMetadata', function() {
        it('should parse fields set the correct align and width params', function() {
            var options = {};
            options.meta = {
                panels: [
                    {
                        fields: [
                            {
                                'name': 'test1',
                                'align': 'left'
                            },
                            {
                                'name': 'test2',
                                'align': 'center'
                            },
                            {
                                'name': 'test3',
                                'align': 'right'
                            },
                            {
                                'name': 'test4',
                                'align': 'invalid'
                            },
                        ]
                    },
                    {
                        fields: [
                            {
                                'name': 'test5',
                                'width': '20%'
                            },
                            {
                                'name': 'test6',
                                'width': '105%'
                            },
                            {
                                'name': 'test7',
                                'width': '105'
                            }
                        ]
                    }
                ]
            };
            options = view.parseFieldMetadata(options);

            expect(options.meta.panels).toEqual([
                    {
                        fields: [
                            {
                                'name': 'test1',
                                'align': 'tleft'
                            },
                            {
                                'name': 'test2',
                                'align': 'tcenter'
                            },
                            {
                                'name': 'test3',
                                'align': 'tright'
                            },
                            {
                                'name': 'test4',
                                'align': ''
                            },
                        ]
                    },
                    {
                        fields: [
                            {
                                'name': 'test5',
                                'width': '20%'
                            },
                            {
                                'name': 'test6',
                                'width': ''
                            },
                            {
                                'name': 'test7',
                                'width': ''
                            }
                        ]
                    }
                ]);
        });
    });
});
