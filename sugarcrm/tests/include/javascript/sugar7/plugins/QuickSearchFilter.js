describe('Plugins.Quicksearchfilter', function() {

    var app, field;

    var getFilterMetaData = function(field, priority) {
        return {
            filters: {
                default: {
                    meta: {
                        quicksearch_field: field,
                        quicksearch_priority: priority
                    }
                }
            }
        };
    };

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('Building the filter definition', function() {
        var quicksearch_field, filterDef;
        var searchTerm = 'John F Kennedy';
        beforeEach(function() {
            field = SugarTest.createField('base', 'account_name', 'relate', 'edit');
            field._moduleQuickSearchMeta = {};
        });

        afterEach(function() {
            filterDef = null;
        });

        using('various inputs to search for a contact "Luis Filipe Madeira Caeiro Figo"', [
            {
                case: 'First part of first name',
                searchValue: 'Luis',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Luis'}}
                    ]
                }]
            }, {
                case: 'First 2 parts of first name',
                searchValue: 'Luis Filipe',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {first_name: {$starts: 'Filipe'}},
                        {last_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Filipe'}},
                        {first_name: {$starts: 'Luis Filipe'}},
                        {last_name: {$starts: 'Luis Filipe'}}
                    ]
                }]
            }, {
                case: 'First name',
                searchValue: 'Luis Filipe Madeira',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {first_name: {$starts: 'Filipe Madeira'}},
                        {last_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Filipe Madeira'}},
                        {first_name: {$starts: 'Luis Filipe'}},
                        {first_name: {$starts: 'Madeira'}},
                        {last_name: {$starts: 'Luis Filipe'}},
                        {last_name: {$starts: 'Madeira'}},
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                case: 'First part of last name',
                searchValue: 'Caeiro',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Caeiro'}},
                        {last_name: {$starts: 'Caeiro'}}
                    ]
                }]
            }, {
                case: 'Last name',
                searchValue: 'Caeiro Figo',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Caeiro'}},
                        {first_name: {$starts: 'Figo'}},
                        {last_name: {$starts: 'Caeiro'}},
                        {last_name: {$starts: 'Figo'}},
                        {first_name: {$starts: 'Caeiro Figo'}},
                        {last_name: {$starts: 'Caeiro Figo'}}
                    ]
                }]
            }, {
                case: 'Last name then first name',
                searchValue: 'Caeiro Figo Luis',
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Caeiro'}},
                        {first_name: {$starts: 'Figo Luis'}},
                        {last_name: {$starts: 'Caeiro'}},
                        {last_name: {$starts: 'Figo Luis'}},
                        {first_name: {$starts: 'Caeiro Figo'}},
                        {first_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Caeiro Figo'}},
                        {last_name: {$starts: 'Luis'}},
                        {first_name: {$starts: 'Caeiro Figo Luis'}},
                        {last_name: {$starts: 'Caeiro Figo Luis'}}
                    ]
                }]
            }],
            function(data) {
                var tokens = data.searchValue.split(' ');
                // Expected number of filters according to our algorithm
                var expectedNumFilters = (tokens.length + tokens.length - 1) * 2;

                it('should search by ' + data.case, function() {
                    quicksearch_field = [['first_name', 'last_name']];
                    sinon.collection.stub(app.metadata, 'getModule', function() {
                        return getFilterMetaData(quicksearch_field, 1);
                    });
                    filterDef = field.getFilterDef('Contacts', data.searchValue);

                    expect(filterDef[0].$or.length).toEqual(expectedNumFilters);
                    expect(filterDef).toEqual(data.expectedFilter);
                });
            });

        using('various quicksearch_field metadata', [
            {
                case: 'Undefined',
                meta: undefined,
                expectedFilter: []
            }, {
                case: '1 Simple Field',
                meta: ['simpleField1'],
                expectedFilter: [{'simpleField1': {'$starts': 'Luis Filipe Madeira'}}]
            }, {
                case: '2 Simple Fields',
                meta: ['simpleField1', 'simpleField2'],
                expectedFilter: [
                    {
                        '$or': [
                            {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                            {'simpleField2': {'$starts': 'Luis Filipe Madeira'}}
                        ]
                    }
                ]
            }, {
                case: '1 Split Term Field',
                meta: [['splitField1']],
                expectedFilter: [{'splitField1': {'$starts': 'Luis Filipe Madeira'}}]
            }, {
                case: '1 Split Term Field composed of 2 Fields',
                meta: [['first_name', 'last_name']],
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {first_name: {$starts: 'Filipe Madeira'}},
                        {last_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Filipe Madeira'}},
                        {first_name: {$starts: 'Luis Filipe'}},
                        {first_name: {$starts: 'Madeira'}},
                        {last_name: {$starts: 'Luis Filipe'}},
                        {last_name: {$starts: 'Madeira'}},
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                case: '1 Simple Field, 1 Split Term Field',
                meta: ['simpleField1', ['splitField1']],
                expectedFilter: [{
                    '$or': [
                        {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                        {'splitField1': {'$starts': 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                case: '1 Simple Field, 1 Split Term Field composed of 2 Fields',
                meta: ['simpleField1', ['first_name', 'last_name']],
                expectedFilter: [{
                    '$or': [
                        {'simpleField1': {'$starts': 'Luis Filipe Madeira'}},
                        {
                            '$or': [
                                {first_name: {$starts: 'Luis'}},
                                {first_name: {$starts: 'Filipe Madeira'}},
                                {last_name: {$starts: 'Luis'}},
                                {last_name: {$starts: 'Filipe Madeira'}},
                                {first_name: {$starts: 'Luis Filipe'}},
                                {first_name: {$starts: 'Madeira'}},
                                {last_name: {$starts: 'Luis Filipe'}},
                                {last_name: {$starts: 'Madeira'}},
                                {first_name: {$starts: 'Luis Filipe Madeira'}},
                                {last_name: {$starts: 'Luis Filipe Madeira'}}
                            ]
                        }
                    ]
                }]
            }, {
                case: '2 Split Term Fields',
                meta: [['first_name', 'last_name'], ['splitField3', 'splitField4']],
                expectedFilter: [{
                    $or: [
                        {first_name: {$starts: 'Luis'}},
                        {first_name: {$starts: 'Filipe Madeira'}},
                        {last_name: {$starts: 'Luis'}},
                        {last_name: {$starts: 'Filipe Madeira'}},
                        {first_name: {$starts: 'Luis Filipe'}},
                        {first_name: {$starts: 'Madeira'}},
                        {last_name: {$starts: 'Luis Filipe'}},
                        {last_name: {$starts: 'Madeira'}},
                        {first_name: {$starts: 'Luis Filipe Madeira'}},
                        {last_name: {$starts: 'Luis Filipe Madeira'}}
                    ]
                }]
            }, {
                case: '1 Split Term Field composed of 3 Fields',
                meta: [['splitField1', 'splitField2', 'splitField3']],
                expectedFilter: []
            }
        ], function(test) {
            var searchTerm = 'Luis Filipe Madeira';

            it('should be valid with ' + test.case, function() {
                quicksearch_field = test.meta;
                sinon.collection.stub(app.metadata, 'getModule', function() {
                    return getFilterMetaData(quicksearch_field, 1);
                });
                filterDef = field.getFilterDef('Accounts', searchTerm);

                expect(filterDef).toEqual(test.expectedFilter);
            });
        });

        it('should search if either field starts with full search string when quicksearch_split_terms not specified', function() {
            quicksearch_field = ['name', 'bug_number'];
            sinon.collection.stub(app.metadata, 'getModule', function() {
                return getFilterMetaData(quicksearch_field, 1);
            });
            filterDef = field.getFilterDef('Bugs', searchTerm);
            expect(filterDef).toEqual([
                { $or: [
                    { name: { $starts: searchTerm } },
                    { bug_number: { $starts: searchTerm } }
                ] }
            ]);
        });
    });

    it('Highest priority filter should be selected among the multiple quick search filters', function() {
        field = SugarTest.createField('base', 'account_name', 'relate', 'edit');
        field._moduleQuickSearchMeta = {};

        var expectedFilterFields = [
                'first_name',
                'last_name'
            ];
        var unexpectedFilterFields = [
                'document_name',
                'bazooka'
            ];
        var expectedSearchTerm = 'Blah';

        sinon.collection.stub(app.metadata, 'getModule', function() {
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
        _.each(actualFilter, function(filter) {
            expect(filter['$or']).toBeDefined();
            expect(filter['$or'].length).toBe(expectedFilterFields.length);
            _.each(filter['$or'], function(search_filter) {
                _.each(search_filter, function(term, field) {
                    expect(_.indexOf(expectedFilterFields, field) >= 0).toBeTruthy();
                    expect(_.indexOf(unexpectedFilterFields, field) >= 0).toBeFalsy();
                    var actualTerm = term['$starts'];
                    expect(actualTerm).toBeDefined();
                    expect(actualTerm).toBe(expectedSearchTerm);
                });
            });
        }, this);
    });

    it('should get the highest priority field for search', function() {
        var layout = SugarTest.createLayout('base', 'Accounts', 'filter', {}, false, false, {layout: new Backbone.View()});
        sinon.collection.stub(app.metadata, 'getModule', function() {
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
            };
        });

        var field = layout.getModuleQuickSearchFields('Accounts');

        expect(field).toEqual('test2');
    });
});
