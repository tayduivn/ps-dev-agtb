//FILE SUGARCRM flav=pro ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

describe('forecasts_chart', function() {

    var app, view, context, parent, stubs = [];

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile(
            '../modules/Forecasts/clients/base/views/forecasts-chart',
            'forecasts-chart', 'js',
            function(d) {
                return eval(d);
            });
        context = app.context.getContext({
            url: 'someurl',
            module: 'Forecasts'
        });
        parent = app.context.getContext({
            url: 'someurl',
            module: 'Forecasts'
        });
        context.parent = parent;
        view.context = context;
    });

    afterEach(function() {
        _.each(stubs, function(stub) {
            stub.restore();
        });
        delete view.context;
    });

    describe('forecasts context change events', function() {
        var toggleRepOptionsVisibilityStub, valuesSpy;
        beforeEach(function() {
            renderStub = sinon.stub(view, 'renderChart', function() {});
            toggleRepOptionsVisibilityStub = sinon.stub(view, 'toggleRepOptionsVisibility', function() {});
            valuesSpy = sinon.spy(view.values, 'on');

            view.bindDataChange();
        });

        afterEach(function() {
            toggleRepOptionsVisibilityStub.restore();
            delete view.context;
            renderStub.restore();
            view.chart = null;
        });

        it('trigger change:selectedUser should not call toggleRepOptionsVisibilityStub', function() {
            view.context.parent.set('selectedUser', {hello: 'world'});
            expect(toggleRepOptionsVisibilityStub).not.toHaveBeenCalled();
        });

        it('trigger change:selectedUser should call toggleRepOptionsVisibilityStub', function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.parent.set('selectedUser', {hello: 'world'});
            expect(toggleRepOptionsVisibilityStub).toHaveBeenCalled();
        });

        it('trigger change:selectedTimePeriod should not call toggleRepOptionsVisibilityStub', function() {
            view.context.parent.set('selectedTimePeriod', {hello: 'world'});
            expect(toggleRepOptionsVisibilityStub).not.toHaveBeenCalled();
        });

        it('trigger change:selectedTimePeriod should call toggleRepOptionsVisibilityStub', function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.parent.set('selectedTimePeriod', {hello: 'world'});
            expect(valuesSpy).toHaveBeenCalled();
        });

        it('trigger change:selectedRanges should not call toggleRepOptionsVisibilityStub', function() {
            view.context.parent.set('selectedRanges', {hello: 'world'});
            expect(toggleRepOptionsVisibilityStub).not.toHaveBeenCalled();
        });

        it('trigger change:selectedRanges should call toggleRepOptionsVisibilityStub', function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.parent.set('selectedRanges', {hello: 'world'});
            expect(valuesSpy).toHaveBeenCalled();
        });
    });

    describe('tests buildChartUrl function', function() {
        it('should return properly formatted url', function() {
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b',
                    display_manager: false
                },
                result = view.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });

    describe('tests buildChartUrl function for manager', function() {
        it('should return properly formatted url', function() {
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b',
                    display_manager: true
                },
                result = view.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastManagerWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });
});
