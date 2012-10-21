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

describe("Forecasts Utils", function(){

    var app, hbt_helper;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) { return eval(d); });
        SugarTest.loadFile("../sidecar/src/utils", "currency", "js", function(d) { return eval(d); });
        hbt_heleper = SugarTest.loadFile("../modules/Forecasts/clients/base/helper","hbt-helpers", "js", function(d) { return eval(d); });
    });

    describe("test createHistoryLog function", function() {
        beforeEach(function() {
            App = app;
            newestModel = new Backbone.Model();
            oldestModel = new Backbone.Model();
        });

        afterEach(function(){
            newestModel = null;
            oldestModel = null;
        });

        describe("should show all values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 800);
                oldestModel.set('worst_case', 600);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_ALL_CHANGED').toBeTruthy();
            });
        });

        describe("should show best and likely values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 800);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_BEST_CHANGED').toBeTruthy();
            });
        });

        describe("should show best and worst values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 800);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 900);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_WORST_CHANGED').toBeTruthy();
            });
        });

        describe("should show likely and worst values changed", function() {
            it("should return object with text attribute indicating both best and likely values changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 800);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 800);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_WORST_CHANGED').toBeTruthy();
            });
        });

        describe("should show best values changed", function() {
            it("should return object with text attribute best case value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 900);
                oldestModel.set('likely_case', 900);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_CHANGED').toBeTruthy();
            });
        });

        describe("should show likely values changed", function() {
            it("should return object with text attribute likely case value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 800);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED').toBeTruthy();
            });
        });

        describe("should show worst values changed", function() {
            it("should return object with text attribute likely case value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 800);
                newestModel.set('worst_case', 800);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 800);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_WORST_CHANGED').toBeTruthy();
            });
        });

        describe("should show no values changed", function() {
            it("should return object with text attribute no value changed", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                oldestModel.set('best_case', 1000);
                oldestModel.set('likely_case', 900);
                oldestModel.set('worst_case', 700);
                oldestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog(oldestModel,newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_NONE_CHANGED').toBeTruthy();
            });
        });
    });
});
