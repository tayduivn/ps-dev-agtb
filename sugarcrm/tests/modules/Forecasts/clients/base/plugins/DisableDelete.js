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

describe("Forecasts.Base.Plugins.Disable-delete", function() {
    
    var app, field, moduleName = 'Opportunities', context, def, model;
    
    beforeEach(function() {
        app = SUGAR.App;
        context = app.context.getContext();
        
        SugarTest.loadFile('../modules/Forecasts/clients/base/plugins', 'DisableDelete', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

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
        delete app.plugins.plugins['field']['DisableDelete'];
        field = null;
        app = null;
        context = null;
        def = null;
        model = null;
    });
    
    describe("when there are no closed RLIs", function() {
        var sales_field = 'sales_status'
        beforeEach(function() {
            model.set("closed_revenue_line_items", 0);
            //BEGIN SUGARCRM flav=pro && flav != ent ONLY
            sales_field = 'sales_stage'
            //END SUGARCRM flav=pro && flav != ent ONLY
        });
        
        describe("when status is Closed Won", function() {
            it("message should contain NOTICE_NO_DELETE_CLOSED", function() {
                model.set(sales_field, "Closed Won");
                field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual("NOTICE_NO_DELETE_CLOSED");
            });
        });
        
        describe("when status is not closed", function() {
            it("message should contain nothing", function() {
                model.set(sales_field, "In Progress");
                field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual(null);
            });
        });
    });
    
    describe("when there are closed RLIs", function() {
        var sales_field = 'sales_status'
        beforeEach(function() {
            model.set("closed_revenue_line_items", 1);
            //BEGIN SUGARCRM flav=pro && flav != ent ONLY
            sales_field = 'sales_stage'
            //END SUGARCRM flav=pro && flav != ent ONLY
        });
        
        describe("when status is Closed Won", function() {
            it("message should contain NOTICE_NO_DELETE_CLOSED", function() {
                model.set(sales_field, "Closed Won");
                field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual("NOTICE_NO_DELETE_CLOSED");
            });
        });

        //BEGIN SUGARCRM flav=ent ONLY
        describe("when status is not closed", function() {
            it("message should contain NOTICE_NO_DELETE_CLOSED_RLIS", function() {
                model.set(sales_field, "In Progress");
                field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
                var message = field.removeDelete();
                expect(message).toEqual("NOTICE_NO_DELETE_CLOSED_RLIS");
            });
        });
        //END SUGARCRM flav=ent ONLY
    });
    
    describe("when sales_stage is used", function() {
        it("message should contain NOTICE_NO_DELETE_CLOSED", function() {
            model.set("closed_revenue_line_items", 0);
            model.set("sales_stage", "Closed Won");
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            var message = field.removeDelete();
            expect(message).toEqual("NOTICE_NO_DELETE_CLOSED");
        });
    });
    
    describe("when the button event is list:deleterow:fire", function() {
        it("message should contain NOTICE_NO_DELETE_CLOSED", function() {
            model.set("closed_revenue_line_items", 0);
            model.set("sales_stage", "Closed Won");
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            field.def.event = "list:deleterow:fire";
            var message = field.removeDelete();
            expect(message).toEqual("NOTICE_NO_DELETE_CLOSED");
        });
    });
});
