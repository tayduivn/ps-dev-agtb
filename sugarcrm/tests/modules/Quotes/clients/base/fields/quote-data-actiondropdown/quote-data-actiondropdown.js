describe('Quotes.Base.Fields.QuoteDataActiondropdown', function() {
    var field,
        fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-data-actiondropdown',
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        field = SugarTest.createField('base', 'quote-data-actiondropdown', 'quote-data-actiondropdown',
            'detail', fieldDef, 'Quotes', null, null, true);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('field.className', function() {
        it('should be quote-data-actiondropdown', function() {
            expect(field.className).toBe('quote-data-actiondropdown');
        });
    });
});
