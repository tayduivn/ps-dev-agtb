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

describe("The forecastsConfigRanges view", function(){
    var app, view, testStub, addHandlerStub;

    beforeEach(function() {
        app = SugarTest.app;
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigRanges", "forecastsConfigRanges", "js", function(d) { return eval(d); });
        view.context = {};
        view.context.forecasts = {};
        view.context.forecasts.config = new (Backbone.Model.extend({
            "defaults": fixtures.metadata.modules.Forecasts.config
        }));
    });

    afterEach(function() {
        delete view;
        delete app;
    });

    it("should have a label parameter to hold the label from metadata for the template", function() {
        expect(view.label).toBeDefined();
    })

    it("should have a forecasts_ranges_field parameter to hold the metadata for the field", function() {
        expect(view.forecast_ranges_field).toBeDefined();
    });

    it("should have a buckets_dom_field parameter to hold the metadata for the field", function() {
        expect(view.buckets_dom_field).toBeDefined();
    });

    it("should have a category_ranges_field parameter to hold the metadata for the field", function() {
        expect(view.category_ranges_field).toBeDefined();
    });

    it("should have a parameter to keep track of the selection between selection changes", function() {
        expect(view.selection).toBeDefined();
    });

    describe("view parameters", function() {

        beforeEach(function() {
            testStub = sinon.stub(app.view.View.prototype, "initialize");
            view.layout = {
                registerBreadCrumbLabel : function(){}
            };
            view.meta = {
                panels : [
                    {
                        label: 'testLabel',
                        fields: [
                            {
                                name:'forecast_ranges',
                                type: 'radioenum',
                                label: 'LBL_FORECASTS_CONFIG_RANGES_OPTIONS',
                                view: 'edit',
                                options: 'forecasts_config_ranges_options_dom',
                                default: false,
                                enabled: true,
                                value: ''
                            },
                            {
                                name: 'category_ranges'
                            },
                            {
                                name: 'buckets_dom',
                                options: {
                                    show_binary: 'commit_stage_binary_dom',
                                    show_buckets: 'commit_stage_dom'
                                },
                                value: ''
                            }
                        ]
                    }
                ]
            };
            view.model = {
                get: function(key) {return ''}
            };
        });

        afterEach(function() {
            testStub.restore();
        });


        it("for label should get initialized to the label string in metadata", function() {
            var options = {
                meta : []
            };
            view.initialize(options);
            expect(testStub).toHaveBeenCalled();
            expect(view.label).toEqual(_.first(view.meta.panels).label);
        });

        it("for fields should get initialized to the field metadata they correspond to", function() {
            var options = {
                     meta : []
                },
                fieldMeta = _.first(view.meta.panels).fields;
            view.initialize(options);
            expect(testStub).toHaveBeenCalled();
            expect(view.forecast_ranges_field).toEqual(fieldMeta[0]);
            expect(view.category_ranges_field).toEqual(fieldMeta[1]);
            expect(view.buckets_dom_field).toEqual(fieldMeta[2]);
        });

        describe("initial value for", function() {

            beforeEach(function() {
                view.model = {
                    get: function(item) {
                        if (item == 'forecast_ranges') {
                            return 'test_ranges';
                        }
                        if (item == 'buckets_dom') {
                            return 'test_ranges_dom';
                        }
                    }
                };
            });

            afterEach(function() {
                delete view.model;
            });

            describe("forecast_ranges_field", function() {
                it("should be defined", function() {
                    view.initialize({ meta : []});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecast_ranges_field.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta : [] });
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecast_ranges_field.value).toEqual('test_ranges');
                });
            });

            describe("bucket_dom_field", function() {
                it("should be defined", function() {
                    view.initialize({ meta : []});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.buckets_dom_field.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta : [] });
                    expect(testStub).toHaveBeenCalled();
                    expect(view.buckets_dom_field.value).toEqual('test_ranges_dom');
                });
            });

        });

    });

    describe("the forecast_ranges radios", function() {
        beforeEach(function() {
            testStub = sinon.stub(app.view.View.prototype, "_render");
            addHandlerStub = sinon.stub(view, "_addForecastRangesSelectionHandler");
        });

        afterEach(function() {
            testStub.restore();
            addHandlerStub.restore();
        });

        it("should have a handler to do the necessary actions when a bucket type is selected", function() {
            view._render();
            expect(testStub).toHaveBeenCalled();
            expect(addHandlerStub).toHaveBeenCalled();
        });

    });
});