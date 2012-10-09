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

describe("The forecasts committed view", function () {

    var app, view, testMethodStub, context, viewController, stubs = [], formatAmountLocaleStub;

    beforeEach(function () {
        app = SugarTest.app;
        viewController = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsCommitted", "forecastsCommitted", "js", function (d) {
            return d;
        });
        SugarTest.loadFile("../sidecar/src/utils", "currency", "js", function (d) {
            return eval(d);
        });
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function (d) {
            return eval(d);
        });
        context = app.context.getContext({
            url:"someurl",
            module:"Forecasts"
        });

        app.defaultSelections = {
            timeperiod_id:{},
            group_by:{},
            dataset:{},
            selectedUser:{}
        };

        var model1 = new Backbone.Model({date_entered:"2012-12-05T11:14:25-04:00", best_case:100, likely_case:90, base_rate:1 });
        var model2 = new Backbone.Model({date_entered:"2012-10-05T11:14:25-04:00", best_case:110, likely_case:100, base_rate:1 });
        var model3 = new Backbone.Model({date_entered:"2012-11-05T11:14:25-04:00", best_case:120, likely_case:110, base_rate:1 });
        var collection = new Backbone.Collection([model1, model2, model3]);
        context.forecasts = new Backbone.Model();
        context.forecasts.committed = collection;

        view = SugarTest.createComponent("View", {
            context:context,
            name:"forecastsCommitted",
            controller:viewController
        });

        formatAmountLocaleStub = sinon.stub(app.currency, "formatAmountLocale", function (value) {
            return value;
        });
    });

    afterEach(function () {
        _.each(stubs, function (stub) {
            stub.restore();
        });
        formatAmountLocaleStub.restore();
    });

    describe("test arrow directions for sales rep", function () {
        it("should show up for both", function () {

            var totals = {
                'amount': 500,
                'best_case':500,
                'best_adjusted':550,
                'likely_case':450,
                'likely_adjusted':475,
                'worst_case':400,
                'worst_adjusted':425
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-up');
            expect(view.likelyCaseCls).toContain('icon-arrow-up');
        });

        it("should show down for both", function () {

            var totals = {
                'best_case':1,
                'best_adjusted':1,
                'likely_case':1,
                'likely_adjusted':1,
                'worst_case':1,
                'worst_adjusted':1
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-down');
            expect(view.likelyCaseCls).toContain('icon-arrow-down');
        });
    });

    describe("test arrow directions for manager", function () {
        beforeEach(function(){
            view.selectedUser = {
                isManager: true,
                showOpps: false
            }
        });

        afterEach(function(){
            view.selectedUser = {}
        });

        it("should show up for both", function () {

            var totals = {
                'amount': 500,
                'best_case':500,
                'best_adjusted':550,
                'likely_case':450,
                'likely_adjusted':475,
                'worst_case':400,
                'worst_adjusted':425
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-up');
            expect(view.likelyCaseCls).toContain('icon-arrow-up');
        });

        it("should show down for both", function () {

            var totals = {
                'best_case':1,
                'best_adjusted':1,
                'likely_case':1,
                'likely_adjusted':1,
                'worst_case':1,
                'worst_adjusted':1
            };

            view.updateTotals(totals);

            expect(view.bestCaseCls).toContain('icon-arrow-down');
            expect(view.likelyCaseCls).toContain('icon-arrow-down');
        });
    });

});