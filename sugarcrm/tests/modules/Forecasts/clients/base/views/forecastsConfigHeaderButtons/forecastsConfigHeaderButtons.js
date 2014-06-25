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

describe("Forecasts.Base.View.forecastsConfigHeaderButtons", function() {
    var app, view;

    beforeEach(function() {
        app = SugarTest.app;
        app.router = {
            goBack: sinon.stub()
        }
        app.drawer = {
            close: sinon.stub()
        };

        view = SugarTest.loadFile("../modules/Forecasts/clients/base/views/forecastsConfigHeaderButtons", "forecastsConfigHeaderButtons", "js", function(d) {
            return eval(d);
        });

        view.context = new Backbone.Model({
            inDrawer: true
        });

        view.context.set({model: new Backbone.Model({
            is_setup: true
        })});

    });

    afterEach(function() {
        delete app.router;
        delete app.drawer;
        app = null;
    });

    describe('cancelConfig', function() {
        it('should call app.drawer.close', function() {
            view.cancelConfig();
            expect(app.drawer.close).toHaveBeenCalled();
        });

        describe('forecast is setup', function() {
            it('should not call app.router.navigate', function() {
                view.cancelConfig();
                expect(app.router.goBack).not.toHaveBeenCalled();
            });
        });

        describe('forecast is not setup', function() {
            beforeEach(function() {
                view.context.get('model').set({ is_setup: false });
            });

            afterEach(function() {
                view.context.get('model').set({ is_setup: true });
            });

            it('and controller.context module is not Forecast, app.router.navigate should not be called', function() {
                view.cancelConfig();
                expect(app.router.goBack).not.toHaveBeenCalled();
            });

            it('and controller.context module is Forecast, app.router.navigate should be called', function() {
                app.controller.context.set('module', 'Forecasts');
                view.cancelConfig();
                expect(app.router.goBack).toHaveBeenCalled();
                app.controller.context.unset('module');
            });
        });
    });
});
