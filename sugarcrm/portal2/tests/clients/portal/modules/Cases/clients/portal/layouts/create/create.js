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

// FILE SUGARCRM flav=ent ONLY

describe('PortalCases.Layout.Create', function() {
    var app;
    var createLayout;
    var superStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.loadComponent('portal', 'layout', 'create', 'Cases');
        createLayout = SugarTest.createLayout('portal', 'Cases', 'create', null, null, true, null, true, 'portal');
        superStub = sinon.stub(createLayout, '_super');
    });

    afterEach(function() {
        superStub.restore();
        createLayout.dispose();
    });

    describe('initComponents', function() {
        it('should show deflection if caseDeflection is enabled', function() {
            app.config.caseDeflection = 'enabled';
            createLayout.initComponents();
            expect(superStub.lastCall.args[0]).toEqual('initComponents');
            expect(superStub.lastCall.args[1][0][0].layout).toEqual('deflect');
        });

        it('should show deflection if caseDeflection is not set', function() {
            app.config.caseDeflection = undefined;
            createLayout.initComponents();
            expect(superStub.lastCall.args[0]).toEqual('initComponents');
            expect(superStub.lastCall.args[1][0][0].layout).toEqual('deflect');
        });

        it('should show create-case if caseDeflection is disabled', function() {
            app.config.caseDeflection = 'disabled';
            createLayout.initComponents();
            expect(superStub.lastCall.args[0]).toEqual('initComponents');
            expect(superStub.lastCall.args[1][0][0].layout).toEqual('create-case');
        });
    });
});
