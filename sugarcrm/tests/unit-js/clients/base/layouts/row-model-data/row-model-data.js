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

describe('View.Layouts.Base.RowModelDataLayout', function() {
    var app;

    beforeEach(function() {
        SugarTest.loadComponent('base', 'layout', 'row-model-data');
        app = SugarTest.app;
    });

    describe('initialize()', function() {
        var testLayout;

        beforeEach(function() {
            var context = app.context.getContext();
            context.prepare();
            context.parent = app.context.getContext();
            testLayout = SugarTest.createLayout('base', null, 'row-model-data', null, context, false);
        });

        afterEach(function() {
            testLayout.dispose();
        });

        it('will set multi-line layout', function() {
            expect(testLayout.context.parent.get('layout')).toEqual('multi-line');
        });
    });
});
