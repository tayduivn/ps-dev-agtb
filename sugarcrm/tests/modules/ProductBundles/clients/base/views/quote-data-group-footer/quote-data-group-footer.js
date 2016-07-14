describe('ProductBundles.Base.Views.QuoteDataGroupFooter', function() {
    var app,
        view,
        viewMeta,
        viewLayoutModel,
        layout,
        layoutDefs;

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
        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-footer', viewMeta, null, true, layout);
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

        it('should set listColSpan to be the layout listColSpan + 1', function() {
            expect(view.listColSpan).toBe(layout.listColSpan + 1);
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
});
