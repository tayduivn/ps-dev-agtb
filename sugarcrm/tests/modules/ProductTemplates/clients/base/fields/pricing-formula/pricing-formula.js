/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

describe('ProductTemplates.Base.Field.PricingFormula', function() {
    var app, field, moduleName = 'ProductTemplates', sandbox;
    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.loadComponent('base', 'field', 'pricing-formula', 'ProductTemplates');

        var testModel = app.data.createBean(moduleName, {
            jasmin_test: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        testModel.isCopy = function() {
            return (testModel.isCopied === true);
        };

        var fieldComponent = {
            name: 'pricing_formula',
            type: 'pricing-formula',
            viewName: 'detail',
            fieldDef: {
                name: 'pricing_formula',
                type: 'pricing-formula',
                options: 'pricing_formula_dom'
            },
            module: moduleName,
            model: testModel,
            context: null,
            loadFromModule: true
        };

        sandbox = sinon.sandbox.create();

        field = SugarTest.createField(fieldComponent);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();

        sandbox.restore();

        field = null;
        app = null;

        SugarTest.testMetadata.dispose();
    });

    describe('checkShouldShowFactorField', function() {
        using('valid values', ['ProfitMargin', 'PercentageMarkup', 'PercentageDiscount'], function(value) {
            it('will return true for valid formula types', function() {
                field.model.set(field.name, value);
                expect(field.checkShouldShowFactorField()).toBeTruthy();
                field.model.unset(field.name);
            });
        });

        using('invalid values', ['Fixed', 'IsList'], function(value) {
            it('will return false for invalid formula types', function() {
                field.model.set(field.name, value);
                expect(field.checkShouldShowFactorField()).toBeFalsy();
                field.model.unset(field.name);
            });
        });
    });

    describe('getFactorFieldLabel', function() {
        it('will return correct label', function() {
            field.model.set(field.name, 'ProfitMargin');
            expect(field.getFactorFieldLabel()).toEqual('LBL_POINTS');
            field.model.set(field.name, 'PercentageMarkup');
            expect(field.getFactorFieldLabel()).toEqual('LBL_PERCENTAGE');
            field.model.set(field.name, 'PercentageDiscount');
            expect(field.getFactorFieldLabel()).toEqual('LBL_PERCENTAGE');
            field.model.set(field.name, '');
            expect(field.getFactorFieldLabel()).toEqual('');
            field.model.set(field.name, 'InvalidFormulaValue');
            expect(field.getFactorFieldLabel()).toEqual('');
            field.model.unset(field.name);
        });
    });

    describe('_setupProfitMarginFormula', function() {
        beforeEach(function() {
            field.model.set('cost_price', 100);
            field.model.set('pricing_factor', 50);
        });

        it('discount_price will equal 200', function() {
            field._setupProfitMarginFormula();

            expect(field.model.get('discount_price')).toEqual(200);
        });
    });

    describe('_setupIsListFormula', function() {
        beforeEach(function() {
            field.model.set('list_price', 100);
        });

        it('discount_price will equal 100', function() {
            field._setupIsListFormula();
            expect(field.model.get('discount_price')).toEqual(100);
        });
    });

    describe('_setupPercentageMarkupFormula', function() {
        beforeEach(function() {
            field.model.set('cost_price', 100);
            field.model.set('pricing_factor', 50);
        });

        it('discount_price will equal 150', function() {
            field._setupPercentageMarkupFormula();
            expect(field.model.get('discount_price')).toEqual(150);
        });
    });

    describe('_setupPercentageDiscountFormula', function() {
        beforeEach(function() {
            field.model.set('list_price', 100);
            field.model.set('pricing_factor', 25);
        });

        it('discount_price will equal 75', function() {
            field._setupPercentageDiscountFormula();
            expect(field.model.get('discount_price')).toEqual(75);
        });
    });

    describe('disableDiscountField', function() {
        var discount_field;
        beforeEach(function() {
            discount_field = {
                name: 'discount_price',
                setDisabled: function(disable) {
                    // do nothing;
                }
            };

            sandbox.stub(discount_field, 'setDisabled');
            sandbox.stub(field.view, 'getField', function() {
                return discount_field;
            });
        });

        afterEach(function() {
            sandbox.restore();
            discount_field = null;
        });

        using('valid values', ['ProfitMargin', 'PercentageMarkup', 'PercentageDiscount', 'IsList'], function(value) {
            it('will call setDisable with true on the discount_field', function() {
                field.model.set(field.name, value, {silent: true});
                field.disableDiscountField();
                expect(discount_field.setDisabled).toHaveBeenCalledWith(true);
            });
        });

        using('invalid values', ['Fixed', '', 'SomeInvalidValue'], function(value) {
            it('will call setDisable with false on the discount_field', function() {
                field.model.set(field.name, value, {silent: true});
                field.disableDiscountField();
                expect(discount_field.setDisabled).toHaveBeenCalledWith(false);
            });
        });
    });
});
