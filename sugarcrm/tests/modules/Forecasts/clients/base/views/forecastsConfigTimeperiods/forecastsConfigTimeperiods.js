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

describe("forecasts_view_forecastsConfigTimeperiods", function(){

    var app,
        view,
        field,
        intervalField,
        _renderFieldStub,
        testIntervalMethodStub,
        testValue,
        testIntervalValue,
        testLeafIntervalValue;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigTimeperiods", "forecastsConfigTimeperiods", "js", function(d) { return eval(d); });
        _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
    });

    afterEach(function() {
        _renderFieldStub.restore();
        view = null;
        app = null;
    });

    describe("timeperiod selects setup method", function() {
        beforeEach(function() {
            testIntervalMethodStub = sinon.stub(view, "_setUpTimeperiodIntervalBind", function() {return field;});
            testShowFWBWMethodStub = sinon.stub(view, "_setUpTimeperiodShowField", function() {return field;});
            field = {
            };
            view.model = {
                get: function(key) {
                    return (key == 'is_setup')?false:key;
                }
            }
        });

        afterEach(function() {
            delete view.model;
            testIntervalMethodStub.restore();
            testShowFWBWMethodStub.restore();
            field = null;
        });


        it("should set up timeperiod_shown_forward field", function() {
            field.name = "timeperiod_shown_forward";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testShowFWBWMethodStub).toHaveBeenCalledWith(field);
        });

        it("should set up timeperiod_shown_backward field", function() {
            field.name = "timeperiod_shown_backward";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testShowFWBWMethodStub).toHaveBeenCalledWith(field);
        });

        //BEGIN SUGARCRM flav=pro ONLY
        it("should set up day field", function() {
            field.name = "timeperiod_interval";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testIntervalMethodStub).toHaveBeenCalledWith(field);
        });
        //END SUGARCRM flav=pro ONLY

        it("should not set up non-date selecting fields", function() {
            field.name = "timeperiod_config_other";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            //BEGIN SUGARCRM flav=pro ONLY
                expect(testIntervalMethodStub).not.toHaveBeenCalled();
            //END SUGARCRM flav=pro ONLY
        });
    });

    describe("timeperiod date field setup", function() {

        beforeEach(function() {
            testValue = 3;  //Simulate March as selected in the dropdown
            testIntervalValue = "Annual";
            testLeafIntervalValue = "Quarter";
            view.model = new Backbone.Model({
                timeperiod_interval: '',
                timeperiod_leaf_interval: '',
                get: function() {
                    return {};
                },
                set: function() {}
                });
            intervalField = {
                name: 'timeperiod_interval',
                def: {
                    options: {}
                }
            };
            intervalField = view._setUpTimeperiodIntervalBind(intervalField);
            intervalField.model = view.model;

        });

        afterEach(function() {
            intervalField = null;
            testValue = null;
            testIntervalValue = null;
            testLeafIntervalValue = null;
        });
        it("should add the event handlers to update the selections for the field", function() {
            expect(intervalField.events["change select"]).toBeDefined();
            expect(intervalField.events["change select"]).toEqual("_updateIntervals");
            expect(intervalField._updateIntervals).toBeDefined();
        });

        //BEGIN SUGARCRM flav=pro ONLY
        it("should check that the method to select the interval and default the leaf was called", function() {
            var testIntervalMethodStub = sinon.stub(intervalField, "_updateIntervals", function() {return '';});
            intervalField._updateIntervals({});
            expect(testIntervalMethodStub).toHaveBeenCalled();
        });

        it("should check that the method to select the interval and default the leaf set the model correctly", function() {
            spyOn($.fn, "val").andReturn("Annual")
            intervalField._updateIntervals({target: 'timeperiod_interval'}, {selected: testIntervalValue});
            expect(view.model.get("timeperiod_interval")).toEqual(testIntervalValue);
            expect(view.model.get("timeperiod_leaf_interval")).toEqual(testLeafIntervalValue);
        });
        //END SUGARCRM flav=pro ONLY
    });
});
