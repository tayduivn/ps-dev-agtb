describe("Base.View.List", function() {
    var view, layout, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("list", 'view', 'base');
        SugarTest.testMetadata.addViewDefinition("list", {
            "panels": [{
                "name": "panel_header",
                "header": true,
                "fields": ["name", "case_number","type","created_by","date_entered","date_modified","modified_user_id"]
            }]
        }, "Cases");
        SugarTest.testMetadata.set();
        //SugarTest.app.data.declareModels();
        view = SugarTest.createView("base", "Cases", "list", null, null);
        layout = SugarTest.createLayout('base', "Cases", "list", null, null);
        view.layout = layout;
        app = SUGAR.App;
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("list",function() {
        it('should open an alert message on sort', function() {
            view.render();
            var ajaxStub = sinon.stub(view.collection, 'fetch');
            var alertStub = sinon.stub(app.alert, 'show');
            view.setOrderBy({target:'[data-fieldname=case_number]'});
            expect(alertStub).toHaveBeenCalled();
            alertStub.restore();
            ajaxStub.restore();
        });

        it('should parse fields and separate default fields from available fields', function() {
            var viewMeta = {
                panels: [
                    {
                        fields: [
                            {
                                name: 'test1',
                                default: false
                            },
                            {
                                name: 'test2',
                                default: false
                            }
                        ]
                    },
                    {
                        fields: [
                            {
                                name: 'test3',
                                default: true
                            },
                            {
                                name: 'test4',
                                default: false
                            }
                        ]
                    }
                ]
            };

            view.parseFields(viewMeta);

            expect(view._fields["default"]).toEqual(["test3"]);
            expect(view._fields["available"]["all"]).toEqual(["test1","test2","test4"]);
        });

        describe('adding actions to list view', function() {

            it('should add single selection', function () {
                var viewMeta = {
                    meta:{
                        selection:{
                            type:'single',
                            label:'LBL_LINK_SELECT'
                        }
                    },
                    module:'Cases'
                };

                view.addActions(viewMeta);

                expect(view._leftActions[0]).toEqual({
                    type:'selection',
                    name: 'Cases_select',
                    sortable:false,
                    label: viewMeta.meta.selection.label
                })
            });


            it('should add multi selection', function() {
                var viewMeta =  {meta: {
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
                }};

                view.addActions(viewMeta);

                expect(view._leftActions[0]).toEqual({
                    type:'fieldset',
                    fields:[
                        {
                            type:'actionmenu',
                            buttons:viewMeta.meta.selection.actions
                        }
                    ],
                    value:false,
                    sortable:false
                })

            });

            it('should add row actions', function () {
                var viewMeta = {meta:{
                    rowactions:{
                        'css_class':'pull-right',
                        'actions':[
                            { type:'rowaction', 'event':'list:preview:fire'},
                            { type:'rowaction', 'event':'list:editrow:fire' },
                            { type:'rowaction', 'event':'list:deleterow:fire' }
                        ]
                    }
                }};

                view.addActions(viewMeta);

                expect(view._rowActions[0]).toEqual({
                    type:'fieldset',
                    fields:[
                        {
                            type:'rowactions',
                            label:'',
                            css_class:'pull-right',
                            buttons:viewMeta.meta.rowactions.actions
                        }
                    ],
                    value:false,
                    sortable:false
                })
            });
        });
    });
});