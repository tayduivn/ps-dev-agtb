describe('ProductBundles.Base.Views.QuoteDataGroupList', function() {
    var app,
        view,
        viewMeta,
        viewLayoutModel,
        layout,
        layoutDefs;

    beforeEach(function() {
        app = SugarTest.app;
        viewLayoutModel = new Backbone.Model({
            related_records: [
                new Backbone.Model({id: 'test1'}),
                new Backbone.Model({id: 'test2'}),
                new Backbone.Model({id: 'test3'})
            ]
        });
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
            selection: {
                type: 'multi',
                actions: [
                    {
                        name: 'edit_row_button',
                        type: 'button'
                    },
                    {
                        name: 'delete_row_button',
                        type: 'button'
                    }
                ]
            }
        };

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        view = SugarTest.createView('base', 'ProductBundles', 'quote-data-group-list', viewMeta, null, true, layout);
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
                meta: viewMeta,
                model: new Backbone.Model(),
                layout: {
                    listColSpan: 2
                }
            });
            expect(view.setElement).toHaveBeenCalled();
        });

        it('should set rowCollection based on related_records', function() {
            expect(view.rowCollection.length).toBe(3);
        });
    });
});
