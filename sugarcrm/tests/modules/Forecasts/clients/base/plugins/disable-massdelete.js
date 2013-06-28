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

describe("when disable-massdelete plugin is used", function() {
    
    var app, view, layout, moduleName = 'Opportunities', context, options, model;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        
        SugarTest.loadFile("../modules/Forecasts/clients/base/plugins", "disable-massdelete", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        
        options = {
            meta: {
                panels: [{
                    fields: []
                }]
            }
        };
        
        SugarTest.seedMetadata(true);
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        SugarTest.loadComponent('base', 'view', 'massupdate');

        layout = SugarTest.createLayout("base", moduleName, "list", null, null);
        view = SugarTest.createView("base", moduleName, "massupdate", options, context, true, layout);
    });
    
    afterEach(function() {
        view = null;
        app = null;
        context = null;
        options = null;
        layout = null;
    });
    
    describe("when nothing is closed", function() {
        beforeEach(function() {
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[]};
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should return null", function() {
            var message = view.confirmDelete(null);
            expect(message).toEqual(null);
        });
    });
    
    describe("when something is closed", function() {
        beforeEach(function() {
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        sales_status: 'Closed Won',
                        closed_revenue_line_items: 0
                    })],
                    remove: function(){}
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should return WARNING_NO_DELETE_SELECTED", function() {
            var message = view.confirmDelete(null);
            expect(message).toEqual("WARNING_NO_DELETE_SELECTED");
        });
    });
    
    describe("when an opp has a closed RLI", function() {
        beforeEach(function() {
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        sales_status: 'In Progress',
                        closed_revenue_line_items: 1
                    })],
                    remove: function(){}
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should return WARNING_NO_DELETE_CLOSED_SELECTED", function() {
            var message = view.confirmDelete(null);
            expect(message).toEqual("WARNING_NO_DELETE_CLOSED_SELECTED");
        });
    });
    
    describe("when an opp is closed and it also has a closed RLI", function() {
        beforeEach(function() {
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        sales_status: 'Closed Won',
                        closed_revenue_line_items: 1
                    })],
                    remove: function(){}
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should return WARNING_NO_DELETE_SELECTED", function() {
            var message = view.confirmDelete(null);
            expect(message).toEqual("WARNING_NO_DELETE_SELECTED");
        });
    });
    
    describe("when an item has a closed sales_stage", function() {
        beforeEach(function() {
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        sales_stage: 'Closed Won',
                        sales_status: null,
                        closed_revenue_line_items: 0
                    })],
                    remove: function(){}
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should return WARNING_NO_DELETE_SELECTED", function() {
            var message = view.confirmDelete(null);
            expect(message).toEqual("WARNING_NO_DELETE_SELECTED");
        });
    });
});