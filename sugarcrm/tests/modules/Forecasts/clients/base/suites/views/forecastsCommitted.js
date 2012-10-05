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
/*
describe("The forecastCommitted view", function(){

    var app, view, committedSaveFunction;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsCommitted", "forecastsCommitted", "js", function(d) { return eval(d); });
    });

    describe("test commitForecast function", function() {

        beforeEach(function() {

            view._collection = new Backbone.Collection();


            //Set a spy on the set function
            committedSaveFunction = sinon.spy(view._collection, "set");

            view.context = {
                forecasts : {
                    get : function(key) {
                        if(key == "commitButtonEnabled") {
                            return true;
                        } else if(key == "selectedUser") {
                            return {
                                isManager : false,
                                showOpps : true
                            }
                        }
                    },

                    set : function(key, value) {

                    }
                }
            };

            view.totals = {
                best_adjusted: 100,
                likely_adjusted: 100,
                worst_adjusted: 100,
                amount: 100,
                included_opp_count: 1
            }

            view.timePeriodId = 'abc';
            view.forecastType = 'Direct';
        });

        afterEach(function() {
            committedSaveFunction.restore();
        })


        it("test to see that forecast uses base currency_id (-99) and base_rate (1)", function() {
            view.commitForecast();
            expect(committedSaveFunction).toHaveBeenCalled();
        });


    });
});
*/