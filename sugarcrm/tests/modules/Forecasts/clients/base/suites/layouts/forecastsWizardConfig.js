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

describe("The forecastsWizardConfig layout controller", function(){

    var app, layout, stubs;

    beforeEach(function() {
        var options = {
            context: {
                set: function() {}
            },
            meta: {
                components: {}
            }
        };

        app = SugarTest.app;
        SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/forecastsWizardConfig", "forecastsWizardConfig", "js", function(d) {
            return eval(d);
        });
        stubs = [];
        app.viewModule = "";
        app.initData = {};

        stubs.push(sinon.stub(app.metadata, "getLayout", function(layout){
            return {
                forecasts: {
                    meta: {
                        components: {}
                    }
                },
                componentsMeta: {}
            };
        }));
        stubs.push(sinon.stub(app.view.Layout.prototype, "initialize", function (options) {}));

        layout = new app.view.layouts.ForecastsWizardConfigLayout(options);
    });

    afterEach(function() {
        // restore the local stubs
        _.each(stubs, function(stub) {
            stub.restore();
        });
        layout = '';
    });

    describe("breadcrumb register", function() {

        it("should return an array containing passed in value", function() {
            layout.registerBreadCrumbLabel('Test1');
            expect(layout.getBreadCrumbLabels()).toContain('Test1');
        });

        it("should only contain contain 1 unique value", function() {
            layout.registerBreadCrumbLabel('Test1');
            layout.registerBreadCrumbLabel('Test1');
            expect(layout.getBreadCrumbLabels().length).toEqual(1);
            expect(layout.getBreadCrumbLabels()).toContain('Test1');
        })
    });
});