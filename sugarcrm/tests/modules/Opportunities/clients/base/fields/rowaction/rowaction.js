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

describe("Opportunities.Base.Fields.Rowaction", function() {
    
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
    
    describe("when closed_revenue_line_items changes", function() {
        beforeEach(function() {
            field = SugarTest.createField("base", "delete_button", "rowaction", "detail", def, moduleName, model, context, true);
            field.view = {
                    STATE: {VIEW:"detail"},
                    initButtons: function(){},
                    setButtonStates: function(){}
            };
            
            sinon.spy(field, "render");
            sinon.spy(field.view, "initButtons");
            sinon.spy(field.view, "setButtonStates");
            
            model.set("closed_revenue_line_items", "1");
        });
        afterEach(function() {
            field.render.restore();
            field.view.setButtonStates.restore();
            field.view.initButtons.restore();
        });
        
        it("should call render on the rowaction", function() {
            expect(field.render.called).toBe(true);
        });
        
        it("should call initButtons on the view", function() {
            expect(field.view.initButtons.called).toBe(true);
        });
        
        it("should call setButtonStates on the view", function() {
            expect(field.view.setButtonStates.called).toBe(true);
        });
    });
});
