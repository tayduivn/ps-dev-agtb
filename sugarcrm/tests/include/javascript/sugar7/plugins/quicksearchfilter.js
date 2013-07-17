describe("Plugins.Quicksearchfilter", function () {

    var app, field, oRouter, buildRouteStub;

    beforeEach(function () {
        app = SugarTest.app;
    });

    afterEach(function () {
        app.cache.cutAll();
        app.view.reset();
        SugarTest.app.router = oRouter;
        delete Handlebars.templates;
    });


    it("Should create quick search terms by retreiving the module filter metadata", function () {
        field = SugarTest.createField("base", "account_name", "relate", "edit");

        var singleExpectedField = ['name'],
            multipleExpectedFields = ['first_name', 'last_name', 'title'],
            expectedSearchTerm = "Foo Bar",
            expectedSearchTerms = expectedSearchTerm.split(' '),
            singleFilterModule = "Tasks",
            multipleFilterModule = "Contacts",
            metadataStub = sinon.stub(app.metadata, 'getModule', function (module) {
                var filterDef = {
                    Tasks: {
                        filters: {
                            _default: {
                                meta: {
                                    quicksearch_field: singleExpectedField,
                                    quicksearch_priority: 1
                                }
                            }
                        }
                    },
                    Contacts: {
                        filters: {
                            _default: {
                                meta: {
                                    quicksearch_field: multipleExpectedFields,
                                    quicksearch_priority: 1
                                }
                            }
                        }
                    }
                };

                return filterDef[module];
            });
        var actualSingleFilter = field.getFilterDef(singleFilterModule, expectedSearchTerm);
        _.each(actualSingleFilter, function (filter) {
            _.each(filter, function (term, field) {
                var actualTerm = term['$starts'];
                expect(_.indexOf(singleExpectedField, field) >= 0).toBeTruthy();
                expect(actualTerm).toBeDefined();
                expect(actualTerm).toBe(expectedSearchTerm);
            });
        }, this);

        //Multiple search filter should contain the "OR" clause
        var actualMultiFilter = field.getFilterDef(multipleFilterModule, expectedSearchTerm);
        _.each(actualMultiFilter, function (filter) {
            expect(filter['$or']).toBeDefined();
            expect(filter['$or'].length).toBe(multipleExpectedFields.length * expectedSearchTerms.length);
            _.each(filter['$or'], function (search_filter) {
                _.each(search_filter, function (term, field) {
                    var actualTerm = term['$starts'];
                    expect(_.indexOf(multipleExpectedFields, field) >= 0).toBeTruthy();
                    expect(actualTerm).toBeDefined();
                    expect(_.indexOf(['Foo', 'Bar'], actualTerm) >= 0).toBeTruthy();
                });
            });
        }, this);
        metadataStub.restore();
    });

    it("Highest priority filter should be selected among the multiple quick search filters", function () {
        field = SugarTest.createField("base", "account_name", "relate", "edit");

        var expectedFilterFields = [
                'first_name',
                'last_name'
            ],
            unexpectedFilterFields = [
                'document_name',
                'bazooka'
            ],
            expectedSearchTerm = "Blah",
            metadataStub = sinon.stub(app.metadata, 'getModule', function () {
                return {
                    filters: {
                        basic: {
                            meta: {
                                quicksearch_field: [
                                    'name'
                                ]
                            },
                            quicksearch_priority: 1
                        },
                        person: {
                            meta: {
                                quicksearch_field: expectedFilterFields,
                                quicksearch_priority: 10 //Higer priority filter will be populated
                            }
                        },
                        _default: {
                            meta: {
                                quicksearch_field: unexpectedFilterFields,
                                quicksearch_priority: 2
                            }
                        }
                    }
                };
            });

        var actualFilter = field.getFilterDef(field.getSearchModule(), expectedSearchTerm);
        _.each(actualFilter, function (filter) {
            expect(filter['$or']).toBeDefined();
            expect(filter['$or'].length).toBe(expectedFilterFields.length);
            _.each(filter['$or'], function (search_filter) {
                _.each(search_filter, function (term, field) {
                    expect(_.indexOf(expectedFilterFields, field) >= 0).toBeTruthy();
                    expect(_.indexOf(unexpectedFilterFields, field) >= 0).toBeFalsy();
                    var actualTerm = term['$starts'];
                    expect(actualTerm).toBeDefined();
                    expect(actualTerm).toBe(expectedSearchTerm);
                });
            });
        }, this);
        metadataStub.restore();
    });

    it('should get the highest priority field for search', function () {
        var layout = SugarTest.createLayout('base', 'Accounts', 'filter', {}, false, false, {layout: new Backbone.View()});
        var metadataStub = sinon.stub(app.metadata, 'getModule', function() {
            return {
                filters: {
                    'meta1': {
                        'meta': {
                            'quicksearch_field': 'test1',
                            'quicksearch_priority': 0
                        }
                    },
                    'meta2': {
                        'meta': {
                            'quicksearch_field': 'test2',
                            'quicksearch_priority': 3
                        }
                    },
                    'meta3': {
                        'meta': {
                            'quicksearch_field': 'test3',
                            'quicksearch_priority': 2
                        }
                    }
                }
            }
        });

        var field = layout.getModuleQuickSearchFields('Accounts');

        expect(field).toEqual('test2');
        metadataStub.restore();
    });
});
