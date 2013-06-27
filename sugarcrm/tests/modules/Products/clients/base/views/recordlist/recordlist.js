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
describe("products_view_recordlist", function() {
    var app, view, options;

    beforeEach(function() {
        app = SugarTest.app;
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
        
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'list');
        SugarTest.loadComponent('base', 'view', 'flex-list');
        SugarTest.loadComponent('base', 'view', 'recordlist');
        SugarTest.testMetadata.set();

        SugarTest.seedMetadata(true, './fixtures');
    });
    afterEach(function() {
        app.metadata.getModule("Forecasts", "config").is_setup = null;
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = null;
        app = null;
    });

    it("should not contain best_case field", function() {        
        app.metadata.getModule("Forecasts", "config").show_worksheet_best = 0;
        view = SugarTest.createView('base', 'Products', 'recordlist', options.meta, null, true);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('best_case');
        });
    });

    it("should not contain commit_stage field", function() {
        app.metadata.getModule("Forecasts", "config").is_setup = 0;
        view = SugarTest.createView('base', 'Products', 'recordlist', options.meta, null, true);
        expect(view._fields.visible.length).toEqual(3);
        _.each(view._fields.visible, function(field) {
            expect(field.name).not.toEqual('commit_stage');
        });
    });
});
