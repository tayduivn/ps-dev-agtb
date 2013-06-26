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

describe('RevenueLineItems.Base.Views.FilterRows', function() {
    var app, view, options, sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        sandbox = sinon.sandbox.create();

        options = {
            meta: {
                panels: [
                    {
                        fields: [
                            {
                                name: "commit_stage"
                            },
                            {
                                name: "best_case"
                            },
                            {
                                name: "likely_case"
                            },
                            {
                                name: "name"
                            }
                        ]
                    }
                ]
            }
        };

        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'filter-rows');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true, './fixtures');
        app.metadata.getModule("Forecasts", "config").is_setup = 1;

        sandbox.stub(app.view.views.BaseFilterRowsView.prototype, 'getFilterableFields', function() {
            return {
                'name': [],
                'commit_stage': [],
                'best_case': [],
                'likely_case': []
            }
        });
        sandbox.stub(app.view.views.BaseFilterRowsView.prototype, 'initialize', function() {
        });

        view = SugarTest.createView('base', 'RevenueLineItems', 'filter-rows', options.meta, null, true);
    });

    afterEach(function() {
        sandbox.restore();
        app.metadata.getModule("Forecasts", "config").is_setup = null;
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = null;
        app = null;
        view = null;
    });

    describe('getFilterableFields', function() {
        it('should delete commit_stage if forecast is not setup', function() {
            app.metadata.getModule("Forecasts", "config").is_setup = 0;
            var fields = view.getFilterableFields('test');
            expect(fields['commit_stage']).toBeUndefined();
        });
        it('should not delete commit_stage if forecast is setup', function() {
            app.metadata.getModule("Forecasts", "config").is_setup = 1;
            var fields = view.getFilterableFields('test');
            expect(fields['commit_stage']).toBeDefined();
        });

        it('should delete base_case', function() {
            app.metadata.getModule("Forecasts", "config").show_worksheet_best = 0;
            var fields = view.getFilterableFields('test');
            expect(fields['best_case']).toBeUndefined();
        });
        it('should not delete base_case', function() {
            app.metadata.getModule("Forecasts", "config").show_worksheet_best = 1;
            var fields = view.getFilterableFields('test');
            expect(fields['best_case']).toBeDefined();
        });
    });

});
