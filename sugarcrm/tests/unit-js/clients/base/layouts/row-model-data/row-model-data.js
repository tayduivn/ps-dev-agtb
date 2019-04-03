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
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'layout', 'row-model-data');
        SugarTest.testMetadata.set();
        app.data.declareModels();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe('initialize()', function() {
        var testLayout;
        var rowModel;

        beforeEach(function() {
            rowModel = app.data.createBean('Cases');
            var context = app.context.getContext();
            context.set('model', rowModel);
            context.prepare();
            testLayout = SugarTest.createLayout('base', null, 'row-model-data', null, context, false);
        });

        afterEach(function() {
            testLayout.dispose();
            testLayout = null;
            rowModel = null;
        });

        it('will set multi-line layout and rowModel in context', function() {
            expect(testLayout.context.parent.get('layout')).toEqual('multi-line');
            expect(testLayout.context.parent.get('rowModel')).toEqual(rowModel);
        });
    });
});
