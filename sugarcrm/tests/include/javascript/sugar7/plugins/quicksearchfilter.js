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

    describe('Building the filter definition', function() {
        var quicksearch_field,
            searchTerm = 'John F Kennedy',
            metadataStub,
            filterDef;

        beforeEach(function() {
            field = SugarTest.createField("base", "account_name", "relate", "edit");
            field._moduleSearchFields = {};
        });
        afterEach(function() {
            metadataStub.restore();
            filterDef = null;
        });

        it('should search if one field starts with one term', function() {
            quicksearch_field = ['name'];
            metadataStub = sinon.stub(app.metadata, 'getModule', function() {
                return {
                    filters: {
                        _default: {
                            meta: {
                                quicksearch_field: quicksearch_field,
                                quicksearch_priority: 1
                            }
                        }
                    }
                };
            });
            filterDef = field.getFilterDef('Contacts', searchTerm);
            expect(filterDef).toEqual([
                { name: { $starts: searchTerm } }
            ]);
        });
        it('should search if any field starts with the term if multiple fields but only one term', function() {
            quicksearch_field = ['first_name', 'last_name'];
            metadataStub = sinon.stub(app.metadata, 'getModule', function() {
                return {
                    filters: {
                        _default: {
                            meta: {
                                quicksearch_field: quicksearch_field,
                                quicksearch_priority: 1
                            }
                        }
                    }
                };
            });
            filterDef = field.getFilterDef('Contacts', 'John');
            expect(filterDef).toEqual([
                { $or: [
                    { first_name: { $starts: 'John' } },
                    { last_name: { $starts: 'John' } }
                ] }
            ]);
        });
        it('should search if first field starts with first term and second field starts with other terms if multiple fields and multiple terms', function() {
            quicksearch_field = ['first_name', 'last_name'];
            metadataStub = sinon.stub(app.metadata, 'getModule', function() {
                return {
                    filters: {
                        _default: {
                            meta: {
                                quicksearch_field: quicksearch_field,
                                quicksearch_priority: 1
                            }
                        }
                    }
                };
            });
            filterDef = field.getFilterDef('Contacts', searchTerm);
            expect(filterDef).toEqual([
                { $and: [
                    { first_name: { $starts: 'John' } },
                    { last_name: { $starts: 'F Kennedy' } }
                ] }
            ]);
        });
    });

    it("Highest priority filter should be selected among the multiple quick search filters", function () {
        field = SugarTest.createField("base", "account_name", "relate", "edit");
        field._moduleSearchFields = {};

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
