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
    var view, field, _renderFieldStub, testMethodStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.min", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.customSort", "js", function(d) { return eval(d); });

        app.user.set({'id' : 'test_userid', 'isManager' : true});

        app.defaultSelections = {
                timeperiod_id: {
                    'id' : 'test_timeperiod'
                },
                group_by: {},
                dataset: {},
                selectedUser: {},
                ranges: {}
            };

        var context = app.context.getContext();
        context.set({'selectedTimePeriod' : new Backbone.Model({'id' : 'fake_id'})});
        context.worksheetmanager = new Backbone.Collection();
        context.config = new (Backbone.Model.extend({
            "defaults": fixtures.metadata.modules.Forecasts.config
        }));

        var meta = {
            panels : [{'fields' : []}]
        };

        view = SugarTest.createView('../modules/Forecasts/clients/base', 'Forecasts', "forecastsWorksheetManager", meta, context);

        // remove the window watcher event
        $(window).unbind("beforeunload");
    });

    afterEach(function() {
        app.user.unset('id');
        app.user.set('isManager', false);
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
            view.context.config = new (Backbone.Model.extend({
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

    describe('Forecasts Worksheet Dirty Models', function() {
        var m;
        beforeEach(function(){
            m = new Backbone.Model({'hello' : 'world'});
            view._collection.add(m);
    	});

    	afterEach(function(){
            view._collection.reset();
            m = undefined;
    	});

        it('isDirty should return false', function() {
            expect(view.isDirty()).toBeFalsy();
        });

        it('isDirty should return true', function() {
            m.set({'hello' : 'jon1'});
            expect(view.isDirty()).toBeTruthy();            
        });

        it('should not be dirty after main collection reset', function() {
            m.set({'hello' : 'jon1'});
            expect(view.isDirty()).toBeTruthy();
            view._collection.reset();
            expect(view.isDirty()).toBeFalsy();
        })
    });

    describe('Forecast Worksheet Save Dirty Models', function() {
        var m, saveStub;
        beforeEach(function(){
            m = new Backbone.Model({'hello' : 'world'});
            saveStub = sinon.stub(m, 'save', function(){});
            sinon.stub(view.context, "trigger");
            view._collection.add(m);
    	});

    	afterEach(function(){
            view._collection.reset();
            view.context.trigger.restore();
            saveStub.restore();
            m = undefined;
    	});

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);           
        });
        
        it('should not have any draft models with no dirty models', function() {
            view.saveWorksheet();
            expect(view.draftModels.length).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            m.set({'hello':'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
        });
        
        it('should not have draft models on a commit save', function() {
            m.set({'hello':'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(view.draftModels.length).toEqual(0);
        });
        
        it('should have draft models on a draft save', function() {
            m.set({'hello':'jon1'});
            expect(view.saveWorksheet(true)).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(view.draftModels.length).toEqual(1);
        });
        
        it('should save the draft models as committed models on "commit"', function() {
            var clearDirtySpy = sinon.spy(view, "cleanUpDirtyModels"),
                clearDraftSpy = sinon.spy(view, "cleanUpDraftModels");
 
            //first save to populate the draft models
            m.set({'hello':'jon1'});
            view.saveWorksheet(true);
            expect(view.draftModels.length).toEqual(1);
            
            //save again for the "commit"
            view.saveWorksheet(false);
            expect(clearDirtySpy).toHaveBeenCalled();
            expect(clearDraftSpy).toHaveBeenCalled();
            expect(view.draftModels.length).toEqual(0);
        });
        
    });
    describe("Forecasts worksheet save dirty models with correct timeperiod after timeperiod changes", function() {
        var m, saveStub, safeFetchStub;
        beforeEach(function(){
            m = new Backbone.Model({'hello' : 'world'});
            saveStub = sinon.stub(m, 'save', function(){});
            safeFetchStub = sinon.stub(view, 'safeFetch', function(){});
            view._collection.add(m);
    	});

    	afterEach(function(){
            view._collection.reset();
            saveStub.restore();
            safeFetchStub.restore();
            m = undefined;
    	});

        it('model should contain the old timeperiod id', function() {
            m.set({'hello':'jon1'});
            view.updateWorksheetBySelectedTimePeriod({'id' : 'my_new_timeperiod'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('timeperiod_id')).toEqual('test_timeperiod');
            expect(view.timePeriod).toEqual('my_new_timeperiod');
            expect(view.dirtyTimeperiod).toEqual('');
        });
    });

    describe("Forecasts worksheet save dirty models with correct user_id after selected_user changes", function() {
        var m, saveStub, safeFetchStub, viewStub;
        beforeEach(function(){
            m = new Backbone.Model({'hello' : 'world'});
            saveStub = sinon.stub(m, 'save', function(){});
            safeFetchStub = sinon.stub(view, 'safeFetch', function(){});
            view._collection.add(m);
            viewStub = sinon.stub(view._collection, 'fetch', function(){});

    	});

    	afterEach(function(){
            view._collection.reset();
            saveStub.restore();
            safeFetchStub.restore();
            m = undefined;
    	});

        it('model should contain the old userid', function() {
            m.set({'hello':'jon1'});
            view.updateWorksheetBySelectedUser({'id' : 'my_new_user_id', 'isManager': true, 'showOpps' : false});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('current_user')).toEqual('test_userid');
            expect(view.selectedUser.id).toEqual('my_new_user_id');
            expect(view.dirtyUser).toEqual('');
        });
    });
    
    describe("Forecasts manager worksheet bindings ", function(){
    	beforeEach(function(){
    		view.context = {
                on: function(event, fcn){},
                worksheetmanager:{
                    on: function(event, fcn){}
                },
                config:{
                    on: function(event, fcn){}
                }
    		};
    		view._collection.on = function(){};
    		
    		sinon.spy(view._collection, "on");
    		sinon.spy(view.context, "on");
    		sinon.spy(view.context.worksheetmanager, "on");
    		sinon.spy(view.context.config, "on");
    		view.bindDataChange();
    	});
    	
    	afterEach(function(){
    		view._collection.on.restore();
    		view.context.on.restore();
    		view.context.worksheetmanager.on.restore();
    		view.context.config.on.restore();
    		delete view.context;
    		view.context = {};
    	});
    	
    	it("collection.on should have been called with reset", function(){
    		expect(view._collection.on).toHaveBeenCalledWith("reset");
    	});
    	
    	it("forecasts.on should have been called with selectedUser", function(){
    		expect(view.context.on).toHaveBeenCalledWith("change:selectedUser");
    	});
    	
    	it("forecasts.on should have been called with selectedTimePeriod", function(){
    		expect(view.context.on).toHaveBeenCalledWith("change:selectedTimePeriod");
    	});
    	
    	it("forecasts.on should have been called with selectedRanges", function(){
    		expect(view.context.on).toHaveBeenCalledWith("change:selectedRanges");
    	});
    	
    	it("forecasts.worksheet.on should have been called with change", function(){
    		expect(view.context.worksheetmanager.on).toHaveBeenCalledWith("change");
    	});
    	
        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_likely", function(){
    		expect(view.context.config.on).toHaveBeenCalledWith("change:show_worksheet_likely");
    	});

        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_best", function(){
    		expect(view.context.config.on).toHaveBeenCalledWith("change:show_worksheet_best");
    	});

        // TODO: tagged for 6.8 see SFA-253 for details
    	xit("forecasts.config.on should have been called with show_worksheet_worst", function(){
    		expect(view.context.config.on).toHaveBeenCalledWith("change:show_worksheet_worst");
    	});
    });
});
