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

describe("forecasts_layout_config", function(){
    var app, layout, stubs, indexLayout;

    beforeEach(function() {
        var options = {
            context: new Backbone.Model()
        };

        options.context.config = new Backbone.Model();

        app = SugarTest.app;
        var configController = SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/config", "config", "js", function(d) {
                return eval(d);
            }),
            indexController = SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/records", "records", "js", function(d) {
                return eval(d);
            });
        stubs = [];
        SugarTest.loadFile("../modules/Forecasts/clients/base/layouts/records", "records", "js", function(d) {
                        return eval(d);
                    });
        app.viewModule = "";
        app.initData = {};
        app.defaultSelections = {
            timeperiod_id: {},
            group_by: {},
            ranges: {},
            dataset: {},
            selectedUser: {}
        };

        stubs.push(sinon.stub(app.metadata, "getLayout", function(){
            return {
                forecasts: {
                    meta: {
                        components: {}
                    }
                },
                componentsMeta: {}
            };
        }));
        stubs.push(sinon.stub(app.metadata, "getModule", function(){
            return {
                config : {
                }
            };
        }));
        stubs.push(sinon.stub(app.view.Layout.prototype, "initialize", function () {}));
        stubs.push(sinon.stub(app.api, "call"));
        indexLayout = SugarTest.createComponent('Layout', {
            name: "records",
            module: "Forecasts",
            context: options.context,
            meta : options.meta,
            controller: indexController
        });

        layout = SugarTest.createComponent('Layout', {
            name: "config",
            module: "Forecasts",
            context: options.context,
            meta : options.meta,
            controller: configController
        });

    });

    afterEach(function() {
        // restore the local stubs
        _.each(stubs, function(stub) {
            stub.restore();
        });
        layout = '';
        indexLayout = '';
    });

    describe("initialize", function () {
        var options, testLayout, baseIndexLayout;

        beforeEach(function() {
            options = {
                context: new Backbone.Model({}),
                meta: {
                    components: {}
                }
            };
            baseIndexLayout = new app.view.layouts.ForecastsRecordsLayout(options);
        });

        afterEach(function() {
            options = null;
            testLayout = null;
            baseIndexLayout = null;
        });

        it("should get a model", function() {
            testLayout = new app.view.layouts.ForecastsConfigLayout(options);
            var getModelStub = sinon.stub(testLayout, '_getConfigModel', function() {
                return {
                    fetch: function(){}
                }
            });

            delete options.context;
            testLayout.initialize(options);
            expect(getModelStub).toHaveBeenCalled();
            getModelStub.restore();
        });

        describe("model for config panel", function() {
            it("should be a new model if one does not exist", function () {
                testLayout = new app.view.layouts.ForecastsConfigLayout(options);
                var testModel = testLayout._getConfigModel(options, 'testUrl', function(){});
                expect(testModel).toBeDefined();
                expect(testModel.attributes).toEqual({});
            });

            it("should be a copy of the model if one exists, so a cancel will not keep values lying around", function() {
                options.context.config = new Backbone.Model({
                    defaults: {
                        test: 'test'
                    }
                });

                testLayout = new app.view.layouts.ForecastsConfigLayout(options);
                var testModel = testLayout._getConfigModel(options, 'testUrl', function(){});
                expect(testModel).not.toBe(options.context.config);
                expect(testModel.attributes).toEqual(options.context.config.attributes);
            });
        });
    });

    describe("getRedirectUrlScenarios", function() {
        var options, testLayout, baseIndexLayout, homeLocation, forecastsLocation, state;

        beforeEach(function() {
            options = {
                context: new Backbone.Model({}),
                meta: {
                    components: {}
                }
            };
            baseIndexLayout = new app.view.layouts.ForecastsRecordsLayout(options);
            testLayout = new app.view.layouts.ForecastsConfigLayout(options);
            homeLocation = '#Home';
            forecastsLocation = '#Forecasts';
        });

        afterEach(function() {
            options = null;
            testLayout = null;
            baseIndexLayout = null;
            homeLocation = null;
            forecastsLocation = null;
            state = null;
        });
       it("should go home if not admin", function() {
           state = {
               isSetup: false,
               isAdmin: false,
               saveClicked: false
           };
           var location = testLayout.getRedirectURL(state);
           expect(location).toBe(homeLocation);
       });

        it("should go home if admin, setup is false and saveClicked is false", function() {
            state = {
                isSetup: false,
                isAdmin: true,
                saveClicked: false
            };
            var location = testLayout.getRedirectURL(state);
            expect(location).toBe(homeLocation);
        });

        it("should go to Forecasts module if admin, isSetup is true and saveClicked is false", function() {
            state = {
                isSetup: true,
                isAdmin: true,
                saveClicked: false
            };
            var location = testLayout.getRedirectURL(state);
            expect(location).toBe(forecastsLocation);
        });

        it("should reload current forecasts module if admin, isSetup is true and saveClicked is true", function() {
            state = {
                isSetup: true,
                isAdmin: true,
                saveClicked: true
            };
            var location = testLayout.getRedirectURL(state);
            expect(location).toBe(forecastsLocation);
        });

        it("should reload current forecasts module if admin, isSetup is false and saveClicked is true", function() {
            state = {
                isSetup: false,
                isAdmin: true,
                saveClicked: true
            };
            var location = testLayout.getRedirectURL(state);
            expect(location).toBe(forecastsLocation);
        });
    });
});
