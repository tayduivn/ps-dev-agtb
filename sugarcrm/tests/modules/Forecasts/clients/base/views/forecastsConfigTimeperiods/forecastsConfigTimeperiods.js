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

describe("forecasts_view_forecastsConfigTimeperiods", function(){

    var app,
        view,
        field,
        intervalField,
        _renderFieldStub,
        testIntervalMethodStub,
        testShowFWBWMethodStub,
        testValue,
        testIntervalValue,
        testLeafIntervalValue,
        configStub;

    beforeEach(function() {
        app = SugarTest.app;

        configStub = sinon.collection.stub(app.metadata, 'getModule', function() {
            return {
                is_setup: 1
            }
        });

        view = SugarTest.createView('base', 'Forecasts', 'forecastsConfigTimeperiods', null, null, true, null, true);

        _renderFieldStub = sinon.collection.stub(view, '_super');
    });

    afterEach(function() {
        configStub.restore();
        _renderFieldStub.restore();
        view = null;
        app = null;
    });

    describe("timeperiod selects setup method", function() {
        beforeEach(function() {
            testIntervalMethodStub = sinon.stub(view, "_setUpTimeperiodIntervalBind", function() {return field;});
            testShowFWBWMethodStub = sinon.stub(view, "_setUpTimeperiodShowField", function() {return field;});
            field = {
                options: {
                    def: {

                    }
                }
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
            expect(_renderFieldStub).toHaveBeenCalledWith('_renderField', [field]);
            expect(testShowFWBWMethodStub).toHaveBeenCalledWith(field);
        });

        it("should set up timeperiod_shown_backward field", function() {
            field.name = "timeperiod_shown_backward";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith('_renderField', [field]);
            expect(testShowFWBWMethodStub).toHaveBeenCalledWith(field);
        });

        //BEGIN SUGARCRM flav=pro ONLY
        it("should set up day field", function() {
            field.name = "timeperiod_interval";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith('_renderField', [field]);
            expect(testIntervalMethodStub).toHaveBeenCalledWith(field);
        });
        //END SUGARCRM flav=pro ONLY

        it("should not set up non-date selecting fields", function() {
            field.name = "timeperiod_config_other";
            view._renderField(field);
            expect(_renderFieldStub).toHaveBeenCalledWith('_renderField', [field]);
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
            expect(intervalField.events["change input"]).toBeDefined();
            expect(intervalField.events["change input"]).toEqual("_updateIntervals");
            expect(intervalField._updateIntervals).toBeDefined();
        });

        //BEGIN SUGARCRM flav=pro ONLY
        it("should check that the method to select the interval and default the leaf was called", function() {
            testIntervalMethodStub = sinon.stub(intervalField, "_updateIntervals", function() {return '';});
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
