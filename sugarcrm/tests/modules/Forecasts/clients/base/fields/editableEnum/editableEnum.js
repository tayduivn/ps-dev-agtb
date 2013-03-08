//FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

describe("forecast editableEnum field", function() {
    var field, fieldDef, context, model, e, getModuleStub;
    
    beforeEach(function() {
        app = SugarTest.app;
        app.user.id = "tester";

        fieldDef = {
            "name": "sales_stage",
            "type": "editableEnum",
            "options": "sales_stage_dom",
            "view": "default"
        };

        getModuleStub = sinon.stub(app.metadata, "getModule", function() {
            return {
                sales_stage_won: ["Closed Won"],
                sales_stage_lost: ["Closed Lost"]
            };
        });

        context = app.context.getContext();
        SugarTest.loadComponent('base', 'field', 'enum');
    });
    
    afterEach(function(){
        getModuleStub.restore();
        app.user.id = null;
        delete app;
    });
                 
    describe("when it is your sheet", function(){
        beforeEach(function(){
            context.get = function(key){
                return {id:"tester"};                    
            }
            
            field = SugarTest.createField("../modules/Forecasts/clients/base", "editableEnum", "editableEnum", "detail", fieldDef, "Forecasts", null, context);                
        });
        
        afterEach(function(){
            $.data(document.body, "cteIcon", null);
        });
       
        it("should have been initialized with currentView = 'default'", function(){
            expect(field.currentView).toBe("default");
        });
        
        it("should have created the CTE Icon html", function(){
            expect($.data(document.body, "cteIcon")).toBeDefined();
        });
        
        it("should have disabled = false", function(){
            expect(field.disabled).toBeFalsy();
        });
        
        it("should have showCteIcon defined", function(){
            expect(field.showCteIcon).toBeDefined();
        });
        
        it("should have hideCteIcon defined", function(){
            expect(field.hideCteIcon).toBeDefined();
        });
        
        describe("when the click event happens", function(){
            beforeEach(function(){
                e = {preventDefault:function(){}};
                sinon.spy(e, "preventDefault");
                sinon.stub(field, "$", function(){
                    return {on: function(){},
                            select2: function(){
                                return "select2";
                            },
                            val: function(){},
                            keydown:function(){}
                    }
                            
                });
                field.clickToEdit(e);
            });
            
            afterEach(function(){
                e.preventDefault.restore();
                field.$.restore();
            });
            
            it("should set currentView to edit", function(){
                expect(field.currentView).toBe("edit");
            });
            
            it("should set def.view to edit", function(){
                expect(field.def.view).toBe("edit");
            });
            
            it("should have called preventDefault", function(){
                expect(e.preventDefault).toHaveBeenCalled();
            });
        });
        
        describe("when the change event happens", function(){
            beforeEach(function(){
                sinon.spy(field, "resetField");
                field.changed();
            });
            
            afterEach(function(){
                field.resetField.restore();
            });
            
            it("should have called resetField", function(){
                expect(field.resetField).toHaveBeenCalled();
            });
            
            it("should have set currentView to default", function(){
                expect(field.currentView).toBe("default");
            });
            
            it("should have set def.view to default", function(){
                expect(field.def.view).toBe("default");
            });
        });
    }); 
    
    describe("when it is not your sheet", function(){
        beforeEach(function(){
            context.get = function(key){
                return {id:"tester2"};                    
            };            
            field = SugarTest.createField("../modules/Forecasts/clients/base", "editableEnum", "editableEnum", "detail", fieldDef, "Forecasts", null, context); 
        });
        
        afterEach(function(){
            $.data(document.body, "cteIcon", null);
        });
       
        it("should have been initialized with currentView = 'default'", function(){
            expect(field.currentView).toBe("default");
        });
        
        it("should have created the CTE Icon html", function(){
            expect($.data(document.body, "cteIcon")).toBeDefined();
        });
        
        it("should have disabled = true", function(){
            expect(field.disabled).toBeTruthy();
        });
        
        it("should have showCteIcon defined", function(){
            expect(field.showCteIcon).toBeDefined();
        });
        
        it("should have hideCteIcon defined", function(){
            expect(field.hideCteIcon).toBeDefined();
        });
        
        describe("when the click event happens", function(){
            beforeEach(function(){
                e = {preventDefault:function(){}};
                sinon.spy(e, "preventDefault");
                sinon.stub(field, "$", function(){
                    return {on: function(){},
                            select2: function(){
                                return "select2";
                            },
                            val: function(){}
                    }
                            
                });
                field.clickToEdit(e);
            });
            
            afterEach(function(){
                e.preventDefault.restore();
                field.$.restore();
            });
            
            it("should set currentView to default", function(){
                expect(field.currentView).toBe("default");
            });
            
            it("should set def.view to edit", function(){
                expect(field.def.view).toBe("default");
            });
            
            it("should not have called preventDefault", function(){
                expect(e.preventDefault).not.toHaveBeenCalled();
            });
        });
        
        describe("when the change event happens", function(){
            beforeEach(function(){
                sinon.spy(field, "resetField");
                field.changed();
            });
            
            afterEach(function(){
                field.resetField.restore();
            });
            
            it("should have called resetField", function(){
                expect(field.resetField).toHaveBeenCalled();
            });
            
            it("should have set currentView to default", function(){
                expect(field.currentView).toBe("default");
            });
            
            it("should have set def.view to default", function(){
                expect(field.def.view).toBe("default");
            });
        });        
    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(field, 'render');

            field.resetField();
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            field.disposed = true;
            field.resetField();
            expect(renderStub).not.toHaveBeenCalled();

        });
    });

    describe("isEditable with sales_stage set", function() {
        beforeEach(function() {
            context.get = function(key){
                return {id:"tester"};
            }

            field = SugarTest.createField("../modules/Forecasts/clients/base", "editableEnum", "editableEnum", "detail", fieldDef, "Forecasts", null, context);
        });

        afterEach(function() {
            field.model.unset('sales_stage');
            context.unset('get');
        });

        it("should not be able to edit", function() {
            field.model.set({sales_stage : "Closed Won"});
            field.isEditable();
            expect(field.disabled).toBeTruthy();
        });

        it("should be able to edit", function() {
            field.model.set({sales_stage : "asdf"});
            field.isEditable();
            expect(field.disabled).toBeFalsy();
        })
    });
});
