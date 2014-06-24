//FILE SUGARCRM flav=pro ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe("Forecasts.View.ForecastsConfigRanges", function() {
    var app, view, layout, context, testStub, cfgModel;

    beforeEach(function() {
        Handlebars.templates = {};
        app = SugarTest.app;
        SugarTest.testMetadata.init();

        cfgModel = new Backbone.Model();
        cfgModel.set({
            is_setup: 1,
            has_commits: 1,
            forecast_ranges: 'show_binary',
            commit_stages_included: ['include'],
            buckets_dom: 'commit_stage_dom',
            forecast_ranges: 'show_binary'
        })
        sinon.stub(app.metadata, "getModule", function() {
            return {
                is_setup: 1,
                has_commits: 1,
                forecast_ranges: 'show_binary',
                commit_stages_included: ['include'],
                buckets_dom: 'commit_stage_dom'
            };
        });
        context = app.context.getContext();
        context.set('module', 'Forecasts');
        context.set('model', cfgModel);

        var meta = {
                panels: [{
                    label: 'testLabel',
                    fields: {
                        forecast_ranges: {
                            name: 'forecast_ranges',
                            type: 'radioenum',
                            label: 'LBL_FORECASTS_CONFIG_RANGES_OPTIONS',
                            view: 'edit',
                            options: 'forecasts_config_ranges_options_dom',
                            'default': false,
                            enabled: true,
                            value: ''
                        },
                        category_ranges: {
                            name: 'category_ranges'
                        },
                        buckets_dom: {
                            name: 'buckets_dom',
                            options: {
                                show_binary: 'commit_stage_binary_dom',
                                show_buckets: 'commit_stage_dom'
                            },
                            value: ''
                        }
                    }
                }]
            };
        SugarTest.loadHandlebarsTemplate('forecastsConfigRanges', 'view', 'base', null, 'Forecasts');
        SugarTest.loadComponent('base', 'view', 'forecastsConfigRanges', 'Forecasts');
        SugarTest.testMetadata.set();
        layout = SugarTest.createLayout("base", 'Forecasts', "config-main", null, context, true);
        view = SugarTest.createView('base', 'Forecasts', 'forecastsConfigRanges', meta, context, true, layout, true);
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
        expect(view.forecastRangesField).toBeDefined();
    });

    it("should have a bucketsDomField parameter to hold the metadata for the field", function() {
        expect(view.bucketsDomField).toBeDefined();
    });

    it("should have a categoryRangesField parameter to hold the metadata for the field", function() {
        expect(view.categoryRangesField).toBeDefined();
    });

    it("should have a parameter to keep track of the selection between selection changes", function() {
        expect(view.selectedRange).toBeDefined();
    });

    describe("view parameters", function() {

        beforeEach(function() {
            testStub = sinon.stub(app.view.View.prototype, "initialize");
        });

        afterEach(function() {
            testStub.restore();
        });

        it("for label should get initialized to the label string in metadata", function() {
            var options = {
                meta: view.meta
            };
            view.initialize(options);
            expect(testStub).toHaveBeenCalled();
            expect(view.label).toEqual(_.first(view.meta.panels).label);
        });

        it("for fields should get initialized to the field metadata they correspond to", function() {
            var options = {
                    meta: view.meta
                },
                fieldMeta = _.first(view.meta.panels).fields;

            view.initialize(options);
            expect(testStub).toHaveBeenCalled();
            expect(view.forecastRangesField).toEqual(fieldMeta.forecast_ranges);
            expect(view.categoryRangesField).toEqual(fieldMeta.category_ranges);
            expect(view.bucketsDomField).toEqual(fieldMeta.buckets_dom);
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

            describe("forecastRangesField", function() {
                it("should be defined", function() {
                    view.initialize({ meta: view.meta});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecastRangesField.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta: view.meta });
                    expect(testStub).toHaveBeenCalled();
                    expect(view.forecastRangesField.value).toEqual('test_ranges');
                });
            });

            describe("bucketDomField", function() {
                it("should be defined", function() {
                    view.initialize({ meta: view.meta});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.bucketsDomField.value).toBeDefined();
                });

                it("should be set to what is in the model during initialize", function() {
                    view.initialize({ meta: view.meta});
                    expect(testStub).toHaveBeenCalled();
                    expect(view.bucketsDomField.value).toEqual('test_ranges_dom');
                });
            });
        });
    });

    describe("the forecast_ranges radios", function() {
        beforeEach(function() {
            testStub = sinon.stub(app.view.View.prototype, "_render");
        });

        afterEach(function() {
            testStub.restore();
        });

        it("should have a handler to do the necessary actions when a bucket type is selected", function() {
            SugarTest.testMetadata.init();
            SugarTest.loadHandlebarsTemplate('forecastsConfigHelpers', 'view', 'base', 'toggleTitle', 'Forecasts');
            SugarTest.testMetadata.set();
            view.$el = {
                addClass: function() {},
                off: function() {},
                remove: function() {},
                find: function() {
                    return {
                        children: function() {
                            return [];
                        },
                        toggleClass: function() {
                        },
                        html: function() {}
                    }
                }
            };
            view.$ = function() {
                return {
                    trigger: function() {},
                    html: function() {}
                };
            };
            view.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');

            view._render();
            expect(testStub).toHaveBeenCalled();

            app.cache.cutAll();
            app.view.reset();
            Handlebars.templates = {};
        });

    });

    describe("test call selectionHandler with different types", function() {
        var expectedValResponse, valStub;
        beforeEach(function() {
            initializeStub = sinon.stub(app.view.View.prototype, "initialize");
            _selectionHandlerStub = sinon.stub(view, "_selectionHandler");
            _customSelectionHandlerStub = sinon.stub(view, "_customSelectionHandler");
            connectSliders = sinon.stub(view, "connectSliders");

            expectedValResponse = '';
            valStub = sinon.stub($.fn, 'val', function() {
                return expectedValResponse;
            });

            // stub view.$el
            view.$el = {
                addClass: function() {},
                off: function() {},
                remove: function() {},
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
            view.$ = function() {
                return {
                    children: function() {
                        return [];
                    },
                    html: function() {},
                    toggleClass: function() {}
                };
            };
        });

        afterEach(function() {
            initializeStub.restore();
            _selectionHandlerStub.restore();
            _customSelectionHandlerStub.restore();
            connectSliders.restore();
            valStub.restore();
        });

        it("call selectionHandler for show_binary", function() {
            view.initialize({ meta: view.meta });

            expectedValResponse = 'show_binary';
            view.selectionHandler({});
            expect(_selectionHandlerStub).toHaveBeenCalled(' -- _selectionHandlerStub');
            expect(_customSelectionHandlerStub).not.toHaveBeenCalled(' -- _customSelectionHandlerStub');
        });

        it("call selectionHandler for show_buckets", function() {
            view.initialize({ meta: view.meta });

            expectedValResponse = 'show_buckets';
            view.selectionHandler({});
            expect(_selectionHandlerStub).toHaveBeenCalled(' -- _selectionHandlerStub');
            expect(_customSelectionHandlerStub).not.toHaveBeenCalled(' -- _customSelectionHandlerStub');
        });

        it("call customSelectionHandler for show_custom_buckets", function() {
            view.initialize({ meta: view.meta });

            expectedValResponse = 'show_custom_buckets';
            view.selectionHandler({});
            expect(_customSelectionHandlerStub).toHaveBeenCalled(' -- _customSelectionHandlerStub');
            expect(_selectionHandlerStub).not.toHaveBeenCalled(' -- _selectionHandlerStub');
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
                include: {
                    min: 85,
                    max: 100
                },
                upside: {
                    min: 70,
                    max: 84
                },
                exclude: {
                    min: 0,
                    max: 69
                }
            };

            options = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude'
            };

            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    label: 'include',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>']
                },
                upside: {
                    name: 'upside',
                    label: 'upside',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>']
                },
                exclude: {
                    name: 'exclude',
                    label: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0,
                    $el: ['<p></p>'],
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {}
                        }
                    }
                }
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
                    return {
                        name: key,
                        label: key,
                        customType: customType,
                        customIndex: customIndex,
                        $: function() {
                            var $el = $('<p/>');
                            $el.on = $.noop;
                            $el.trigger = $.noop;
                            $el.html = $.noop;
                            return $el;
                        }
                    };
                }
                return null;
            });
            connectSlidersStub = sinon.stub(view, "connectSliders");

            // stub view.$el
            view.$el = {
                addClass: function() {},
                off: function() {},
                remove: function() {},
                find: function() {
                    return {
                        noUiSlider: function() {},
                        hide: function() {}
                    };
                }
            };
            view.$ = function() {
                return {
                    html: function() {},
                    hide: function() {}
                };
            };
            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: options
            });

            $.fn.noUiSlider = function() {}
        });

        afterEach(function() {
            _renderCustomRangeStub.restore();
            connectSlidersStub.restore();
            delete $.fn.noUiSlider;
        });

        it("test addCustomRange method - add custom field with probability", function() {
            _.each(['custom_1', 'custom_2'], function(name) {
                view.addCustomRange({
                    currentTarget: '<a class="btn addCustomRange" href="javascript:void(0)" data-type="custom" data-category="show_custom_buckets">'
                });

                var bucketOptions = view.model.get('show_custom_buckets_options'),
                    bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketRanges[name].max).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].min).not.toBeUndefined(' -- bucketRanges[' + name + '].min');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                if(name == 'custom_1') {
                    expect(bucketRanges[name].max).toBe(69);
                    expect(bucketRanges[name].min).toBe(68);
                } else {
                    expect(bucketRanges[name].max).toBe(67);
                    expect(bucketRanges[name].min).toBe(66);
                }
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });

        it("test addCustomRange method - add custom field without probability", function() {
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.addCustomRange({
                    currentTarget: '<a class="btn addCustomRange" href="javascript:void(0)" data-type="custom_without_probability" data-category="show_custom_buckets">'
                });

                var bucketOptions = view.model.get('show_custom_buckets_options'),
                    bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketRanges[name].max).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].min).not.toBeUndefined(' -- bucketRanges[' + name + '].max');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].max).toBe(0);
                expect(bucketRanges[name].min).toBe(0);
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });

        it('adds accessibility for events', function() {
            var stubAccessibilityRun = sinon.stub(app.accessibility, 'run'),
                target = '<a class="btn addCustomRange" href="javascript:void(0);" '
                    + 'data-type="custom_without_probability" data-category="show_custom_buckets">';
            view.addCustomRange({currentTarget: target});
            expect(app.accessibility.run).toHaveBeenCalled();
            stubAccessibilityRun.restore();
        });
    });

    describe("test removeCustomRange method", function() {
        var options,
            ranges;

        beforeEach(function() {
            ranges = {
                include: {
                    min: 85,
                    max: 100
                },
                upside: {
                    min: 70,
                    max: 84
                },
                custom_1: {
                    min: 68,
                    max: 69
                },
                custom_2: {
                    min: 66,
                    max: 67
                },
                exclude: {
                    min: 0,
                    max: 65
                },
                custom_without_probability_1: {
                    min: 0,
                    max: 0
                },
                custom_without_probability_2: {
                    min: 0,
                    max: 0
                }
            };

            options = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude',
                custom_1: 'custom_1',
                custom_2: 'custom_2',
                custom_without_probability_1: 'custom_without_probability_1',
                custom_without_probability_2: 'custom_without_probability_2'
            };

            // each item of view.fieldRanges must be View.field objectm in this case stub remove method of View.field
            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                upside: {
                    name: 'upside',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                custom_1: {
                    name: 'custom_1',
                    customType: 'custom',
                    customIndex: 1,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                custom_2: {
                    name: 'custom_2',
                    customType: 'custom',
                    customIndex: 2,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                exclude: {
                    name: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0,
                    remove: function() {}
                },
                custom_without_probability_1: {
                    name: 'custom_without_probability_1',
                    customType: 'custom_without_probability',
                    customIndex: 1,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                },
                custom_without_probability_2: {
                    name: 'custom_without_probability_2',
                    customType: 'custom_without_probability',
                    customIndex: 2,
                    remove: function() {},
                    $: function() {
                        return {
                            noUiSlider: function() {},
                            trigger: function() {},
                            html: function() {},
                            off: function() {}
                        }
                    }
                }
            };

            connectSlidersStub = sinon.stub(view, "connectSliders");
            // stub view.$el
            view.$el = {
                addClass: function() {},
                off: function() {},
                remove: function() {},
                find: function(key) {
                    return {
                        noUiSlider: function() {
                        },
                        show: function() {
                        }
                    }
                }
            };
            view.$ = function() {
                return {
                    html: function() {},
                    show: function() {}
                };
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
                result = view.removeCustomRange({
                    currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="'+name+'" data-category="show_custom_buckets">'
                });
                expect(result).toBeFalsy();
            });
        });

        it("test removeCustomRange method - remove custom field with probability", function() {
            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_1" data-category="show_custom_buckets">'
            });

            var bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_1']).toBeUndefined(' -- custom_1');
            expect(view.fieldRanges['show_custom_buckets']['custom_2']).not.toBeUndefined(' -- custom_2');
            expect(bucketRanges.custom_1).toBeUndefined(' -- bucketRanges.custom_1');
            expect(bucketRanges.custom_2).not.toBeUndefined(' -- bucketRanges.custom_2');

            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_2" data-category="show_custom_buckets">'
            });

            bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_2']).toBeUndefined();
            expect(bucketRanges.custom_2).toBeUndefined(' -- custom_2');
        });

        it("test removeCustomRange method - remove custom field without probability", function() {
            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_without_probability_1" data-category="show_custom_buckets">'
            });

            var bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_1']).toBeUndefined(' -- bucketRanges.custom_without_probability_1');
            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2']).not.toBeUndefined(' -- bucketRanges.custom_without_probability_2');
            expect(bucketRanges.custom_without_probability_1).toBeUndefined();
            expect(bucketRanges.custom_without_probability_2).not.toBeUndefined();

            view.removeCustomRange({
                currentTarget: '<a class="btn removeCustomRange" href="javascript:void(0)" data-key="custom_without_probability_2" data-category="show_custom_buckets">'
            });

            bucketRanges = view.model.get('show_custom_buckets_ranges');

            expect(view.fieldRanges['show_custom_buckets']['custom_without_probability_2']).toBeUndefined();
            expect(bucketRanges.custom_without_probability_2).toBeUndefined(' -- bucketRanges.custom_without_probability_2');
        });
    });

    describe("test updateCustomRangeLabel method", function() {
        var options,
            ranges,
            new_name;

        beforeEach(function() {
            options = {
                include: 'include',
                upside: 'upside',
                exclude: 'exclude',
                custom_1: 'custom_1',
                custom_2: 'custom_2',
                custom_without_probability_1: 'custom_without_probability_1',
                custom_without_probability_2: 'custom_without_probability_2'
            };

            view.fieldRanges['show_custom_buckets'] = {
                include: {
                    name: 'include',
                    customType: 'custom_default',
                    customIndex: 0
                },
                upside: {
                    name: 'upside',
                    customType: 'custom_default',
                    customIndex: 0
                },
                custom_1: {
                    name: 'custom_1',
                    customType: 'custom',
                    customIndex: 1
                },
                custom_2: {
                    name: 'custom_2',
                    customType: 'custom',
                    customIndex: 2
                },
                exclude: {
                    name: 'exclude',
                    customType: 'custom_default',
                    customIndex: 0
                },
                custom_without_probability_1: {
                    name: 'custom_without_probability_1',
                    customType: 'custom_without_probability',
                    customIndex: 1
                },
                custom_without_probability_2: {
                    name: 'custom_without_probability_2',
                    customType: 'custom_without_probability',
                    customIndex: 2
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                show_custom_buckets_options: options
            });
        });

        it("test updateCustomRangeLabel method - rename default custom field", function() {
            _.each(['include', 'upside', 'exclude'], function(name) {
                new_name = name + '_CUSTOM';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(new_name);
            });
        });

        it("test updateCustomRangeLabel method - rename custom field with probability", function() {
            _.each(['custom_1', 'custom_2'], function(name) {
                new_name = name + '_CUSTOM';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(new_name);
            });
        });

        it("test updateCustomRangeLabel method - rename custom field without probability", function() {
            _.each(['custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                new_name = name + '_CUSTOM_WITHOUT_PR';
                view.updateCustomRangeLabel({ target: '<input type="text" value="'+new_name+'" data-key="'+name+'" data-category="show_custom_buckets">'});

                var bucketOptions = view.model.get('show_custom_buckets_options');

                expect(bucketOptions).not.toBeUndefined(' -- bucketOptions');
                expect(bucketOptions[name]).not.toBeUndefined(' -- bucketOptions[' + name + ']');
                expect(bucketOptions[name]).toBe(new_name);
            });
        });
    });

    describe("test updateCustomRangeIncludeInTotal method", function() {
        var ranges,
            bucketRanges;

        beforeEach(function() {
            ranges = {
                include: {
                    min: 85,
                    max: 100,
                    in_included_total: true
                },
                upside: {
                    min: 70,
                    max: 84,
                    in_included_total:false
                },
                custom_1: {
                    min: 68,
                    max: 69,
                    in_included_total:false
                },
                custom_2: {
                    min: 66,
                    max: 67,
                    in_included_total:false
                },
                exclude: {
                    min: 0,
                    max: 65,
                    in_included_total:false
                },
                custom_without_probability_1: {
                    min: 0,
                    max: 0,
                    in_included_total:false
                },
                custom_without_probability_2: {
                    min: 0,
                    max: 0,
                    in_included_total:false
                }
            };

            view.model.set({
                show_custom_buckets_ranges: ranges,
                commit_stages_included: ['include']
            });
        });

        it("test updateCustomRangeIncludeInTotal method", function() {
            _.each(['include', 'upside', 'custom_1', 'custom_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="'+name+'" data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].in_included_total).toBe(true);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="'+name+'" data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].in_included_total).toBe(false);
            });

            _.each(['exclude', 'custom_without_probability_1', 'custom_without_probability_2'], function(name) {
                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" checked="true" data-key="'+name+'" data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].in_included_total).toBe(false);

                view.updateCustomRangeIncludeInTotal({
                    target: '<input type="checkbox" value="1" data-key="'+name+'" data-category="show_custom_buckets">'
                });

                bucketRanges = view.model.get('show_custom_buckets_ranges');

                expect(bucketRanges[name]).not.toBeUndefined(' -- bucketRanges[' + name + ']');
                expect(bucketRanges[name].in_included_total).not.toBeUndefined(' -- bucketRanges[' + name + '].in_included_total');
                expect(bucketRanges[name].in_included_total).toBe(false);
            });
        });
    });
});
