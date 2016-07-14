describe('Products.Base.Fields.QuoteDataActionmenu', function() {
    var field,
        fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-data-actionmenu',
            label: 'testLbl',
            css_class: '',
            buttons: ['button1'],
            no_default_action: true
        };
        field = SugarTest.createField('base', 'quote-data-actionmenu', 'quote-data-actionmenu',
            'detail', fieldDef, 'Products', null, null, true);
    });

    afterEach(function() {
        field.dispose();
        field = null;
    });

    describe('_getChildFieldsMeta()', function() {
        it('should return a copy of the buttons', function() {
            expect(field._getChildFieldsMeta()).toEqual(['button1']);
        });
    });
});
