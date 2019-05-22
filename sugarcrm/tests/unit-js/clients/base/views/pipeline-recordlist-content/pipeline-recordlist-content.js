// FILE SUGARCRM flav=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('Base.Views.PipelineRecordlistContent', function() {
    var view;
    var app;
    var context;
    var viewMeta;

    beforeEach(function() {
        app = SUGAR.App;
        var context = new app.Context({
            module: 'Opportunities',
            model: app.data.createBean('Opportunities'),
            layout: 'pipeline-records'
        });
        viewMeta = {
            fields: {
                label: 'LBL_PIPELINE_TYPE',
                name: 'pipeline_type',
                type: 'pipeline-type'
            }
        };
        view = SugarTest.createView('base', 'Opportunities', 'pipeline-recordlist-content', viewMeta, context, false);
        sinon.collection.stub(app.metadata, 'getModule').withArgs('VisualPipeline', 'config').returns(
            {
                table_header: {
                    Leads: 'date_closed',
                    Opportunities: 'status'
                },
                header_colors: ['#FFFFFF', '#000000']
            }
        ).withArgs(view.module, 'fields').returns(
            {
                name: {
                    type: 'text',
                    name: 'name'
                },
                amount: {
                    type: 'currency',
                    name: 'amount'
                },
                sales_status: {
                    options: 'sales_status_dom'
                },
                status: {
                    options: 'status'
                },
                dupeTest: {
                    type: 'test'
                }
            }
        );
        sinon.collection.stub(view.context, 'on', function() {});
        sinon.collection.stub(view, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        app.view.reset();
        view = null;
    });

    describe('initialize', function() {
        beforeEach(function() {
            view.initialize({context: context});
        });

        it('should set format for start date', function() {
            expect(view.startDate).toEqual(app.date().format('YYYY-MM-DD'));
        });

        it('should call app.metadata.getModule method with VisualPipeline and config', function() {
            expect(app.metadata.getModule).toHaveBeenCalledWith('VisualPipeline', 'config');
        });

        it('should initialize view.pipelineFilters with []', function() {
            expect(view.pipelineFilters).toEqual([]);
        });

        it('should initialize view.hiddenHeaderValues with []', function() {
            expect(view.hiddenHeaderValues).toEqual([]);
        });

        it('should initialize view.action as list', function() {
            expect(view.action).toEqual('list');
        });
    });

    describe('bindDataChange', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'resizeContainer', function() {});
            sinon.collection.stub(window, 'addEventListener', function() {});
            view.bindDataChange();

        });
        it('should call view.context.on', function() {
            expect(view.context.on).toHaveBeenCalledWith('pipeline:recordlist:model:created');
            expect(view.context.on).toHaveBeenCalledWith('pipeline:recordlist:filter:changed');
            expect(view.context.on).toHaveBeenCalledWith('button:delete_button:click');
            expect(view.context.on).toHaveBeenCalledWith('pipeline:recordlist:resizeContent');
        });

        it('should call window.addEventListener with resize', function() {
            expect(window.addEventListener).toHaveBeenCalledWith('resize', view.resizeContainerHandler);
        });
    });

    describe('buildTileMeta', function() {
        beforeEach(function() {
            view.meta.tileDef = {
                fields: {
                    label: 'LBL_PIPELINE_TYPE',
                    name: 'pipeline_type',
                    type: 'pipeline-type'
                },
                panels: [
                    {
                        is_header: true,
                        name: 'header',
                        fields: []
                    },
                    {
                        name: 'body',
                        fields: []
                    }
                ]
            };
            view.pipelineConfig = {
                tile_header: {
                    Opportunities: 'name'
                },
                tile_body_fields: {
                    Opportunities: ['amount']
                }
            };
            view.pipelineType = 'date_closed';
            view.buildTileMeta();
        });

        afterEach(function() {
            view.pipelineConfig = undefined;
            view.meta.tileDef = undefined;
        });

        it('should call app.metadata.getModule method with view.module and fields', function() {
            expect(app.metadata.getModule).toHaveBeenCalled();
        });

        it('should update fields in view.meta.tileDef', function() {
            expect(view.meta.tileDef.panels[0].fields).toEqual([{
                type: 'text',
                name: 'name'
            }]);
            expect(view.meta.tileDef.panels[1].fields).toEqual([{
                type: 'currency',
                name: 'amount'
            }]);
        });
    });

    describe('setResultsPerPageColumn', function() {
        var resultsNum;
        beforeEach(function() {
            view.module = 'Leads';
        });
        describe('when records_per_column is a number', function() {
            it('should assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = undefined;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 20
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(20);
            });

            it('should assign resultsNum to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = undefined;
                resultsNum = 50;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 20
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(50);
            });
        });

        describe('when records_per_column is a not number', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: 'test'
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(7);
            });
        });

        describe('when records_per_column is a numeric string', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                resultsNum = undefined;
                view.pipelineConfig = {
                    records_per_column: {
                        Leads: '15'
                    }
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(15);
            });
        });

        describe('when records_per_column is a not defined', function() {
            it('should not assign records_per_column to resultsPerPageColumn', function() {
                view.resultsPerPageColumn = 7;
                view.pipelineConfig = {
                    records_per_column: 'test'
                };

                view.setResultsPerPageColumn(resultsNum);
                expect(view.resultsPerPageColumn).toBe(7);
            });
        });
    });

    describe('setHiddenHeaderValues', function() {
        var hiddenValues;
        beforeEach(function() {
            view.module = 'Opportunities';
        });
        describe('when view.pipelineConfig.hiddenValues is empty', function() {
            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = [];
                view.pipelineConfig = {
                    hidden_values: {
                        Tasks: []
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toBe(undefined);
            });

            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = [];
                view.pipelineConfig = {
                    hidden_values: {
                        Opportunities: []
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toBe(undefined);
            });
        });

        describe('when view.pipelineConfig.hiddenValues is not empty', function() {
            it('should not assign view.pipelineConfig.hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = undefined;
                view.pipelineConfig = {
                    hidden_values: {
                        Cases: [],
                        Leads: [],
                        Opportunities: ['Closed Won', 'Closed Lost']
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toEqual(['Closed Won', 'Closed Lost']);
            });

            it('should not assign hiddenValues to view.hiddenHeaderValues', function() {
                view.hiddenHeaderValues = undefined;
                hiddenValues = ['Test1', 'Test2'];
                view.pipelineConfig = {
                    hidden_values: {
                        Cases: [],
                        Leads: [],
                        Opportunities: ['Closed Won', 'Closed Lost']
                    }
                };
                view.setHiddenHeaderValues(hiddenValues);

                expect(view.hiddenHeaderValues).toEqual(['Test1', 'Test2']);
            });
        });
    });

    describe('buildFilters', function() {
        var filterDef;
        beforeEach(function() {
            filterDef = ['test'];
            sinon.collection.stub(view, 'loadData', function() {});
            view.buildFilters(filterDef);
        });

        it('should set view.offset to 0', function() {
            expect(view.offset).toBe(0);
        });

        it('should assign filterDef to view.pipelineFilters', function() {
            expect(view.pipelineFilters).toEqual(['test']);
        });

        it('should should call loadData method', function() {
            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('loadData', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'buildTileMeta', function() {});
            sinon.collection.stub(view, 'setResultsPerPageColumn', function() {});
            sinon.collection.stub(view, 'setHiddenHeaderValues', function() {});
            sinon.collection.stub(view, 'getTableHeader', function() {});
            sinon.collection.stub(view, 'buildRecordsList', function() {});
        });

        it('should set view.recordsToDisplay as an empty array', function() {
            view.loadData();

            expect(view.recordsToDisplay).toEqual([]);
        });

        it('should call the view.buildTileMeta method', function() {
            view.loadData();

            expect(view.buildTileMeta).toHaveBeenCalled();
        });

        it('should call the view.setResultsPerPageColumn method', function() {
            view.loadData();

            expect(view.setResultsPerPageColumn).toHaveBeenCalled();
        });

        it('should call the view.setHiddenHeaderValues method', function() {
            view.loadData();

            expect(view.setHiddenHeaderValues).toHaveBeenCalled();
        });

        it('should call the view.getTableHeader method', function() {
            view.loadData();

            expect(view.getTableHeader).toHaveBeenCalled();
        });

        describe('when view.hasAccessToView is true', function() {
            it('should call the view.buildRecordsList method', function() {
                view.hasAccessToView = true;
                view.loadData();

                expect(view.buildRecordsList).toHaveBeenCalled();
            });
        });

        describe('when view.hasAccessToView is false', function() {
            it('should not call the view.buildRecordsList method', function() {
                view.hasAccessToView = false;
                view.loadData();

                expect(view.buildRecordsList).not.toHaveBeenCalled();
            });
        });
    });

    describe('getTableHeader', function() {
        var headerColors;
        beforeEach(function() {
            view.recordsToDisplay = [];
            sinon.collection.stub(view, 'getColumnColors', function() {
                return ['#FFFFFF', '#000000', '#FFFFFF', '#000000', '#FFFFFF', '#000000'];
            });
            headerColors = view.getColumnColors();
        });

        it('should call the view.getColumnColors', function() {
            view.module = 'Opportunities';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.getTableHeader();

            expect(view.getColumnColors).toHaveBeenCalled();
        });

        it('should update view.hasAccessToView', function() {
            view.module = 'Opportunities';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.getTableHeader();

            expect(view.hasAccessToView).toBeTruthy();
        });

        it('should call view._super with render', function() {
            view.module = 'Opportunities';
            view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            view.getTableHeader();

            expect(view._super).toHaveBeenCalledWith('render');
        });

        describe('when pipeline_type is not date_closed', function() {
            beforeEach(function() {
                view.pipelineType = 'sales_status';
                view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
            });

            it('should assign headerField to view.headerField', function() {
                view.getTableHeader();

                expect(view.headerField).toEqual('status');
            });

            describe('when app.acl.hasAccessToModel is false', function() {
                it('should call view.context.trigger to have been called with open:config:fired', function() {
                    sinon.collection.stub(app.acl, 'hasAccessToModel').withArgs('read', view.model, 'status')
                        .returns(false);
                    sinon.collection.stub(view.context, 'trigger', function() {});
                    view.getTableHeader();

                    expect(view.context.trigger).toHaveBeenCalledWith('open:config:fired');
                });
            });

            describe('when app.acl.hasAccessToModel is true', function() {
                it('should not call view.context.trigger to have been called with open:config:fired', function() {
                    sinon.collection.stub(app.acl, 'hasAccessToModel').withArgs('read', view.model, 'status')
                        .returns(true);
                    sinon.collection.stub(view.context, 'trigger', function() {});
                    view.getTableHeader();

                    expect(view.context.trigger).not.toHaveBeenCalledWith('open:config:fired');
                });

                describe('when headerField is defined', function() {
                    beforeEach(function() {
                        view.recordsToDisplay = [];
                    });

                    describe('when optionList is defined', function() {
                        it('should call app.lang.getAppListStrings', function() {
                            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                                return {
                                    Lost: 'Closed Lost',
                                    New: 'New'
                                };
                            });
                            view.getTableHeader();

                            expect(app.lang.getAppListStrings).toHaveBeenCalled();
                        });
                    });

                    describe('when options is empty', function() {
                        it('should not populate view.recordsToDisplay', function() {
                            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                                return [];
                            });
                            view.getTableHeader();

                            expect(view.recordsToDisplay).toEqual([]);
                        });
                    });

                    describe('when options is not empty', function() {
                        it('should populate view.recordsToDisplay', function() {
                            sinon.collection.stub(app.lang, 'getAppListStrings', function() {
                                return {
                                    Lost: 'Closed Lost',
                                    New: 'New'
                                };
                            });
                            view.getTableHeader();

                            expect(view.recordsToDisplay.length).toEqual(2);
                        });
                    });
                });
            });
        });

        describe('when pipeline_type is date_closed', function() {
            beforeEach(function() {
                view.module = 'Leads';
                view.pipelineConfig = app.metadata.getModule('VisualPipeline', 'config');
                view.pipelineType = 'date_closed';
            });
            it('should set view.headerField as date_closed', function() {
                view.getTableHeader();

                expect(view.headerField).toEqual('date_closed');
            });

            it('should populate view.recordsToDisplay', function() {
                view.monthsToDisplay = 6;
                view.getTableHeader();

                expect(view.recordsToDisplay.length).toEqual(6);
            });

            it('should call app.date with view.startDate', function() {
                view.monthsToDisplay = 6;
                sinon.collection.stub(app, 'date', function() {
                    return {
                        add: sinon.collection.stub(),
                        format: sinon.collection.stub()
                    };
                });
                view.getTableHeader();

                expect(app.date).toHaveBeenCalledWith(view.startDate);
            });
        });
    });

    describe('getColumnColors', function() {
        var columnColor;

        describe('when columnColors is null', function() {
            it('should assign columnColor to {}', function() {
                view.pipelineConfig = {
                    header_colors: null
                };
                columnColor = view.pipelineConfig.header_colors;
                columnColor = view.getColumnColors();

                expect(columnColor).toEqual({});
            });
        });

        describe('when columnColors is empty', function() {
            it('should assign columnColor to {}', function() {
                view.pipelineConfig = {
                    header_colors: []
                };
                columnColor = view.pipelineConfig.header_colors;
                columnColor = view.getColumnColors();

                expect(columnColor).toEqual({});
            });
        });
    });

    describe('preRender', function() {
        it('should set the view.offset to 0', function() {
            view.offset = 10;
            view.preRender();

            expect(view.offset).toBe(0);
        });
    });

    describe('render', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'preRender', function() {});
            sinon.collection.stub(view, 'postRender', function() {});
            view.render();
        });

        it('should call preRender function', function() {

            expect(view.preRender).toHaveBeenCalled();
        });

        it('should call _super with render function', function() {

            expect(view._super).toHaveBeenCalledWith('render');
        });

        it('should call postRender function', function() {

            expect(view.postRender).toHaveBeenCalled();
        });
    });

    describe('postRender', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'resizeContainer', function() {});
            sinon.collection.stub(view, 'buildDraggable', function() {});
            sinon.collection.stub(view, 'bindScroll', function() {});
            view.postRender();
        });

        it('should call resizeContainer function', function() {

            expect(view.resizeContainer).toHaveBeenCalled();
        });

        it('should call buildDraggable function', function() {

            expect(view.buildDraggable).toHaveBeenCalled();
        });

        it('should call bindScroll function', function() {

            expect(view.bindScroll).toHaveBeenCalled();
        });
    });

    describe('addModelToCollection', function() {
        var collection;
        var literal;
        var model;
        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            literal = [];
            sinon.collection.stub(view, 'addTileVisualIndicator', function() {
                return [{
                    tileVisualIndicator: '#F0F0F0'
                }];
            });
            sinon.collection.stub(view, 'postRender', function() {});
        });

        it('should add model to the column when visible', function() {
            sinon.collection.stub(view, 'getColumnCollection', function() {
                return {
                    color: '#FFF000',
                    headerKey: 'testKey',
                    headerName: 'testName',
                    records: {
                        models: [],
                        add: function() {
                            return model;
                        }
                    }
                };
            });

            collection = view.getColumnCollection();

            view.addModelToCollection(model);
            expect(view.getColumnCollection).toHaveBeenCalled();
            expect(view.addTileVisualIndicator).toHaveBeenCalled();
            expect(model.attributes.tileVisualIndicator).toEqual('#F0F0F0');
            expect(view._super).toHaveBeenCalledWith('render');
            expect(view.postRender).toHaveBeenCalled();
        });

        it('should not add model when the column header is not visible', function() {
            sinon.collection.stub(view, 'getColumnCollection', function() {
                return null;
            });

            collection = view.getColumnCollection();

            view.addModelToCollection(model);
            expect(view.getColumnCollection).toHaveBeenCalled();
            expect(view.addTileVisualIndicator).not.toHaveBeenCalled();
            expect(view._super).toHaveBeenCalledWith('render');
            expect(view.postRender).toHaveBeenCalled();
        });

    });

    describe('getColumnCollection', function() {
        var model;
        beforeEach(function() {
            model = app.data.createBean('Opportunities');
        });

        afterEach(function() {
            model = null;
        });

        describe('when pipeline_type is date_closed', function() {
            it('should check the pipeline-type of the model', function() {
                view.pipelineType = 'date_closed';
                sinon.collection.stub(app, 'date', function() {
                    return {
                        format: function() {}
                    };
                });
                view.getColumnCollection(model);

                expect(app.date).toHaveBeenCalled();
            });
        });
    });

    describe('buildRecordList', function() {
        beforeEach(function() {
            sinon.collection.stub(app.alert, 'show', $.noop);
            sinon.collection.stub(view, 'getRecords', function() {});
            view.buildRecordsList();
        });

        it('should find the #loadingCell element and call show method on it', function() {
            expect(app.alert.show).toHaveBeenCalled();
        });

        it('should call the view.getRecords method', function() {
            expect(view.getRecords).toHaveBeenCalled();
        });
    });

    describe('getFilters', function() {
        var filter;
        var column;
        beforeEach(function() {
            column = {
                color: '#36850F',
                headerKey: 'April 2019',
                headerName: 'April 2019',
                records: []
            };
            filter = [];
        });

        afterEach(function() {
            filter = null;
        });

        describe('when pipeline_type is not date_closed', function() {
            beforeEach(function() {
                sinon.collection.stub(view.context, 'get', function() {
                    return {
                        get: function() {
                            return 'testType';
                        }
                    };
                });
            });

            it('should set headerField object in filter', function() {
                view.headerField = 'sales_status';
                filter = view.getFilters(column);

                expect(filter[0].sales_status).toEqual({
                    '$equals': column.headerKey
                });
            });

            it('should add all the view.pipelineFilters to filter array', function() {
                view.headerField = 'date_closed';
                view.pipelineFilters = [
                    {
                        test_filter: {
                            $random_check: 'testFilter'
                        }
                    }
                ];
                filter = view.getFilters(column);

                expect(filter.length).toEqual(2);
                expect(filter[1].test_filter).toEqual({
                    $random_check: 'testFilter'
                });
            });
        });

        describe('when pipeline_type is date_closed', function() {
            beforeEach(function() {
                view.pipelineType = 'date_closed';
                view.headerField = 'date_closed';
            });

            it('should set the start and end dates in filter', function() {
                filter = view.getFilters(column);

                expect(filter[0].date_closed).toEqual({
                    '$dateBetween': [
                        app.date(column.headerName, 'MMMM YYYY').startOf('month').format('YYYY-MM-DD'),
                        app.date(column.headerName, 'MMMM YYYY').endOf('month').format('YYYY-MM-DD')
                    ]
                });
            });

            it('should add all the view.pipelineFilters to filter array', function() {
                view.pipelineFilters = [
                    {
                        sales_status: {
                            $not_empty: ''
                        }
                    },
                    {
                        test_filter: {
                            $random_check: 'testFilter'
                        }
                    }
                ];
                filter = view.getFilters(column);

                expect(filter.length).toEqual(3);
                expect(filter[2].test_filter).toEqual({
                    $random_check: 'testFilter'
                });
            });
        });
    });

    describe('getFieldsForFetch', function() {
        var fields;

        beforeEach(function() {
            view.meta.tileDef = {
                panels: [
                    {
                        is_header: true,
                        name: 'header',
                        fields: [{
                            name: 'name'
                        }]
                    },
                    {
                        name: 'body',
                        fields: [
                            {
                                name: 'amount'
                            },
                            {
                                name: 'account_name'
                            },
                            {
                                name: 'sales_status'
                            },
                            {
                                name: 'dupeTest'
                            },
                            {
                                name: 'dupeTest'
                            }
                        ]
                    }
                ]
            };

            view.tileVisualIndicatorFields = {
                Leads: 'status',
                Opportunities: 'date_closed'
            };
        });

        afterEach(function() {
            fields = null;
        });

        it('should call app.metadata.getModule method', function() {
            view.getFieldsForFetch();

            expect(app.metadata.getModule).toHaveBeenCalled();
        });

        it('should reject the invalid field names from fields array', function() {
            fields = view.getFieldsForFetch();

            expect(fields).toEqual(['name', 'amount', 'sales_status', 'dupeTest']);
        });
    });

    describe('getRecords', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'getFieldsForFetch', function() {});
            sinon.collection.stub(view, 'buildRequests', function() {});
            sinon.collection.stub(view, 'fetchData', function() {});

            view.getRecords();
        });

        it('should call getFieldsForFetch method', function() {

            expect(view.getFieldsForFetch).toHaveBeenCalled();
        });

        it('should call buildRequests method', function() {

            expect(view.buildRequests).toHaveBeenCalled();
        });

        it('should call fetchData method', function() {

            expect(view.fetchData).toHaveBeenCalled();
        });
    });

    describe('buildRequests', function() {
        var request;
        beforeEach(function() {
            request = {
                requests: []
            };

            view.recordsToDisplay = [
                {
                    color: '#FFF000',
                    headerKey: 'Test Key1',
                    headerName: 'Test name1',
                    records: []
                },
                {
                    color: '#000FFF',
                    headerKey: 'Test Key2',
                    headerName: 'Test name2',
                    records: []
                }
            ];

            sinon.collection.stub(app.api, 'buildURL', function() {
                return 'testUrl';
            });
        });
        afterEach(function() {
            request = null;
        });

        it('should populate the request object', function() {
            request = view.buildRequests();

            expect(request.requests).toEqual(
                [
                    {
                        dataType: 'json',
                        method: 'GET',
                        url: 'testUrl'
                    },
                    {
                        dataType: 'json',
                        method: 'GET',
                        url: 'testUrl'
                    }
                ]);
        });
    });

    describe('fetchData', function() {
        var url;

        beforeEach(function() {
            url = 'testUrl';

            sinon.collection.stub(app.api, 'buildURL', function() {
                return 'testUrl';
            });
            sinon.collection.stub(app.api, 'call', function() {});
            view.fetchData('testRequest');
        });

        afterEach(function() {
            url = null;
        });

        it('should set view.modeData to false', function() {

            expect(view.moreData).toBe(false);
        });

        it('should call app.api.buildURL', function() {

            expect(app.api.buildURL).toHaveBeenCalled();
        });

        it('should call app.api.call with create, url and requests', function() {

            expect(app.api.call).toHaveBeenCalledWith('create', url, 'testRequest');
        });
    });

    describe('resizeContainer', function() {
        var height;
        beforeEach(function() {
            sinon.collection.stub(view.$el, 'parents', function() {
                return {
                    height: function() {
                        return 500;
                    },
                    find: function() {
                        return {
                            height: function() {
                                return 200;
                            }
                        };
                    }
                };
            });
            sinon.collection.stub(view.$el, 'height', function() {});
            sinon.collection.stub(jQuery.fn, 'height', function() {});

            view.resizeContainer();
            height = (view.$el.parents('.main-pane').height() -
                view.$el.parents('.main-pane').find('.search-filter').height());
        });

        it('should height to be 300', function() {

            expect(height).toEqual(300);
        });

        it('should set height for my-pipeline-content element', function() {

            expect(view.$el.height).toHaveBeenCalledWith('300px');
            expect(jQuery.fn.height).toHaveBeenCalledWith('150px');
        });
    });

    describe('buildDraggable', function() {
        var addClassStub;
        var findStub;
        var sortableStub;
        beforeEach(function() {
            addClassStub = sinon.collection.stub();
            findStub = sinon.collection.stub();
            sortableStub = sinon.collection.stub();

            sinon.collection.stub(view, '$', function() {
                return {
                    sortable: sortableStub,
                    addClass: function() {
                        return {
                            find: function() {
                                return {
                                    addClass: addClassStub
                                };
                            }
                        };
                    }
                };
            });
        });

        describe('when app.acl.hasAccessTOModel is false', function() {
            beforeEach(function() {
                view.headerField = 'date_closed';
            });

            it('should not call view.$.sortable method', function() {
                sinon.collection.stub(app.acl, 'hasAccessToModel').withArgs('edit', view.model)
                    .returns(false);
                view.buildDraggable();

                expect(sortableStub).not.toHaveBeenCalled();
            });
        });

        describe('when app.acl.hasAccessTOModel is true', function() {
            beforeEach(function() {
                view.headerField = 'date_closed';
            });
            it('should not call view.$.sortable method', function() {
                sinon.collection.stub(app.acl, 'hasAccessToModel').withArgs('edit', view.model)
                    .returns(true);
                view.buildDraggable();

                expect(sortableStub).toHaveBeenCalled();
            });
        });
    });

    describe('switchCollection', function() {
        var oldCollection;
        var newCollection;
        var model;
        beforeEach(function() {
            model = {
                cid: 'testCid2'
            };

            oldCollection = {
                color: '#000000',
                headerKey: 'April 2019',
                headerName: 'April 2019',
                records: {
                    models: [
                        {
                            cid: 'testCid1'
                        },
                        {
                            cid: 'testCid2'
                        },
                        {
                            cid: 'testCid3'
                        }
                    ],

                    remove: sinon.collection.stub()
                }
            };

            newCollection = {
                color: '#FFFFFF',
                headerKey: 'May 2019',
                headerName: 'May 2019',
                records: {
                    models: [
                        {
                            cid: 'testCid4'
                        },
                        {
                            cid: 'testCid5'
                        },
                        {
                            cid: 'testCid6'
                        }
                    ],

                    add: sinon.collection.stub()
                }
            };

            view.switchCollection(oldCollection, model, newCollection);
        });

        it('should remove the model from oldCollection', function() {

            expect(oldCollection.records.remove).toHaveBeenCalled();
        });

        it('should add the model into newCollection', function() {

            expect(newCollection.records.add).toHaveBeenCalled();
        });
    });

    describe('saveModel', function() {
        var model;
        var ui;
        beforeEach(function() {
            view.headerField = 'testHeader';
            ui = {
                item: 'test'
            };
            model = app.data.createBean('Opportunities');
            sinon.collection.stub(view, '$', function() {
                return {
                    parent: function() {
                        return {
                            data: function() {
                                return 'testColumn';
                            }
                        };
                    }
                };
            });

            sinon.collection.stub(model, 'set', function() {});
            sinon.collection.stub(model, 'save', function() {});

            view.saveModel(model, ui);
        });

        it('should set view.headerField for the model', function() {

            expect(model.set).toHaveBeenCalledWith('testHeader', 'testColumn');
        });

        it('should call model.save function', function() {

            expect(model.save).toHaveBeenCalled();
        });
    });

    describe('deleteRecord', function() {
        var model;
        beforeEach(function() {
            model = app.data.createBean('Opportunities');
            sinon.collection.stub(app.alert, 'show', function() {});
            sinon.collection.stub(view, 'postRender');
        });

        it('should double check with the user when delete button is clicked', function() {
            sinon.collection.stub(view, 'getDeleteMessages', function() {
                return {
                    confirmation: false
                };
            });
            view.deleteRecord(model);

            expect(app.alert.show).toHaveBeenCalledWith('delete_confirmation',
                jasmine.objectContaining({level: 'confirmation'}));
        });
    });

    describe('getDeleteMessages', function() {
        var model;
        var messages;
        beforeEach(function() {
            model = {
                module: 'Opportunities'
            };
            messages = {};
            sinon.collection.stub(app.lang, 'get', function(lbl) {
                return lbl;
            });
            sinon.collection.stub(app.lang, 'getModuleName', function(lbl) {
                return lbl;
            });
            sinon.collection.spy(app.utils, 'formatString');
            sinon.collection.stub(app.utils, 'getRecordName', function() {});

            messages = view.getDeleteMessages(model);
        });

        it('should call app.utils.getRecordName', function() {
            expect(app.utils.getRecordName).toHaveBeenCalled();
        });

        it('should call app.utils.ƒormatString', function() {
            expect(app.utils.formatString).toHaveBeenCalled();
        });

        it('should call app.lang.getModuleName', function() {
            expect(app.lang.getModuleName).toHaveBeenCalled();
        });

        it('should call app.lang.get', function() {
            expect(app.lang.get).toHaveBeenCalledWith('NTC_DELETE_SUCCESS');
        });

        it('should return confirmation and success messages', function() {
            expect(messages).toEqual({
                confirmation: 'NTC_DELETE_CONFIRMATION_FORMATTED',
                success: 'NTC_DELETE_SUCCESS'
            });
        });
    });

    describe('bindScroll', function() {
        it('should bind scroll to the .my-pipeline-content element', function() {
            sinon.collection.stub(view, 'listScrolled', function() {});
            sinon.collection.stub(view.$el, 'on', function() {});
            view.bindScroll();

            expect(view.$el.on).toHaveBeenCalledWith('scroll');
        });
    });

    describe('addTileVisualIndicator', function() {
        var modelsList;
        beforeEach(function() {
            view.tileVisualIndicator = {
                default: '#000000'
            };

            sinon.collection.stub(view, 'addIndicatorBasedOnStatus');
            sinon.collection.stub(view, 'addIndicatorBasedOnDate');
        });

        describe('when module is Cases', function() {
            it('should call view.addIndicatorBasedOnStatus with model', function() {
                modelsList = [
                    {
                        _module: 'Cases',
                        tileVisualIndicator: '#FFFFFF'
                    }
                ];
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnStatus).toHaveBeenCalledWith(modelsList[0]);
            });
        });

        describe('when module is Opportunities', function() {
            it('should call view.addIndicatorBasedOnDate with model and expectedCloseDate', function() {
                modelsList = [
                    {
                        _module: 'Opportunities',
                        tileVisualIndicator: '#FFFFFF',
                        date_closed: '2019-04-04'
                    }
                ];
                expectedCloseDate = app.date(modelsList[0].date_closed, 'YYYY-MM-DD');
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnDate).toHaveBeenCalledWith(modelsList[0], expectedCloseDate);
            });
        });

        describe('when module is Tasks', function() {
            it('should call view.addIndicatorBasedOnDate with model and expectedCloseDate', function() {
                modelsList = [
                    {
                        _module: 'Tasks',
                        tileVisualIndicator: '#FFFFFF',
                        date_due: '2019-04-04'
                    }
                ];
                dueDate = app.date.parseZone(modelsList[0].date_due);
                view.addTileVisualIndicator(modelsList);

                expect(view.addIndicatorBasedOnDate).toHaveBeenCalledWith(modelsList[0], dueDate);
            });
        });

        describe('when module is not Opportunities/Cases/Tasks/Leads', function() {
            it('should assign tileVisualIndicator to view.tileVisualIndicator.default', function() {
                modelsList = [
                    {
                        _module: 'Accounts',
                        tileVisualIndicator: '#FFFFFF'
                    }
                ];
                view.addTileVisualIndicator(modelsList);

                expect(modelsList[0].tileVisualIndicator).toEqual('#000000');
            });
        });
    });

    describe('addIndicatorBasedOnDate', function() {
        var model;
        var date;
        beforeEach(function() {
            date = app.date('2019-04-04', 'YYYY-MM-DD');
            model = {
                tileVisualIndicator: '#000000'
            };
            view.tileVisualIndicator = {
                outOfDate: '#FFFFFF',
                inFuture: '#F0F0F0',
                nearFuture: '#000FFF'
            };
        });

        describe('when date is before now', function() {
            it('should set model.tileVisualIndicator to outOfDate', function() {
                sinon.collection.stub(date, 'isBefore', function() {
                    return true;
                });
                sinon.collection.stub(date, 'isAfter', function() {
                    return false;
                });
                sinon.collection.stub(date, 'isBetween', function() {
                    return false;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#FFFFFF');
            });
        });

        describe('when date is after now', function() {
            it('should set model.tileVisualIndicator to inFuture', function() {
                sinon.collection.stub(date, 'isBefore', function() {
                    return false;
                });
                sinon.collection.stub(date, 'isAfter', function() {
                    return true;
                });
                sinon.collection.stub(date, 'isBetween', function() {
                    return false;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#F0F0F0');
            });
        });

        describe('when date is between now and a month from now', function() {
            it('should set model.tileVisualIndicator to nearFuture', function() {
                sinon.collection.stub(date, 'isBefore', function() {
                    return false;
                });
                sinon.collection.stub(date, 'isAfter', function() {
                    return false;
                });
                sinon.collection.stub(date, 'isBetween', function() {
                    return true;
                });
                view.addIndicatorBasedOnDate(model, date);

                expect(model.tileVisualIndicator).toEqual('#000FFF');
            });
        });
    });

    describe('addIndicatorBasedOnStatus', function() {
        var inFuture;
        var outOfDate;
        var nearFuture;
        var model;
        beforeEach(function() {
            inFuture = ['New', 'Converted'];
            outOfDate = ['Dead', 'Closed', 'Rejected', 'Duplicate','Recycled'];
            nearFuture = ['Assigned', 'In Process', , 'Pending Input', ''];

            view.tileVisualIndicator = {
                outOfDate: '#FFFFFF',
                inFuture: '#F0F0F0',
                nearFuture: '#000FFF'
            };
        });

        describe('when model.status is in outOfDate', function() {
            it('should set model.tileVisualIndicator to outOfDate', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Duplicate'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#FFFFFF');
            });
        });

        describe('when model.status is in inFuture', function() {
            it('should set model.tileVisualIndicator to inFuture', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Converted'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#F0F0F0');
            });
        });

        describe('when model.status is in nearFuture', function() {
            it('should set model.tileVisualIndicator to nearFuture', function() {
                model = {
                    tileVisualIndicator: '#000000',
                    status: 'Assigned'
                };
                view.addIndicatorBasedOnStatus(model);

                expect(model.tileVisualIndicator).toEqual('#000FFF');
            });

            describe('when model.status is empty', function() {
                it('should set model.tileVisualIndicator to nearFuture', function() {
                    model = {
                        tileVisualIndicator: '#000000',
                        status: ''
                    };
                    view.addIndicatorBasedOnStatus(model);

                    expect(model.tileVisualIndicator).toEqual('#000FFF');
                });
            });

            describe('when model.status is not defined', function() {
                it('should set model.tileVisualIndicator to nearFuture', function() {
                    model = {
                        tileVisualIndicator: '#000000'
                    };
                    view.addIndicatorBasedOnStatus(model);

                    expect(model.tileVisualIndicator).toEqual('#000FFF');
                });
            });
        });
    });

    describe('navigateLeft', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'loadData', function() {});
            view.startDate = app.date('2019-04-04', 'YYYY-MM-DD');
            view.navigateLeft();
        });

        it('should set the startDate to 5 months earlier', function() {

            expect(view.startDate).toEqual('2018-11-04');
        });

        it('should set offset to 0', function() {

            expect(view.offset).toBe(0);
        });

        it('should call view.loadData method', function() {

            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('navigateRight', function() {
        beforeEach(function() {
            sinon.collection.stub(view, 'loadData', function() {});
            view.startDate = app.date('2019-04-04', 'YYYY-MM-DD');
            view.navigateRight();
        });

        it('should set the startDate to 5 months later', function() {

            expect(view.startDate).toEqual('2019-09-04');
        });

        it('should set offset to 0', function() {

            expect(view.offset).toBe(0);
        });

        it('should call view.loadData method', function() {

            expect(view.loadData).toHaveBeenCalled();
        });
    });

    describe('_dispose', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'off', function() {});
            sinon.collection.stub(view.$el, 'off', function() {});
            sinon.collection.stub(window, 'removeEventListener', function() {});
            view._dispose();
        });

        it('should call view.context.off method', function() {
            expect(view.context.off).toHaveBeenCalledWith('pipeline:recordlist:model:created');
            expect(view.context.off).toHaveBeenCalledWith('pipeline:recordlist:filter:changed');
            expect(view.context.off).toHaveBeenCalledWith('button:delete_button:click');
            expect(view.context.off).toHaveBeenCalledWith('pipeline:recordlist:resizeContent');
        });

        it('should call window.removeEventListener with resize and view.resizeContainerHandler', function() {

            expect(window.removeEventListener).toHaveBeenCalledWith('resize', view.resizeContainerHandler);
        });

        it('should call view.$el.off method with scroll', function() {

            expect(view.$el.off).toHaveBeenCalledWith('scroll');
        });

        it('should call view._super wtih _dispose', function() {
            expect(view._super).toHaveBeenCalledWith('_dispose');
        });
    });
});
