describe("Base.View.FlexList", function () {
    var view, layout, app;

    beforeEach(function () {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels":[
                {
                    "name":"panel_header",
                    "header":true,
                    "fields":["name", "case_number", "type", "created_by", "date_entered", "date_modified", "modified_user_id"]
                }
            ],
            last_state: {
                id: 'record-list'
            }
        }, "Cases");
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Cases", "flex-list", null, null);
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

    describe('adding actions to list view', function () {

        it('should add single selection', function () {
            view.meta = {
                selection:{
                    type:'single',
                    label:'LBL_LINK_SELECT'
                }
            };
            view.addActions();
            expect(view.leftColumns[0]).toEqual({
                type:'selection',
                name:'Cases_select',
                sortable:false,
                label:view.meta.selection.label
            });
        });


        it('should add multi selection', function () {
            view.meta = {
                selection:{
                    type:'multi',
                    actions:[
                        {
                            name:'edit_button',
                            type:'button'
                        },
                        {
                            name:'delete_button',
                            type:'button'
                        }
                    ]
                },
                rowactions:{
                    'css_class':'pull-right',
                    'actions':[
                        {
                            type:'rowaction',
                            'event':'list:preview:fire'
                        },
                        {
                            type:'rowaction',
                            'event':'list:editrow:fire'
                        },
                        {
                            type:'rowaction',
                            'event':'list:deleterow:fire'
                        }
                    ]
                }
            };

            view.addActions();

            expect(view.leftColumns[0]).toEqual({
                type:'fieldset',
                fields:[
                    {
                        type:'actionmenu',
                        buttons:view.meta.selection.actions
                    }
                ],
                value:false,
                sortable:false
            });
        });

        it('should add row actions', function () {
            view.meta = {
                rowactions:{
                    'css_class':'pull-right',
                    'actions':[
                        { type:'rowaction', 'event':'list:preview:fire'},
                        { type:'rowaction', 'event':'list:editrow:fire' },
                        { type:'rowaction', 'event':'list:deleterow:fire' }
                    ]
                }
            };

            view.addActions();

            expect(view.rightColumns[0]).toEqual({
                type:'fieldset',
                fields:[
                    {
                        type:'rowactions',
                        label:'',
                        css_class:'pull-right',
                        buttons:view.meta.rowactions.actions
                    }
                ],
                value:false,
                sortable:false
            });
        });
    });



    describe('default fields and available fields', function() {

        beforeEach(function () {
            view.meta = {
                panels: [
                    {
                        fields: [
                            {
                                'name': 'test1',
                                'default': false
                            },
                            {
                                'name': 'test2',
                                'default': false
                            }
                        ]
                    },
                    {
                        fields: [
                            {
                                'name': 'test3',
                                'default': true
                            },
                            {
                                'name': 'test4',
                                'default': false
                            }
                        ]
                    }
                ]
            };
        });

        it('should generate user last state key for visible fields', function() {
            expect(view.visibleFieldsLastStateKey).not.toBeEmpty();
        });

        it('should retrieve the last state of fields when parse fields', function() {
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get', function(key) {
                return ['test2'];
            });
            view._fields = view.parseFields();

            expect(lastStateGetStub).toHaveBeenCalled();
            expect(view._fields.visible).toEqual([
                {
                    'name': 'test2',
                    'default': false
                }
            ]);
            lastStateGetStub.restore();
        });

        it('should parse fields and separate default fields from available fields', function() {
            view._fields = view.parseFields();

            expect(view._fields['default']).toEqual([
                {
                    'name': 'test3',
                    'default': true
                }
            ]);
            expect(view._fields.available).toEqual([
                {
                    'name': 'test1',
                    'default': false
                },
                {
                    'name': 'test2',
                    'default': false
                },
                {
                    'name': 'test4',
                    'default': false
                }
            ]);
            expect(view._fields.visible).toEqual([
                {
                    'name': 'test3',
                    'default': true
                }
            ]);
            expect(view._fields.options).toEqual([
                {
                    'name': 'test1',
                    'default': false,
                    'selected' : false
                },
                {
                    'name': 'test2',
                    'default': false,
                    'selected' : false
                },
                {
                    'name': 'test3',
                    'default': true,
                    'selected' : true
                },
                {
                    'name': 'test4',
                    'default': false,
                    'selected' : false
                }
            ]);


        });
        it('should parse fields and use default fields as visible when user last state empty', function() {
            var lastStateGetStub = sinon.stub(app.user.lastState, 'get');
            view._fields = view.parseFields();
            expect(view._fields.visible).toEqual([
                {
                    'name': 'test3',
                    'default': true
                }
            ]);
            lastStateGetStub.restore();

        });
    });
});
