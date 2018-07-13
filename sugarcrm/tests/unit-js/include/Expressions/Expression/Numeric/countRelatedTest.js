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
describe('CountRelatedExpression Function', function () {
    var app, dm, sinonSandbox, meta, model;

    var getSLContext = function(modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model =  isCollection ? new modelOrCollection.model() : modelOrCollection;
        context = context || app.context.getContext({
            url: "someurl",
            module: model.module,
            model: model
        });
        var view = SugarTest.createComponent("View", {
            context: context,
            type: "edit",
            module: model.module
        });
        return new SUGAR.expressions.SidecarExpressionContext(view, model, isCollection ? modelOrCollection : false);
    };

    beforeEach(function () {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture("revenue-line-item-metadata");
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        model = dm.createBean("RevenueLineItems", SugarTest.loadFixture("rli"));
    });

    afterEach(function () {
        sinonSandbox.restore();
    });

    describe('Count Related Expression Function', function () {
        it('should return the count for a certain relationship/link (in this case opportunities)', function () {
            var opp = new SUGAR.expressions.StringLiteralExpression(['opportunities']);
            var payload = JSON.parse(JSON.stringify(model.get('opportunities')));
            payload.count = model.get('opportunities').records.length;
            var res = new SUGAR.expressions.CountRelatedExpression([opp], getSLContext(model));
            sinonSandbox.stub(res.context.model, "get").returns(payload);//.withArgs("opportunities").returns(payload);
            expect(parseFloat(res.evaluate())).toBe(2);
        });
    });
});
