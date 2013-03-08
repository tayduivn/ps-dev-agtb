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

describe("forecasts_view_forecastsConfigRanges", function() {
    var app, view, testStub, addHandlerStub;

    beforeEach(function() {
        app = SugarTest.app;
        sinon.stub(app.metadata, "getModule", function() {
            return {
                has_commits: 1,
                forecast_ranges: 'show_binary'
            };
        });
        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigRanges", "forecastsConfigRanges", "js", function(d) {
            return eval(d);
        });
        view.model = new Backbone.Model();
    });

    afterEach(function() {
        app.metadata.getModule.restore();
        view = null;
        app = null;
    });

    it("should have a label parameter to hold the label from metadata for the template", function() {
        expect(view.label).toBeDefined();
    });

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
                registerBreadCrumbLabel: function() {
                }
            };
            view.meta = {
                panels: [
                    {
                        label: 'testLabel',
                        fields: [
                            {
                                name: 'forecast_ranges',
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
        });

        afterEach(function() {
            testStub.restore();
        });

        it("for label should get initialized to the label string in metadata", function() {
            var options = {
                meta: []
            };
            view.initialize(options);
            expect(testStub).toHaveBeenCalled();
            expect(view.label).toEqual(_.first(view.meta.panels).label);
        });

        it("for fields should get initialized to the field metadata they correspond to", function() {
            var options = {
                    meta: []
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
                view.model.set({
                    forecast_ranges: 'test_ranges',
                    buckets_dom: 'test_ranges_dom'
                });
                // Restore stub to set it custom for these tests
                app.metadata.getModule.restore();
                sinon.stub(app.metadata, "getModule", function() {
                    return {
                        has_commits: 1,
                        forecast_ranges: 'test_ranges',
                        buckets_dom: 'test_ranges_dom'
                    };
                });
            });

            afterEach(function() {
            });

            describe("forecast_ranges_field", function() {
                it("should be defined", function() {
                    view.initialize({ meta: []});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecast_ranges_field.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta: [] });
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecast_ranges_field.value).toEqual('test_ranges');
                });
            });

            describe("bucket_dom_field", function() {
                it("should be defined", function() {
                    view.initialize({ meta: []});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.buckets_dom_field.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta: [] });
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

    describe("test call selectionHandler whit different types", function() {
        beforeEach(function() {
            initializeStub = sinon.stub(app.view.View.prototype, "initialize");
            _selectionHandlerStub = sinon.stub(view, "_selectionHandler");
            _customSelectionHandlerStub = sinon.stub(view, "_customSelectionHandler");
            connectSliders = sinon.stub(view, "connectSliders");

            view.layout = {
                registerBreadCrumbLabel: function() {
                }
            };
            view.meta = {
                panels: [
                    {
                        label: 'testLabel',
                        fields: [
                            {
                                name: 'forecast_ranges',
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
                                    show_buckets: 'commit_stage_dom',
                                    show_custom_buckets: 'commit_stage_custom_dom'
                                },
                                value: ''
                            }
                        ]
                    }
                ]
            };

            // stub view.$el
            view.$el = {
                find: function() {
                    return {
                        children: function() {
                            return [];
                        },
                        toggleClass: function() {
                        }
                    }
                }
            };
        });

        afterEach(function() {
            initializeStub.restore();
            _selectionHandlerStub.restore();
            _customSelectionHandlerStub.restore();
            connectSliders.restore();
        });

        it("call selectionHandler for show_binary", function() {
            view.initialize({ meta: [] });

            view.value = 'show_binary';
            view.selectionHandler({ data: { view: view } });
            expect(_selectionHandlerStub).toHaveBeenCalled();
            expect(_customSelectionHandlerStub).not.toHaveBeenCalled();
        });

        it("call selectionHandler for show_buckets", function() {
            view.initialize({ meta: [] });

            view.value = 'show_buckets';
            view.selectionHandler({ data: { view: view } });
            expect(_selectionHandlerStub).toHaveBeenCalled();
            expect(_customSelectionHandlerStub).not.toHaveBeenCalled();
        });

        it("call customSlectionHandler for show_custom_buckets", function() {
            view.initialize({ meta: [] });

            view.value = 'show_custom_buckets';
            view.selectionHandler({ data: { view: view } });
            expect(_customSelectionHandlerStub).toHaveBeenCalled();
            expect(_selectionHandlerStub).not.toHaveBeenCalled();
        });
    });

    describe("test _getLastCustomRangeIndex method", function() {
        var lastIndex;

        beforeEach(function() {
            view.fieldRanges['show_custom_buckets'] = {
                include: { customType: 'custom_default', customIndex: 0 },
                upside: { customType: 'custom_default', customIndex: 0 },
                exclude: { customType: 'custom_default', customIndex: 0 }
            }
        });

        afterEach(function() {
        });

        it("test _getLastCustomRangeIndex method - default set", function() {
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(0);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(0);
        });

        it("test _getLastCustomRangeIndex method - case 1", function() {
            view.fieldRanges['show_custom_buckets']['custom_1'] = {
                customType: 'custom', customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_1'] = {
                customType: 'custom_without_probability', customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_2'] = {
                customType: 'custom_without_probability', customIndex: 2
            };
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(1);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(2);
        });

        it("test _getLastCustomRangeIndex method - case 2", function() {
            view.fieldRanges['show_custom_buckets']['custom_10'] = {
                customType: 'custom', customIndex: 10
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_21'] = {
                customType: 'custom_without_probability', customIndex: 21
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_11'] = {
                customType: 'custom_without_probability', customIndex: 11
            };
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom');
            expect(lastIndex).toBe(10);
            lastIndex = view._getLastCustomRangeIndex('show_custom_buckets', 'custom_without_probability');
            expect(lastIndex).toBe(21);
        });
    });

    describe("test _getLastCustomRange method", function() {
        var lastRange;

        beforeEach(function() {
            view.fieldRanges['show_custom_buckets'] = {
                include: { label: 'include', customType: 'custom_default', customIndex: 0 },
                upside: { label: 'upside', customType: 'custom_default', customIndex: 0 },
                exclude: { label: 'exclude', customType: 'custom_default', customIndex: 0 }
            }
        });

        afterEach(function() {
        });

        it("test _getLastCustomRange method - default set", function() {
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('upside');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('exclude');
        });

        it("test _getLastCustomRange method - case 1", function() {
            view.fieldRanges['show_custom_buckets']['custom_1'] = {
                label: 'custom_1', customType: 'custom', customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_1'] = {
                label: 'custom_without_probability_1', customType: 'custom_without_probability', customIndex: 1
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_2'] = {
                label: 'custom_without_probability_2', customType: 'custom_without_probability', customIndex: 2
            };
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_1');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_without_probability_2');
        });

        it("test _getLastCustomRange method - case 2", function() {
            view.fieldRanges['show_custom_buckets']['custom_10'] = {
                label: 'custom_10', customType: 'custom', customIndex: 10
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_21'] = {
                label: 'custom_without_probability_21', customType: 'custom_without_probability', customIndex: 21
            };
            view.fieldRanges['show_custom_buckets']['custom_without_probability_11'] = {
                label: 'custom_without_probability_11', customType: 'custom_without_probability', customIndex: 11
            };
            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_10');

            lastRange = view._getLastCustomRange('show_custom_buckets', 'custom_without_probability');
            expect(lastRange).not.toBeUndefined();
            expect(lastRange).not.toBeNull();
            expect(lastRange.label).not.toBeUndefined();
            expect(lastRange.label).not.toBeNull();
            expect(lastRange.label).toBe('custom_without_probability_21');
        });
    });

    describe("test addCustomRange method", function() {
        var options,
            ranges;

        beforeEach(function() {
            ranges = {
                include: {min: 85, max: 100},
                upside: {min: 70, max: 84},
                exclude: {min: 0, max: 69}
            };

            options = [];
            options.push(['include', 'include']);
            options.push(['upside', 'upside']);
            options.push(['exclude', 'exclude']);

            view.fieldRanges['show_custom_buckets'] = {
                include: { name: 'include', label: 'include', customType: 'custom_default', customIndex: 0 },
                upside: { name: 'upside', label: 'upside', customType: 'custom_default', customIndex: 0 },
                exclude: { name: 'exclude', label: 'exclude', customType: 'custom_default', customIndex: 0 }
            };
            // stub method _renderCustomRange, the method _renderCustomRange should return new created field
            // return stub object to add to view.fieldRanges
            _renderCustomRangeStub = sinon.stub(view, "_renderCustomRange", function(key) {
                var customType, customIndex;
                if(key.substring(0, 26) == 'custom_without_probability') {
                    customType = 'custom_without_probability';
                    customIndex = key.substring(27);
                } else if(key.substring(0, 6) == 'custom') {
                    customType = 'custom';
                    customIndex = key.substring(7);
                }
                if(customType) {
                    return { name: key, label: key, customType: customType, customIndex: customIndex };
                }
                return null;
            });
            connectSlidersStub = sinon.stub(view, "connectSliders");
            // stub view.$el
            view.$el = {
                find: function() {
                    return {
                        noUiSlider: function() {
                        },
                        hide: function() {
                        }
                    }
                }
            };
            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: options
            });
        });
        afterEach(function() {
            _renderCustomRangeStub.restore();
            connectSlidersStub.restore();
        });
        it("test addCustomRange method - add custom field with probability", function() {
            var options_item;
            _.each(['custom_1', 'custom_2'], function(name) {
                view.addCustomRange({ handleObj: {
                    selector: '<a class="btn addCustomRange" href="javascript:void(0)" data-type="custom" data-category="show_custom_buckets">'
                }});
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].max).not.toBeUndefined();
                expect(ranges[name].min).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                if(name == 'custom_1') {
                    expect(ranges[name].max).toBe(69);
                    expect(ranges[name].min).toBe(68);
                } else {
                    expect(ranges[name].max).toBe(67);
                    expect(ranges[name].min).toBe(66);
                }
                expect(ranges[name].in_included_total).toBe(false);
                options_item = _.filter(options, function(item) {
                    return item[0] == this.name
                }, {name: name});
                expect(options_item).not.toBeUndefined();
                expect(options_item.length).toBe(1);
            });
        });
        it("test addCustomRange method - add custom field without probability", function() {
            var options_item;
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.addCustomRange({ handleObj: {
                    selector: '<a class="btn addCustomRange" href="javascript:void(0)" data-type="custom_without_probability" data-category="show_custom_buckets">'
                }});
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].max).not.toBeUndefined();
                expect(ranges[name].min).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                expect(ranges[name].max).toBe(0);
                expect(ranges[name].min).toBe(0);
                expect(ranges[name].in_included_total).toBe(false);
                options_item = _.filter(options, function(item) {
                    return item[0] == this.name
                }, {name: name});
                expect(options_item).not.toBeUndefined();
                expect(options_item.length).toBe(1);
            });
        });
    });

    describe("test removeCustomRange method", function() {
        var options,
            ranges;

        beforeEach(function() {
            ranges = {
                include: {min: 85, max: 100},
                upside: {min: 70, max: 84},
                custom_1: {min: 68, max: 69},
                custom_2: {min: 66, max: 67},
                exclude: {min: 0, max: 65},
                custom_without_probability_1: {min: 0, max: 0},
                custom_without_probability_2: {min: 0, max: 0}
            };

            options = [];
            options.push(['include', 'include']);
            options.push(['upside', 'upside']);
            options.push(['exclude', 'exclude']);
            options.push(['custom_1', 'custom_1']);
            options.push(['custom_2', 'custom_2']);
            options.push(['custom_without_probability_1', 'custom_without_probability_1']);
            options.push(['custom_without_probability_2', 'custom_without_probability_2']);

            // each item of view.fieldRanges must be View.field objectm in this case stub remove method of View.field
            view.fieldRanges['show_custom_buckets'] = {
                include: { name: 'include', customType: 'custom_default', customIndex: 0, remove: function() {
                } },
                upside: { name: 'upside', customType: 'custom_default', customIndex: 0, remove: function() {
                } },
                custom_1: { name: 'custom_1', customType: 'custom', customIndex: 1, remove: function() {
                } },
                custom_2: { name: 'custom_2', customType: 'custom', customIndex: 2, remove: function() {
                } },
                exclude: { name: 'exclude', customType: 'custom_default', customIndex: 0, remove: function() {
                } },
                custom_without_probability_1: { name: 'custom_without_probability_1', customType: 'custom_without_probability', customIndex: 1, remove: function() {
                } },
                custom_without_probability_2: { name: 'custom_without_probability_2', customType: 'custom_without_probability', customIndex: 2, remove: function() {
                } }
            };
            connectSlidersStub = sinon.stub(view, "connectSliders");
            // stub view.$el
            view.$el = {
                find: function(key) {
                    return {
                        noUiSlider: function() {
                        },
                        show: function() {
                        }
                    }
                }
            };
            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: options
            });
        });
        afterEach(function() {
            connectSlidersStub.restore();
        });
        it("test removeCustomRange method - remove default custom field", function() {
            var result;
            _.each(['include', 'upside', 'exclude'], function(name) {
                result = view.removeCustomRange({ handleObj: {
                    selector: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="'+name+'" data-category="show_custom_buckets">'
                }});
                expect(result).toBeFalsy();
            });
        });
        it("test removeCustomRange method - remove custom field with probability", function() {
            var options_item;
            view.removeCustomRange({ handleObj: {
                selector: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_1" data-category="show_custom_buckets">'
            }});
            expect(view.fieldRanges['show_custom_buckets']['custom_1']).toBeUndefined();
            expect(view.fieldRanges['show_custom_buckets']['custom_2']).not.toBeUndefined();
            expect(ranges.custom_1).toBeUndefined();
            expect(ranges.custom_2).not.toBeUndefined();
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_1'});
            expect(options_item.length).toBe(0);
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_2'});
            expect(options_item).not.toBeUndefined();
            expect(options_item.length).toBe(1);

            view.removeCustomRange({ handleObj: {
                selector: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_2" data-category="show_custom_buckets">'
            }});
            expect(view.fieldRanges['show_custom_buckets']['custom_2']).toBeUndefined();
            expect(ranges.custom_2).toBeUndefined();
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_2'});
            expect(options_item.length).toBe(0);
        });
        it("test removeCustomRange method - remove custom field without probability", function() {
            var options_item;
            view.removeCustomRange({ handleObj: {
                selector: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_without_probability_1" data-category="show_custom_buckets">'
            }});
            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_1']).toBeUndefined();
            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2']).not.toBeUndefined();
            expect(ranges.custom_without_probability_1).toBeUndefined();
            expect(ranges.custom_without_probability_2).not.toBeUndefined();
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_without_probability_1'});
            expect(options_item.length).toBe(0);
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_without_probability_2'});
            expect(options_item).not.toBeUndefined();
            expect(options_item.length).toBe(1);

            view.removeCustomRange({ handleObj: {
                selector: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_without_probability_2" data-category="show_custom_buckets">'
            }});
            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2']).toBeUndefined();
            expect(ranges.custom_without_probability_2).toBeUndefined();
            options_item = _.filter(options, function(item) {
                return item[0] == this.name
            }, {name: 'custom_without_probability_2'});
            expect(options_item.length).toBe(0);
        });
    });

    describe("test updateCustomRangeLabel method", function() {
        var options,
            ranges;

        beforeEach(function() {
            options = [];
            options.push(['include', 'include']);
            options.push(['upside', 'upside']);
            options.push(['exclude', 'exclude']);
            options.push(['custom_1', 'custom_1']);
            options.push(['custom_2', 'custom_2']);
            options.push(['custom_without_probability_1', 'custom_without_probability_1']);
            options.push(['custom_without_probability_2', 'custom_without_probability_2']);

            view.fieldRanges['show_custom_buckets'] = {
                include: { name: 'include', customType: 'custom_default', customIndex: 0 },
                upside: { name: 'upside', customType: 'custom_default', customIndex: 0 },
                custom_1: { name: 'custom_1', customType: 'custom', customIndex: 1 },
                custom_2: { name: 'custom_2', customType: 'custom', customIndex: 2 },
                exclude: { name: 'exclude', customType: 'custom_default', customIndex: 0 },
                custom_without_probability_1: { name: 'custom_without_probability_1', customType: 'custom_without_probability', customIndex: 1 },
                custom_without_probability_2: { name: 'custom_without_probability_2', customType: 'custom_without_probability', customIndex: 2 }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: options
            });
        });
        afterEach(function() {
        });
        it("test updateCustomRangeLabel method - rename default custom field", function() {
            var options_item,
                new_name;
            _.each(['include', 'upside', 'exclude'], function(name) {
                new_name = name + '_CUSTOM';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});
                options_item = _.filter(options, function(item) {
                    return item[0] == this.name
                }, {name: name});
                expect(options_item).not.toBeUndefined();
                expect(options_item.length).toBe(1);
                expect(options_item[0][1]).toBe(new_name);
                expect(options_item[0][0]).toBe(name);
            });
        });
        it("test updateCustomRangeLabel method - rename custom field with probability", function() {
            var options_item,
                new_name;
            _.each(['custom_1', 'custom_2'], function(name) {
                new_name = name + '_CUSTOM';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});
                options_item = _.filter(options, function(item) {
                    return item[0] == this.name
                }, {name: name});
                expect(options_item).not.toBeUndefined();
                expect(options_item.length).toBe(1);
                expect(options_item[0][1]).toBe(new_name);
                expect(options_item[0][0]).toBe(name);
            });
        });
        it("test updateCustomRangeLabel method - rename custom field without probability", function() {
            var options_item,
                new_name;
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                new_name = name + '_CUSTOM_WITHOUT_PR';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});
                options_item = _.filter(options, function(item) {
                    return item[0] == this.name
                }, {name: name});
                expect(options_item).not.toBeUndefined();
                expect(options_item.length).toBe(1);
                expect(options_item[0][1]).toBe(new_name);
                expect(options_item[0][0]).toBe(name);
            });
        });
    });

    describe("test updateCustomRangeIncludeInTotal method", function() {
        var ranges;

        beforeEach(function() {
            ranges = {
                include: {min: 85, max: 100, in_included_total: true},
                upside: {min: 70, max: 84, in_included_total:false},
                custom_1: {min: 68, max: 69, in_included_total:false},
                custom_2: {min: 66, max: 67, in_included_total:false},
                exclude: {min: 0, max: 65, in_included_total:false},
                custom_without_probability_1: {min: 0, max: 0, in_included_total:false},
                custom_without_probability_2: {min: 0, max: 0, in_included_total:false}
            };
            // stub view.model
            view.model = {
                get: function(key) {
                    if(key == 'show_custom_buckets_ranges') {
                        return ranges;
                    }
                },
                set: function(key, value) {
                    if(key == 'show_custom_buckets_ranges') {
                        ranges = value;
                    }
                },
                unset: function(key) {
                    if(key == 'show_custom_buckets_ranges') {
                        ranges = null;
                    }
                }
            };
        });
        afterEach(function() {
        });
        it("test updateCustomRangeIncludeInTotal method", function() {
            _.each(['include', 'upside', 'custom_1', 'custom_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="'+name+'" data-category="show_custom_buckets">'
                });
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                expect(ranges[name].in_included_total).toBe(true);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="'+name+'" data-category="show_custom_buckets">'
                });
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                expect(ranges[name].in_included_total).toBe(false);
            });
            _.each(['exclude', 'custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="'+name+'" data-category="show_custom_buckets">'
                });
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                expect(ranges[name].in_included_total).toBe(false);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="'+name+'" data-category="show_custom_buckets">'
                });
                expect(ranges[name]).not.toBeUndefined();
                expect(ranges[name].in_included_total).not.toBeUndefined();
                expect(ranges[name].in_included_total).toBe(false);
            });
        });
    });
});
