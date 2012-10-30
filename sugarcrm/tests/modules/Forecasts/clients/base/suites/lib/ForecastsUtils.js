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
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_LIKELY_WORST_CHANGED').toBeTruthy();
            });

            it("should return object with text attribute indicating both best and likely values changed (first commit)", function() {
                newestModel.set('best_case', 1000);
                newestModel.set('likely_case', 900);
                newestModel.set('worst_case', 700);
                newestModel.set('date_entered', '2012-07-12 18:37:36');

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_LIKELY_WORST_SETUP').toBeTruthy();
            });

            it("should return label indicating all values have changed", function() {
               //call getCommitted history label with best_changed = true, likely_changed = true and worst_changed = true
               text = app.forecasts.utils.getCommittedHistoryLabel(true, true, true);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_LIKELY_WORST_CHANGED');
            });

            it("should return label indicating all values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = true, likely_changed = true, worst_changed = true and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(true, true, true, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_LIKELY_WORST_SETUP');
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
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_LIKELY_CHANGED').toBeTruthy();
            });

            it("should return object with text attribute indicating both best and likely values changed (first commit)", function() {
                newestModel.set('worst_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_LIKELY_SETUP').toBeTruthy();
            });

            it("should return label indicating best and likely values have changed", function() {
               //call getCommitted history label with best_changed = true, likely_changed = true and worst_changed = false
               text = app.forecasts.utils.getCommittedHistoryLabel(true, true, false);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_LIKELY_CHANGED');
            });

            it("should return label indicating best and likely values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = true, likely_changed = true, worst_changed = false and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(true, true, false, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_LIKELY_SETUP');
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

            it("should return object with text attribute indicating both best and likely values changed (first commit)", function() {
                newestModel.set('likely_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_WORST_SETUP').toBeTruthy();
            });

            it("should return label indicating best and worst values have changed", function() {
               //call getCommitted history label with best_changed = true, likely_changed = false and worst_changed = true
               text = app.forecasts.utils.getCommittedHistoryLabel(true, false, true);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_WORST_CHANGED');
            });

            it("should return label indicating best and worst values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = true, likely_changed = false, worst_changed = true and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(true, false, true, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_WORST_SETUP');
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

            it("should return object with text attribute indicating both best and likely values changed (first commit)", function() {
                newestModel.set('best_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_WORST_SETUP').toBeTruthy();
            });

            it("should return label indicating likely ad worst values have changed", function() {
               //call getCommitted history label with best_changed = false, likely_changed = true and worst_changed = true
               text = app.forecasts.utils.getCommittedHistoryLabel(false, true, true);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_LIKELY_WORST_CHANGED');
            });

            it("should return label indicating likely ad worst values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = false, likely_changed = true, worst_changed = true and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(false, true, true, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_LIKELY_WORST_SETUP');
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

            it("should return object with text attribute best case value changed", function() {
                newestModel.set('likely_case', 0);
                newestModel.set('worst_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_BEST_SETUP').toBeTruthy();
            });

            it("should return label indicating best values have changed", function() {
               //call getCommitted history label with best_changed = true, likely_changed = false and worst_changed = false
               text = app.forecasts.utils.getCommittedHistoryLabel(true, false, false);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_CHANGED');
            });

            it("should return label indicating best values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = true, likely_changed = false, worst_changed = false and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(true, false, false, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_BEST_SETUP');
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

            it("should return object with text attribute likely case value changed (first commit)", function() {
                newestModel.set('best_case', 0);
                newestModel.set('worst_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_LIKELY_SETUP').toBeTruthy();
            });

            it("should return label indicating likely values have changed", function() {
               //call getCommitted history label with best_changed = false, likely_changed = true and worst_changed = false
               text = app.forecasts.utils.getCommittedHistoryLabel(false, true, false);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_LIKELY_CHANGED');
            });

            it("should return label indicating likely values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = false, likely_changed = true, worst_changed = false and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(false, true, false, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_LIKELY_SETUP');
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

            it("should return object with text attribute likely case value changed (first commit)", function() {
                newestModel.set('best_case', 0);
                newestModel.set('likely_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_WORST_SETUP').toBeTruthy();
            });

            it("should return label indicating worst values have changed", function() {
               //call getCommitted history label with best_changed = false, likely_changed = false and worst_changed = true
               text = app.forecasts.utils.getCommittedHistoryLabel(false, false, true);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_WORST_CHANGED');
            });

            it("should return label indicating worst values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = false, likely_changed = false, worst_changed = true and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(false, false, true, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_WORST_SETUP');
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
                expect(result.text == 'LBL_COMMITTED_HISTORY_CHANGED').toBeTruthy();
            });

            it("should return object with text attribute no value changed (first commit)", function() {
                newestModel.set('best_case', 0);
                newestModel.set('likely_case', 0);
                newestModel.set('worst_case', 0);

                result = app.forecasts.utils.createHistoryLog('',newestModel);
                expect(result.text == 'LBL_COMMITTED_HISTORY_SETUP').toBeTruthy();
            });

            it("should return label indicating no values have changed", function() {
               //call getCommitted history label with best_changed = false, likely_changed = false and worst_changed = false
               text = app.forecasts.utils.getCommittedHistoryLabel(false, false, false);
               expect(text).toEqual('LBL_COMMITTED_HISTORY_CHANGED');
            });

            it("should return label indicating no values have changed (first commit)", function() {
                //call getCommitted history label with best_changed = false, likely_changed = false, worst_changed = false and is_first_commit = true
                text = app.forecasts.utils.getCommittedHistoryLabel(false, false, false, true);
                expect(text).toEqual('LBL_COMMITTED_HISTORY_SETUP');
            });
        });

        describe("Should return a span for the arrow dependent on direction passed", function() {
           it("should return a span indicating an arrow up", function() {
               arrowText = app.forecasts.utils.getArrowDirectionSpan("LBL_UP");
               expect(arrowText).toEqual('&nbsp;<span class="icon-arrow-up font-green"></span>');
           });

           it("should return a span indicating an arrow down", function() {
                arrowText = app.forecasts.utils.getArrowDirectionSpan("LBL_DOWN");
                expect(arrowText).toEqual('&nbsp;<span class="icon-arrow-down font-red"></span>');
           });

           it("should return a span indicating no change", function() {
               arrowText = app.forecasts.utils.getArrowDirectionSpan("");
               expect(arrowText).toEqual('');
           });
        });
    });
});
