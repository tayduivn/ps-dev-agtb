describe('Base.Field.Fieldset', function() {

    describe('normal render of child fields', function() {
        var field;

        beforeEach(function() {

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
            field.dispose();
            field = null;
            sinon.collection.restore();
            Handlebars.templates = {};
        });

        it('should initialize all the private properties correctly', function() {

            field.fields = ['fields must be initialized'];

            field.initialize(field.options);
            var expectedFields = [];
            expect(field.fields).toEqual(expectedFields);
        });

        it('should render nested fields on render', function() {
            field._getChildFields();
            _.each(field.fields, function(childField) {
                sinon.collection.spy(childField, 'render');
            });

            field.render();

            _.each(field.fields, function(childField) {
                expect(childField.render).toHaveBeenCalled();
            });
        });

        it('should render with css classes', function() {

            var addClass = sinon.collection.spy(field.getFieldElement(), 'addClass');

            field.render();

            expect(addClass).toHaveBeenCalled();
            expect(field.getFieldElement().hasClass('address_fields')).toBeTruthy();
        });

        it('should update the CSS classes of the itself and its child fields', function() {
            var editClass = 'edit',
                viewClass = 'view',
                addViewClassSpy = sinon.collection.spy(field, '_addViewClass'),
                removeViewClassSpy = sinon.collection.spy(field, '_removeViewClass');

            field._getChildFields();
            _.each(field.fields, function(childField) {
                sinon.collection.spy(childField, '_addViewClass');
                sinon.collection.spy(childField, '_removeViewClass');
            });

            field.render();
            expect(addViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeFalsy();
            _.each(field.fields, function(childField) {
                expect(childField._addViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeFalsy();
            });

            field.setMode('view');
            expect(removeViewClassSpy.calledWith(editClass)).toBeTruthy();
            expect(addViewClassSpy.calledWith(viewClass)).toBeTruthy();
            _.each(field.fields, function(childField) {
                expect(childField._removeViewClass.calledWith(editClass)).toBeTruthy();
                expect(childField._addViewClass.calledWith(viewClass)).toBeTruthy();
            });
        });

        it('should not show no data if not readonly', function() {
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });
    });

    describe('render with nodata/readonly fields', function() {
        var field;

        beforeEach(function() {
            var fieldDef = {
                readonly: true,
                fields: [
                    {
                        name: 'date_entered'
                    },
                    {
                        name: 'created_by'
                    }
                ]
            };
            field = SugarTest.createField('base', 'fieldset', 'fieldset', 'edit', fieldDef, 'Contacts');
            field.render();
        });

        afterEach(function() {
            field.dispose();
            field = null;
        });

        it('should show no data if readonly and none of its data fields have data', function() {
            var actual = _.result(field, 'showNoData');
            expect(actual).toBe(true);
            //after one of the child field's value is assigned, it should fall back to false
            field.model.set('date_entered', '1999-01-01T12:00');
            actual = _.result(field, 'showNoData');
            expect(actual).toBe(false);
        });
    });
});
