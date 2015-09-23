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
describe('NotificationCenter.Layout.ConfigDrawerContent', function() {
    var app,
        layout,
        sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'NotificationCenter', 'config-drawer-content', null, null, true);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        layout = null;
    });

    describe('initialize()', function() {
        it('should fetch model', function() {
            var fetch = sandbox.spy(layout.model, 'fetch');
            layout.initialize(layout.options);
            expect(fetch).toHaveBeenCalled();
        });
    });

    describe('_switchHowToData()', function() {
        beforeEach(function() {
            layout.currentHowToData = {};
            module = layout.module;
        });
        it('should set currentHowToData properly for Carriers', function() {
            layout._switchHowToData('config-carriers');
            expect(layout.currentHowToData.title).toEqual(app.lang.get('LBL_CARRIERS_CONFIG_TITLE', module));
            expect(layout.currentHowToData.text).toEqual(app.lang.get('LBL_CARRIERS_CONFIG_HELP', module));
        });

        it('should set currentHowToData properly for any Module', function() {
            layout._switchHowToData('config-module');
            expect(layout.currentHowToData.title).toEqual(app.lang.get('LBL_MODULE_CONFIG_TITLE', module));
            expect(layout.currentHowToData.text).toEqual(app.lang.get('LBL_MODULE_CONFIG_HELP', module));
        });
    });
});
