describe('Quotes.Base.Views.QuoteDataListHeader', function() {
    var app,
        view,
        viewMeta;

    beforeEach(function() {
        app = SugarTest.app;

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
        view = SugarTest.createView('base', 'Quotes', 'quote-data-list-header', viewMeta, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('flex-list-view scroll-width left-actions quote-data-list-header');
        });
    });
});
