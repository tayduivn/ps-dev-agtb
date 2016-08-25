describe('ProductBundles.Base.Fields.QuoteGroupTitle', function() {
    var field;
    var fieldDef;
    beforeEach(function() {
        fieldDef = {
            type: 'quote-group-title',
            label: 'testLbl',
            css_class: 'test-css-class'
        };
        field = SugarTest.createField('base', 'quote-group-title', 'quote-group-title',
            'detail', fieldDef, 'ProductBundles', null, null, true);

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
