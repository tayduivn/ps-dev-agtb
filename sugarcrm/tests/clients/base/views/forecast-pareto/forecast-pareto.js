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

describe('Base.Views.ForecastPareto', function() {

    var app, view, context, sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        app.user.setPreference('decimal_precision', 2);
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadPlugin('Dashlet');

        context = app.context.getContext();
        context.parent = new Backbone.Model();
        context.parent.set('selectedUser', {id: 'test_user', is_manager: false});
        context.parent.set('selectedTimePeriod', 'test_timeperiod');
        context.parent.set('module', 'Forecasts');
        context.parent.children = [];

        getViewStub = sandbox.stub(app.metadata, 'getView', function() {
            return {
                'chart': {
                    'name': 'paretoChart',
                    'label': 'Pareto Chart',
                    'type': 'forecast-pareto-chart'
                },
                'group_by': {
                    'name': 'group_by',
                    'label': 'LBL_DASHLET_FORECASTS_GROUPBY',
                    'type': 'enum',
                    'searchBarThreshold': 5,
                    'default': true,
                    'enabled': true,
                    'view': 'edit',
                    'options': 'forecasts_chart_options_group'
                },
                'dataset': {
                    'name': 'dataset',
                    'label': 'LBL_DASHLET_FORECASTS_DATASET',
                    'type': 'enum',
                    'searchBarThreshold': 5,
                    'default': true,
                    'enabled': true,
                    'view': 'edit',
                    'options': 'forecasts_options_dataset'
                }
            }
        });

        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                'show_worksheet_worst': 0,
                'show_worksheet_likely': 1,
                'show_worksheet_best': 1
            }
        });

        sandbox.stub(app.lang, 'getAppListStrings', function() {
            return {
                'worst': 1,
                'best': 1,
                'likely': 1
            }
        });

        var meta = {
                config: false
            },
            layout = SugarTest.createLayout("base", 'Forecasts', "list", null, context.parent);

        view = SugarTest.createView('base', 'Forecasts', 'forecasts-chart', meta, context, true, layout, true);
    });

    afterEach(function() {
        // delete the plug so it doesn't affect other dashlets unless they load the plugin
        delete app.plugins.plugins['view']['Dashlet'];
        sandbox.restore();
    });

    describe('initDashlet', function() {
        it('dashletConfig should not have worst', function() {
            expect(view.dashletConfig.dataset.options['worst']).toBeUndefined();
        });
        it('dashletConfig should have best and likely', function() {
            expect(view.dashletConfig.dataset.options['likely']).toBeDefined();
            expect(view.dashletConfig.dataset.options['best']).toBeDefined();
        });
    });
});
