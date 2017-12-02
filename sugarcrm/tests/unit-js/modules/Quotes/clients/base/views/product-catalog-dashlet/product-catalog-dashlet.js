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
describe('Quotes.Base.Views.ProductCatalogDashlet', function() {
    var app;
    var view;
    var viewMeta;
    var context;
    var layout;

    beforeEach(function() {
        app = SugarTest.app;

        context = app.context.getContext();
        context.set('model', new Backbone.Model());
        viewMeta = {
            config: false
        };

        layout = SugarTest.createLayout('base', 'Quotes', 'record', {});
        view = SugarTest.createView('base', 'Quotes', 'product-catalog-dashlet', viewMeta, context, true, layout);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should set isConfig based on meta to false', function() {
            expect(view.isConfig).toBeFalsy();
        });

        it('should set isConfig based on meta to true', function() {
            view.initialize({
                meta: {
                    config: true
                }
            });
            expect(view.isConfig).toBeTruthy();
        });
    });

    describe('loadData()', function() {
        beforeEach(function() {
            sinon.collection.stub(view, '_super', function() {});
        });

        it('should do nothing if isConfig is true', function() {
            view.isConfig = true;
            view.loadData();

            expect(app.api.buildURL).not.toHaveBeenCalled();
        });

        it('should call the server if isConfig is false', function() {
            view.isConfig = false;
            view.loadData();

            expect(view._super).toHaveBeenCalled();
        });
    });

    describe('toggleLoading()', function() {
        var addClassStub;
        var removeClassStub;

        beforeEach(function() {
            addClassStub = sinon.collection.stub();
            removeClassStub = sinon.collection.stub();

            sinon.collection.stub(view.layout, '$', function() {
                return {
                    addClass: addClassStub,
                    removeClass: removeClassStub
                };
            });
        });

        afterEach(function() {
            addClassStub = null;
            removeClassStub = null;
        });

        it('should call show if startLoading is true', function() {
            view.toggleLoading(true);

            expect(view.layout.$).toHaveBeenCalledWith('i[data-action=loading]');
            expect(addClassStub).toHaveBeenCalledWith('fa-refresh fa-spin');
            expect(removeClassStub).toHaveBeenCalledWith('fa-cog');
        });

        it('should call show if startLoading is false', function() {
            view.toggleLoading(false);

            expect(view.layout.$).toHaveBeenCalledWith('i[data-action=loading]');
            expect(addClassStub).toHaveBeenCalledWith('fa-cog');
            expect(removeClassStub).toHaveBeenCalledWith('fa-refresh fa-spin');
        });
    });
});
