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

describe('Home.Layouts.ConsoleSideDrawer', function() {
    var app;
    var layout;
    var sandbox = sinon.sandbox.create();

    beforeEach(function() {
        app = SugarTest.app;
        var context = new app.Context();
        SugarTest.loadComponent('base', 'layout', 'side-drawer');
        SugarTest.loadComponent('base', 'layout', 'console-side-drawer', 'Home');
        layout = SugarTest.createLayout('base', 'Home', 'console-side-drawer', {}, context, 'Home');
    });

    afterEach(function() {
        sandbox.restore();
        sinon.collection.restore();
        layout = null;
    });

    describe('edit access', function() {
        var aclStub;
        var userStub;
        beforeEach(function() {
            userStub = sinon.collection.stub(app.user, 'get');
            aclStub = sinon.collection.stub(app.user, 'getAcls');
        });

        it('should have edit access for admins (default)', function() {
            expect(layout.hasEditAccess).toEqual(true);
        });

        it('should have edit access for system admins', function() {
            userStub.returns('admin');
            aclStub.returns({
                'ConsoleConfiguration': {
                    admin: false
                }
            });
            layout.setEditAccess();
            expect(layout.hasEditAccess).toEqual(true);
        });

        it('should NOT have edit access for regular users', function() {
            userStub.returns('user');
            aclStub.returns({
                'ConsoleConfiguration': {
                    admin: false
                }
            });
            layout.setEditAccess();
            expect(layout.hasEditAccess).toEqual(false);
        });
    });

    describe('button actions', function() {
        it('should have the actions enabled by default', function() {
            expect(layout.areActionsEnabled).toEqual(true);
        });

        it('should disable button actions', function() {
            layout.edit();
            expect(layout.areActionsEnabled).toEqual(false);
        });

        it('should propagate the close action when possible', function() {
            sinon.collection.spy(layout, '_super');
            layout.close();
            expect(layout._super).toHaveBeenCalledWith('close');
        });

        it('should not let perform actions if they are disabled', function() {
            sinon.collection.spy(app.events, 'trigger');
            sinon.collection.spy(layout, '_super');
            layout.edit();
            layout.close();
            layout.edit();
            expect(layout.areActionsEnabled).toEqual(false);
            expect(app.events.trigger).toHaveBeenCalledOnce();
            expect(layout._super).not.toHaveBeenCalled();
        });
    });
});
