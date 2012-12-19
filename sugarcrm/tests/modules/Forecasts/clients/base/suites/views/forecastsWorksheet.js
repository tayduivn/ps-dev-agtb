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

describe("The forecasts worksheet", function(){

    var app, view, field, _renderClickToEditStub, _renderFieldStub, testMethodStub, testValue;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../styleguide/styleguide/js", "jquery.datatables", "js", function(d) { return eval(d); });

        app.user.set({'id' : 'test_userid'});

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
        context.forecasts = new Backbone.Model({'selectedTimePeriod' : new Backbone.Model({'id' : 'fake_id'})});
        context.forecasts.worksheet = new Backbone.Collection();
        context.forecasts.config = new (Backbone.Model.extend({
            "defaults": fixtures.metadata.modules.Forecasts.config
        }));

        view = SugarTest.createView('../modules/Forecasts/clients/base', 'Forecasts', "forecastsWorksheet", {}, context);

        // remove the window watcher event
        $(window).unbind("beforeunload");

        var cte = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ClickToEdit", "js", function(d) { return eval(d); });
        var bge = SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "BucketGridEnum", "js", function(d) { return eval(d); });
    });

    afterEach(function() {
        app.user.unset('id');
        view.unbindData();
    });

    describe("clickToEdit field", function() {

        beforeEach(function() {
            testValue = 'testValue';
            _renderClickToEditStub = sinon.stub(app.view, "ClickToEditField");
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            field = {
                viewName:'worksheet',
                def:{
                    clickToEdit:true
                },
                options : {},
                model : {
                    get : function(param) {
                        return [testValue];
                    }
                }
            };
        });

        afterEach(function(){
            _renderClickToEditStub.restore();
            _renderFieldStub.restore();
            testMethodStub.restore();
        })

        describe("should render", function() {
            beforeEach(function() {
                view.isEditableWorksheet = true;
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function() {
                testMethodStub.restore();
            });


            it("has clickToEdit set to true in metadata and a user is viewing their own worksheet", function() {
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).toHaveBeenCalled();
            });
        });

        describe("should not render", function() {
            beforeEach(function(){
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return true;
                });
            });

            afterEach(function(){
                testMethodStub.restore();
            });

            it("does not contain a value for clickToEdit in metadata", function() {
                field = {
                    viewName:'worksheet',
                    def:{}
                };
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });

            it("has clickToEdit set to something other than true in metadata", function() {
                field = {
                    viewName:'worksheet',
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
                    viewName:'worksheet',
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

            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                testMethodStub.restore();
                testMethodStub = sinon.stub(view, "isMyWorksheet", function() {
                    return false;
                });
                view._renderField(field);
                expect(_renderFieldStub).toHaveBeenCalled();
                expect(_renderClickToEditStub).not.toHaveBeenCalled();
            });
        });
    });

    describe("isMyWorksheet method", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function(id) {
                return 'a_user_id';
            });
        });

        afterEach(function(){
            testMethodStub.restore();
            view.selectedUser = '';
        });

        describe("should return true", function() {
            it("is a user viewing their own worksheet", function() {
                view.selectedUser = {
                    id: 'a_user_id'
                };
                expect(view.isMyWorksheet()).toBeTruthy();
            });
        });

        describe("should return false", function() {
            it("is a user not viewing their own worksheet (i.e. manager viewing a reportee)", function() {
                view.selectedUser = {
                    id: 'a_different_user_id'
                };
                expect(view.isMyWorksheet()).toBeFalsy();
            });

            it("receives a selectedUser that is not the expected object", function() {
                view.selectedUser = 'a_user_id';
                expect(view.isMyWorksheet()).toBeFalsy();
            });
        });
    });

    describe("Forecast Worksheet Config functions tests", function() {
        it("should test checkConfigForColumnVisibility passing in Null", function() {
            var testVal = null;
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in found key", function() {
            var testVal = 'best_case';
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
        it("should test checkConfigForColumnVisibility passing in random key not found", function() {
            var testVal = 'abc9def!';
            expect(view.checkConfigForColumnVisibility(testVal)).toBe(true);
        });
    });
   
    describe("Forecast Worksheet collection.fetch", function(){
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
            view._collection.add(m);
    	});

    	afterEach(function(){
            view._collection.reset();
            saveStub.restore();
            m = undefined;
    	});

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            m.set({'hello':'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
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
            view.updateWorksheetBySelectedUser({'id' : 'my_new_user_id'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('current_user')).toEqual('test_userid');
            expect(view.selectedUser.id).toEqual('my_new_user_id');
            expect(view.dirtyUser).toEqual('');
        });
    });

    
    describe("Forecasts worksheet bindings ", function(){
    	beforeEach(function(){
    		sinon.spy(view.context.forecasts, "on");
    		sinon.spy(view.context.forecasts.worksheet, "on");
    		sinon.spy(view.context.forecasts.config, "on");
    		view.bindDataChange();
    	});
    	
    	afterEach(function(){
    		view.context.forecasts.on.restore();
    		view.context.forecasts.worksheet.on.restore();
    		view.context.forecasts.config.on.restore();
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
    		expect(view.context.forecasts.worksheet.on).toHaveBeenCalledWith("change");
    	});
    	
    	it("forecasts.on should have been called with forecasts:committed:saved", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("forecasts:committed:saved");
    	});
    	
    	it("forecasts.on should have been called with forecasts:commitButtons:enabled", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("forecasts:commitButtons:enabled");
    	});
    	
    	it("forecasts.on should have been called with forecasts:commitButtons:disabled", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("forecasts:commitButtons:disabled");
    	});

        /*
         * Skip this test.  Expected Opportunities is not a part of nutmeg
         */
    	xit("forecasts.on should have been called with expectedOpportunities", function(){
    		expect(view.context.forecasts.on).toHaveBeenCalledWith("change:expectedOpportunities");
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