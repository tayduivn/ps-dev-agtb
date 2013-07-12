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

describe("RevenueLineItems.Base.Fields.Rowaction", function() {
    var app, field, moduleName = "RevenueLineItems", context, def, model, message;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        def = {
            type:"rowaction",
            event:"button:delete_button:click",
            name:"delete_button",
            label:"LBL_DELETE_BUTTON_LABEL",
            acl_action:"delete"
        };
            
        model = new Backbone.Model({
            id: 'aaa',
            name: 'boo',
            module: moduleName
            
        });
        
        SugarTest.seedMetadata(true);
        app.metadata.getModule("Forecasts", "config").is_setup = 1;
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
    });
    
    afterEach(function() {
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
        message = null;
    });
    
    describe("when deleteCommitWarning is called", function() {
        
        beforeEach(function() {
            message = null;
        });
        
        it("should should return WARNING_DELETED_RECORD_RECOMMIT when commit_stage = include", function() {
            model.set("commit_stage", "include");
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            message = field.deleteCommitWarning();
            expect(message).toEqual("WARNING_DELETED_RECORD_RECOMMIT");
        });
        
        it("should should return NULL when commit_stage != include", function() {
            model.commit_stage = "exclude";
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            message = field.deleteCommitWarning();
            expect(message).toEqual(null);
        });
    });
});
