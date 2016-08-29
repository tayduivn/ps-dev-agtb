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
describe('Forecasts.Base.View.HelpDashlet', function() {
    var app,
        view,
        testObj,
        testModule = 'Forecasts',
        sandbox;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();

        testObj = {
            title: 'testTitle',
            body: 'testBody',
            more_help: 'testMoreHelp'
        };

        sandbox.stub(app.metadata, 'getModule', function() {
            return {
                'forecast_by' : 'RevenueLineItems'
            };
        });

        sandbox.stub(app.lang, 'getModuleName').returnsArg(0);

        sandbox.stub(app.lang, 'get', function(label, module) {
            var obj = {
                LBL_HELP_RECORD_TITLE: testObj.title,
                LBL_HELP_RECORD: testObj.body,
                LBL_HELP_MORE_INFO: testObj.more_help
            };
            return (obj[label]) ? obj[label] : label;
        });

        sandbox.spy(app.help, 'get');

        var context = app.context.getContext({
            module: 'help-dashlet',
            layout: 'dashlet'
        });
        context.parent = app.context.getContext({
            module: testModule,
            layout: 'record'
        });
        context.prepare();
        context.parent.prepare();

        layout = app.view.createLayout({
            name: 'dashlet',
            context: context
        });

        var meta = {
            config: false,
            label: 'LBL_TEST_LBL'
        };

        SugarTest.loadComponent('base', 'view', 'help-dashlet');
        view = SugarTest.createView('base', 'Forecasts', 'help-dashlet', meta, context, true, layout, true);
    });

    afterEach(function() {
        sandbox.restore();
        view.dispose();
        layout.dispose();
        testObj = null;
        app = null;
    });

    describe('getHelpObject()', function() {
        it('should call app.help.get with forecastby values set', function() {

            sandbox.stub(view, 'createMoreHelpLink').returns('JSTest');
            view.getHelpObject();

            expect(app.help.get).toHaveBeenCalledWith('Forecasts', 'record', {
                forecastby_singular_module: 'RevenueLineItems',
                forecastby_module: 'RevenueLineItems',
                more_info_url: 'JSTest',
                more_info_url_close: '</a>'
            });
        });
    });
});
