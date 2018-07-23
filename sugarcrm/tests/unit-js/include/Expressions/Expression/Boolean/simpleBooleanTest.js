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

describe('Simple Boolean Functions Test', function() {
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

    var trueExpr = new SUGAR.expressions.TrueExpression([]);
    var falseExpr = new SUGAR.expressions.FalseExpression([]);

    describe('True and Equal Expression Function', function() {
        it('should return true if true expression is true', function() {
            var res =  new SUGAR.expressions.EqualExpression([trueExpr, new SUGAR.expressions.TrueExpression([])],
                getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('False and Equal Expression Function', function() {
        it('should return true if false expression is false', function() {
            var res =  new SUGAR.expressions.EqualExpression([falseExpr, new SUGAR.expressions.FalseExpression([])],
                getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('And Expression Function', function() {
        it('should return the LOGICAL AND of two boolean expressions', function() {
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

    describe('Or Expression Function', function() {
        it('should return the LOGICAL OR of two boolean expressions', function() {
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

    describe('Not Expression Function', function() {
        it('should return the LOGICAL NOT of a boolean expression', function() {
            var res =  new SUGAR.expressions.NotExpression([falseExpr], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.NotExpression([trueExpr], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('isAlphaNumeric Expression Function', function() {
        var stringAlphaNum = new SUGAR.expressions.StringLiteralExpression(['A1B2C3']);
        var randString = new SUGAR.expressions.StringLiteralExpression(['!-_?./']);

        it('should return whether are string is alphanumeric or not', function() {
            var res =  new SUGAR.expressions.IsAlphaNumericExpression([stringAlphaNum], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsAlphaNumericExpression([randString], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('isAlpha Expression Function', function() {
        var stringAlphaNum = new SUGAR.expressions.StringLiteralExpression(['A1B2C3']);
        var alphaOnly = new SUGAR.expressions.StringLiteralExpression(['ABCDEF']);

        it('should return whether are string is alpha only or not', function() {
            var res =  new SUGAR.expressions.IsAlphaExpression([alphaOnly], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsAlphaExpression([stringAlphaNum], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('Greater Than Expression Function', function() {
        var num1 = new SUGAR.expressions.ConstantExpression([1]);
        var num2 = new SUGAR.expressions.ConstantExpression([200]);

        it('should return where num1 > num2', function() {
            var res =  new SUGAR.expressions.GreaterThanExpression([num1, num2], getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.GreaterThanExpression([num2, num1], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Befor e Expression Function', function() {
        var datestr1 = new SUGAR.expressions.StringLiteralExpression(['05/10/2000']);
        var datestr2 = new SUGAR.expressions.StringLiteralExpression(['05/10/2010']);

        it('should return whether date1 is befor e date2', function() {
            var date1 = new SUGAR.expressions.DefineDateExpression([datestr1], getSLContext(model));
            var date2 = new SUGAR.expressions.DefineDateExpression([datestr2], getSLContext(model));
            var res =  new SUGAR.expressions.isBeforeExpression([date1,date2],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.isBeforeExpression([date2, date1], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('After Expression Function', function() {
        var datestr1 = new SUGAR.expressions.StringLiteralExpression(['05/10/2000']);
        var datestr2 = new SUGAR.expressions.StringLiteralExpression(['05/10/2010']);
        it('should return whether date1 is after date2', function() {
            var date1 = new SUGAR.expressions.DefineDateExpression([datestr1], getSLContext(model));
            var date2 = new SUGAR.expressions.DefineDateExpression([datestr2], getSLContext(model));
            var res =  new SUGAR.expressions.isAfterExpression([date1,date2],  getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.isAfterExpression([date2, date1], getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Is Valid Email Expression', function() {

        it('should return whether date1 is after date2', function() {
            var email = new SUGAR.expressions.StringLiteralExpression(['a@b.c']);
            var res =  new SUGAR.expressions.IsValidEmailExpression([email],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
            email = new SUGAR.expressions.StringLiteralExpression(['abc']);
            res =  new SUGAR.expressions.IsValidEmailExpression([email],  getSLContext(model));
            expect(res.evaluate()).toBe('false');

        });
    });

    describe('Is Valid Date Expression', function() {
        //Needs to be in YYYY-MM-DD for mat
        var datestr1 = new SUGAR.expressions.StringLiteralExpression(['2018-07-16']);
        var datestr2 = new SUGAR.expressions.StringLiteralExpression(['07/1221/2018']);

        it('should return datestr* is a valid date', function() {
            var res =  new SUGAR.expressions.IsValidDateExpression([datestr1],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsValidDateExpression([datestr2],  getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('Is Valid Phone Expression', function() {
        var phoneStr1 = new SUGAR.expressions.StringLiteralExpression(['!!test_phone_fake!!']);
        var phoneStr2 = new SUGAR.expressions.StringLiteralExpression(['(408) 123-4567']);

        it('should return whether a string is a valid Phone Number', function() {
            var res =  new SUGAR.expressions.IsValidPhoneExpression([phoneStr1],  getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.IsValidPhoneExpression([phoneStr2],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Is Valid Time Expression', function() {
        //needs to be in HH:MM for mat
        var phoneStr1 = new SUGAR.expressions.StringLiteralExpression(['12:00:00']);
        var phoneStr2 = new SUGAR.expressions.StringLiteralExpression(['12:00']);

        it('should return whether a string is a valid time string', function() {
            var res =  new SUGAR.expressions.IsValidTimeExpression([phoneStr1],  getSLContext(model));
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.IsValidTimeExpression([phoneStr2],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Is In Range', function() {
        var a = new SUGAR.expressions.ConstantExpression([1]);
        var b = new SUGAR.expressions.ConstantExpression([100]);
        var queryOne = new SUGAR.expressions.ConstantExpression([50]);
        var queryTwo = new SUGAR.expressions.ConstantExpression([101]);
        it('should return if num query in range a to b', function() {
            var res =  new SUGAR.expressions.IsInRangeExpression([queryOne, a,b],  getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsInRangeExpression([queryTwo, a,b],  getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('Is Numeric Expression', function() {
        var num1 = new SUGAR.expressions.ConstantExpression([1]);
        var num2 = new SUGAR.expressions.ConstantExpression([12321]);
        var num3 = new SUGAR.expressions.StringLiteralExpression('hello');
        var num4 = new SUGAR.expressions.StringLiteralExpression(['12a132']);
        var num5 = new SUGAR.expressions.StringLiteralExpression('12.2');
        var num6 = new SUGAR.expressions.StringLiteralExpression('-5.0');

        it('should return whether date1 is after date2', function() {
            var res =  new SUGAR.expressions.IsNumericExpression([num1]);
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsNumericExpression([num2]);
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsNumericExpression([num3]);
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.IsNumericExpression([num4]);
            expect(res.evaluate()).toBe('false');
            res =  new SUGAR.expressions.IsNumericExpression([num5]);
            expect(res.evaluate()).toBe('true');
            res =  new SUGAR.expressions.IsNumericExpression([num6]);
            expect(res.evaluate()).toBe('true');
        });
    });

    describe('Is In Enum Expression Function', function() {
        it('should return if value is in a provided list', function() {
            var a = new SUGAR.expressions.ConstantExpression([4]);
            var b = new SUGAR.expressions.ConstantExpression([5]);
            var c = new SUGAR.expressions.ConstantExpression([6]);
            var d = new SUGAR.expressions.ConstantExpression([7]);
            var e = new SUGAR.expressions.ConstantExpression([10]);

            var not = new SUGAR.expressions.ConstantExpression([0]);

            var test = new SUGAR.expressions.DefineEnumExpression([a,b,c,d,e]);

            var res = new SUGAR.expressions.IsInEnumExpression([c, test], getSLContext(model));
            expect(res.evaluate()).toBe('true');
            res = new SUGAR.expressions.IsInEnumExpression([not, test], getSLContext(model));
            expect(res.evaluate()).toBe('false');
        });
    });

    describe('Is Valid DB Name Expression Function', function() {
        var trueValues = [
            'sugarCRM',
            'sugar_crm',
            'sugarCRM',
            'sugar_crm',
            'sugarCRM_ver6',
        ];
        var falseValues = [
            'sugar/crm',
            'sugar\\crm',
            'sugar.crm',
            'sugar\\CRM',
            'sugar crm',
            'sugarCRM_var#63',
            '622sugarCRM',
            'sugar crm',
            '#sugarCRM_ver6'
        ];
        var lenT = trueValues.length;
        var lenF = falseValues.length;
        it('should return if value is a valid db name', function() {
            for (var i = 0; i < lenT; i = i + 1) {
                expect(new SUGAR.expressions.IsValidDBNameExpression([new SUGAR.expressions.StringLiteralExpression
                    ([trueValues[i]])], getSLContext(model)).evaluate()).toBe('true');
            }
            for (i = 0; i < lenF; i = i + 1) {
                expect(new SUGAR.expressions.IsValidDBNameExpression([new SUGAR.expressions.StringLiteralExpression
                ([falseValues[i]])], getSLContext(model)).evaluate()).toBe('false');
            }
        });
    });

    describe('Binary Dependency Expression Function', function() {
        var a = new SUGAR.expressions.StringLiteralExpression(['']);
        var b = new SUGAR.expressions.StringLiteralExpression(['valid']);
        it('should return if both values exist', function() {
            expect(new SUGAR.expressions.BinaryDependencyExpression([a, a],
                getSLContext(model)).evaluate()).toBe('false');
            expect(new SUGAR.expressions.BinaryDependencyExpression([a, b],
                getSLContext(model)).evaluate()).toBe('false');
            expect(new SUGAR.expressions.BinaryDependencyExpression([b, a],
                getSLContext(model)).evaluate()).toBe('false');
            expect(new SUGAR.expressions.BinaryDependencyExpression([b, b],
                getSLContext(model)).evaluate()).toBe('true');
        });
    });
});
