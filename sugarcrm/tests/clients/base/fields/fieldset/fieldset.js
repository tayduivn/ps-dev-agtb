describe('fieldset field', function () {

    var model,
        field;

    beforeEach(function () {

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

    afterEach(function () {
        model = null;
        field = null;
    });

    it('should initialize all the private properties correctly', function () {

        field.fields = ['fields must be initialized'];

        field.initialize(field.options);
        var expectedFields = [];
        expect(field.fields).toEqual(expectedFields);
    });

    it('should render fields html nested on the fieldset', function () {

        var html = field.getPlaceholder();
        var regex = new RegExp('<span sfuuid="' + field.sfId + '"><span (.*)>(.*)</span></span>');
        expect(html).toMatch(regex);
    });

    it('should render nested fields on render', function () {

        _.each(field.fields, function (childField) {
            sinon.spy(childField, 'render');
        });

        field.render();

        _.each(field.fields, function (childField) {
            expect(childField.render).toHaveBeenCalled();
            childField.render.restore();
        });
    });

    it('should render with css classes', function () {

        var addClass = sinon.spy(field.getFieldElement(), 'addClass');

        field.render();

        expect(addClass).toHaveBeenCalled();
        expect(field.getFieldElement().hasClass('address_fields')).toBeTruthy();

        addClass.restore();
    });

    describe('Edit mode css class', function () {
        var editClass = 'edit';
        var viewClass = 'view';

        it('should update the CSS classes of the itself and its child fields', function () {
            var addViewClassSpy = sinon.spy(field, '_addViewClass'),
                removeViewClassSpy = sinon.spy(field, '_removeViewClass');

            _.each(field.fields, function (childField) {
                sinon.spy(childField, '_addViewClass');
                sinon.spy(childField, '_removeViewClass');
            });

            field.render();
            expect(addViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeFalsy();
            _.each(field.fields, function (childField) {
                expect(childField._addViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeFalsy();
            });

            field.setMode('view');
            expect(removeViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeTruthy();
            _.each(field.fields, function (childField) {
                expect(childField._removeViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeTruthy();
            });

            addViewClassSpy.restore();
            removeViewClassSpy.restore();

            _.each(field.fields, function (childField) {
                childField._addViewClass.restore();
                childField._removeViewClass.restore();
            });
        });
    });
});
