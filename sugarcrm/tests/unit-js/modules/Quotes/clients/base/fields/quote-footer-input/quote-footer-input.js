describe('Quotes.Base.Fields.QuoteFooterInput', function() {
    var app;
    var field;
    var fieldDef;
    beforeEach(function() {
        app = SugarTest.app;
        fieldDef = {
            type: 'quote-footer-input',
            label: 'testLbl'
        };
        field = SugarTest.createField('base', 'quote-footer-input', 'quote-footer-input',
            'detail', fieldDef, 'Quotes', null, null, true);

        sinon.collection.stub(app.currency, 'formatAmountLocale', function() {
            return '$0.00';
        });
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        field = null;
    });

    describe('format()', function() {
        it('should set value_amount and value_percent', function() {
            field.format();
            expect(field.value_amount).toBe('$0.00');
            expect(field.value_percent).toBe('0%');
        });
    });
});
