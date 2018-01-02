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
describe('SugarCharts', function() {
    var app;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile(
            '../include/SugarCharts/sucrose/js',
            'sugarCharts',
            'js',
            function(d) {
                eval(d);
            });
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        app.cache.cutAll();
        sinon.collection.restore();
    });

    it('should return a state object when buildChartState is called', function() {
        var eo;
        var labels;
        var state;
        eo = {
            series: {seriesIndex: 1}
        };
        labels = [];
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(1);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        eo = {seriesIndex: 2};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(2);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        eo = {pointIndex: 3};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(3);
        expect(state.groupIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();

        labels = ['Foo'];
        eo = {pointIndex: 4};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).not.toBeDefined();
        expect(state.pointIndex).toBe(4);
        expect(state.groupIndex).not.toBeDefined();

        eo = {groupIndex: 5};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).not.toBeDefined();
        expect(state.pointIndex).not.toBeDefined();
        expect(state.groupIndex).toBe(5);

        eo = {seriesIndex: 6, groupIndex: 6, pointIndex: 6};
        state = SUGAR.charts.buildChartState(eo, labels);
        expect(state.seriesIndex).toBe(6);
        expect(state.pointIndex).toBe(6);
        expect(state.groupIndex).toBe(6);
    });

    it('should return a series label when extractSeriesLabel is called', function() {
        var state = {seriesIndex: 1};
        var data = [{key: 'Foo'}, {key: 'Bar'}];
        var label = SUGAR.charts.extractSeriesLabel(state, data);
        expect(label).toBe('Bar');
    });

    it('should return a group label when extractGroupLabel is called', function() {
        var state = {groupIndex: 1};
        var labels = ['Foo', 'Bar'];
        var label = SUGAR.charts.extractGroupLabel(state, labels);
        expect(label).toBe('Bar');
    });

    it('should return a grouping definition when getGrouping is called', function() {
        var reportDef = {group_defs: [{key0: 'Foo'}, {key1: 'Bar'}]};
        var i;
        var grouping;

        i = 0;
        grouping = SUGAR.charts.getGrouping(reportDef, i);
        expect(grouping.key0).toBe('Foo');

        i = 1;
        grouping = SUGAR.charts.getGrouping(reportDef, i);
        expect(grouping.key1).toBe('Bar');

        reportDef.group_defs = [{key0: 'Baz'}];
        grouping = SUGAR.charts.getGrouping(reportDef);
        expect(grouping).toBe(reportDef.group_defs);
    });

    it('should define a cache fiscaltimeperiods property when defineFiscalYearStart is called', function() {
        var apiStub = sinon.stub(app.api, 'call', function() {
            app.cache.set('fiscaltimeperiods', {'annualDate': '2017-07-01'});
        });
        var timeperiods;

        SUGAR.charts.defineFiscalYearStart();

        timeperiods = app.cache.get('fiscaltimeperiods');
        expect(timeperiods).toEqual({annualDate: '2017-07-01'});

        apiStub.restore();
    });

    it('should define annualDate in cache when setFiscalStartDate is called', function() {
        var firstQuarter = {
            'name': '2017 Q1',
            'start_date': '2017-01-01',
            'end_date': '2017-03-31',
            'type': 'Quarter'
        };
        var date;
        SUGAR.charts.setFiscalStartDate(firstQuarter);
        date = app.cache.get('fiscaltimeperiods');
        expect(date.annualDate).toBe('Sun, 01 Jan 2017 00:00:00 GMT');

        firstQuarter = {
            'name': '2017 Q1',
            'start_date': '2017-07-01',
            'end_date': '2017-09-30',
            'type': 'Quarter'
        };
        SUGAR.charts.setFiscalStartDate(firstQuarter);
        date = app.cache.get('fiscaltimeperiods');
        expect(date.annualDate).toBe('Sat, 01 Jul 2017 00:00:00 GMT');

        firstQuarter = {
            'name': '2018 Q1',
            'start_date': '2017-10-01',
            'end_date': '2017-12-31',
            'type': 'Quarter'
        };
        SUGAR.charts.setFiscalStartDate(firstQuarter);
        date = app.cache.get('fiscaltimeperiods');
        expect(date.annualDate).toBe('Sun, 01 Oct 2017 00:00:00 GMT');
    });

    it('should return a date string when getFiscalStartDate is called', function() {
        var date = SUGAR.charts.getFiscalStartDate();
        expect(date).toBeNull();
        app.cache.set('fiscaltimeperiods', {annualDate: '2017-04-01'});
        date = SUGAR.charts.getFiscalStartDate();
        expect(date).toBe('2017-04-01');
    });

    it('should return a date value array when getDateValues is called', function() {
        var values = SUGAR.charts.getDateValues('March 2017', 'month');
        expect(values[0]).toBe('2017-03-01');
        expect(values[1]).toBe('2017-03-31');
        expect(values[2]).toBe('month');
        //TODO: test the other date formats?
    });

    it('should return a values array when getValues is called', function() {
        var def = {table_key: 'self', name: 'Bar', column_function: ''};
        var enums = {'self:Bar': {Foo: 'Baz'}};
        var type;
        var label;

        var langGetStub = sandbox.stub(app.lang, 'get', function() {
            return 'Undefined';
        });
        var getAppListStringsStub = sandbox.stub(app.lang, 'getAppListStrings', function() {
            return {on: 'Foo', off: 'Bar'};
        });

        var values;

        type = 'bool';
        label = 'Foo';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['1']);
        label = 'Bar';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['0']);

        type = 'enum';
        label = 'Foo';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['Baz']);
        type = 'radioenum';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['Baz']);

        type = 'anythingelse';
        label = 'Foo';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['Foo']);
        label = 'Undefined';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['']);

        sinon.collection.spy(SUGAR.charts, 'getDateValues');

        type = 'date';
        def.column_function = 'year';
        label = '2017-09-09';
        values = SUGAR.charts.getValues(label, def, type, enums);
        expect(values).toEqual(['2017-01-01', '2017-12-31', 'year']);
        expect(SUGAR.charts.getDateValues).toHaveBeenCalledWith(label, def.column_function);

        langGetStub.restore();
        getAppListStringsStub.restore();
    });

    it('should return a filter array when buildFilter is called', function() {
        var reportDef = {
            'module': 'Mock',
            'group_defs': [
                {
                    'name': 'group_mock',
                    'label': 'Foo',
                    'table_key': 'self'
                },
                {
                    'name': 'series_mock',
                    'label': 'Bar',
                    'table_key': 'self'
                }
            ]
        };
        var params = {
            groupType: 'grouped',
            groupLabel: 'Foo',
            seriesLabel: 'Bar'
        };
        var enums = {'self:group_mock': {Foo: 'Baz'}, 'self:series_mock': {Bar: 'Fiz'}};

        var getFieldDefStub = sandbox.stub(SUGAR.charts, 'getFieldDef', function() {
                return {type: 'enum'};
            });

        var filter = SUGAR.charts.buildFilter(reportDef, params, enums);
        var expected = [
            {'self:group_mock': ['Baz']},
            {'self:series_mock': ['Fiz']}
        ];

        expect(filter).toEqual(expected);

        getFieldDefStub.restore();
    });

    it('should return and array of enum groupings when _getEnums is called', function() {
        var groupings = [{name: 'mock'}];
        var enumType;
        var getGroupingStub = sandbox.stub(SUGAR.charts, 'getGrouping', function() {
                return groupings;
            });
        var getFieldDefStub = sandbox.stub(SUGAR.charts, 'getFieldDef', function() {
                return enumType;
            });
        var enums;

        enumType = {type: 'enum'};
        enums = SUGAR.charts._getEnums({});
        expect(enums).toEqual(groupings);

        enumType = {type: 'radioenum'};
        enums = SUGAR.charts._getEnums({});
        expect(enums).toEqual(groupings);

        enumType = {type: 'anythingelse'};
        enums = SUGAR.charts._getEnums({});
        expect(enums).toEqual([]);

        getGroupingStub.restore();
        getFieldDefStub.restore();
    });

    it('should return a field metadata definition when getFieldDef is called', function() {
        var fieldDefMock = {type: 'Foo'};
        var getFieldStub = sandbox.stub(app.metadata, 'getField', function() {
                return fieldDefMock;
            });
        var groupDef = {
            table_key: 'self',
            name: 'mock'
        };
        var reportDef = {
            'module': 'Mock',
            'group_defs': [
                {
                    'name': 'mock',
                    'table_key': 'self',
                }
            ]
        };
        var fieldDef = SUGAR.charts.getFieldDef(groupDef, reportDef);
        expect(fieldDefMock).toEqual(fieldDef);
        //TODO: test table_key split
    });

    it('should return a translated string when translateString is called', function() {
        var string;
        var langGetStub = sandbox.stub(app.lang, 'get', function(label, module) {
            return module ? 'Foo' : 'Bar';
        });
        string = SUGAR.charts.translateString('LBL_CHART_UNDEFINED');
        expect(string).toBe('Bar');
        string = SUGAR.charts.translateString('LBL_CHART_NO_DRILLTHRU', 'Mock');
        expect(string).toBe('Foo');
        langGetStub.restore();
    });

    it('should return a chart data json object when translateDataToD3 is called', function() {
        var json = {
            'properties': [{
                'title': 'Total is 20'
            }],
            'label': [
                'Red',
                'Yellow',
                'Green'
            ],
            'values': [
                {
                    'label': 'Apples',
                    'values': [6, 4, 2],
                    'valuelabels': ['6', '4', '2']
                },
                {
                    'label': 'Oranges',
                    'values': [3, 5, 0],
                    'valuelabels': ['3', '5', '0']
                }
            ]
        };
        var params = {barType: 'grouped', module: 'Mock'};
        var config = {chartType: 'barChart', barType: 'stacked', ReportModule: true};

        var chartData = SUGAR.charts.translateDataToD3(json, params, config);

        var expected = {
            'properties': {
                'title': 'Total is 20',
                'groups': [
                    {'group': 1, 'label': 'Apples'},
                    {'group': 2, 'label': 'Oranges'}
                ],
                'values': [
                    {'group': 1, 'total': 12},
                    {'group': 2, 'total': 8}
                ]
            },
            'data': [
                {
                    'key': 'Red',
                    'type': 'bar',
                    'values': [
                        {'series': 0, 'label': '6', 'x': 1, 'y': 6},
                        {'series': 0, 'label': '3', 'x': 2, 'y': 3}
                    ]
                },
                {
                    'key': 'Yellow',
                    'type': 'bar',
                    'values': [
                        {'series': 1, 'label': '4', 'x': 1, 'y': 4},
                        {'series': 1, 'label': '5', 'x': 2, 'y': 5}
                    ]
                },
                {
                    'key': 'Green',
                    'type': 'bar',
                    'values': [
                        {'series': 2, 'label': '2', 'x': 1, 'y': 2},
                        {'series': 2, 'label': '0', 'x': 2, 'y': 0}
                    ]
                }
            ]
        };

        expect(chartData).toEqual(expected);
    });

    it('should return boolean when isDataEmpty is called', function() {
        var empty;
        empty = SUGAR.charts.isDataEmpty();
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty('No Data');
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty('');
        expect(empty).toBe(false);
        empty = SUGAR.charts.isDataEmpty(['Foo']);
        expect(empty).toBe(true);
    });

    // Not suitable for unit tests. See integration tests.
    // callback
    // openDrawer
    // renderChart
    // renderError'
    // get
    // trackWindowResize
    // saveImageFile
});
