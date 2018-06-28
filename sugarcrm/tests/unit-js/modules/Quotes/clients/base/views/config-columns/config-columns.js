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
describe('Quotes.View.ConfigColumns', function() {
    var app;
    var context;
    var options;
    var view;
    var quotesFieldsMeta;
    var productsFieldsMeta;
    var productQuoteDataGroupListMeta;

    beforeEach(function() {
        var meta;
        var depFields;
        var relFields;

        app = SugarTest.app;
        context = app.context.getContext();
        context.set('model', new Backbone.Model());

        meta = {
            label: 'testLabel',
            panels: [
                {
                    fields: []
                }
            ]
        };

        options = {
            context: context,
            meta: meta
        };

        depFields = SugarTest.loadFixture('dependent-fields', '../tests/modules/Quotes/fixtures');
        relFields = SugarTest.loadFixture('related-fields', '../tests/modules/Quotes/fixtures');
        productsFieldsMeta = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');
        quotesFieldsMeta = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');

        productQuoteDataGroupListMeta = {
            panels: [
                {
                    name: 'products_quote_data_group_list',
                    label: 'LBL_PRODUCTS_QUOTE_DATA_LIST',
                    fields: [
                        {
                            name: 'line_num',
                            label: null,
                            type: 'line-num',
                            readonly: true
                        }, {
                            name: 'quantity',
                            label: 'LBL_QUANTITY',
                            type: 'float'
                        }, {
                            name: 'product_template_name',
                            label: 'LBL_ITEM_NAME',
                            type: 'quote-data-relate',
                            required: true
                        }, {
                            name: 'mft_part_num',
                            label: 'LBL_MFT_PART_NUM',
                            type: 'base'
                        }, {
                            name: 'discount_price',
                            label: 'LBL_DISCOUNT_PRICE',
                            type: 'currency',
                            convertToBase: true,
                            showTransactionalAmount: true,
                            related_fields: [
                                'discount_price',
                                'currency_id',
                                'base_rate'
                            ]
                        }, {
                            name: 'discount',
                            label: 'LBL_DISCOUNT_AMOUNT',
                            type: 'fieldset',
                            fields: [
                                {
                                    name: 'discount_amount',
                                    label: 'LBL_DISCOUNT_AMOUNT',
                                    type: 'discount',
                                    convertToBase: true,
                                    showTransactionalAmount: true
                                }, {
                                    name: 'discount_select',
                                    type: 'discount-select',
                                    no_default_actions: true,
                                    buttons: [
                                        {
                                            name: 'select_discount_amount_button',
                                            label: 'LBL_DISCOUNT_AMOUNT',
                                            type: 'rowaction',
                                            event: 'button:discount_select_change:click'
                                        }, {
                                            name: 'select_discount_percent_button',
                                            label: 'LBL_DISCOUNT_PERCENT',
                                            type: 'rowaction',
                                            event: 'button:discount_select_change:click'
                                        }
                                    ]
                                }
                            ]
                        }, {
                            name: 'total_amount',
                            label: 'LBL_LINE_ITEM_TOTAL',
                            type: 'currency',
                            showTransactionalAmount: true,
                            related_fields: [
                                'total_amount',
                                'currency_id',
                                'base_rate'
                            ]
                        }
                    ]
                }
            ]
        };

        sinon.collection.stub(app.metadata, 'getModule')
            .withArgs('Products', 'fields').returns(productsFieldsMeta)
            .withArgs('Quotes', 'fields').returns(quotesFieldsMeta);

        sinon.collection.stub(app.metadata, 'getView')
            .withArgs('Products', 'quote-data-group-list').returns(productQuoteDataGroupListMeta);

        SugarTest.loadComponent('base', 'view', 'config-panel');
        SugarTest.loadComponent('base', 'view', 'config-panel', 'Quotes');
        view = SugarTest.createView('base', 'Quotes', 'config-columns', meta, context, true);

        view.context.set({
            dependentFields: depFields,
            productsFields: productsFieldsMeta,
            relatedFields: relFields
        });
    });

    afterEach(function() {
        quotesFieldsMeta = null;
        productsFieldsMeta = null;
        productQuoteDataGroupListMeta = null;
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set eventViewName', function() {
            expect(view.eventViewName).toBe('worksheet_columns');
        });

        it('should set productsFieldMeta', function() {
            expect(view.productsFieldMeta).toBe(productsFieldsMeta);
        });

        it('should set defaultFields', function() {
            expect(view.defaultFields).toEqual([
                {
                    name: 'quantity',
                    label: 'LBL_QUANTITY',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    css_class: ''
                }, {
                    name: 'product_template_name',
                    label: 'LBL_ITEM_NAME',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    required: true,
                    type: 'quote-data-relate',
                    css_class: ''
                }, {
                    name: 'mft_part_num',
                    label: 'LBL_MFT_PART_NUM',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    css_class: ''
                }, {
                    name: 'discount_price',
                    label: 'LBL_DISCOUNT_PRICE',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    css_class: ''
                }, {
                    name: 'discount',
                    label: 'LBL_DISCOUNT_AMOUNT',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    css_class: ' quote-discount-percent',
                    type: 'fieldset',
                    fields: [
                        {
                            name: 'discount_amount',
                            label: 'LBL_DISCOUNT_AMOUNT',
                            type: 'discount',
                            convertToBase: true,
                            showTransactionalAmount: true
                        }, {
                            name: 'discount_select',
                            type: 'discount-select',
                            no_default_action: true,
                            buttons: [
                                {
                                    name: 'select_discount_amount_button',
                                    type: 'rowaction',
                                    label: 'LBL_DISCOUNT_AMOUNT',
                                    event: 'button:discount_select_change:click'
                                }, {
                                    name: 'select_discount_percent_button',
                                    type: 'rowaction',
                                    label: 'LBL_DISCOUNT_PERCENT',
                                    event: 'button:discount_select_change:click'
                                }
                            ]
                        }
                    ]
                }, {
                    name: 'total_amount',
                    label: 'LBL_LINE_ITEM_TOTAL',
                    labelModule: 'Quotes',
                    widthClass: undefined,
                    css_class: ''
                }
            ]);
        });

        it('should set listHeaderFields', function() {
            expect(view.listHeaderFields).toEqual([
                {
                    name: 'quantity',
                    label: 'LBL_QUANTITY',
                    type: 'float',
                    labelModule: 'Quotes'
                }, {
                    name: 'product_template_name',
                    label: 'LBL_ITEM_NAME',
                    type: 'quote-data-relate',
                    required: true,
                    labelModule: 'Quotes'
                }, {
                    name: 'mft_part_num',
                    label: 'LBL_MFT_PART_NUM',
                    type: 'base',
                    labelModule: 'Quotes'
                }, {
                    name: 'discount_price',
                    label: 'LBL_DISCOUNT_PRICE',
                    type: 'currency',
                    convertToBase: true,
                    showTransactionalAmount: true,
                    related_fields: [
                        'discount_price',
                        'currency_id',
                        'base_rate'
                    ],
                    labelModule: 'Quotes'
                }, {
                    name: 'discount',
                    label: 'LBL_DISCOUNT_AMOUNT',
                    type: 'fieldset',
                    fields: [
                        {
                            name: 'discount_amount',
                            label: 'LBL_DISCOUNT_AMOUNT',
                            type: 'discount',
                            convertToBase: true,
                            showTransactionalAmount: true
                        }, {
                            name: 'discount_select',
                            type: 'discount-select',
                            no_default_actions: true,
                            buttons: [
                                {
                                    name: 'select_discount_amount_button',
                                    label: 'LBL_DISCOUNT_AMOUNT',
                                    type: 'rowaction',
                                    event: 'button:discount_select_change:click'
                                }, {
                                    name: 'select_discount_percent_button',
                                    label: 'LBL_DISCOUNT_PERCENT',
                                    type: 'rowaction',
                                    event: 'button:discount_select_change:click'
                                }
                            ]
                        }
                    ],
                    labelModule: 'Quotes'
                }, {
                    name: 'total_amount',
                    label: 'LBL_LINE_ITEM_TOTAL',
                    type: 'currency',
                    showTransactionalAmount: true,
                    related_fields: [
                        'total_amount',
                        'currency_id',
                        'base_rate'
                    ],
                    labelModule: 'Quotes'
                }
            ]);
        });

        it('should set listDefaultFieldNameLabels', function() {
            expect(view.listDefaultFieldNameLabels).toBe('LBL_QUANTITY, LBL_ITEM_NAME, LBL_MFT_PART_NUM,' +
                ' LBL_DISCOUNT_PRICE, LBL_DISCOUNT_AMOUNT, LBL_LINE_ITEM_TOTAL');
        });

        it('should set worksheet_columns on the model', function() {
            expect(view.model.get('worksheet_columns')).toBe(view.listHeaderFields);
        });
    });

    describe('_getFieldLabelModule()', function() {
        it('should fallback to Quotes if not found on products', function() {
            expect(view._getFieldLabelModule(productsFieldsMeta.quantity)).toBe('Quotes');
        });

        it('should use labelModule when it exists', function() {
            sinon.collection.stub(app.lang, 'get', function() {
                return 'test';
            });
            productsFieldsMeta.quantity.labelModule = 'ProductBundles';
            expect(view._getFieldLabelModule(productsFieldsMeta.quantity)).toBe('ProductBundles');
        });

        it('should use Products if labelModule does not exist', function() {
            sinon.collection.stub(app.lang, 'get', function() {
                return 'test';
            });
            productsFieldsMeta.quantity.labelModule = undefined;
            expect(view._getFieldLabelModule(productsFieldsMeta.quantity)).toBe('Products');
        });
    });

    describe('_onDependentFieldsChange()', function() {
        beforeEach(function() {
            sinon.collection.stub(view.context, 'trigger');

            view._onDependentFieldsChange(view.context, view.dependentFields);
        });

        it('should set the _related_fields', function() {
            expect(view.model.get(view.eventViewName + '_related_fields')).toEqual([
                'base_rate',
                'currency_id',
                'deal_calc',
                'discount_amount',
                'discount_price',
                'discount_select',
                'quantity',
                'subtotal',
                'total_amount'
            ]);
        });
    });

    describe('_onConfigFieldChange()', function() {
        var addColumnHeaderFieldStub;
        var removeColumnHeaderFieldStub;
        var testField;

        beforeEach(function() {
            addColumnHeaderFieldStub = sinon.collection.stub();
            removeColumnHeaderFieldStub = sinon.collection.stub();
            sinon.collection.stub(view.context, 'trigger');
            sinon.collection.spy(view.model, 'set');
            sinon.collection.stub(view, '_getFieldLabelModule', function() {
                return 'Products';
            });
            view.listHeaderView = {
                addColumnHeaderField: addColumnHeaderFieldStub,
                removeColumnHeaderField: removeColumnHeaderFieldStub,
                dispose: $.noop
            };

            testField = productsFieldsMeta.total_amount;
            testField.def = {
                relatedFields: [
                    'currency_id'
                ]
            };
        });

        afterEach(function() {
            addColumnHeaderFieldStub = null;
            removeColumnHeaderFieldStub = null;
            testField = null;
        });

        describe('when changing field wasVisible = false, isVisible = true', function() {
            it('should call addColumnHeaderField on listHeaderView when field has vname', function() {
                testField.vname = 'LBL_LINE_ITEM_TOTAL1';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addColumnHeaderFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.vname,
                    labelModule: 'Products'
                });
            });

            it('should call addColumnHeaderField on listHeaderView', function() {
                delete testField.vname;
                testField.label = 'LBL_LINE_ITEM_TOTAL2';
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(addColumnHeaderFieldStub).toHaveBeenCalledWith({
                    name: testField.name,
                    type: testField.type,
                    label: testField.label,
                    labelModule: 'Products'
                });
            });

            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'unchecked', 'checked');

                expect(view.context.trigger).toHaveBeenCalledWith(
                    'config:worksheet_columns:currency_id:related:toggle',
                    testField,
                    true
                );
            });
        });

        describe('when changing field wasVisible = true, isVisible = false', function() {
            it('should call removeColumnHeaderField on listHeaderView', function() {
                view._onConfigFieldChange(testField, 'checked', 'unchecked');

                expect(removeColumnHeaderFieldStub).toHaveBeenCalledWith(testField);
            });

            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'checked', 'unchecked');

                expect(view.context.trigger).toHaveBeenCalled();
            });
        });

        describe('when changing field wasVisible = false, isVisible = false, isUnchecked = true', function() {
            it('should trigger config:<eventViewName>:<fieldName>:related:toggle event on context', function() {
                view._onConfigFieldChange(testField, 'filled', 'unchecked');

                expect(view.context.trigger).toHaveBeenCalledWith(
                    'config:worksheet_columns:currency_id:related:toggle',
                    testField,
                    false
                );
            });
        });
    });

    describe('_getPanelFields()', function() {
        it('should return the productFields array on the context', function() {
            var fields = [
                {
                    name: 'aaa'
                }
            ];
            view.context.set('productsFields', fields);

            expect(view._getPanelFields()).toEqual(fields);
        });
    });

    describe('_getPanelFieldsModule()', function() {
        it('should return Products', function() {
            expect(view._getPanelFieldsModule()).toEqual('Products');
        });
    });

    describe('_customFieldsSorting()', function() {
        it('should use the custom sorting function to sort alphabetically by name', function() {
            var result = view._customFieldsSorting([
                {
                    name: 'bbb'
                }, {
                    name: 'aaa'
                }
            ]);

            expect(result[0].name).toBe('aaa');
            expect(result[1].name).toBe('bbb');
        });
    });

    describe('render()', function() {
        var setColumnHeaderFieldsStub;
        var appendStub;

        beforeEach(function() {
            setColumnHeaderFieldsStub = sinon.collection.stub();
            appendStub = sinon.collection.stub();
            sinon.collection.stub(app.view, 'createView', function() {
                return {
                    setColumnHeaderFields: setColumnHeaderFieldsStub,
                    el: 'test',
                    dispose: $.noop
                };
            });
            sinon.collection.stub(view, '$', function() {
                return {
                    append: appendStub
                };
            });
            sinon.collection.stub(view, '_render');

            view.render();
        });

        afterEach(function() {
            setColumnHeaderFieldsStub = null;
            appendStub = null;
        });

        it('should call $ to select .quote-data-list-table element', function() {
            expect(view.$).toHaveBeenCalledWith('.quote-data-list-table');
        });

        it('should append the listHeaderView element', function() {
            expect(appendStub).toHaveBeenCalledWith(view.listHeaderView.el);
        });

        it('should call setColumnHeaderFields with the listHeaderFields', function() {
            expect(setColumnHeaderFieldsStub).toHaveBeenCalledWith(view.listHeaderFields);
        });
    });

    describe('onClickRestoreDefaultsBtn()', function() {
        var setColumnHeaderFieldsStub;

        beforeEach(function() {
            setColumnHeaderFieldsStub = sinon.collection.stub();
            sinon.collection.stub(view.context, 'trigger');
            view.listHeaderView = {
                setColumnHeaderFields: setColumnHeaderFieldsStub,
                el: 'test',
                dispose: $.noop
            };

            view.onClickRestoreDefaultsBtn({});
        });

        afterEach(function() {
            setColumnHeaderFieldsStub = null;
        });

        it('should call setColumnHeaderFields on the listHeaderView', function() {
            expect(setColumnHeaderFieldsStub).toHaveBeenCalledWith(view.defaultFields);
        });

        it('should trigger config:fields:<eventViewName>:reset on the context', function() {
            expect(view.context.trigger).toHaveBeenCalledWith('config:fields:' + view.eventViewName + ':reset');
        });
    });

    describe('_customFieldDef()', function() {
        it('should set the def.eventViewName from this.eventViewName', function() {
            var def = {};
            view.eventViewName = 'test1';
            expect(view._customFieldDef(def)).toEqual({
                eventViewName: view.eventViewName
            });
        });
    });

    describe('_dispose()', function() {
        var disposeStub;

        beforeEach(function() {
            disposeStub = sinon.collection.stub();
            sinon.collection.stub(view, '_super');
            view.listHeaderView = {
                dispose: disposeStub
            };
        });

        afterEach(function() {
            disposeStub = null;
        });

        it('should dispose the listHeaderView', function() {
            view._dispose();

            expect(disposeStub).toHaveBeenCalled();
            expect(view.listHeaderView).toBeNull();
        });
    });
});
