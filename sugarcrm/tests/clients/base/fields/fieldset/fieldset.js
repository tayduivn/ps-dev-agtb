describe('fieldset field', function() {

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

        it('should update the CSS classes of the itself and its child fields', function() {
            var editClass = 'edit',
                viewClass = 'view',
                addViewClassSpy = sinon.spy(field, '_addViewClass'),
                removeViewClassSpy = sinon.spy(field, '_removeViewClass');

            _.each(field.fields, function(childField) {
                sinon.spy(childField, '_addViewClass');
                sinon.spy(childField, '_removeViewClass');
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

            addViewClassSpy.restore();
            removeViewClassSpy.restore();

            _.each(field.fields, function(childField) {
                childField._addViewClass.restore();
                childField._removeViewClass.restore();
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
            field.getPlaceholder();
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
