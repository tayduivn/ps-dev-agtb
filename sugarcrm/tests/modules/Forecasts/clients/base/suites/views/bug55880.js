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

/*
 * Skip this test.  Expected Opportunities is not a part of nutmeg
 */
describe("Bug 55880", function() {
    var app,
        view,
        layout,
        field,
        _renderFieldStub,
        _renderFieldSpy,
        _setForecastColumnStub,
        _renderStub,
        _isMyWorksheetStub;

    describe("Expected opportunities fields", function (){
        beforeEach(function() {
            app = SugarTest.app;

            // Set up context.forecasts for the view
            metadata = fixtures.metadata;
            app.viewModule = "Forecasts";
            app.initData = {};
            app.defaultSelections = {
                timeperiod_id: {},
                group_by: {},
                dataset: {},
                selectedUser: {}
            };
            SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/forecasts", "forecasts", "js", function(d) { return eval(d); });
            var options = {
                context: {
                    forecasts: {
                        set: function() {}
                    }
                }
            };
            stubs = new Array();
            stubs.push(sinon.stub(app.metadata, "getLayout", function(layout){
                return {
                    forecasts: {
                        meta: {
                            components: {}
                        }
                    },
                    componentsMeta: {}
                };
            }));
            stubs.push(sinon.stub(app.view.layouts.ForecastsLayout.prototype, "initialize", function (options) {}));
            layout = new app.view.layouts.ForecastsLayout(options);

            view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastSchedule", "forecastSchedule", "js", function(d) { return eval(d); });
            _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
            _renderFieldSpy = sinon.spy(view, "_renderField");

            field = {
                name:'expected_best_case',
                viewName:'worksheet',
                def:{}
            };
        });

        afterEach(function () {
            _renderFieldStub.restore();
            // restore the local stubs
            _.each(stubs, function(stub) {
                stub.restore();
            });
        });

        xit("should be click to editable on a user's own worksheet.", function () {
            view.editableWorksheet = true;
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderFieldSpy).toHaveBeenCalled();
        });

        xit("should not be click to editable on a worksheet that does not belong to the user.", function () {
            view.editableWorksheet = false;
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalled();
            expect(_renderFieldSpy).toHaveBeenCalled();
        });

        describe("editableWorksheet property", function () {
            beforeEach(function() {
                _setForecastColumnStub = new sinon.stub(view, "_setForecastColumn");
                _renderStub = new sinon.stub(app.view.View.prototype, "_render");
                view.meta = {};
                view.meta.panels = new Array({fields:{}});
                view.context = {};
                view.context.forecasts = new (Backbone.Model.extend({
                    "defaults" : {
                        "currentWorksheet" : "worksheet"
                    }
                }));
            });

            afterEach(function () {
                _setForecastColumnStub.restore();
                _renderStub.restore();
                _isMyWorksheetStub.restore();
            });

            xit("should be set to true for a user's own worksheet", function () {
                _isMyWorksheetStub = new sinon.stub(view, "isMyWorksheet", function () { return true; });
                view._render();
                expect(_isMyWorksheetStub).toHaveBeenCalled();
                expect(view.editableWorksheet).toBeTruthy();
            });
            xit("should be set to false for worksheet that does not belong to the user", function () {
                _isMyWorksheetStub = new sinon.stub(view, "isMyWorksheet", function () { return false; });
                view._render();
                expect(_isMyWorksheetStub).toHaveBeenCalled();
                expect(view.editableWorksheet).toBeFalsy();
            });
        });
    });
});
