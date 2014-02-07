describe('Filter Plugin', function() {
    var view, layout, app, sinonSandbox;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'layout', 'filter');
        SugarTest.loadComponent('base', 'layout', 'togglepanel');
        SugarTest.loadComponent('base', 'layout', 'filterpanel');
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout('base', "Cases", "filterpanel", {}, null, null, { layout: new Backbone.View() });
        layout._components.push(SugarTest.createLayout('base', "Cases", "filter", {}, null, null, { layout: new Backbone.View() }));
        view = SugarTest.createView("base", "Cases", "filter-rows", null, null, null, layout);
        view.layout = layout;
        view.layout.editingFilter = new Backbone.Model();
        app = SUGAR.App;
        sinonSandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sinonSandbox.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        view = null;
    });

    describe('_getFilterableFields', function() {
        it('should return the list of filterable fields with fields definition', function() {
            sinonSandbox.stub(app.metadata, 'getModule', function() {
                var moduleMeta = {
                    fields: {
                        account_name_related: {
                            name: 'account_name_related',
                            dbFields: ['accounts.name'],
                            type: 'text',
                            vname: 'LBL_ACCOUNT_NAME'
                        },
                        name: {
                            name: 'name',
                            type: 'varchar',
                            len: 100
                        },
                        date_modified: {
                            name: 'date_modified',
                            options: 'date_range_search_dom',
                            type: 'datetime',
                            vname: 'LBL_DATE_MODIFIED'
                        },
                        number: {
                            name: 'number',
                            type: 'varchar',
                            len: 100,
                            readonly: true
                        }
                    },
                    filters: {
                        'default': {
                            meta: {
                                default_filter: 'all_records',
                                fields: {
                                    account_name_related: {
                                        dbFields: ['accounts.name'],
                                        type: 'text',
                                        vname: 'LBL_ACCOUNT_NAME'
                                    },
                                    date_modified: {},
                                    number: {}
                                },
                                filters: [
                                    {
                                        'id': 'test_filter',
                                        'name': 'Test Filter',
                                        'filter_definition': {
                                            '$starts': 'Test'
                                        }
                                    }
                                ]
                            }
                        }
                    }
                };
                return moduleMeta;
            });
            var fields = view._getFilterableFields('Cases');
            var expected = {
                account_name_related: {
                    name: 'account_name_related',
                    dbFields: ['accounts.name'],
                    type: 'text',
                    vname: 'LBL_ACCOUNT_NAME'
                },
                date_modified: {
                    name: 'date_modified',
                    options: 'date_range_search_dom',
                    type: 'datetime',
                    vname: 'LBL_DATE_MODIFIED'
                },
                number: {
                    name: 'number',
                    type: 'varchar',
                    len: 100
                }
            };
            expect(fields).toEqual(expected);
            expect(fields.number['readonly']).not.toBe(true);
        });
    });
});
