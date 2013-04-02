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

describe("forecasts_view_forecastsWorksheetManager", function() {
    var view, apiClassStub;

    beforeEach(function() {
        app = SugarTest.app;
        sinon.stub(app.metadata, "getModule", function() {
            return {
                show_worksheet_likely: 1,
                show_worksheet_best: 1,
                show_worksheet_worst: 0
            };
        });
        sinon.stub(app.metadata, 'getCurrency', function() {
            return {
                conversion_rate: 1
            }
        })

        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.min", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../include/javascript/jquery/", "jquery.dataTables.customSort", "js", function(d) {
            return eval(d);
        });

        app.user.set({'id': 'test_userid', 'isManager': true});

        app.defaultSelections = {
            timeperiod_id: {
                'id': 'test_timeperiod'
            },
            group_by: {},
            dataset: {},
            selectedUser: {},
            ranges: {}
        };

        var collection = new Backbone.Collection(),
            context = app.context.getContext();

        apiClassStub = sinon.stub(app.api, 'call');

        context.set({
            selectedTimePeriod: new Backbone.Model({
                id: 'fake_id'
            }),
            collection: collection
        });

        var meta = {
            panels: [
                {'fields': []}
            ]
        };

        view = SugarTest.createView('base', 'Forecasts', "forecastsWorksheetManager", meta, context, true);

        // set the selectedUser
        view.selectedUser = {
            id: app.user.get('id'),
            full_name: 'Test User',
            isManager: app.user.get('isManager'),
            showOpps: false,
            reportees: [
                {id: 'test1', name: 'test 1'},
                {id: 'test2', name: 'test 2'}
            ]
        };

        // remove the window watcher event
        $(window).unbind("beforeunload");
    });

    afterEach(function() {
        apiClassStub.restore();
        app.metadata.getCurrency.restore();
        app.metadata.getModule.restore();
        app.user.unset('id');
        app.user.set('isManager', false);
    });

    describe("Forecast Manager Worksheet Config functions tests", function() {
        beforeEach(function() {
            // Add a faux layout emulating
            // modules/Forecasts/clients/base/layouts/forecasts/forecasts.js
            view.layout = {};
            view.layout.tableColumnsConfigKeyMapManager = {
                'best_case': 'show_worksheet_best'
            };
        });
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

    describe("Forecast Manager Worksheet collection.fetch", function() {
        beforeEach(function() {
            sinon.spy(view.collection, 'fetch')
        });

        afterEach(function() {
            view.collection.fetch.restore()
        });
        it("should not have been called with safeFetch(false) ", function() {
            view.safeFetch(false);
            expect(view.collection.fetch).not.toHaveBeenCalled();
        });

        it("should have been called with safeFetch(true) ", function() {
            view.safeFetch();
            expect(view.collection.fetch).toHaveBeenCalled();
        });
    });

    describe("collection", function() {
        var collectionFetchStub;
        beforeEach(function() {
            sinon.spy(view, 'collectionSuccess');
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
                view.collectionSuccess([]);
            });
        });

        afterEach(function() {
            view.collectionSuccess.restore();
            collectionFetchStub.restore();
        });

        it("should contains all reportees", function() {
            view.loadData();

            expect(view.collectionSuccess).toHaveBeenCalled();
            expect(view.collection.length).toEqual(3);
        });
    });

    describe("collectionSuccess", function() {
        var collectionFetchStub;
        beforeEach(function() {
            sinon.spy(view, 'collectionSuccess');
            collectionFetchStub = sinon.stub(view.collection, 'fetch', function() {
                view.collectionSuccess([]);
            });
        });

        afterEach(function() {
            view.collectionSuccess.restore();
            collectionFetchStub.restore();
        });

        it("should not update reportees array on selectedUser", function() {
            view.loadData();

            expect(view.collectionSuccess).toHaveBeenCalled();
            expect(view.collection.length).toEqual(3);
            expect(view.selectedUser.reportees.length).toEqual(2);
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

    describe("when the Forecast Worksheet has Draft models in the collection", function(){
        beforeEach(function(){
            view.collection.add(new Backbone.Model({"version": "0", "id": "GABE!"}));
            view.collection.add(new Backbone.Model({"version": "0", "id": "MADE ME"}));
            view.collection.add(new Backbone.Model({"version": "1", "id": "DO THIS!"}));
        });

        afterEach(function(){
            view.collection.reset();
        });

        it("should have 2 items in draft", function(){
            expect(view.getDraftModels().length).toEqual(2);
        });
    });

    describe('Forecast Worksheet Save Dirty Models', function() {
        var m, saveStub;
        beforeEach(function() {
            m = new Backbone.Model({'hello': 'world'});
            saveStub = sinon.stub(m, 'save', function() {
            });
            sinon.stub(view.context, "trigger");
            view.collection.add(m);
        });

        afterEach(function() {
            view.collection.reset();
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
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
        });

        it('should not have draft models on a commit save', function() {
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(view.draftModels.length).toEqual(0);
        });

        it('should have draft models on a draft save', function() {
            m.set({'hello': 'jon1'});
            expect(view.saveWorksheet(true)).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(view.draftModels.length).toEqual(1);
        });

        it('should save the draft models as committed models on "commit"', function() {
            var clearDirtySpy = sinon.spy(view, "cleanUpDirtyModels"),
                clearDraftSpy = sinon.spy(view, "cleanUpDraftModels");

            //first save to populate the draft models
            m.set({'hello': 'jon1'});
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
            view.collection.reset();
            saveStub.restore();
            safeFetchStub.restore();
            m = undefined;
        });

        it('model should contain the old userid', function() {
            m.set({'hello': 'jon1'});
            view.updateWorksheetBySelectedUser({'id': 'my_new_user_id', 'isManager': true, 'showOpps': false});
            expect(view.saveWorksheet()).toEqual(1);
            expect(saveStub).toHaveBeenCalled();
            expect(safeFetchStub).toHaveBeenCalled();

            expect(m.get('current_user')).toEqual('test_userid');
            expect(view.selectedUser.id).toEqual('my_new_user_id');
            expect(view.dirtyUser).toEqual('');
        });
    });

    describe("Forecasts manager worksheet bindings ", function() {
        beforeEach(function() {
            view.context = {
                on: function() {
                }
            };
            view.collection.on = function() {
            };

            sinon.spy(view.collection, "on");
            sinon.spy(view.context, "on");
            view.bindDataChange();
        });

        afterEach(function() {
            view.collection.on.restore();
            view.context.on.restore();
            delete view.context;
            view.context = {};
        });

        it("collection.on should have been called with reset", function() {
            expect(view.collection.on).toHaveBeenCalledWith("reset");
        });

        it("collection.on should have been called with change", function() {
            expect(view.collection.on).toHaveBeenCalledWith("change");
        });

        it("context.on should have been called with selectedUser", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedUser");
        });

        it("context.on should have been called with selectedTimePeriod", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedTimePeriod");
        });

        it("context.on should have been called with selectedRanges", function() {
            expect(view.context.on).toHaveBeenCalledWith("change:selectedRanges");
        });
    });

    describe("dispose safe", function() {
        it("should not render if disposed", function() {
            var renderStub = sinon.stub(view, 'render');

            view.collection.trigger('reset');
            expect(renderStub).toHaveBeenCalled();
            renderStub.reset();

            view.disposed = true;
            view.collection.trigger('reset');
            expect(renderStub).not.toHaveBeenCalled();
        });
    });
});
