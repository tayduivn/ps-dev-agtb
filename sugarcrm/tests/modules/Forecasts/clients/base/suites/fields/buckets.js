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

describe("forecast buckets field", function() {
    var field, oldApp, createBucketsSpy, getLanguageValueSpy;
    
    beforeEach(function() {
        var fieldDef = {
                "name": "commit_stage",
                "type": "buckets",
                "options": "commit_stage_dom"
            };
        field = SugarTest.createField("../modules/Forecasts/clients/base", "buckets", "buckets", "detail", fieldDef, "Forecasts");
        app = SugarTest.app;
        app.user.id = "tester";
    });
    
    afterEach(function(){
        delete app;
    });
    
    describe("when buckets are set to show_binary", function(){        
        beforeEach(function(){
            field.context.forecasts = {
                config:{
                    get:function(key){
                        return "show_binary";
                    }
                }
            };
        });
        
        afterEach(function(){            
            delete field.context.forecasts;
        });
        
        describe("when it is your sheet", function(){
            beforeEach(function(){
                field.context.forecasts.get = function(key){
                    return {id:"tester"};                    
            };
                field._render();
            });
            it("should have def.view set to bool", function(){
                expect(field.def.view).toBe("bool");
            });
            
            it("should have a format function defined", function(){
                expect(field.format).toBeDefined();
            });
            
            it("should have an unformat function defined", function(){
                expect(field.unformat).toBeDefined();
            });
            
            it("should have disabled = false", function(){
                expect(field.disabled).toBeFalsy();
            });
        });
        
        describe("when it is not your sheet", function(){
            beforeEach(function(){
                field.context.forecasts.get = function(key){
                    return {id:"tester2"};                    
            };
                field._render();
            });
            it("should have def.view set to bool", function(){
                expect(field.def.view).toBe("bool");
            });
            
            it("should have a format function defined", function(){
                expect(field.format).toBeDefined();
            });
            
            it("should have an unformat function defined", function(){
                expect(field.unformat).toBeDefined();
            });
            
            it("should have disabled = true", function(){
                expect(field.disabled).toBeTruthy();
            });
        });    
    });
    
    describe("when buckets are set to show_buckets", function(){
        beforeEach(function(){ 
            field.context.forecasts = {
                config:{
                    get:function(key){
                        return "show_buckets";
                    }
                },
                get:function(key){
                    return "1";
                }
            };
        });
        
        afterEach(function(){
            delete field.context.forecasts;
        });
        
        describe("when it is your sheet", function(){
            beforeEach(function(){
                field.context.forecasts.get = function(key){
                        return {id:"tester"};                    
                };
                createBucketsSpy = sinon.spy(field, "createBuckets");
                field._render();
            });
            
            afterEach(function(){
                createBucketsSpy.restore();
            });
            
            it("should have def.view set to enum", function(){
                expect(field.def.view).toBe("enum");                
            });
            
            it("should have called createBuckets", function(){
                expect(field.createBuckets).toHaveBeenCalled();
            });
        });
        
        describe("when it is not your sheet", function(){
            beforeEach(function(){
                field.context.forecasts.get = function(key){
                    return {id:"tester2"};
                };
                getLanguageValueSpy = sinon.spy(field, "getLanguageValue");
               field._render();
            });
            
            afterEach(function(){
                getLanguageValueSpy.restore();
            });
            
            it("should have def.view set to default", function(){
                expect(field.def.view).toBe("default");                
            });
            
            it("should have called getLanguageValue", function(){
                expect(field.getLanguageValue).toHaveBeenCalled();
            });
        });
    });
});