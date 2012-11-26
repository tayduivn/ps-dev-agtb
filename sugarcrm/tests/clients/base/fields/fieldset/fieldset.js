describe('fieldset field', function() {

    var model;
    var field;

    beforeEach(function() {

        model = new Backbone.Model({
            address_street: '1 Foo Way',
            address_city: 'Castro Valley',
            address_state: 'CA',
            address_postalcode: '94546',
            address_country: 'USA'
        });

        var fieldDef = {
            css_class: 'address_fields',
            fields: [
                'address_street',
                'address_city',
                'address_state',
                'address_postalcode',
                'address_country'
            ]
        };
        field = SugarTest.createField('base', 'fieldset', 'fieldset', 'edit', fieldDef);
    });

    afterEach(function() {
        model = null;
        field = null;
    });

    it('should initialize all the private properties correctly', function() {

        field.fields = ['fields must be initialized'];

        field.initialize(field.options);
        var expectedFields = [];
        expect(field.fields).toEqual(expectedFields);
    });

    it('should render fields html nested on the fieldset', function() {

        var html = field.getPlaceholder();
        var regex = new RegExp('<span sfuuid="' + field.sfId + '"><span (.*)>(.*)</span></span>');
        expect(html).toMatch(regex);
    });

    it('should render nested fields on render', function() {

        _.each(field.fields, function(childField) {
            sinon.spy(childField, 'render');
        });

        field.render();

        _.each(field.fields, function(childField) {
            expect(childField.render).toHaveBeenCalled();
            childField.render.restore();
        });
    });

    it('should render with css classes', function() {

        var addClass = sinon.spy(field.getFieldElement(), 'addClass');

        field.render();

        expect(addClass).toHaveBeenCalled();
        expect(field.getFieldElement().hasClass('address_fields')).toBeTruthy();

        addClass.restore();
    });
});
