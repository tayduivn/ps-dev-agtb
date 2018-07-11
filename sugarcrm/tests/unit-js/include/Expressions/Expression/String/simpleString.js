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

/*
 * For simple string expressions, we still need to add tests for
 * ForecastCommitStageExpression
 * FormatedNameExpression
 * SugarDropDownValueExpression
 * SugarTranslateExpression
 */

describe('Simple Numeric Functions Test', function () {
    var app, dm, sinonSandbox, meta, model;
    var getSLContext = function (modelOrCollection, context) {
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
    var initializeContexts = function () {
        getSLContext(model).initialize(meta.modules.Quotes.dependencies);
        getSLContext(model.get('bundles')).initialize(meta.modules.ProductBundles.dependencies);
        model.get('bundles').each(function (bundle) {
            var prods = bundle.getRelatedCollection('products');
            getSLContext(prods).initialize(meta.modules.Products.dependencies || []);
        });
    };

    beforeEach(function () {
        sinonSandbox = sinon.sandbox.create();
        SugarTest.seedMetadata();
        app = SugarTest.app;
        meta = SugarTest.loadFixture('nested-collections-metadata');
        app.metadata.set(meta);
        dm = app.data;
        dm.reset();
        dm.declareModels();
        sinon.stub(app.currency, 'convertAmount', function (val, from, to) {
            from = !from || from == '-99' ? 1 : parseFloat(from);
            to = !to || to == '-99' ? 1 : parseFloat(to);

            return (parseFloat(val) / from) * to;
        });
        model = dm.createBean('Quotes', SugarTest.loadFixture('quote'));
        initializeContexts();
    });

    afterEach(function () {
        app.currency.convertAmount.restore();
        sinonSandbox.restore();
    });

    describe('Character At Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello ']);
        var b = new SUGAR.expressions.ConstantExpression([0]);
        var c = new SUGAR.expressions.ConstantExpression([1]);
        var d = new SUGAR.expressions.ConstantExpression([2]);
        var e = new SUGAR.expressions.ConstantExpression([3]);
        var f = new SUGAR.expressions.ConstantExpression([4]);
        var g = new SUGAR.expressions.ConstantExpression([5]);
        it('should return character at a certain place', function () {
            var res =  new SUGAR.expressions.CharacterAtExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('H');
            res =  new SUGAR.expressions.CharacterAtExpression([a,c], getSLContext(model));
            expect(res.evaluate()).toBe('e');
            res =  new SUGAR.expressions.CharacterAtExpression([a,d], getSLContext(model));
            expect(res.evaluate()).toBe('l');
            res =  new SUGAR.expressions.CharacterAtExpression([a,e], getSLContext(model));
            expect(res.evaluate()).toBe('l');
            res =  new SUGAR.expressions.CharacterAtExpression([a,f], getSLContext(model));
            expect(res.evaluate()).toBe('o');
            res =  new SUGAR.expressions.CharacterAtExpression([a,g], getSLContext(model));
            expect(res.evaluate()).toBe(' ');
        });
    });

    describe('Concatenate Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello ']);
        var b = new SUGAR.expressions.StringLiteralExpression(['World']);

        it('should return a string that combines Hello World', function () {
            var res =  new SUGAR.expressions.ConcatenateExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('Hello World');
        });
    });

    describe('Contains Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['HelloWorld']);
        var b = new SUGAR.expressions.StringLiteralExpression(['World']);

        it('should return whether string A contains string B (true or false)', function () {
            var res =  new SUGAR.expressions.ContainsExpression([a,b], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.ContainsExpression([b,a], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('String to Lower Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['HelloWorld']);
        var b = new SUGAR.expressions.StringLiteralExpression((['HelloWorld']));
        
        it('should return all lowercase version of string A', function () {
            var res =  new SUGAR.expressions.StrToLowerExpression([a], getSLContext(model));
            expect(res.evaluate()).toBe('helloworld');
            res =  new SUGAR.expressions.StrToLowerExpression([b], getSLContext(model));
            expect(res.evaluate()).toBe('helloworld');
        });
    });

    describe('String to Upper Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['helloworld']);
        var b = new SUGAR.expressions.StringLiteralExpression((['HelloWorld']));

        it('should return all uppercase version of string A', function () {
            var res =  new SUGAR.expressions.StrToUpperExpression([a], getSLContext(model));
            expect(res.evaluate()).toBe('HELLOWORLD');
            res =  new SUGAR.expressions.StrToUpperExpression([b], getSLContext(model));
            expect(res.evaluate()).toBe('HELLOWORLD');
        });
    });

    describe('Substring of String Expression Function', function () {
        var a = new SUGAR.expressions.StringLiteralExpression(['Hello World']);
        var b = new SUGAR.expressions.ConstantExpression([0]);  // for beginning of string
        var c = new SUGAR.expressions.ConstantExpression([5]);  // for ending of first word
        var d = new SUGAR.expressions.ConstantExpression([6]);  // for beginning of second word
        var e = new SUGAR.expressions.ConstantExpression([11]); // for end of string

        it('should return all uppercase version of string A', function () {
            var res =  new SUGAR.expressions.SubStrExpression([a,b,c], getSLContext(model));
            expect(res.evaluate()).toBe('Hello');
            res =  new SUGAR.expressions.SubStrExpression([a,d,e], getSLContext(model));
            expect(res.evaluate()).toBe('World');
        });
    });

});
/*
 * For simple string expressions, we still need to add tests for
 * ForecastCommitStageExpression
 * FormatedNameExpression
 * SugarDropDownValueExpression
 * SugarTranslateExpression
 */
