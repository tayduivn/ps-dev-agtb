/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

describe("Forecasts.Views.Info", function() {
    var view, layout, moduleName = "Forecasts", sbox = sinon.sandbox.create();

    beforeEach(function() {
        layout = SugarTest.createLayout("base", "ForecastWorksheets", "list", null, null);
        view = SugarTest.createView("base", moduleName, "info", null, null, true, layout, true);
    });

    afterEach(function() {
        view = null;
        layout = null;
        sbox.restore();
    });

    describe("when resetSelection is called", function() {
        beforeEach(function() {
            view.fields = [{
                        name:"selectedTimePeriod",
                        render: function(){},
                        dispose: function(){}
            }];
            sbox.spy(view.fields[0], "render");
            sbox.stub(view.tpModel, "set", function(){});
            sbox.stub(view, "dispose", function(){});

            view.resetSelection();
        });

        afterEach(function() {
            sbox.restore();
        });

        it("should have called render", function() {
            expect(view.fields[0].render).toHaveBeenCalled();
        });

        it("should have called set on tpModel", function() {
            expect(view.tpModel.set).toHaveBeenCalled();
        });
    });

    describe('tpModel is changed', function() {
        var tpMapValues = {
                    start: '2014-01-01',
                    end: '2014-03-31'
                };
        beforeEach(function() {
            sbox.stub(view.context, 'trigger', function(event, model, object) {
            });
            sbox.stub(view, 'getField', function() {
                return {
                    tpTooltipMap: {
                        'test_1' : tpMapValues
                    }
                };
            });
        });

        afterEach(function() {

        });

        it('will trigger event with model and object', function() {
            var m = new Backbone.Model({selectedTimePeriod: 'test_1'});
            view.tpModel.trigger('change', m);

            expect(view.context.trigger).toHaveBeenCalled();
            expect(view.context.trigger).toHaveBeenCalledWith('forecasts:timeperiod:changed', m, tpMapValues);
        });
    });
});
