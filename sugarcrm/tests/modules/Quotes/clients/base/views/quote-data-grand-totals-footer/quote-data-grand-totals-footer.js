describe('Quotes.Base.Views.QuoteDataGrandTotalsFooter', function() {
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
        layout = SugarTest.createLayout('base', 'Quotes', 'default', layoutDefs);
        layout.model = viewLayoutModel;
        layout.listColSpan = 3;
        viewMeta = {
            panels: [{
                fields: ['field1', 'field2']
            }]
        };
        view = SugarTest.createView('base', 'Quotes', 'quote-data-grand-totals-footer', viewMeta, null, true, layout);
    });

    afterEach(function() {
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-grand-totals-footer');
        });
    });
});
