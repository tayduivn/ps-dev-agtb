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
describe("revenuelineitems_view_recordlist", function() {
    var app, view, options, context, layout;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set({
            module: 'RevenueLineItems',
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
        SugarTest.testMetadata.set();
        
        SugarTest.seedMetadata(true);
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        layout = SugarTest.createLayout("base", "RevenueLineItems", "list", null, null);
        
    });
    
    afterEach(function() {
        app.metadata.getModule("Forecasts", "config").is_setup = null;
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = null;
        app = null;
        view = null;
        layout = null;
        options = null;
    });

    it("should not contain best_case field", function() {
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = 0;
        view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('best_case');
        })
    });

    it("should not contain commit_stage field", function() {
        app.metadata.getModule("Forecasts", "config").is_setup = 0;
        view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('commit_stage');
        });
    });
    
    describe("when deleteCommitWarning is called", function() {
        var model;
        beforeEach(function() {
            message = null;
            model = new Backbone.Model({
                id: "aaa",
                name: "boo",
                module: "RevenueLineItems"
            });
            view = SugarTest.createView('base', 'RevenueLineItems', 'recordlist', options.meta, context, true, layout);
        });
        
        afterEach(function() {
            model = null;
        });
        
        it("should should return WARNING_DELETED_RECORD_RECOMMIT when commit_stage = include", function() {
            model.set("commit_stage", "include");
            message = view.deleteCommitWarning(model);
            expect(message).toEqual("WARNING_DELETED_RECORD_RECOMMIT");
        });
        
        it("should should return NULL when commit_stage != include", function() {
            model.commit_stage = "exclude";
            message = view.deleteCommitWarning(model);
            expect(message).toEqual(null);
        });
    });
});
