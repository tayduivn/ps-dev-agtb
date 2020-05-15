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
describe('PurchasedLineItems.Base.Plugins.PurchaseAndServiceChangeHandler', function() {
    var app;
    var plugin;
    var moduleName = 'PurchasedLineItems';
    var model;

    beforeEach(function() {
        app = SUGAR.App;
        SugarTest.loadFile(
            '../modules/PurchasedLineItems/clients/base/plugins',
            'PurchaseAndServiceChangeHandler',
            'js',
            function(data) {
                app.events.off('app:init');
                eval(data);
                app.events.trigger('app:init');
            }
        );
        plugin = app.plugins.plugins.view.PurchaseAndServiceChangeHandler;
        plugin.model = app.data.createBean(moduleName, {
            id: '123test',
            name: 'Lorem ipsum dolor sit amet'
        });

        plugin._super = sinon.stub();
    });

    afterEach(function() {
        sinon.collection.restore();
        plugin = null;
        model = null;
    });

    describe('bindDataChange()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.collection.stub();
            plugin.model = {
                on: stub,
                off: stub
            };
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        it('should call _super bindDataChange method', function() {
            plugin.bindDataChange();

            expect(plugin._super).toHaveBeenCalledWith('bindDataChange');
        });

        it('should call bind handler for service and purchase_name change', function() {
            plugin.bindDataChange();

            expect(plugin.model.on).toHaveBeenCalledWith('change:service');
            expect(plugin.model.on).toHaveBeenCalledWith('change:purchase_name');
        });

    });

    describe('handleServiceChange()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.collection.stub();
            plugin.model = {
                set: stub,
                off: stub
            };
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        it('when service is true should set service_duration_unit with year', function() {
            plugin.model.get = function() { return true; };
            plugin.handleServiceChange();

            expect(plugin.model.set).toHaveBeenCalledWith('service_duration_unit', 'year');
        });

        it('when service is false should set service_duration_unit with day', function() {
            plugin.model.get = function() { return false; };
            plugin.handleServiceChange();

            expect(plugin.model.set).toHaveBeenCalledWith('service_duration_unit', 'day');
        });
    });

    describe('handlePurchaseChange()', function() {
        var stub;
        beforeEach(function() {
            stub = sinon.collection.stub();
            plugin.model = {
                set: stub,
                off: stub
            };

            sinon.collection.stub(plugin, 'setProductAutoPopulateFields');
        });

        afterEach(function() {
            stub = null;
            plugin.model = null;
        });

        describe('when product template id is defined', function() {
            it('should call setProductAutoPopulateFields', function() {
                plugin.model.get = function() {
                    return {
                        product_template_id: 'test123'
                    };
                };
                plugin.model.fields = {
                    product_template_name: {
                        populate_list: {test: 'testList'}
                    }
                };
                plugin.handlePurchaseChange();

                expect(plugin.setProductAutoPopulateFields).toHaveBeenCalledWith('test123', {test: 'testList'});
            });
        });

        describe('when product template id is empty', function() {
            it('should not call setProductAutoPopulateFields', function() {
                plugin.model.get = function() {
                    return {
                        product_template_id: ''
                    };
                };
                plugin.handlePurchaseChange();

                expect(plugin.setProductAutoPopulateFields).not.toHaveBeenCalled();
            });
        });
    });

    describe('setProductAutoPopulateFields', function() {
        var stub;
        var populateList;
        var prodId;
        beforeEach(function() {
            populateList = {test: 'testList'};

            stub = sinon.collection.stub();
            plugin.model = {
                set: stub,
                off: stub
            };
            sinon.collection.stub(app.api, 'call');
        });

        afterEach(function() {
            stub = null;
            populateList = null;
            prodId = null;
            plugin.model = null;
        });

        it('should make an api call with ProductTemplates/prodTemplateId', function() {
            plugin.model.get = function() {
                return {
                    product_template_id: 'test123'
                };
            };
            prodId = plugin.model.get().product_template_id;
            plugin.setProductAutoPopulateFields(prodId, populateList);

            expect(app.api.call).toHaveBeenCalledWith();
        });

        it('should not make an api call when prodTemplateId is empty', function() {
            plugin.model.get = function() {
                return {
                    product_template_id: ''
                };
            };
            prodId = plugin.model.get().product_template_id;
            plugin.setProductAutoPopulateFields(prodId, populateList);

            expect(app.api.call).not.toHaveBeenCalledWith();
        });

    });
});
