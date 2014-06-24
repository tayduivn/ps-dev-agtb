// todo-sfa - this will be revisited in a future release
// BEGIN SUGARCRM flav=int ONLY
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

describe("forecasts_view_forecastsConfigForecastBy", function() {
    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        sinon.stub(app.metadata, "getModule", function() {
            return {
                has_commits: 1,
                forecast_ranges: 'show_binary'
            };
        });

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('forecastsConfigHelpers', 'view', 'base', 'toggleTitle', 'Forecasts');
        SugarTest.testMetadata.set();

        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigForecastBy", "forecastsConfigForecastBy", "js", function(d) {
            return eval(d);
        });

        view.$el = {
            addClass: function() {},
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

        view.toggleTitleTpl = app.template.getView('forecastsConfigHelpers.toggleTitle', 'Forecasts');
        sinon.stub(app.view.View.prototype, "initialize");
        sinon.stub(app.lang, 'get', function() { return 'title' });
        sinon.stub(app.lang, 'getAppListStrings', function() { return {
            products: 'Revenue Line Items',
            opportunities: 'Opportunities'
        }});

        view.model = new Backbone.Model();
    });

    afterEach(function() {
        app.metadata.getModule.restore();
        app.lang.get.restore();
        app.lang.getAppListStrings.restore();
        app.view.View.prototype.initialize.restore();
        app.cache.cutAll();
        app.view.reset();

        Handlebars.templates = {};
        view = null;
        app = null;
    });

    describe('initialize()', function() {
        it('should update titleViewNameTitle correctly', function() {
            view.initialize();
            expect(view.titleViewNameTitle).toBe('title');
        });

        it('should update optionsLang correctly', function() {
            view.initialize();
            expect(view.optionsLang.opportunities).toBe('Opportunities');
        });
    });
});
// END SUGARCRM flav=int ONLY
