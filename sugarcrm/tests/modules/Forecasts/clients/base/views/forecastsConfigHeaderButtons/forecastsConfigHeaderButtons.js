/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

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
