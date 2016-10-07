describe('Quotes.Base.Views.QuoteDataListHeader', function() {
    var app;
    var view;
    var viewMeta;
    var metaPanels;

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
        metaPanels = [{
            fields: [
                'field1', 'field2', 'field3', 'field4'
            ]
        }];
        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: metaPanels
            };
        });
        view = SugarTest.createView('base', 'Quotes', 'quote-data-list-header', viewMeta, null, true);
    });

    afterEach(function() {
        sinon.collection.restore();
        metaPanels = null;
        viewMeta = null;
        view.dispose();
        view = null;
    });

    describe('initialize()', function() {
        it('should set className', function() {
            expect(view.className).toBe('quote-data-list-header');
        });

        it('should set this.meta.panels', function() {
            expect(view.meta.panels).toEqual(metaPanels);
        });

        it('should set this._fields', function() {
            expect(view._fields).toEqual(_.flatten(_.pluck(metaPanels, 'fields')));
        });
    });
});
