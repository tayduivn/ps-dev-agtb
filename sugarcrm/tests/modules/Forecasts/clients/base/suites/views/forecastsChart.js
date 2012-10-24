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

describe("The forecasts chart view", function () {

    var app, view, config, testMethodStub, context, viewController, stubs = [];

    beforeEach(function () {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsChart", "forecastsChart", "js", function (d) {
            return eval(d);
        });
        context = app.context.getContext({
            url:"someurl",
            module:"Forecasts"
        });

        context.forecasts = new Backbone.Model();

        view.context = context;
    });

    afterEach(function () {
        _.each(stubs, function (stub) {
            stub.restore();
        })
    });

    describe("getChartDatasets method", function () {

        var lstubs = [];

        beforeEach(function () {
            lstubs.push(sinon.stub(app.metadata, "getStrings", function () {
                return {
                    'forecasts_chart_options_dataset':{
                        'likely':'Likely',
                        'best':'Best',
                        'worst':'Worst'
                    }
                }
            }));

            view.context.forecasts.config = new Backbone.Model({
                'show_worksheet_likely' : 0,
                'show_worksheet_best' : 0,
                'show_worksheet_worst' : 0
            });
        });

        afterEach(function () {
            _.each(lstubs, function (stub) {
                stub.restore();
            });

            delete view.context.forecasts.config;
        });

        it("should return no dataset", function () {
            ds = view.getChartDatasets();

            expect(_.isEmpty(ds)).toBeTruthy();
        });
        it("should return likely dataset", function(){
            view.context.forecasts.config.set({'show_worksheet_likely': 1});
            ds = view.getChartDatasets();

            expect(_.keys(ds)).toEqual(['likely']);
        });
        it("should return likely and best dataset", function(){
            view.context.forecasts.config.set({'show_worksheet_likely': 1, 'show_worksheet_best': 1});
            ds = view.getChartDatasets();

            expect(_.keys(ds)).toEqual(['likely', 'best']);
        });
        it("should return likely, best and worst dataset", function(){
            view.context.forecasts.config.set({'show_worksheet_likely': 1, 'show_worksheet_best': 1, 'show_worksheet_worst': 1});
            ds = view.getChartDatasets();

            expect(_.keys(ds)).toEqual(['likely', 'best', 'worst']);
        });
    });
});