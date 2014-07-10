describe('Data.Base.FiltersBean', function() {
    var app, filter, prototype, filterModuleName = 'Accounts';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        SugarTest.declareData('base', 'Filters');

        prototype = app.data.getBeanClass('Filters').prototype;
    });

    afterEach(function() {
        sinon.collection.restore();
        SugarTest.testMetadata.dispose();
        filter = null;
    });

    describe('getFilterableFields', function() {

        var varDefs = {}, filterDefs = {};

        beforeEach(function() {
            sinon.collection.stub(app.metadata, 'getFilterOperators').returns({
                'text': {},
                'date': {},
                'varchar': {}
            });
            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName).returns(
                {
                    fields: varDefs,
                    filters: {
                        'default': {
                            meta: {
                                default_filter: 'all_records',
                                fields: filterDefs
                            }
                        }
                    }
                }
            );
        });

        it('should extend the vardefs with the filter defs', function() {
            varDefs.name = {
                name: 'name',
                type: 'varchar',
                vname: 'LBL_ACCOUNT_NAME',
                len: 100
            };
            filterDefs.name = {
                vname: 'LBL_CUSTOM_ACCOUNT_NAME'
            };

            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.name).toEqual({
                name: 'name',
                type: 'varchar',
                vname: 'LBL_CUSTOM_ACCOUNT_NAME',
                len: 100
            });
        });

        it('should validate that an operator is available for this field', function() {
            varDefs.date_modified = {
                name: 'date_modified',
                options: 'date_range_search_dom',
                type: 'datetime',
                vname: 'LBL_DATE_MODIFIED'
            };
            varDefs.another = {
                name: 'another',
                type: 'invalidType'
            };
            filterDefs.date_modified = {};
            filterDefs.another = {};

            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.date_modified).toBeDefined();
            expect(fields.another).toBeUndefined();
        });

        it('should return predefined filters', function() {
            filterDefs.$favorite = {
                predefined_filter: true
            };
            var fields = prototype.getFilterableFields(filterModuleName);
            expect(fields.$favorite).toBeDefined();
        });
    });

    describe('populateFilterDefinition', function() {

        using('different filters', [
            // no populate object
            {
                filterDef: [
                    {'aField': {$in: ['aValue']}}
                ],
                expectedFilterDef: [
                    {'aField': {$in: ['aValue']}}
                ]
            },
            // value is not empty
            {
                filterDef: [
                    {'aField': 'aValue'}
                ],
                populateObj: {'aField': 'anotherValue'},
                expectedFilterDef: [
                    {'aField': 'aValue'}
                ]
            },
            // value is not empty and is a number
            {
                filterDef: [
                    {'aField': 1}
                ],
                populateObj: {'aField': 2},
                expectedFilterDef: [
                    {'aField': 1}
                ]
            },
            // value is empty
            {
                filterDef: [
                    {'aField': {$in: []}}
                ],
                populateObj: {'aField': ['aValue']},
                expectedFilterDef: [
                    {'aField': {$in: ['aValue']}}
                ]
            },
            // value is empty and operator is $equals
            {
                filterDef: [
                    {'aField': ''}
                ],
                populateObj: {'aField': 'aValue'},
                expectedFilterDef: [
                    {'aField': 'aValue'}
                ]
            }
        ], function(dataSet) {
            it('should parse the filter definition and fill empty values', function() {
                var filterDef = prototype.populateFilterDefinition(dataSet.filterDef, dataSet.populateObj);
                expect(filterDef).toEqual(dataSet.expectedFilterDef);
            });
        });
    });

    describe('getModuleQuickSearchMeta', function() {

        var filtersMetadata = {};

        beforeEach(function() {
            sinon.collection.stub(app.metadata, 'getModule')
                .withArgs(filterModuleName).returns({
                    filters: filtersMetadata
                });
            filtersMetadata.meta1 = {
                'meta': {
                    'quicksearch_field': 'test1',
                    'quicksearch_priority': 0
                }
            };
            sinon.collection.spy(prototype, '_getQuickSearchMetaByPriority');
        });

        using('different metadata', [
            {
                templateObjects: {
                    meta2: {
                        meta: {
                            'quicksearch_field': ['test2'],
                            'quicksearch_priority': 3
                        }
                    },
                    meta3: {
                        meta: {
                            'quicksearch_field': ['test3'],
                            'quicksearch_priority': 2
                        }
                    }
                },
                expected: {
                    fieldNames: ['test2'],
                    splitTerms: false
                }
            },
            {
                templateObjects: {
                    meta2: {
                        meta: {
                            'quicksearch_field': ['first_name', 'last_name'],
                            'quicksearch_split_terms': true,
                            'quicksearch_priority': 3
                        }
                    }
                },
                expected: {
                    fieldNames: ['first_name', 'last_name'],
                    splitTerms: true
                }
            }
        ], function(dataSet) {
            it('should retrieve and cache the quick search metadata of a module', function() {
                filtersMetadata.meta2 = dataSet.templateObjects.meta2;
                filtersMetadata.meta3 = dataSet.templateObjects.meta3;

                var quickSearchMetadata = prototype.getModuleQuickSearchMeta(filterModuleName);
                expect(quickSearchMetadata).toEqual(dataSet.expected);
                expect(prototype._getQuickSearchMetaByPriority).toHaveBeenCalled();

                // verify it is saved in memory
                prototype._moduleQuickSearchMeta = prototype._moduleQuickSearchMeta || {};
                expect(prototype._moduleQuickSearchMeta[filterModuleName]).toBeDefined();

                // reset spy
                prototype._getQuickSearchMetaByPriority.reset();

                // verify we get the metadata from memory
                quickSearchMetadata = prototype.getModuleQuickSearchMeta(filterModuleName);
                expect(quickSearchMetadata).toEqual(dataSet.expected);
                expect(prototype._getQuickSearchMetaByPriority).not.toHaveBeenCalled();
            });
        });
    });

    describe('buildSearchTermFilter', function() {

        using('different metadata and terms', [
            {
                quickSearchMetadata: {
                    fieldNames: ['aField'],
                    splitTerm: false
                },
                term: 'aTerm',
                expectedFilterDef: [{'aField': {$starts: 'aTerm'}}]
            },
            {
                quickSearchMetadata: {
                    fieldNames: ['first_name', 'last_name'],
                    splitTerms: true
                },
                term: 'Cli',
                expectedFilterDef: [{
                    $or: [
                        {first_name: {$starts: 'Cli'}},
                        {last_name: {$starts: 'Cli'}}
                    ]
                }]
            },
            {
                quickSearchMetadata: {
                    fieldNames: ['first_name', 'last_name'],
                    splitTerms: true
                },
                term: 'Clint Oram',
                expectedFilterDef: [{
                    $and: [
                        {first_name: {$starts: 'Clint'}},
                        {last_name: {$starts: 'Oram'}}
                    ]
                }]
            },
            {
                quickSearchMetadata: {
                    fieldNames: ['first_name', 'last_name'],
                    splitTerms: true
                },
                term: 'Clint De Oram',
                expectedFilterDef: [{
                    $and: [
                        {first_name: {$starts: 'Clint'}},
                        {last_name: {$starts: 'De Oram'}}
                    ]
                }]
            }
        ], function(dataSet) {
            it('should build the filter definition', function() {
                sinon.collection.stub(prototype, 'getModuleQuickSearchMeta').returns(dataSet.quickSearchMetadata);
                var filterDef = prototype.buildSearchTermFilter(filterModuleName, dataSet.term);
                expect(filterDef).toEqual(dataSet.expectedFilterDef);
            });
        });

        it('should augment the filter of Users and Employees module', function() {
            sinon.collection.stub(prototype, 'getModuleQuickSearchMeta').returns({
                fieldNames: ['first_name', 'last_name'],
                splitTerms: true
            });
            var filterDef = prototype.buildSearchTermFilter('Users', 'Test');
            expect(filterDef).toEqual([{
                $and: [
                    {status: {$not_equals: 'Inactive' }},
                    {$or: [
                        {first_name: {$starts: 'Test'}},
                        {last_name: {$starts: 'Test'}}
                    ]}
                ]
            }]);
        });
    });

    describe('combineFilterDefinitions', function() {

        using('different filters', [
            {
                expectedFilterDef: []
            },
            {
                baseFilter: [{status: {$not_equals: 'Inactive' }}],
                expectedFilterDef: [{status: {$not_equals: 'Inactive' }}]
            },
            {
                searchTermFilter: [{aField: {$starts: 'test' }}],
                expectedFilterDef: [{aField: {$starts: 'test' }}]
            },
            {
                baseFilter: [{status: {$not_equals: 'Inactive' }}],
                searchTermFilter: [{aField: {$starts: 'test' }}],
                expectedFilterDef: [{
                    $and: [
                        {status: {$not_equals: 'Inactive'}},
                        {aField: {$starts: 'test'}}
                    ]
                }]
            }
        ], function(dataSet) {
            it('should combine them and return a single filter', function() {
                var filterDef = prototype.combineFilterDefinitions(dataSet.baseFilter, dataSet.searchTermFilter);
                expect(filterDef).toEqual(dataSet.expectedFilterDef);
            });
        });
    });
});
