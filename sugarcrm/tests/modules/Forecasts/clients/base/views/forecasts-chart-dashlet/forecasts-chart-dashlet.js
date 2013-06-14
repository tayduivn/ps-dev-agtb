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

describe('forecasts_chart_dashlet', function() {

    var app, view, context, parent, stubs = [];

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile(
            '../modules/Forecasts/clients/base/views/forecasts-chart-dashlet',
            'forecasts-chart-dashlet', 'js',
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
