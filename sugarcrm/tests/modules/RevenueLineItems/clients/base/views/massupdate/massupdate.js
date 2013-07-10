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
 * Copyright 2004-2013 SugarCRM Inc. All rights reserved.
 */
describe("RevenueLineItems.Base.Fields.Massupdate", function() {
    var app, view, layout, moduleName = "RevenueLineItems", context, options, message;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        options = {
            meta: {
                panels: [{
                    fields: []
                }]
            }
        };
        
        SugarTest.seedMetadata(true);
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
        message = null;
    });
    
    describe("when trying to delete an RLI with commit_stage = include", function() {
        
        beforeEach(function() {
            message = null;
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        commit_stage: "include"
                    })]
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should should return WARNING_DELETED_RECORD_LIST_RECOMMIT", function() {
            message = view.deleteCommitWarning(view.getMassUpdateModel().models);
            expect(message).toEqual("WARNING_DELETED_RECORD_LIST_RECOMMIT");
        });
    });
    
    describe("when trying to delete an RLI with commit_stage != include", function() {
        
        beforeEach(function() {
            message = null;
            sinon.stub(view, "getMassUpdateModel", function() {
                return {models:[new Backbone.Model({
                        id: "aaa",
                        name: "boo",
                        module: moduleName,
                        commit_stage: "exclude"
                    })]
                };
            });
        });
        
        afterEach(function() {
            view.getMassUpdateModel.restore();
        });
        
        it("should should return NULL", function() {
            message = view.deleteCommitWarning(view.getMassUpdateModel().models);
            expect(message).toEqual(null);
        });
    });
});
