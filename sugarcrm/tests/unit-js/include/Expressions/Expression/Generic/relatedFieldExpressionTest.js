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
describe('Related Field Expression Function', function() {
    var app;
    var dm;
    var sinonSandbox;
    var meta;
    var model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model = isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || app.context.getContext({
            url: 'someurl',
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent('View', {
            context: context,
            type: 'edit',
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    beforeEach(function() {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('revenue-line-item-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        model = dm.createBean('RevenueLineItems', SugarTest.loadFixture('rli'));

    });

    afterEach(function() {
        sinonSandbox.restore();
    });

    describe('Related Field Expression Test with String link', function() {
        it('returns the value of a field in the related module link', function() {
            var field = new SUGAR.expressions.StringLiteralExpression(['test_field']);
            var link = new SUGAR.expressions.StringLiteralExpression(['opportunities']);
            var res = new SUGAR.expressions.RelatedFieldExpression([link, field], getSLContext(model));
            var mockObj = sinonSandbox.mock(res.context);
            mockObj.expects('getRelatedField').once().withArgs('opportunities', 'related', 'test_field')
                .returns('value');
            expect(res.evaluate()).toBe('value');
            mockObj.verify();
        });
    });

    describe('Related Field Expression Test with Other Type of link', function() {
        it('returns the value of a field in the related module link', function() {
            var field = new SUGAR.expressions.StringLiteralExpression(['test_field']);
            var link = new SUGAR.expressions.ConstantExpression([0]);
            var res = new SUGAR.expressions.RelatedFieldExpression([link, field], getSLContext(model));
            expect(res.evaluate()).toBe('');
        });
    });
});
