describe('ProductBundles.Base.Fields.QuoteFooterInput', function() {
    var field,
        fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-footer-input',
            label: 'testLbl'
        };
        field = SugarTest.createField('base', 'quote-footer-input', 'quote-footer-input',
            'detail', fieldDef, 'ProductBundles', null, null, true);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('format()', function() {
        it('should set value_amount and value_percent', function() {
            field.format();
            expect(field.value_amount).toBe('0.00');
            expect(field.value_percent).toBe('0%');
        });
    });
});
