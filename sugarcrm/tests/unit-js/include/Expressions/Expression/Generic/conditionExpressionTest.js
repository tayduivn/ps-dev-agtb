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
describe("Sugar Condition Expression Function", function () {
    var app, dm, sinonSandbox, meta, model;

    var getSLContext = function (modelOrCollection, context) {
        var isCollection = (modelOrCollection instanceof dm.beanCollection);
        var model = isCollection ? new modelOrCollection.model() : modelOrCollection;
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

    describe("Sugar Condition Expression Function", function () {
        it("returns param[1] if param[0] is true or param[2] if param[0] is false", function () {
            var trueExpr = new SUGAR.expressions.TrueExpression();
            var falseExpr = new SUGAR.expressions.FalseExpression();
            var num_one = new SUGAR.expressions.ConstantExpression([0]);
            var num_two = new SUGAR.expressions.ConstantExpression([10]);
            var res = new SUGAR.expressions.ConditionExpression([trueExpr, num_one, num_two], getSLContext(model));
            expect(res.evaluate()).toBe(0);
            res = new SUGAR.expressions.ConditionExpression([falseExpr, num_one, num_two], getSLContext(model));
            expect(res.evaluate()).toBe(10);
        });
    });
});
