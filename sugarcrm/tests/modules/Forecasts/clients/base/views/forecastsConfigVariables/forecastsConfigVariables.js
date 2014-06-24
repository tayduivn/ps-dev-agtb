//FILE SUGARCRM flav=pro ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

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
