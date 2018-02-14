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
describe('Quotes.Layout.ConfigDrawerContent', function() {
    var app;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;
        layout = SugarTest.createLayout('base', 'Quotes', 'config-drawer-content', null, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('_initHowTo()', function() {
        beforeEach(function() {
            sinon.collection.stub(app.lang, 'get')
                .withArgs('LBL_MODULE_NAME').returns('LBL_MODULE_NAME')
                .withArgs('LBL_CONFIG_FIELD_SELECTOR').returns('LBL_MODULE_NAME LBL_CONFIG_FIELD_SELECTOR');
            layout.currentHowToData = {};
        });

        it('should set currentHowToData', function() {
            layout._switchHowToData('config-columns');

            expect(layout.currentHowToData.title).toBe('LBL_MODULE_NAME LBL_CONFIG_FIELD_SELECTOR');
        });
    });
});
