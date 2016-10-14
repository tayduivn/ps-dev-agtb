describe('Quotes.Base.Views.Create', function() {
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

        modMeta = {
            fields: [
                {
                    name: 'name'
                }
            ]
        };

        sinon.collection.stub(app.data, 'getRelatedModule');
        sinon.collection.stub(app.metadata, 'getView', function() {
            return viewMeta;
        });
        sinon.collection.stub(app.metadata, 'getModule', function() {
            return modMeta;
        });
        app.data.getRelatedModule.withArgs('Quotes', 'product_bundles').returns('ProductBundles');
        app.data.getRelatedModule.withArgs('ProductBundles', 'products').returns('Products');
        app.data.getRelatedModule.withArgs('ProductBundles', 'product_bundle_notes').returns('ProductBundleNotes');

        context = app.context.getContext();
        model = app.data.createBean('Quotes');
        context.set('model', model);
        view = SugarTest.createView('base', 'Quotes', 'create', viewMeta, context, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        context = null;
        model.dispose();
        model = null;
    });

    describe('hasUnsavedChanges()', function() {
        beforeEach(function() {
            view.hasUnsavedQuoteChanges = function() {};
            sinon.collection.stub(view, 'hasUnsavedQuoteChanges', function() {});
        });

        it('should call hasUnsavedQuoteChanges', function() {
            view.hasUnsavedChanges();

            expect(view.hasUnsavedQuoteChanges).toHaveBeenCalled();
        });
    });

    describe('validateModels', function() {
        var bundleGet;
        var callback;

        beforeEach(function() {
            view.model.get = function() {
                return {
                    get: bundleGet
                };
            };

            callback = sinon.collection.spy();
        });

        afterEach(function() {
            view.model.get = null;
        });

        it('should return false for no bundles (from create view)', function() {
            bundleGet = function() {
                return [];
            };
            view.validateModels(true, callback, true);
            expect(callback).toHaveBeenCalledWith(false);
        });

        it('should return true for no bundles (not from create view)', function() {
            bundleGet = function() {
                return [];
            };
            view.validateModels(true, callback, false);
            expect(callback).toHaveBeenCalledWith(true);
        });

        it('should return false for the default empty bundle (from create view)', function() {
            bundleGet = function() {
                return [
                    {
                        get: function() {
                            return [];
                        }
                    }
                ];
            };
            view.validateModels(true, callback, true);
            expect(callback).toHaveBeenCalledWith(false);
        });

        it('should return true for the default empty bundle (not from create view)', function() {
            bundleGet = function() {
                return [
                    {
                        get: function() {
                            return [];
                        }
                    }
                ];
            };
            view.validateModels(true, callback, false);
            expect(callback).toHaveBeenCalledWith(true);
        });

        it('should return false for a valid bundle of one item (from create view)', function() {
            bundleGet = function() {
                return [
                    {
                        get: function() {
                            return [{
                                doValidate: function(stuff, fcn) {
                                    fcn();
                                }
                            }];
                        },
                        doValidate: function(stuff, fcn) {
                            fcn();
                        }
                    }
                ];
            };
            view.validateModels(true, callback, true);
            expect(callback).toHaveBeenCalledWith(false);
        });

        it('should return true for a valid bundle of one item (not from create view)', function() {
            bundleGet = function() {
                return [
                    {
                        get: function() {
                            return [{
                                doValidate: function(stuff, fcn) {
                                    fcn();
                                }
                            }];
                        },
                        doValidate: function(stuff, fcn) {
                            fcn();
                        }
                    }
                ];
            };
            view.validateModels(true, callback, false);
            expect(callback).toHaveBeenCalledWith(true);
        });
    });
});
