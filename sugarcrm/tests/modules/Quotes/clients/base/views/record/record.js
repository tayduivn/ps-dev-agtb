describe('Quotes.Base.Views.Record', function() {
    var app;
    var view;
    var viewMeta;
    var model;
    var context;
    var quoteFields;
    var bundleFields;
    var productFields;

    beforeEach(function() {
        app = SugarTest.app;

        quoteFields = SugarTest.loadFixture('quote-fields', '../tests/modules/Quotes/fixtures');
        bundleFields = SugarTest.loadFixture('product-bundle-fields', '../tests/modules/ProductBundles/fixtures');
        productFields = SugarTest.loadFixture('product-fields', '../tests/modules/Products/fixtures');

        SugarTest.testMetadata.init();
        SugarTest.seedMetadata(true, './fixtures');
        SugarTest.testMetadata.updateModuleMetadata('ProductBundles', {
            fields: bundleFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Products', {
            fields: productFields
        });
        SugarTest.testMetadata.updateModuleMetadata('Quotes', {
            fields: quoteFields
        });

        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'data', 'model', 'Quotes');
        SugarTest.loadComponent('base', 'data', 'model', 'ProductBundles');
        SugarTest.loadComponent('base', 'data', 'model', 'Products');
        SugarTest.loadComponent('base', 'data', 'model', 'ProductBundleNotes');
        SugarTest.loadPlugin('VirtualCollection');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        viewMeta = {
            panels: [
                {
                    fields: [
                        {
                            name: 'name'
                        }, {
                            name: 'total',
                            calculated: true
                        }, {
                            name: 'bundles',
                            fields: [
                                'id',
                                'name',
                                {
                                    name: 'product_bundle_items',
                                    fields: [
                                        'name',
                                        'id'
                                    ]
                                }
                            ]
                        }
                    ]
                }
            ]
        };

        sinon.collection.stub(app.data, 'getRelatedModule');
        app.data.getRelatedModule.withArgs('Quotes', 'product_bundles').returns('ProductBundles');
        app.data.getRelatedModule.withArgs('ProductBundles', 'products').returns('Products');
        app.data.getRelatedModule.withArgs('ProductBundles', 'product_bundle_notes').returns('ProductBundleNotes');

        context = app.context.getContext();
        model = app.data.createBean('Quotes');
        context.set('model', model);
        view = SugarTest.createView('base', 'Quotes', 'record', viewMeta, context, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('initialize', function() {
        it('should find all the calculated fields', function() {
            expect(view.calculatedFields).toContain('total');
        });
    });

    describe('hasUnsavedChanges', function() {
        var tmpRow;
        var callReturn;
        beforeEach(function() {
            tmpRow = {
                id: 1234,
                name: 'test',
                total: '100',
                bundles: [{
                    id: 1233,
                    name: 'bundle_1',
                    _module: 'ProductBundles',
                    product_bundle_items: [{
                        id: 12345,
                        name: 'item_1',
                        _module: 'Products'
                    }]
                }]
            };
            model.setSyncedAttributes(tmpRow);
            model.set(tmpRow);
        });

        it('will reset the noEditFields variable', function() {
            var existingValues = view.noEditFields;
            view.hasUnsavedChanges();
            expect(view.noEditFields).toBe(existingValues);
        });

        it('should call super with hasUnsavedChanges', function() {
            sinon.collection.stub(view, '_super', function() {});
            callReturn = view.hasUnsavedChanges();
            expect(view._super).toHaveBeenCalledWith('hasUnsavedChanges');
            expect(callReturn).toBeFalsy();
        });

        it('should find no changes', function() {
            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should ignore changes on the total field', function() {
            model.set('total', '125.00');
            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should find the change on the bundle', function() {
            var b = model.get('bundles').at(0);
            b.set('name', 'bundle_123');

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should find the change on the item in the bundle', function() {
            var b = model.get('bundles').at(0);
            var i = b.get('product_bundle_items').at(0);
            i.set('name', 'item_123');

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should find a change when an item is added to a group and is still new', function() {
            var b = model.get('bundles').at(0);
            b.get('product_bundle_items').add(app.data.createBean('Products'));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeTruthy();
        });

        it('should find a change when an item is added to a group and is not new', function() {
            var b = model.get('bundles').at(0);
            b.get('product_bundle_items').add(app.data.createBean('Products', {id: 'my_new_id'}));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find nay change when an item is removed from a group', function() {
            var b = model.get('bundles').at(0).get('product_bundle_items');
            b.remove(b.at(0));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find any change when a group is added', function() {
            var b = model.get('bundles');
            b.add(app.data.createBean('ProductBundles'));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });

        it('should not find any change when a group is removed', function() {
            var b = model.get('bundles');
            b.remove(b.at(0));

            callReturn = view.hasUnsavedChanges();
            expect(callReturn).toBeFalsy();
        });
    });
});
