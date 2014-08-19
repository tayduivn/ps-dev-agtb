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
describe('Base.View.ConfigHeaderButtons', function() {
    var app,
        context,
        view,
        options;

    beforeEach(function() {
        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        options = {
            context: context
        };

        view = SugarTest.createView('base', null, 'config-header-buttons');
    });

    afterEach(function() {
        sinon.collection.restore();
        view = null;
    });

    describe('cancelConfig()', function() {
        it('if app.drawer exists, should call app.drawer.close()', function() {
            app.drawer = {
                close: function() {}
            };
            sinon.collection.spy(app.drawer, 'close');
            view.cancelConfig();
            expect(app.drawer.close).toHaveBeenCalled();
            delete app.drawer;
        });

        describe('if app.drawer does not exists', function() {
            beforeEach(function() {
                sinon.collection.stub(app.router, 'goBack', function() {});
            });

            describe('and module is_setup == 0', function() {
                it('and module != "Administration", should call app.router.goBack()', function() {
                    view.context.get('model').set('is_setup', 0);
                    view.cancelConfig();
                    expect(app.router.goBack).toHaveBeenCalled();
                });

                it('and module == "Administration", should not call app.router.goBack()', function() {
                    sinon.collection.stub(app.controller.context, 'get', function() {
                        return 'Administration';
                    });
                    view.context.get('model').set('is_setup', 0);
                    view.cancelConfig();
                    expect(app.router.goBack).not.toHaveBeenCalled();
                });
            });

            it('and module is_setup == 1, should not call app.router.goBack()', function() {
                view.context.get('model').set('is_setup', 1);
                view.cancelConfig();
                expect(app.router.goBack).not.toHaveBeenCalled();
            });
        });
    });
});
