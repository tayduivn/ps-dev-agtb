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

describe("The forecasts manager worksheet", function(){

    var view, field, _renderClickToEditStub, _renderFieldStub, testMethodStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) { return eval(d); });
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsWorksheetManager", "forecastsWorksheetManager", "js", function(d) { return eval(d); });
        view.context = {};
        view.context.forecasts = {};
        view.context.forecasts.config = new (Backbone.Model.extend({
            "defaults": fixtures.metadata.modules.Forecasts.config
        }));
        var cte = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ClickToEdit", "js", function(d) { return eval(d); });
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'forecastsWorksheetManager',
                def:{
                    clickToEdit:true
                }
            };
        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
        })

        describe("should render", function() {
            beforeEach(function() {
                view.selectedUser.id = "test_user_id";
                testMethodStub = sinon.stub(app.user, "get", function(property){
                    var user = {
                        id: "test_user_id"
                    }
                    return user[property];
                });
            });

            afterEach(function() {
                view.selectedUser.id = null;
                testMethodStub.restore();
            })

            it("has clickToEdit set to true in metadata", function() {
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).toHaveBeenCalled();
            });
        });

        describe("should not render", function() {
            it("does not contain a value for clickToEdit in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{}
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to something other than true in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{
                        clickToEdit: 'true'
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to false in metadata", function() {
                field = {
                    viewName:'forecastsWorksheetManager',
                    def:{
                        clickToEdit: false
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("is an edit view", function() {
                field = {
                    viewName:'edit',
                    def:{
                        clickToEdit: true
                    }
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });
        });

    });

    describe("Forecast Manager Worksheet Config functions tests", function() {
        beforeEach(function() {
            // Add a faux layout emulating
            // modules/Forecasts/clients/base/layouts/forecasts/forecasts.js
            view.layout = {};
            view.layout.tableColumnsConfigKeyMapManager = {
                'best_case':'show_worksheet_best'
            };
        });
        it("should test checkConfigForColumnVisibility passing in Null", function() {
            var testVal = null;
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in found key", function() {
            var testVal = 'best_case';
            view.context = {};
            view.context.forecasts = {};
            view.context.forecasts.config = new (Backbone.Model.extend({
                "defaults": fixtures.metadata.modules.Forecasts.config
            }));
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in random key not found", function() {
            var testVal = 'abc9def!';
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
    });
    
    describe("Forecast Manager Worksheet collection.fetch", function(){
    	beforeEach(function(){    		  		
    		view._collection.fetch = function(){};
    	});
    	 
    	afterEach(function(){
    	
    	});
    	
    	it("should not have been called with safeFetch(false) ", function(){
    		sinon.spy(view._collection, "fetch");
    		view.safeFetch(false);
    		expect(view._collection.fetch).not.toHaveBeenCalled();
    	});
    	
    	it("should have been called with safeFetch(true) ", function(){
    		sinon.spy(view._collection, "fetch");
    		view.safeFetch();
    		expect(view._collection.fetch).toHaveBeenCalled();
    	});
    });
    
    describe("Forecasts manager worksheet bindings ", function(){
    	beforeEach(function(){
    		view.context = {
    				forecasts:{
    					on: function(event, fcn){},
    					worksheetmanager:{
    						on: function(event, fcn){}
    					},
    					config:{
        					on: function(event, fcn){}
        				} 
    				},
    				   				
    		};
    		view._collection.on = function(){};
    		
    		sinon.spy(view._collection, "on");
    		sinon.spy(view.context.forecasts, "on");
    		sinon.spy(view.context.forecasts.worksheetmanager, "on");
    		sinon.spy(view.context.forecasts.config, "on");
    		view.bindDataChange();
    	});
    	
    	afterEach(function(){
    		view._collection.on.restore();
    		view.context.forecasts.on.restore();
    		view.context.forecasts.worksheetmanager.on.restore();
    		view.context.forecasts.config.on.restore();
    		delete view.context;
    		view.context = {};
    	});
    	
    	it("collection.on should have been called with reset", function(){
    		expect(view._collection.on).toHaveBeenCalledWith("reset");
    	});
    	
    	it("forecasts.on should have been called with selectedUser", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedUser");
    	});
    	
    	it("forecasts.on should have been called with selectedTimePeriod", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedTimePeriod");
    	});
    	
    	it("forecasts.on should have been called with selectedRanges", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:selectedRanges");
    	});
    	
    	it("forecasts.worksheet.on should have been called with change", function(){
    		expect(view.context.forecasts.worksheetmanager.on).toHaveBeenCalledWith("change");
    	});
    	
        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_likely", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_likely");
    	});

        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_best", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_best");
    	});

        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_worst", function(){
    		expect(view.context.forecasts.config.on).toHaveBeenCalledWith("change:show_worksheet_worst");
    	});
    });
});