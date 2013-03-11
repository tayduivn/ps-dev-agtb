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

describe("forecasts_view_forecastsConfigVariables", function(){

    var app, view, field, _renderFieldStub, testMethodStub, testValue;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigVariables", "forecastsConfigVariables", "js", function(d) { return eval(d); });
        _renderFieldStub = sinon.stub(app.view.View.prototype, "_renderField");
    });

    afterEach(function() {
        _renderFieldStub.restore();
        view = null;
        app = null;
    });

    describe("multiselect setup method", function() {
        beforeEach(function() {
            testMethodStub = sinon.stub(view, "_setUpMultiselectField", function() {return field;});
            field = {
                $el: $('<div class="testfield"></div>'),
                def: {
                    multi: ''
                }
            }
        });

        afterEach(function() {
            testMethodStub.restore();
            field = null;
        });

        it("should set up multiselect fields", function() {
            field.def.multi = true;
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testMethodStub).toHaveBeenCalledWith(field);
        });

        it("should not set up non-multiselect fields", function() {
            field.def.multi = false;
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith(field);
            expect(testMethodStub).not.toHaveBeenCalled();
        });
    });

    describe("multiselect field setup", function() {

        beforeEach(function() {
            testValue = 'testValue';
            view.model = {
                get: function() {
                    return {};
                }
            };
            field = {
                model: {
                    get: function() {
                        return {};
                    },
                    set: function() {}
                },
                name: 'testField',
                def: {
                    multi: true,
                    value: []
                }
            };
            field = view._setUpMultiselectField(field);
        });

        afterEach(function() {
            testMethodStub.restore();
            field = null;
            testValue = null;
        });

        it("should add the event handlers to update the selections for the field", function() {
            expect(field.events["change select"]).toBeDefined();
            expect(field.events["change select"]).toEqual("_updateSelections");
            expect(field._updateSelections).toBeDefined();
        });

        describe("update selections event handler", function() {
            it("should properly add a value when a user selects one", function() {
                field.def.value = [];
                expect(field.def.value).not.toContain(testValue);
                field._updateSelections({}, {selected: testValue});
                expect(field.def.value).toContain(testValue);
            });

            it("should properly remove a value when a user removes one", function() {
                field.model.get = function() {return [testValue]};
                field.def.value = field.model.get('test');
                expect(field.def.value).toContain(testValue);
                field._updateSelections({}, {deselected: testValue});
                expect(field.def.value).not.toContain(testValue);
            })
        })
    });
});
