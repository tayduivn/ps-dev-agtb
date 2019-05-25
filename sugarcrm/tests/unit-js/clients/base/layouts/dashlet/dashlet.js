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

describe('Base.Layout.Dashlet', function() {
    var layout;

    beforeEach(function() {
        layout = SugarTest.createLayout('base', 'Home', 'dashlet', {empty: true});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('getComponentsFromMetadata', function() {
        it('should return component from current tab', function() {
            var currentTab = 0;
            var tab0 = {name: 'tab0', components: [{rows: ['row 1, tab 0', 'row 2, tab 0'], width: 22}]};
            var tab1 = {name: 'tab1', components: [{view: 'multi-line-list'}]};
            var metadata = {tabs: [tab0, tab1]};
            layout.context = {
                get: sinon.collection.stub().returns(currentTab),
                off: $.noop
            };
            expect(layout.getComponentsFromMetadata(metadata)).toEqual(metadata.tabs [currentTab].components);
        });
    });
});
