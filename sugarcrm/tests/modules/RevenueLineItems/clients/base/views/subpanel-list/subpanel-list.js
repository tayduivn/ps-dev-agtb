/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 */
describe('RevenueLineItems.Base.Views.SubpanelList', function() {
    var app, view, options, context, layout, parentLayout, sandbox = sinon.sandbox.create(), config;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            module: 'RevenueLineItems'
        });
        context.prepare();

        options = {
            meta: {
                panels: [{
                    fields: [{
                        name: 'commit_stage'
                    },{
                        name: 'best_case'
                    },{
                        name: 'likely_case'
                    },{
                        name: 'worst_case'
                    },{
                        name: 'name'
                    }]
                }]
            }
        };


        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.loadComponent('base', 'view', 'subpanel-list');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true);
        config = app.metadata.getModule('Forecasts', 'config');
        config.is_setup = 1;
        config.show_worksheet_worst = 1;
        config.show_worksheet_likely = 1;
        config.show_worksheet_best = 1;
        layout = SugarTest.createLayout('base', 'RevenueLineItems', 'subpanels', null, null);
        parentLayout = SugarTest.createLayout('base', 'RevenueLineItems', 'list', null, null);
        layout.layout = parentLayout;
    });

    afterEach(function() {
        config.is_setup = null;
        config.show_worksheet_worst = null;
        config.show_worksheet_likely = null;
        config.show_worksheet_best = null;
        sandbox.restore();
        config = null;
        app = null;
        view = null;
        layout = null;
        options = null;
    });

    describe('parseFields', function() {
        beforeEach(function() {
        });

        afterEach(function() {
            config.is_setup = null;
            config.show_worksheet_worst = 1;
            config.show_worksheet_likely = 1;
            config.show_worksheet_best = 1;
            view = null;
        });

        it('should remove the commit_stage field when forecast is not setup', function() {
            config.is_setup = 0;
            view = SugarTest.createView(
                'base',
                'RevenueLineItems',
                'subpanel-list',
                options.meta,
                context,
                true,
                layout
            );

            expect(view._fields.visible.length).toEqual(4);
            expect(_.where(view._fields.visible, {name: 'commit_stage'})).toEqual([]);
        });

        it('should not remove the commit_stage field when forecast is setup', function() {
            config.is_setup = 1;
            view = SugarTest.createView(
                'base',
                'RevenueLineItems',
                'subpanel-list',
                options.meta,
                context,
                true,
                layout
            );

            expect(view._fields.visible.length).toEqual(5);
        });

        it('should remove worst_case field when not shown', function() {
            config.is_setup = 1;
            config.show_worksheet_worst = 0;

            view = SugarTest.createView(
                'base',
                'RevenueLineItems',
                'subpanel-list',
                options.meta,
                context,
                true,
                layout
            );

            expect(view._fields.visible.length).toEqual(4);
            expect(_.where(view._fields.visible, {name: 'worst_case'})).toEqual([]);
        });

        it('should remove best_case field when not shown', function() {
            config.is_setup = 1;
            config.show_worksheet_best = 0;

            view = SugarTest.createView(
                'base',
                'RevenueLineItems',
                'subpanel-list',
                options.meta,
                context,
                true,
                layout
            );

            expect(view._fields.visible.length).toEqual(4);
            expect(_.where(view._fields.visible, {name: 'best_case'})).toEqual([]);
        });

        it('should remove likely_case field when not shown', function() {
            config.is_setup = 1;
            config.show_worksheet_likely = 0;

            view = SugarTest.createView(
                'base',
                'RevenueLineItems',
                'subpanel-list',
                options.meta,
                context,
                true,
                layout
            );

            expect(view._fields.visible.length).toEqual(4);
            expect(_.where(view._fields.visible, {name: 'likely_case'})).toEqual([]);
        });
    });
});
