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

describe("forecasts_view_forecastsChart", function () {

    var app, view, context, stubs = [];

    beforeEach(function () {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsChart", "forecastsChart", "js", function (d) {
            return eval(d);
        });
        context = app.context.getContext({
            url:"someurl",
            module:"Forecasts"
        });

        view.context = context;
    });

    afterEach(function () {
        _.each(stubs, function (stub) {
            stub.restore();
        })
    });

    describe("handleRenderOptions", function(){
        var renderStub, valuesSpy;
        beforeEach(function() {
            renderStub = sinon.stub(view, "renderChart", function(){});
            valuesSpy = sinon.spy(view.values, "on");
            view.context = {
                'on': function() {},
                'worksheetmanager' : {
                    'on' : function() {}
                },
                'worksheet' : {
                    'on' : function() {}
                }
            };
            view.values.set({hello : 'world'}, {silent: true});
            view.bindDataChange();
        });

        afterEach(function() {
            valuesSpy.restore();
            renderStub.restore();
            delete view.context;
        });

        it("should cause values model to fire change event", function(){
            view.handleRenderOptions({hello: 'jon'});
            expect(valuesSpy).toHaveBeenCalled();
            expect(renderStub).toHaveBeenCalled();
            expect(view.values.get('hello')).toEqual('jon');
        });

        it("should cause values model not fire change event", function(){
            view.handleRenderOptions({hello: 'world'});
            expect(valuesSpy).toHaveBeenCalled();
            expect(renderStub).not.toHaveBeenCalled();
            expect(view.values.get('hello')).toEqual('world');
        });
    });

    describe("forecasts context change events", function() {
        var handleRenderOptionsStub, toggleRepOptionsVisibilityStub, valuesSpy;
        beforeEach(function() {
            handleRenderOptionsStub = sinon.stub(view, "handleRenderOptions", function(){});
            toggleRepOptionsVisibilityStub = sinon.stub(view, "toggleRepOptionsVisibility", function(){});
            valuesSpy = sinon.spy(view.values, "on");

            view.context.worksheetmanager = {
                    'on' : function() {}
                };
            view.context.worksheet = {
                    'on' : function() {}
                };
            view.bindDataChange();
        });

        afterEach(function() {
            handleRenderOptionsStub.restore();
            toggleRepOptionsVisibilityStub.restore();
            delete view.context;
            view.chart = null;
        });

        it("trigger change:selectedUser should not call handleRenderOptions", function() {
            view.context.set('selectedUser', {hello: 'world'});
            expect(handleRenderOptionsStub).not.toHaveBeenCalled()
        });

        it("trigger change:selectedUser should call handleRenderOptions", function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.set('selectedUser', {hello: 'world'});
            expect(handleRenderOptionsStub).toHaveBeenCalled()
        });

        it("trigger change:selectedTimePeriod should not call handleRenderOptions", function() {
            view.context.set('selectedTimePeriod', {hello: 'world'});
            expect(handleRenderOptionsStub).not.toHaveBeenCalled()
        });

        it("trigger change:selectedTimePeriod should call handleRenderOptions", function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.set('selectedTimePeriod', {hello: 'world'});
            expect(handleRenderOptionsStub).toHaveBeenCalled()
        });

        it("trigger change:selectedGroupBy should not call handleRenderOptions", function() {
            view.context.set('selectedGroupBy', {hello: 'world'});
            expect(handleRenderOptionsStub).not.toHaveBeenCalled()
        });

        it("trigger change:selectedGroupBy should call handleRenderOptions", function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.set('selectedGroupBy', {hello: 'world'});
            expect(handleRenderOptionsStub).toHaveBeenCalled()
        });

        it("trigger change:selectedRanges should not call handleRenderOptions", function() {
            view.context.set('selectedRanges', {hello: 'world'});
            expect(handleRenderOptionsStub).not.toHaveBeenCalled()
        });

        it("trigger change:selectedRanges should call handleRenderOptions", function() {
            view.chart = {'chart_object' : 'obj'};
            view.context.set('selectedRanges', {hello: 'world'});
            expect(handleRenderOptionsStub).toHaveBeenCalled()
        });

        describe("worksheet save draft", function() {
            var renderChartStub;

            beforeEach(function(){
                renderChartStub = sinon.stub(view, "renderChart", function(){})
            });

            afterEach(function(){
                renderChartStub.restore();
            });

            it("should fire renderChart when one row saved and isDraft is true", function(){
                view.context.trigger('forecasts:worksheet:saved', 1, 'rep_worksheet', true);

                expect(renderChartStub).toHaveBeenCalled();
            });

            it("should NOT fire renderChart when one row saved and isDraft is false", function(){
                view.context.trigger('forecasts:worksheet:saved', 1, 'rep_worksheet', false);

                expect(renderChartStub).not.toHaveBeenCalled();
            });

            it("should NOT fire renderChart when zero rows are saved and isDraft is true", function(){
                view.context.trigger('forecasts:worksheet:saved', 0, 'rep_worksheet', true);

                expect(renderChartStub).not.toHaveBeenCalled();
            });

            it("should NOT fire renderChart when zero rows are saved and isDraft is false", function(){
                view.context.trigger('forecasts:worksheet:saved', 0, 'rep_worksheet', false);

                expect(renderChartStub).not.toHaveBeenCalled();
            });
        });

        describe("sidebarHidden listener, stopRender value", function(){
            var renderChartStub;

            beforeEach(function(){
                renderChartStub = sinon.stub(view, "renderChart", function(){})
            });

            afterEach(function(){
                renderChartStub.restore();
            });

            it("should be true", function(){
                view.context.trigger('forecasts:commitButtons:sidebarHidden', true);

                expect(view.stopRender).toBeTruthy();
                expect(renderChartStub).not.toHaveBeenCalled();
            });
            it("should be false", function(){
                view.context.trigger('forecasts:commitButtons:sidebarHidden', false);

                expect(view.stopRender).toBeFalsy();
                expect(renderChartStub).toHaveBeenCalled();
            });
        })
    });

    describe("tests buildChartUrl function", function(){
        it("should return properly formatted url", function(){
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b'
                },
                result = view.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    });

    describe("tests buildChartUrl function for manager", function(){
        it("should return properly formatted url", function(){
            var params = {
                    timeperiod_id: 'a',
                    user_id: 'b'
                },
                result = view.buildChartUrl(params);

            result = result.split('/');
            expect(result[0]).toBe('ForecastManagerWorksheets');
            expect(result[1]).toBe('chart');
            expect(result[2]).toBe('a');
            expect(result[3]).toBe('b');
        });
    })
});
