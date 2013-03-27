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

describe("forecasts_lib_forecastsUtils", function() {

    var app, getModuleStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/lib", "ForecastsUtils", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../sidecar/src/utils", "currency", "js", function(d) {
            return eval(d);
        });
        SugarTest.loadFile("../modules/Forecasts/clients/base/helper", "hbt-helpers", "js", function(d) {
            return eval(d);
        });

    });

    afterEach(function() {
    });

    describe("test getCommittedHistoryLabel function", function() {
        var likelyArgs = {},
            bestArgs = {},
            worstArgs = {},
            isFirstCommit = false;

        beforeEach(function() {
            // reset all args to false
            isFirstCommit = false;
            likelyArgs = {
                changed: false,
                show: false
            };
            bestArgs = {
                changed: false,
                show: false
            };
            worstArgs = {
                changed: false,
                show: false
            };
            getModuleStub = sinon.stub(app.metadata, "getModule", function(module, type) {
                return {
                    show_worksheet_likely: 1,
                    show_worksheet_best: 1,
                    show_worksheet_worst: 0
                };
            });
        });

        afterEach(function() {
            getModuleStub.restore();
        });

        // Testing just the number of args returned
        describe("Should return the right amount of args", function() {
            it("has no items shown, should return 1 arg", function() {
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args.length).toBe(1);
            });
            it("has 1 item shown, should return 2 args", function() {
                bestArgs.show = true;
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args.length).toBe(2);
            });
            it("has 2 items shown, should return 3 args", function() {
                bestArgs.show = true;
                likelyArgs.show = true;
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args.length).toBe(3);
            });
            it("has 3 items shown, should return 4 args", function() {
                bestArgs.show = true;
                likelyArgs.show = true;
                worstArgs.show = true;
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args.length).toBe(4);
            });
        });

        // Testing just the proper lang strings returned for the first arg of
        // LBL_COMMITTED_HISTORY_SETUP_FORECAST or LBL_COMMITTED_HISTORY_UPDATED_FORECAST
        describe("First Time Setup vs. Updated Forecasts tests", function() {
            it("has isFirstCommit = true & should return array with first arg being SETUP_FORECAST string", function() {
                isFirstCommit = true;
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args[0] == 'LBL_COMMITTED_HISTORY_SETUP_FORECAST').toBeTruthy();
            });

            it("has isFirstCommit = false & should return array with first arg being UPDATED_FORECAST string", function() {
                isFirstCommit = false;
                args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                expect(args[0] == 'LBL_COMMITTED_HISTORY_UPDATED_FORECAST').toBeTruthy();
            });
        });

        // now that args count returned has been tested, and the first returned arg SETUP vs UPDATED has passed
        // only testing likely/best/worst strings
        describe("Likely/Best/Worst lang strings returned in the proper order", function() {

            // test the return of just one column at a time
            describe("One column shown or hidden at a time", function() {

                it("likely is only column shown, no change", function() {
                    likelyArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_LIKELY_SAME').toBeTruthy();
                });
                it("likely is only column shown, has changed", function() {
                    likelyArgs.show = true;
                    likelyArgs.changed = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_LIKELY_CHANGED').toBeTruthy();
                });

                it("best is only column shown, no change", function() {
                    bestArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_BEST_SAME').toBeTruthy();
                });
                it("best is only column shown, has changed", function() {
                    bestArgs.show = true;
                    bestArgs.changed = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_BEST_CHANGED').toBeTruthy();
                });

                it("worst is only column shown, no change", function() {
                    worstArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_WORST_SAME').toBeTruthy();
                });
                it("worst is only column shown, has changed", function() {
                    worstArgs.show = true;
                    worstArgs.changed = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_WORST_CHANGED').toBeTruthy();
                });

            });

            describe("Two columns shown, checking proper order is maintained", function() {
                // One column has been tested, just making sure order is right
                // LIKELY > BEST > WORST
                it("likely and best shown, likely should be first", function() {
                    likelyArgs.show = true;
                    bestArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_LIKELY_SAME').toBeTruthy();
                    expect(args[2] == 'LBL_COMMITTED_HISTORY_BEST_SAME').toBeTruthy();
                });
                it("likely and worst shown, likely should be first", function() {
                    likelyArgs.show = true;
                    worstArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_LIKELY_SAME').toBeTruthy();
                    expect(args[2] == 'LBL_COMMITTED_HISTORY_WORST_SAME').toBeTruthy();
                });
                it("best and worst shown, best should be first", function() {
                    bestArgs.show = true;
                    worstArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_BEST_SAME').toBeTruthy();
                    expect(args[2] == 'LBL_COMMITTED_HISTORY_WORST_SAME').toBeTruthy();
                });
            });

            describe("Three columns shown, checking proper order is maintained", function() {
                // One and two columns have been tested, just making sure order is right
                // LIKELY > BEST > WORST
                it("likely and best shown, likely should be first", function() {
                    likelyArgs.show = true;
                    bestArgs.show = true;
                    worstArgs.show = true;
                    args = app.utils.getCommittedHistoryLabel(bestArgs, likelyArgs, worstArgs, isFirstCommit);
                    expect(args[1] == 'LBL_COMMITTED_HISTORY_LIKELY_SAME').toBeTruthy();
                    expect(args[2] == 'LBL_COMMITTED_HISTORY_BEST_SAME').toBeTruthy();
                    expect(args[3] == 'LBL_COMMITTED_HISTORY_WORST_SAME').toBeTruthy();
                });
            });

        });

    });

    describe("test createHistoryLog function", function() {
        beforeEach(function() {
            newestModel = new Backbone.Model();
            oldestModel = new Backbone.Model();
            // set the exact same default models so change is not a factor in these tests
            newestModel.set({
                best_case: 1000,
                likely_case: 900,
                worst_case: 700,
                date_entered: '2012-07-12 18:37:36'
            });
            oldestModel.set({
                best_case: 1000,
                likely_case: 900,
                worst_case: 700,
                date_entered: '2012-07-12 18:37:36'
            });
        });

        afterEach(function() {
            getModuleStub.restore();
            newestModel = null;
            oldestModel = null;
        });

        // These tests are only testing createHistoryLog and the returned lang string which
        // shows how many columns should be shown based on config params
        it("The one column lang string should be used", function() {
            getModuleStub = sinon.stub(app.metadata, "getModule", function(module, type) {
                return {
                    show_worksheet_likely: 1,
                    show_worksheet_best: 0,
                    show_worksheet_worst: 0
                };
            });
            result = app.utils.createHistoryLog(oldestModel, newestModel);
            expect(result.text == 'LBL_COMMITTED_HISTORY_1_SHOWN').toBeTruthy();
        });
        it("The two column lang string should be used", function() {
            getModuleStub = sinon.stub(app.metadata, "getModule", function(module, type) {
                return {
                    show_worksheet_likely: 1,
                    show_worksheet_best: 1,
                    show_worksheet_worst: 0
                };
            });
            result = app.utils.createHistoryLog(oldestModel, newestModel);
            expect(result.text == 'LBL_COMMITTED_HISTORY_2_SHOWN').toBeTruthy();
        });
        it("The three column lang string should be used", function() {
            getModuleStub = sinon.stub(app.metadata, "getModule", function(module, type) {
                return {
                    show_worksheet_likely: 1,
                    show_worksheet_best: 1,
                    show_worksheet_worst: 1
                };
            });
            result = app.utils.createHistoryLog(oldestModel, newestModel);
            expect(result.text == 'LBL_COMMITTED_HISTORY_3_SHOWN').toBeTruthy();
        });
    });

    describe("test parseArgsAndLabels function", function() {
        var argsArray = [],
            labels = [];

        beforeEach(function() {
            // reset arrays before each
            argsArray = [];
            labels = [];
        });

        // "mismatched array lengths" refers to argsArray appropriately being 1 less than labels
        // labels will have a label for SETUP/UPDATED lang string which needs no args so it should return null
        it("on mismatched array lengths should return null", function() {
            result = app.utils.parseArgsAndLabels(argsArray, labels);
            expect(result).toBeNull();
        });

        // No further tests can be done on this function because Handlebars doesn't compile in our jasmine tests
    });

    describe("test getDifference function", function() {
        beforeEach(function() {
            newestModel = new Backbone.Model();
            oldestModel = new Backbone.Model();
            // set the exact same default models
            newestModel.set({
                best_case: 1000
            });
            oldestModel.set({
                best_case: 1000
            });
        });

        it("newestModel being higher should give a positive difference", function() {
            newestModel.set({best_case: 2000});
            result = app.utils.getDifference(oldestModel, newestModel, 'best_case');
            expect(result).toBe(1000);
        });
        it("newestModel being lower should give a negative difference", function() {
            oldestModel.set({best_case: 2000});
            result = app.utils.getDifference(oldestModel, newestModel, 'best_case');
            expect(result).toBe(-1000);
        });
        it("newestModel being the same as oldestModel should give a difference of 0", function() {
            result = app.utils.getDifference(oldestModel, newestModel, 'best_case');
            expect(result).toBe(0);
        });
        it("newestModel being different by decimals smaller than 0.01 should give a difference of 0", function() {
            newestModel.set({best_case: 1000.009});
            result = app.utils.getDifference(oldestModel, newestModel, 'best_case');
            expect(result).toBe(0);
        });
    });

    describe("test getDirection function", function() {
        var testDifference = 0;
        beforeEach(function() {
            testDifference = 0;
        });

        it("testDifference being positive should result in LBL_UP", function() {
            testDifference = 100;
            result = app.utils.getDirection(testDifference);
            expect(result).toEqual('LBL_UP');
        });
        it("testDifference being negative should result in LBL_DOWN", function() {
            testDifference = -100;
            result = app.utils.getDirection(testDifference);
            expect(result).toEqual('LBL_DOWN');
        });
        it("testDifference being 0 should result in an empty string", function() {
            result = app.utils.getDirection(testDifference);
            expect(result).toEqual('');
        });
    });

    describe("test gatherLangArgsByParams function", function() {
        var dir = '',
            arrow = '',
            diff = 0,
            model = {},
            attrStr = '';

        beforeEach(function() {
            dir = 'LBL_UP';
            arrow = '&nbsp;<span class="icon-arrow-up font-green"></span>';
            diff = 1000;
            model = new (Backbone.Model.extend({
                defaults: {
                    likely_case: 250,
                    best_case: 500,
                    worst_case: 100
                }
            }));
            attrStr = 'likely_case';
        });

        it("should return an array of three args", function() {
            var testArgs = app.utils.gatherLangArgsByParams(dir, arrow, diff, model, attrStr);
            expect(testArgs.length).toEqual(3);
        });
    });

    describe("Should return a span for the arrow dependent on direction passed", function() {
        it("should return a span indicating an arrow up", function() {
            arrowText = app.utils.getArrowDirectionSpan("LBL_UP");
            expect(arrowText).toEqual('&nbsp;<span class="icon-arrow-up font-green"></span>');
        });

        it("should return a span indicating an arrow down", function() {
            arrowText = app.utils.getArrowDirectionSpan("LBL_DOWN");
            expect(arrowText).toEqual('&nbsp;<span class="icon-arrow-down font-red"></span>');
        });

        it("should return a span indicating no change", function() {
            arrowText = app.utils.getArrowDirectionSpan("");
            expect(arrowText).toEqual('');
        });
    });
});
