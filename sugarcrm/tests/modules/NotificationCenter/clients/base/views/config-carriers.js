/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('NotificationCenter.View.ConfigCarriers', function() {
    var app, layout, view, context, sandbox, module = 'NotificationCenter';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.declareData('base', module, true, false);
        layout = SugarTest.createLayout('base', module, 'config-drawer', null, null, true);
        context = app.context.getContext({model: layout.model});
        view = SugarTest.createView('base', module, 'config-carriers', {}, context, true, layout);
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        view = null;
        sandbox.restore();
    });

    it('should call populateCarriers() before it is rendered', function() {
        var method = sandbox.spy(view, 'populateCarriers');
        view.triggerBefore('render');
        expect(method).toHaveBeenCalled();
    });

    describe('populateCarriers()', function() {
        beforeEach(function() {
            view.model.set('configMode', 'default')
        });

        it('should not populate carriers if model is empty', function() {
            view.populateCarriers();
            expect(view.carriers.length).toBe(0);
        });

        it('should populate carriers with data for each of carrier found in model', function() {
            view.model.set('carriers', {
                foo: {},
                bar: {}
            });
            view.populateCarriers();
            expect(view.carriers.length).toBe(2);
        });

        it('should populate carriers with correct metadata', function() {
            view.model.set('carriers', {
                foo: {}
            });
            view.populateCarriers();
            expect(view.carriers[0].type).toBe('carrier');
            expect(view.carriers[0].name).toBe('foo');
        });

        it('should add an address field metadata to corresponding carrier if carrier is selectable', function() {
            view.model.set('carriers', {
                foo: {selectable: true}
            });
            view.populateCarriers();
            expect(view.carriers[0].address).not.toBeNull();
        });

        it('should not add an address field metadata to carrier if carrier is not selectable', function() {
            view.model.set('carriers', {
                foo: {selectable: false}
            });
            view.populateCarriers();
            expect(view.carriers[0].address).toBeNull();
        });

        it('should add an address field definition with correct metadata', function() {
            var options = {
                'one': 1
            };
            view.model.set('carriers', {
                foo: {
                    selectable: true,
                    options: options
                }
            });
            view.populateCarriers();
            expect(view.carriers[0].address.type).toBe('address');
            expect(view.carriers[0].address.name).toBe('foo-address');
            expect(view.carriers[0].address.carrier).toBe('foo');
            expect(view.carriers[0].address.options).toBe(options);
        });
    });
});
