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

describe("Products.Base.Field.Discount", function() {
    var app, field, fieldDef, moduleName = 'Products', sandbox;

    beforeEach(function() {
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app.user.setPreference('currency_id', '-99');
        app.user.setPreference('decimal_separator', '.');
        app.user.setPreference('number_grouping_separator', ',');

        SugarTest.loadComponent('base', 'field', 'base');
        SugarTest.loadComponent('base', 'field', 'currency');
        SugarTest.loadComponent('base', 'field', 'discount', 'Products');

        var testModel = app.data.createBean(moduleName, {
            jasmin_test: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        testModel.isCopy = function() {
            return (testModel.isCopied === true);
        };

        var fieldComponent = {
            name: 'jasmin_test',
            type: 'discount',
            viewName: 'detail',
            fieldDef: {
                name: 'jasmin_test',
                type: 'discount'
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
        fieldDef = null;
        app = null;

        SugarTest.testMetadata.dispose();
    });

    describe('format', function() {
        beforeEach(function() {
            sandbox.stub(field, '_super', function() {
                return true;
            });

            sandbox.stub(app.utils, 'formatNumberLocale', function() {
                return true;
            });
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call _super when discount_select not set', function() {
            field.format(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to 0', function() {
            field.model.set('discount_select', 0, {silent: true});
            field.format(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to "0"', function() {
            field.model.set('discount_select', '0', {silent: true});
            field.format(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field.format(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).not.toHaveBeenCalled();
        });

        it('should call formatNumberLocale when discount_select is set to 1', function() {
            field.model.set('discount_select', 1, {silent: true});
            field.format(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).toHaveBeenCalled();
        });

        it('should call formatNumberLocale when discount_select is set to "1"', function() {
            field.model.set('discount_select', '1', {silent: true});
            field.format(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).toHaveBeenCalled();
        });

        it('should call formatNumberLocale when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field.format(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.formatNumberLocale).toHaveBeenCalled();
        });
    });

    describe('unformat', function() {
        beforeEach(function() {
            sandbox.stub(field, '_super', function() {
                return true;
            });

            sandbox.stub(app.utils, 'unformatNumberStringLocale', function() {
                return true;
            });
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call _super when discount_select not set', function() {
            field.unformat(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to 0', function() {
            field.model.set('discount_select', 0, {silent: true});
            field.unformat(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to "0"', function() {
            field.model.set('discount_select', '0', {silent: true});
            field.unformat(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field.unformat(10.69);

            expect(field._super).toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).not.toHaveBeenCalled();
        });

        it('should call unformatNumberStringLocal when discount_select is set to 1', function() {
            field.model.set('discount_select', 1, {silent: true});
            field.unformat(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).toHaveBeenCalled();
        });

        it('should call unformatNumberStringLocal when discount_select is set to "1"', function() {
            field.model.set('discount_select', '1', {silent: true});
            field.unformat(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).toHaveBeenCalled();
        });

        it('should call unformatNumberStringLocale when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field.unformat(10.69);

            expect(field._super).not.toHaveBeenCalled();
            expect(app.utils.unformatNumberStringLocale).toHaveBeenCalled();
        });
    });

    describe('_loadTemplate', function() {
        beforeEach(function() {
            sandbox.stub(field, '_super', function() {
                return true;
            });

            sandbox.stub(app.template, 'getField', function() {
                return true;
            });

            field.view.action = 'detail';
            field.action = 'detail';
        });

        afterEach(function() {
            field.model.clear({silent: true});
            sandbox.restore();
        });

        it('should call _super when discount_select is set to 1', function() {
            field.model.set('discount_select', 1, {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to "1"', function() {
            field.model.set('discount_select', '1', {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).not.toHaveBeenCalled();
        });

        it('should call _super when discount_select is set to true', function() {
            field.model.set('discount_select', true, {silent: true});
            field._loadTemplate();

            expect(field._super).toHaveBeenCalled();
            expect(app.template.getField).not.toHaveBeenCalled();
        });

        it('should call app.template.getField when discount_select is set to 0', function() {
            field.model.set('discount_select', 0, {silent: true});
            field._loadTemplate();

            expect(field._super).not.toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail', moduleName);
        });

        it('should call app.template.getField when discount_select is set to "0"', function() {
            field.model.set('discount_select', '0', {silent: true});
            field._loadTemplate();

            expect(field._super).not.toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail', moduleName);
        });

        it('should call app.template.getField when discount_select is set to false', function() {
            field.model.set('discount_select', false, {silent: true});
            field._loadTemplate();

            expect(field._super).not.toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail', moduleName);
        });

        it('should call app.template.getField when discount_select is set empty', function() {
            field._loadTemplate();

            expect(field._super).not.toHaveBeenCalled();
            expect(app.template.getField).toHaveBeenCalledWith('currency', 'detail', moduleName);
        });
    });

});
