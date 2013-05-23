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

describe("Forecasts.Layout.Records", function() {

    var app, layout, stubs = [], apiCallStub, moduleName = 'Forecasts';

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.testMetadata.init();
        SugarTest.loadFile("../include/javascript/sugar7", "utils", "js", function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });

        SugarTest.testMetadata.set();

        app.data.reset();
        app.data.declareModel(moduleName, SugarTest.app.metadata.getModule(moduleName));

        app.user.set({'id': 'test_userid', full_name: 'Selected User'});

        apiCallStub = sinon.stub(app.api, 'call', function() {
        });
        stubs.push(sinon.stub(app.api, 'buildURL', function() {
        }));
        stubs.push(sinon.stub(app.data, 'getSyncCallbacks', function() {
        }));

        layout = SugarTest.createLayout('base', moduleName, 'records', null, null, true);
    });

    afterEach(function() {
        // restore the local stubs
        _.each(stubs, function(stub) {
            stub.restore();
        });
        apiCallStub.restore();
        layout = '';
    });

    it('should have initOptions set', function() {
        expect(_.isUndefined(layout.initOptions)).toBeFalsy();
    });

    it('should have called all stubs', function() {
        _.each(stubs, function(stub) {
            expect(stub).toHaveBeenCalled();
        });
    });

    describe('initForecastsModule', function() {
        var initData = {}, getSelectedUsersReporteesStub, ctxOnceStub;
        beforeEach(function() {
            getSelectedUsersReporteesStub = sinon.stub(app.utils, 'getSelectedUsersReportees', function() {
            });
            ctxOnceStub = sinon.stub(layout.initOptions.context, 'once', function() {
            });
            initData = {
                "initData": {
                    "userData": {"isManager": false, "showOpps": false, "first_name": "Max", "last_name": "Jensen"},
                    "forecasts_setup": 1
                },
                "defaultSelections": {
                    "timeperiod_id": {"id": "test_tp_id", "label": "Q2 (04\/01\/2013 - 06\/30\/2013)"},
                    "ranges": ["include"],
                    "group_by": "forecast",
                    "dataset": "likely"}
            };
        });

        afterEach(function() {
            initData = {};
            getSelectedUsersReporteesStub.restore();
            ctxOnceStub.restore();
        });

        it('should set default values on the initOptions Context', function() {
            layout.initForecastsModule(initData, {});

            expect(layout.initOptions.context.get('currentForecastCommitDate')).toEqual(undefined);
            expect(layout.initOptions.context.get('selectedTimePeriod')).toEqual(initData.defaultSelections.timeperiod_id.id);
            expect(layout.initOptions.context.get('selectedRanges')).toEqual(initData.defaultSelections.ranges);

            expect(getSelectedUsersReporteesStub).toHaveBeenCalled();
        });

        it('should set a once trigger on initOptions.context', function() {
            layout.initForecastsModule(initData, {});
            expect(ctxOnceStub).toHaveBeenCalled();
        })
    });

    describe('_onceInitSelectedUser', function() {
        var renderStub, changedOptions = {};
        beforeEach(function() {
            changedOptions = {
                id: 'test_id',
                isManager: false,
                showOpps: false
            };
            renderStub = sinon.stub(layout, 'render', function() {
            });
        });

        afterEach(function() {
            changedOptions = {};
            renderStub.restore();
        });

        describe('layout.render', function() {
            it('should be called', function() {
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(renderStub).toHaveBeenCalled();
            });

            it('should not be called when disposed', function() {
                layout.disposed = true;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(renderStub).not.toHaveBeenCalled();
            });
        });

        describe('model should have correct forecast type', function() {
            it('when sales rep', function() {
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Direct');
            });
            it('when manager viewing rep worksheet', function() {
                changedOptions.isManager = true;
                changedOptions.showOpps = true;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Direct');
            });
            it('when manager viewing manager worksheet', function() {
                changedOptions.isManager = true;
                changedOptions.showOpps = false;
                layout._onceInitSelectedUser(layout.initOptions.context.model, changedOptions);
                expect(layout.model.get('forecastType')).toEqual('Rollup');
            });
        });
    });

    describe('sync', function() {
        var filters = undefined;
        beforeEach(function() {
            apiCallStub.restore();
            apiCallStub = sinon.stub(app.api, 'call', function(method, url, payload, callback) {
                filters = payload;
            });
            layout._onceInitSelectedUser(layout.initOptions.context.model, {
                id: 'test_id',
                isManager: false,
                showOpps: false
            });
        });

        afterEach(function() {
            apiCallStub.restore();
            apiCallStub = sinon.stub(app.api, 'call', function() {
            });
        });

        it('should have user_id and forecast_type defined in the filters', function() {
            layout.sync('read', layout.collection, {});
            expect(_.isObject(filters)).toBeTruthy();
            expect(_.isUndefined(filters['filter'][0])).toBeFalsy();
            expect(filters['filter'][0]['user_id']).toEqual(layout.model.get('selectedUserId'));
            expect(_.isUndefined(filters['filter'][1])).toBeFalsy();
            expect(filters['filter'][1]['forecast_type']).toEqual(layout.model.get('forecastType'));
        });

        it('should have timeperiod_id defined in the filters', function(){
            layout.model.unset('selectedUserId');
            layout.model.set('selectedTimePeriod', 'jasmin_test');
            layout.sync('read', layout.collection, {});
            expect(_.isObject(filters)).toBeTruthy();
            expect(_.isUndefined(filters['filter'][0])).toBeFalsy();
            expect(filters['filter'][0]['timeperiod_id']).toEqual(layout.model.get('selectedTimePeriod'));
        });
    });
});
