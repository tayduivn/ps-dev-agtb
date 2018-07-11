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

    var trueExpr = new SUGAR.expressions.TrueExpression([]);
    var falseExpr = new SUGAR.expressions.FalseExpression([]);

    describe('True and Equal Expression Function', function () {
        it('should return true if true expression is true', function () {
            var res =  new SUGAR.expressions.EqualExpression([trueExpr, new SUGAR.expressions.TrueExpression([])],
                getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('False and Equal Expression Function', function () {
        it('should return true if false expression is false', function () {
            var res =  new SUGAR.expressions.EqualExpression([falseExpr, new SUGAR.expressions.FalseExpression([])],
                getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('And Expression Function', function () {
        it('should return the LOGICAL AND of two boolean expressions', function () {
            var res =  new SUGAR.expressions.AndExpression([falseExpr,falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.AndExpression([falseExpr,trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.AndExpression([trueExpr,falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.AndExpression([trueExpr,trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Or Expression Function', function () {
        it('should return the LOGICAL OR of two boolean expressions', function () {
            var res =  new SUGAR.expressions.OrExpression([falseExpr,falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.OrExpression([falseExpr,trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.OrExpression([trueExpr,falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.OrExpression([trueExpr,trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Not Expression Function', function () {
        it('should return the LOGICAL NOT of a boolean expression', function () {
            var res =  new SUGAR.expressions.NotExpression([falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.NotExpression([trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('isAlphaNumeric Expression Function', function () {
        var stringAlphaNum = new SUGAR.expressions.StringLiteralExpression(['A1B2C3']);
        var randString = new SUGAR.expressions.StringLiteralExpression(['!-_?./'])

        it('should return whether are string is alphanumeric or not', function () {
            var res =  new SUGAR.expressions.IsAlphaNumericExpression([stringAlphaNum], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsAlphaNumericExpression([randString], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('isAlpha Expression Function', function () {
        var stringAlphaNum = new SUGAR.expressions.StringLiteralExpression(['A1B2C3']);
        var alphaOnly = new SUGAR.expressions.StringLiteralExpression(['ABCDEF']);

        it('should return whether are string is alpha only or not', function () {
            var res =  new SUGAR.expressions.IsAlphaExpression([alphaOnly], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsAlphaExpression([stringAlphaNum], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('Greater Than Expression Function', function () {
        var num1 = new SUGAR.expressions.ConstantExpression([1]);
        var num2 = new SUGAR.expressions.ConstantExpression([200]);

        it('should return where num1 > num2', function () {
            var res =  new SUGAR.expressions.GreaterThanExpression([num1, num2], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.GreaterThanExpression([num2, num1], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    // describe('Before Expression Function', function () {
    //     var expr = '05/25/2010';
    //     var m = '05';
    //     var d = '25';
    //     var y = '2010';
    //     var month = parseFloat(m);
    //     var day = parseFloat(d);
    //     var year  = parseFloat(y);
    //     console.log(month, day, year);
    //     var date = new SUGAR.expressions.DateExpression([month, day, year]);
    //     console.log(date);
    //     var date1 = new SUGAR.expressions.DefineDateExpression(['05/10/2000'], getSLContext(model));
    //     var date2 = new SUGAR.expressions.DefineDateExpression(['05/10/2010'], getSLContext(model));
    //     console.log(date1);
    //     console.log(date2);
    //
    //     it('should return whether date1 is before date2', function () {
    //         var res =  new SUGAR.expressions.isBeforeExpression([date1,date2],  getSLContext(model));
    //         expect(res.evaluate()).toBe('true');
    //         res =  new SUGAR.expressions.isBeforeExpression([date2, date1], getSLContext(model));
    //         expect(res.evaluate()).toBe('false');
    //     });
    // });
    //
    // describe('After Expression Function', function () {
    //     var date1 = new SUGAR.expressions.DateExpression([5, 25, 2005]);
    //     var date2 = new SUGAR.expressions.DateExpression([5, 25, 2006]);
    //     console.log(date1);
    //     console.log(date2);
    //
    //     it('should return whether date1 is after date2', function () {
    //         var res =  new SUGAR.expressions.isAfterExpression([date1,date2],  getSLContext(model));
    //         expect(res.evaluate()).toBe('false');
    //         res =  new SUGAR.expressions.isBeforeExpression([date2, date1], getSLContext(model));
    //         expect(res.evaluate()).toBe('true');
    //     });
    // });

    // describe('Is Valid Email Expression', function () {
    //
    //     it('should return whether date1 is after date2', function () {
    //         var res =  new SUGAR.expressions.IsValidEmailExpression(['a@b.c'],  getSLContext(model));
    //         console.log(res);
    //         console.log(res.evaluate());
    //         res =  new SUGAR.expressions.IsValidEmailExpression(['abc@abc'],  getSLContext(model));
    //         console.log(res);
    //         //console.log(res.)
    //         // expect(res.evaluate()).toBe('true');
    //     });
    // });

    // describe('Is Valid Date Expression', function () {
    //     var date1 = new SUGAR.expressions.DateExpression(['07/12/2018']);
    //     console.log(date1);
    //
    //     it('should return whether date1 is after date2', function () {
    //         var res =  new SUGAR.expressions.IsValidDateExpression(['07/12/2018'],  getSLContext(model));
    //         console.log(res);
    //         console.log(res.evaluate());
    //         res =  new SUGAR.expressions.IsValidDateExpression(['07/1221/2018'],  getSLContext(model));
    //         console.log(res);
    //         //console.log(res.)
    //         // expect(res.evaluate()).toBe('true');
    //     });
    // });
});
/*
 * For simple boolean expressions, we still need to add tests for
 * BinaryDependencyExpression.php
 * IsForecastClosedExpression.php
 * IsForecastClosedLostExpression.php
 * IsForecastClosedWonExpression.php
 * IsInEnumExpression.php
 * IsInRangeExpression.php
 * IsNumericExpression.php
 * IsRequiredCollectionExpression.php
 * IsValidDateExpression.php
 * IsValidDBNameExpression.php
 * IsValidEmailExpression.php
 * IsValidPhoneExpression.php
 * IsValidTimeExpression.php
 */
