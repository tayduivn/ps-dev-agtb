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

describe('Forecasts.Base.Views.ForecastChart', function() {

    var app, view, context, parent, sandbox;

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

        var meta = {
            config: false
        }

        view = SugarTest.createView('base', 'forecasts', 'forecasts-chart', meta, context, true, null, true);

        view.serverData = {
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

    describe('findWorksheetContexts', function() {
        var c1, c2, m1, m2, c1stub, c2stub;
        beforeEach(function() {
            // set the parent context collection
            c1 = new Backbone.Collection();
            m1 = new Backbone.Model({'module': 'ForecastWorksheets', 'collection': c1});
            c1stub = sandbox.stub(c1, 'on', function() {
            });

            c2 = new Backbone.Collection();
            m2 = new Backbone.Model({'module': 'ForecastManagerWorksheets', 'collection': c2});
            sandbox.stub(c2, 'on', function() {
            });
            c2stub = view.context.parent.children = [m1, m2];
        });

        afterEach(function() {
            sandbox.restore();
            view.context.parent.children = [];
        });

        it('should find and call on on collections', function() {
            view.findWorksheetContexts();
            expect(c1stub).toHaveBeenCalled();
            expect(c1stub).toHaveBeenCalled();
        });
    });

    describe('repWorksheetChanged', function() {
        var m;
        beforeEach(function() {
            m = new Backbone.Model({'id': 'test_row_1', 'base_rate': 1.0});
            sandbox.stub(view, 'convertDataToChartData', function() {
            });
            sandbox.stub(view, 'generateD3Chart', function() {
            });
        });

        afterEach(function() {
            sandbox.restore();
            m = null;
        });

        it('should call adjustProbabilityLabels if a probability is changed', function() {
            var c1 = sandbox.stub(view, 'adjustProbabilityLabels', function() {
            });
            m.changed = {'probability': 50};
            view.repWorksheetChanged(m);
            expect(c1).toHaveBeenCalled();
        });

        it('should not call adjustProbabilityLabels if a probability is changed', function() {
            var c1 = sandbox.stub(view, 'adjustProbabilityLabels', function() {
            });
            m.changed = {'sales_stage': 'fake_stage'};
            view.repWorksheetChanged(m);
            expect(c1).not.toHaveBeenCalled();
        });

        it('should update sales_stage', function() {
            m.set({'sales_stage': 'fake_stage'});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].sales_stage).toEqual('fake_stage');
        });

        it('should update forecast', function() {
            m.set({'commit_stage': 'fake_stage'});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].forecast).toEqual('fake_stage');
        });

        it('should update likely', function() {
            m.set({'likely_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].likely).toEqual(60);
        });

        it('should update best', function() {
            m.set({'best_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].best).toEqual(60);
        });

        it('should update worst', function() {
            m.set({'worst_case': 60});
            view.repWorksheetChanged(m);
            expect(view.serverData.data[0].worst).toEqual(60);
        });
    });

    describe('adjustProbabilityLabels', function() {
        beforeEach(function() {
            view.serverData = {
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
            view.adjustProbabilityLabels();

            expect(_.keys(view.serverData.labels.probability).length).toEqual(3);
            expect(view.serverData.labels.probability).toEqual({ 10: 10, 15: 15, 20: 20 });
        });
    });

    describe('mgrWorksheetChanged', function() {
        var m;
        beforeEach(function() {
            m = new Backbone.Model({'user_id': 'test_1', 'base_rate': 1.0, 'quota': 5});
            sandbox.stub(view, 'convertDataToChartData', function() {
            });
            sandbox.stub(view, 'generateD3Chart', function() {
            });
            view.serverData = {
                'title': 'Test',
                'quota': 5,
                'labels': [],
                'data': [
                    {
                        'id': 'test_row_1',
                        'user_id': 'test_1',
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
            sandbox.restore();
        });

        it('should change likely_adjusted', function() {
            m.set({likely_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].likely_adjusted).toEqual(45);
        });
        it('should change worst_adjusted', function() {
            m.set({worst_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].worst_adjusted).toEqual(45);
        });
        it('should change best_adjusted', function() {
            m.set({best_case_adjusted: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.data[0].best_adjusted).toEqual(45);
        });

        it('should change quota', function() {
            m.set({quota: 45});
            view.mgrWorksheetChanged(m);
            expect(view.serverData.quota).toEqual(45);
        });
    });

    describe('convertDataToChartData', function() {
        var s1, s2;
        beforeEach(function() {
            s1 = sandbox.stub(view, 'convertManagerDataToChartData', function() {
            });
            s2 = sandbox.stub(view, 'convertRepDataToChartData', function() {
            });
        });

        afterEach(function() {
            sandbox.restore();
            s1 = null;
            s2 = null;
        });

        it('should call convertManagerDataToChartData when manager', function() {
            view.values.set('display_manager', true, {silent: true});
            view.convertDataToChartData();

            expect(s1).toHaveBeenCalled();
            expect(s2).not.toHaveBeenCalled();
        });
        it('should call convertManagerDataToChartData when manager', function() {
            view.values.set('display_manager', false, {silent: true});
            view.convertDataToChartData();

            expect(s1).not.toHaveBeenCalled();
            expect(s2).toHaveBeenCalled();
        });
    });

    describe('convertManagerDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(view, 'convertDataToChartData', function() {
            });
            sandbox.stub(view, 'generateD3Chart', function() {
            });

            view.values.set('dataset', 'likely', {silent: true});
            view.serverData = {
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
            view.convertManagerDataToChartData();
            expect(view.d3Data.data.length).toEqual(4);

            // the first two should be bars
            var b = view.d3Data.data.slice(0, 2);
            // she second two should be lines
            var l = view.d3Data.data.slice(2);

            expect(b[0].type).toEqual('bar');
            expect(b[1].type).toEqual('bar');

            expect(l[0].type).toEqual('line');
            expect(l[1].type).toEqual('line');
        });

        it('properties should contain two groupData items', function() {
            view.convertManagerDataToChartData();
            expect(view.d3Data.properties.groupData.length).toEqual(2);
        });

        it('properties should contain quota', function() {
            view.convertManagerDataToChartData();
            expect(view.d3Data.properties.quota).toEqual(5);
        });
    });

    describe('convertRepDataToChartData', function() {
        beforeEach(function() {
            sandbox.stub(view, 'convertDataToChartData', function() {
            });
            sandbox.stub(view, 'generateD3Chart', function() {
            });

            view.values.set('dataset', 'likely', {silent: true});
            view.values.set('group_by', 'forecast');
            view.values.set('ranges', ['include']);
            view.serverData = {
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
            view.convertRepDataToChartData('forecast');
            expect(view.d3Data.data.length).toEqual(2);

            expect(view.d3Data.data[0].type).toEqual('bar');
            expect(view.d3Data.data[1].type).toEqual('line');
        });
        it('should contain 3 x-axis groups', function() {
            view.convertRepDataToChartData('forecast');
            expect(view.d3Data.properties.groupData.length).toEqual(3);
        });

        it('bar should contain 3 values', function() {
            view.convertRepDataToChartData('forecast');
            expect(view.d3Data.data[0].values.length).toEqual(3);
            expect(view.d3Data.data[0].values[0].y).toEqual(100);
            expect(view.d3Data.data[0].values[1].y).toEqual(50);
            expect(view.d3Data.data[0].values[2].y).toEqual(50);
        });

        it('line should contain 3 values', function() {
            view.convertRepDataToChartData('forecast');
            expect(view.d3Data.data[1].values.length).toEqual(3);
            expect(view.d3Data.data[1].values[0].y).toEqual(100);
            expect(view.d3Data.data[1].values[1].y).toEqual(150);
            expect(view.d3Data.data[1].values[2].y).toEqual(200);
        });

    });

    describe('tests buildChartUrl function', function() {
        it('should return properly formatted url', function() {
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b',
                    display_manager: false
                },
                result = view.buildChartUrl(params);

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
                result = view.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastManagerWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });
});
