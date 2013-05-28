/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

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

        delete Handlebars.templates;
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
