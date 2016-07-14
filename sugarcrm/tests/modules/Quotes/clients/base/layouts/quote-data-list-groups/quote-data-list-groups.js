describe('Quotes.Base.Layouts.QuoteDataListGroups', function() {
    var app,
        layout;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        sinon.collection.stub(app.metadata, 'getView', function() {
            return {
                panels: [{
                    fields: [
                        'field1', 'field2', 'field3', 'field4'
                    ]
                }]
            };
        });

        layout = SugarTest.createLayout('base', 'Quotes', 'quote-data-list-groups', null, null, true);
        sinon.collection.stub(layout, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        layout.dispose();
        layout = null;
    });

    describe('initialize()', function() {
        it('should have className', function() {
            expect(layout.className).toBe('table dataTable quote-data-list-table');
        });

        it('should have tagName', function() {
            expect(layout.tagName).toBe('table');
        });
    });
});
