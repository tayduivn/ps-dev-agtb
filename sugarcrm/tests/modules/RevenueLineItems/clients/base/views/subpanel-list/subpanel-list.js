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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
describe("RevenueLineItems.Base.Views.SubpanelList", function() {
    var app, view, options, context, layout, parentLayout, sandbox = sinon.sandbox.create();

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
                        name: "commit_stage"
                    },{
                        name: "best_case"
                    },{
                        name: "likely_case"
                    },{
                        name: "name"
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
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        layout = SugarTest.createLayout("base", "RevenueLineItems", "subpanels", null, null);
        parentLayout = SugarTest.createLayout("base", "RevenueLineItems", "list", null, null);
        layout.layout = parentLayout;
    });
    
    afterEach(function() {
        app.metadata.getModule("Forecasts", "config").is_setup = null;
        sandbox.restore();
        app = null;
        view = null;
        layout = null;
        options = null;
    });

    describe('parseFields', function() {
        beforeEach(function() {
        });

        afterEach(function() {
            app.metadata.getModule("Forecasts", "config").is_setup = null;
            view = null;
        });

        it('should remove the commit_stage field when forecast is not setup', function() {
            app.metadata.getModule("Forecasts", "config").is_setup = 0;
            view = SugarTest.createView('base', 'RevenueLineItems', 'subpanel-list', options.meta, context, true, layout);

            expect(view._fields.visible.length).toEqual(3);
            _.each(view._fields.visible, function(field) {
                expect(field.name).not.toEqual('commit_stage');
            });
        });

        it('should not remove the commit_stage field when forecast is setup', function() {
            app.metadata.getModule("Forecasts", "config").is_setup = 1;
            view = SugarTest.createView('base', 'RevenueLineItems', 'subpanel-list', options.meta, context, true, layout);

            expect(view._fields.visible.length).toEqual(4);
        });
    });
});
