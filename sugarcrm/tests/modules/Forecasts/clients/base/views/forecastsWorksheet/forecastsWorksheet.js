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

describe("forecasts_view_forecastsWorksheet", function() {

    var app, view, testMethodStub, collection, apiCallStub, getModuleStub;

    beforeEach(function() {
        app = SugarTest.app;
        getModuleStub = sinon.stub(app.metadata, "getModule", function() {
            return {
                sales_stage_won: ["Closed Won"],
                sales_stage_lost: ["Closed Lost"],
                forecast_by: 'products'
            };
        });


        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.min", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.customSort", "js", function(d) {
            return eval(d);
        });

        app.user.set({'id': 'test_userid'});

        app.defaultSelections = {
            timeperiod_id: {
                'id': 'test_timeperiod'
            },
            group_by: {},
            dataset: {},
            selectedUser: {},
            ranges: {}
        };

        apiCallStub = sinon.stub(app.api, 'call');

        var context = app.context.getContext();
        context.set({'selectedTimePeriod': new Backbone.Model({'id': 'fake_id'})});
        context.set({'collection': new Backbone.Collection()});

        var meta = {
            panels: [
                {'fields': []}
            ]
        };

        view = SugarTest.createView('base', 'Forecasts', 'forecastsWorksheet', meta, context, true);

        // remove the window watcher event
        $(window).unbind("beforeunload");
    });

    afterEach(function() {
        getModuleStub.restore();
        apiCallStub.restore();
        app.user.unset('id');
        view.unbindData();
    });

    describe('createUrl test', function() {
        var url;
        it('should return products in url', function() {
            url = view.createURL();
            expect(url.filters.filter[0].type).toBe("products");
        });
        it('should return opportunities in url', function() {
            // reset getModuleStub for just this test
            getModuleStub.restore();
            getModuleStub = sinon.stub(app.metadata, "getModule", function() {
                return {
                    sales_stage_won: ["Closed Won"],
                    sales_stage_lost: ["Closed Lost"],
                    forecast_by: 'opportunities'
                };
            });
            url = view.createURL();
            expect(url.filters.filter[0].type).toBe("opportunities");
        });
    });

    describe("isMyWorksheet method", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(app.user, "get", function() {
                return 'a_user_id';
            });
        });

        afterEach(function() {
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

    describe("Forecast Worksheet collection.fetch", function() {
        beforeEach(function() {
            view.collection.fetch = function() {
            };
        });

        afterEach(function() {

        });

        it("should not have been called with safeFetch(false) ", function() {
            sinon.spy(view.collection, "fetch");
            view.safeFetch(false);
            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it("should have been called with safeFetch(true) ", function() {
            sinon.spy(view.collection, "fetch");
            view.safeFetch();
            expect(view.collection.fetch).toHaveBeenCalled();
        });
    });

    describe('Forecasts Worksheet Dirty Models', function() {
        var m;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
            m = undefined;
        });

        it('isDirty should return false', function() {
            expect(view.isDirty()).toBeFalsy();
        });

        it('isDirty should return true', function() {
            m.set({'hello': 'jon1'});
            expect(view.isDirty()).toBeTruthy();
        });

        it('should not be dirty after main collection reset', function() {
            m.set({'hello': 'jon1'});
            expect(view.isDirty()).toBeTruthy();
            view.collection.reset();
            expect(view.isDirty()).toBeFalsy();
        })
    });

    describe('Forecast Worksheet Save Dirty Models', function() {
        var m, saveStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
            saveStub.restore();
            m = undefined;
        });

        it('should return zero with no dirty models', function() {
            expect(view.saveWorksheet()).toEqual(0);
        });

        it('should return 1 when one model is dirty', function() {
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
        });
    });

    describe("Forecasts worksheet save dirty models with correct timeperiod after timeperiod changes", function() {
        var m, saveStub, safeFetchStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            safeFetchStub = sinon.stub(view, 'safeFetch', function() {
            });
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
            saveStub.restore();
            safeFetchStub.restore();
            m = undefined;
        });

        it('model should contain the old timeperiod id', function() {
            m.set({'hello': 'jon1'});
            view.updateWorksheetBySelectedTimePeriod({'id': 'my_new_timeperiod'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('timeperiod_id')).toEqual('test_timeperiod');
            expect(view.timePeriod).toEqual('my_new_timeperiod');
            expect(view.dirtyTimeperiod).toEqual('');
        });
    });

    describe("Forecasts worksheet save dirty models with correct user_id after selected_user changes", function() {
        var m, saveStub, safeFetchStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            safeFetchStub = sinon.stub(view, 'safeFetch', function() {
            });
            view.collection.add(m);
        });

        afterEach(function() {
            saveStub.restore();
            safeFetchStub.restore();
            m = undefined;
        });

        it('model should contain the old userid', function() {
            m.set({'hello': 'jon1'});
            view.updateWorksheetBySelectedUser({'id': 'my_new_user_id'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('current_user')).toEqual('test_userid');
            expect(view.selectedUser.id).toEqual('my_new_user_id');
            expect(view.dirtyUser).toEqual('');
        });
    });


    describe("Forecasts worksheet bindings ", function() {
        beforeEach(function() {
            sinon.spy(view.collection, "on");
            sinon.spy(view.context, "on");
            view.bindDataChange();
        });

        afterEach(function() {
            view.context.on.restore();
            view.collection.on.restore();
        });

        it("collection.on should have been called with reset", function() {
            expect(view.collection.on).toHaveBeenCalledWith("reset");
        });

        it("forecasts.worksheet.on should have been called with change", function() {
            expect(view.collection.on).toHaveBeenCalledWith("change");
        });

        it("forecasts.on should have been called with selectedUser", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedUser");
        });

        it("forecasts.on should have been called with selectedTimePeriod", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedTimePeriod");
        });

        it("forecasts.on should have been called with selectedRanges", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedRanges");
        });

        it("forecasts.on should have been called with forecasts:committed:saved", function() {
            expect(view.context.on).toHaveBeenCalledWith("forecasts:committed:saved");
        });

        it("forecasts.on should have been called with forecasts:commitButtons:enabled", function() {
            expect(view.context.on).toHaveBeenCalledWith("forecasts:commitButtons:enabled");
        });

        it("forecasts.on should have been called with forecasts:commitButtons:disabled", function() {
            expect(view.context.on).toHaveBeenCalledWith("forecasts:commitButtons:disabled");
        });

        /*
         * Skip this test.  Expected Opportunities is not a part of nutmeg
         */
        xit("forecasts.on should have been called with expectedOpportunities", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:expectedOpportunities");
        });

    });
});
