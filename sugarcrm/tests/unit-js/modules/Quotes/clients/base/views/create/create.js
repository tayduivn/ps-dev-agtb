describe('Quotes.Base.Views.Create', function() {
    var app;
    var view;
    var viewMeta;
    var model;
    var context;
    var quoteFields;
    var bundleFields;
    var productFields;
    var modMeta;

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

    describe('initialize()', function() {
        var options;
        beforeEach(function() {
            options = {
                context: context
            };

            sinon.collection.stub(view, '_prepopulateQuoteWithOpp', function() {});
            sinon.collection.stub(view, '_buildMeta', function() {});
        });

        afterEach(function() {
            options = null;
        });

        it('should call _prepopulateQuoteWithOpp if convert is on the context', function() {
            options.context.set('convert', true);
            view.initialize(options);

            expect(view._prepopulateQuoteWithOpp).toHaveBeenCalledWith(options);

        });

        it('should not call _prepopulateQuoteWithOpp if convert is not on the context', function() {
            view.initialize(options);

            expect(view._prepopulateQuoteWithOpp).not.toHaveBeenCalled();
        });

        it('should call _buildMeta with ProductBundleNotes params', function() {
            view.initialize(options);

            expect(view._buildMeta).toHaveBeenCalledWith('ProductBundleNotes', 'quote-data-group-list');
        });

        it('should call _buildMeta with ProductBundles params', function() {
            view.initialize(options);

            expect(view._buildMeta).toHaveBeenCalledWith('ProductBundles', 'quote-data-group-header');
        });

        it('should call _buildMeta with Products params', function() {
            view.initialize(options);

            expect(view._buildMeta).toHaveBeenCalledWith('Products', 'quote-data-group-list');
        });
    });

    describe('_prepopulateQuoteWithOpp()', function() {
        var options;
        var quoteModel;
        var oppModel;

        beforeEach(function() {
            quoteModel = new Backbone.Model();
            oppModel = new Backbone.Model({
                id: 'oppId1',
                name: 'oppName1',
                account_id: 'acctId1',
                account_name: 'acctName1'
            });
            context.set({
                model: quoteModel,
                parentModel: oppModel
            });
            options = {
                context: context
            };
        });

        afterEach(function() {
            options = null;
        });

        it('should map fields and prepopulate the Quote context', function() {
            view._prepopulateQuoteWithOpp(options);

            expect(quoteModel.get('opportunity_id')).toBe('oppId1');
            expect(quoteModel.get('opportunity_name')).toBe('oppName1');
            expect(quoteModel.get('billing_account_id')).toBe('acctId1');
            expect(quoteModel.get('billing_account_name')).toBe('acctName1');
        });
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
