describe('Quotes.Base.Fields.QuoteFooter', function() {
    var field,
        fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-footer',
            label: 'testLbl',
            css_class: 'test-css-class'
        };
        field = SugarTest.createField('base', 'quote-footer', 'quote-footer',
            'detail', fieldDef, 'Quotes', null, null, true);

        sinon.collection.stub(field, '_super', function() {});
    });

    afterEach(function() {
        sinon.collection.restore();
        field.dispose();
        field = null;
    });

    describe('initialize()', function() {
        it('should set css_class if it exists', function() {
            field.initialize({
                def: fieldDef
            });
            expect(field.css_class).toBe('test-css-class');
        });
    });
});
