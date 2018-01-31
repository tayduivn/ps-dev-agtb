/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

describe('Quotes.Layout.ConfigDrawer', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer', null, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        layout = null;
    });

    describe('_checkModuleAccess()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.user, 'get', function() {
                return 'user';
            });
        });

        it('should allow access when user is System Admin', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    Quotes: {
                        developer: 'no',
                        admin: 'no'
                    }
                };
            });
            app.user.get.restore();
            sinon.collection.stub(app.user, 'get', function() {
                return 'admin';
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should allow access when user has Quotes Developer role', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    Quotes: {
                        admin: 'no'
                    }
                };
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should allow access when user is Quotes Admin', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    Quotes: {
                        developer: 'no'
                    }
                };
            });

            expect(layout._checkModuleAccess()).toBeTruthy();
        });

        it('should not allow access when user is not a System Admin nor Developer', function() {
            sinon.collection.stub(app.user, 'getAcls', function() {
                return {
                    Quotes: {
                        developer: 'no',
                        admin: 'no'
                    }
                };
            });

            expect(layout._checkModuleAccess()).toBeFalsy();
        });
    });
});