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
describe('CalDav.Layout.ConfigDrawer', function() {
    var app,
        layout;

    var layoutName = 'config-drawer';

    beforeEach(function() {
        app = SugarTest.app;
    });

    afterEach(function() {
        sinon.collection.restore();
        layout = null;
    });

    describe('_checkModuleAccess() by admin (CalDav/config)', function() {
        beforeEach(function() {
            var context = app.context.getContext();
            context.set({ url: undefined, module: 'CalDav', layout: layoutName, 'section': ''});
            context.prepare();

            layout = SugarTest.createLayout('base', 'CalDav', layoutName, null, context, true);

            sinon.collection.stub(app.user, 'get', function() {
                return 'user';
            });
        });

        it('should allow access when user is System Admin', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    CalDav: {
                        developer: 'no',
                        admin: 'no'
                    }
                }
            });
            app.user.get.restore();
            sinon.collection.stub(app.user, 'get', function() {
                return 'admin';
            });

            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('should allow to access when user is CalDav Admin', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    CalDav: {
                        developer: 'no'
                    }
                }
            });

            expect(layout._checkUserAccess()).toBeFalsy();
        });

        it('should not allow access when user is not a System Admin nor Developer', function() {

            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    CalDav: {
                        developer: 'no',
                        admin: 'no'
                    }
                }
            });

            expect(layout._checkUserAccess()).toBeFalsy();
        });


    });

    describe('_checkModuleAccess() by user (CalDav/config/user)', function() {
        beforeEach(function() {
            var layoutName = 'config-drawer';
            var context = app.context.getContext();
            context.set({ url: undefined, module: 'CalDav', layout: layoutName, 'section': 'user'});
            context.prepare();

            layout = SugarTest.createLayout('base', 'CalDav', layoutName, null, context, true);

            sinon.collection.stub(app.user, 'get', function() {
                return 'user';
            });
        });

        it('should allow access to user setings', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    CalDav: {
                        developer: 'no',
                        admin: 'no'
                    }
                }
            });

            sinon.collection.stub(app.controller.context, 'get').withArgs('section').returns('user');

            expect(layout._checkUserAccess()).toBeTruthy();
        });

    });
});
