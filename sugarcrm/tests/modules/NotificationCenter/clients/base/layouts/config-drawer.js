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
describe('NotificationCenter.Layout.ConfigDrawer', function() {
    var app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'NotificationCenter', 'config-drawer', null, null, true);
    });

    afterEach(function() {
        layout = null;
    });

    describe('_checkConfigMetadata()', function() {
        it('should always return true', function() {
            expect(layout._checkConfigMetadata()).toBeTruthy();
        });
    });

    describe('_checkUserAccess()', function() {
        var sandbox;

        beforeEach(function() {
            sandbox = sinon.sandbox.create();
        });

        afterEach(function() {
            sandbox.restore();
        });

        it('should restrict regular user to access global configuration', function() {
            sandbox.stub(app.user, 'get', function() {
                return 'user';
            });
            layout.model.set('configMode', 'admin');
            expect(layout._checkUserAccess()).toBeFalsy();
        });

        it('should allow regular user to access his configuration', function() {
            sandbox.stub(app.user, 'get', function() {
                return 'user';
            });
            layout.model.set('configMode', 'user');
            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('should allow admin to access global configuration', function() {
            sandbox.stub(app.user, 'get', function() {
                return 'admin';
            });
            layout.model.set('configMode', 'admin');
            expect(layout._checkUserAccess()).toBeTruthy();
        });

        it('should allow admin to access his configuration', function() {
            sandbox.stub(app.user, 'get', function() {
                return 'admin';
            });
            layout.model.set('configMode', 'user');
            expect(layout._checkUserAccess()).toBeTruthy();
        });
    });

});
