//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

describe('Base.Fields.ForecastParetoChart', function() {

    var app, field, context, parent, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        context = app.context.getContext();
        context.parent = new Backbone.Model();
        context.parent.set('selectedUser', {id: 'test_user', is_manager: false});
        context.parent.set('selectedTimePeriod', 'test_timeperiod');
        context.parent.set('module', 'Forecasts');

        var def = {
                'name': 'paretoChart',
                'label': 'Pareto Chart',
                'type': 'forecast-pareto-chart',
                'view': 'detail'
            },
            model = new Backbone.Model();

        field = SugarTest.createField('base', 'paretoChart', 'forecast-pareto-chart', 'def', 'Forecasts', model);

        field.serverData = {
            'title': 'Test',
            'labels': [],
            'data': [
                {
                    'id': 'test_row_1',
                    'sales_stage': 'test_1',
                    'likely': 50,
                    'best': 50,
                    'worst': 50,
                    'forecast': 'exclude',
                    'probability': 10
                }
            ]
        };
    });

    afterEach(function() {
        sandbox.restore();
    });

    describe('adjustProbabilityLabels', function() {
        beforeEach(function() {
            field.serverData = {
                'title': 'Test',
                'labels': [],
                'data': [
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 10
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 15
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 20
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'exclude',
                        'probability': 10
                    }
                ]
            };
        });

        afterEach(function() {
        });

        it('should set 3 probability labels in the correct order', function() {
            field.adjustProbabilityLabels();

            expect(_.keys(field.serverData.labels.probability).length).toEqual(3);
            expect(field.serverData.labels.probability).toEqual({ 10: 10, 15: 15, 20: 20 });
        });
    });

    describe('convertDataToChartData', function() {
        var s1, s2;
        beforeEach(function() {
            s1 = sandbox.stub(field, 'convertManagerDataToChartData', function() {
            });
            s2 = sandbox.stub(field, 'convertRepDataToChartData', function() {
            });
        });

        afterEach(function() {
            sandbox.restore();
            s1 = null;
            s2 = null;
        });

        it('should call convertManagerDataToChartData when manager', function() {
            field.model.set('display_manager', true, {silent: true});
            field.convertDataToChartData();

            expect(s1).toHaveBeenCalled();
            expect(s2).not.toHaveBeenCalled();
        });
        it('should call convertManagerDataToChartData when manager', function() {
            field.model.set('display_manager', false, {silent: true});
            field.convertDataToChartData();

            expect(s1).not.toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
        });
    });

    describe('convertManagerDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(field, 'convertDataToChartData', function() {
            });
            sandbox.stub(field, 'generateD3Chart', function() {
            });

            field.model.set('dataset', 'likely', {silent: true});
            field.serverData = {
                'title': 'Test',
                'quota': 5,
                'labels': {
                    dataset: {
                        'likely': 'Likely',
                        'likely_adjusted': 'Likely (Adjusted)',
                        'best': 'Best',
                        'best_adjusted': 'Best (Adjusted)'
                    }
                },
                'data': [
                    {
                        'id': 'test_row_1',
                        'user_id': 'test_1',
                        'name': 'test 1',
                        'likely': 50,
                        'likely_adjusted': 55,
                        'best': 50,
                        'best_adjusted': 55,
                        'worst': 50,
                        'worst_adjusted': 55
                    },
                    {
                        'id': 'test_row_2',
                        'user_id': 'test_2',
                        'name': 'test 2',
                        'likely': 50,
                        'likely_adjusted': 55,
                        'best': 50,
                        'best_adjusted': 55,
                        'worst': 50,
                        'worst_adjusted': 55
                    }
                ]
            };
        });

        afterEach(function() {
        });

        it('should contain two bars and two lines', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.data.length).toEqual(4);

            // the first two should be bars
            var b = field.d3Data.data.slice(0, 2);
            // she second two should be lines
            var l = field.d3Data.data.slice(2);

            expect(b[0].type).toEqual('bar');
            expect(b[1].type).toEqual('bar');

            expect(l[0].type).toEqual('line');
            expect(l[1].type).toEqual('line');
        });

        it('properties should contain two groupData items', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.properties.groupData.length).toEqual(2);
        });

        it('properties should contain quota', function() {
            field.convertManagerDataToChartData();
            expect(field.d3Data.properties.quota).toEqual(5);
        });
    });

    describe('convertRepDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(field, 'convertDataToChartData', function() {
            });
            sandbox.stub(field, 'generateD3Chart', function() {
            });

            field.model.set('dataset', 'likely', {silent: true});
            field.model.set('group_by', 'forecast');
            field.model.set('ranges', ['include']);
            field.serverData = {
                'title': 'Test',
                'labels': {
                    forecast: {
                        'include': 'Included',
                        'exclude': 'Excluded'
                    },
                    dataset: {
                        'likely': 'Likely',
                        'likely_adjusted': 'Likely (Adjusted)',
                        'best': 'Best',
                        'best_adjusted': 'Best (Adjusted)'
                    }
                },
                'x-axis': [
                    {
                        'label': 'G1',
                        'start_timestamp': 50,
                        'end_timestamp': 150
                    },
                    {
                        'label': 'G2',
                        'start_timestamp': 151,
                        'end_timestamp': 250
                    },
                    {
                        'label': 'G3',
                        'start_timestamp': 251,
                        'end_timestamp': 350
                    }
                ],
                'data': [
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 10,
                        'date_closed_timestamp': 100
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 15,
                        'date_closed_timestamp': 100
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 20,
                        'date_closed_timestamp': 200
                    },
                    {
                        'id': 'test_row_1',
                        'sales_stage': 'test_1',
                        'likely': 50,
                        'best': 50,
                        'worst': 50,
                        'forecast': 'include',
                        'probability': 10,
                        'date_closed_timestamp': 300
                    }
                ]
            };
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should contain one bar and one line', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data.length).toEqual(2);

            expect(field.d3Data.data[0].type).toEqual('bar');
            expect(field.d3Data.data[1].type).toEqual('line');
        });
        it('should contain 3 x-axis groups', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.properties.groupData.length).toEqual(3);
        });

        it('bar should contain 3 values', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data[0].values.length).toEqual(3);
            expect(field.d3Data.data[0].values[0].y).toEqual(100);
            expect(field.d3Data.data[0].values[1].y).toEqual(50);
            expect(field.d3Data.data[0].values[2].y).toEqual(50);
        });

        it('line should contain 3 values', function() {
            field.convertRepDataToChartData('forecast');
            expect(field.d3Data.data[1].values.length).toEqual(3);
            expect(field.d3Data.data[1].values[0].y).toEqual(100);
            expect(field.d3Data.data[1].values[1].y).toEqual(150);
            expect(field.d3Data.data[1].values[2].y).toEqual(200);
        });

    });

    describe('tests buildChartUrl function', function() {
        it('should return properly formatted url', function() {
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b',
                    display_manager: false
                },
                result = field.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });

    describe('tests buildChartUrl function for manager', function() {
        it('should return properly formatted url', function() {
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b',
                    display_manager: true
                },
                result = field.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastManagerWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });
});
