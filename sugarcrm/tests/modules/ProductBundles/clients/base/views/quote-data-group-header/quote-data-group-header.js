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

describe('ProductBundles.Base.Views.QuoteDataGroupHeader', function() {
    var app;
    var view;
    var viewMeta;
    var viewLayoutModel;
    var layout;
    var layoutDefs;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model();
        layoutDefs = {
            'components': [
                {'layout': {'span': 4}},
                {'layout': {'span': 8}}
            ]
        };
        layout = SugarTest.createLayout('base', 'ProductBundles', 'default', layoutDefs);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-header',
            viewMeta, null, true, layout);
        sinon.collection.stub(view, 'setElement');
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should have the same model as the layout', function() {
            expect(view.model).toBe(viewLayoutModel);
        });

        it('should set listColSpan to be the layout listColSpan', function() {
            expect(view.listColSpan).toBe(layout.listColSpan);
        });

        it('should set el to be the layout el', function() {
            expect(view.el).toBe(layout.el);
        });

        it('should call setElement', function() {
            view.initialize({
                meta: {
                    panels: [{
                        fields: ['field1', 'field2']
                    }]
                },
                model: new Backbone.Model(),
                layout: {
                    listColSpan: 2
                }
            });
            expect(view.setElement).toHaveBeenCalled();
        });
    });

    describe('_onDeleteBundleBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            view.context.parent = SugarTest.app.context.getContext();
            sinon.collection.spy(view.context.parent, 'trigger');
            view._onDeleteBundleBtnClicked();

            expect(view.context.parent.trigger).toHaveBeenCalledWith('quotes:group:delete');
        });
    });

    describe('_onCreateQLIBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.collection.spy(view.context, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateQLIBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create:qli:viewModel1');
        });
    });

    describe('_onCreateCommentBtnClicked()', function() {
        it('should trigger quotes:group:delete event', function() {
            sinon.collection.spy(view.context, 'trigger');
            view.model.set('id', 'viewModel1');
            view._onCreateCommentBtnClicked();

            expect(view.context.trigger).toHaveBeenCalledWith('quotes:group:create:note:viewModel1');
        });
    });
});
