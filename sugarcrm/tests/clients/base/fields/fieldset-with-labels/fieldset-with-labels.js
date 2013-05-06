describe("Fieldset With Labels", function() {
    var field;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('fieldset-with-labels', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('base', 'field', 'base', 'edit');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.testMetadata.set();

        field = SugarTest.createField('base', 'foo', 'fieldset-with-labels', 'detail', {
            name: 'foo',
            type: 'fieldset-with-labels',
            fields: [{
                name: 'name',
                type: 'text',
                label: 'foo'
            }, {
                name: 'description',
                type: 'text',
                label: 'bar'
            }]
        });
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
    });

    describe('Render', function() {
        it('should create two fields', function() {
            field.render();

            expect(field.fields.length).toBe(2);
        });

        it('should display labels for all fields', function() {
            field.render();

            expect(field.$('.record-label').length).toBe(2);
            expect(field.$('.record-label').first().text()).toBe('foo');
            expect(field.$('.record-label').last().text()).toBe('bar');
        });
    });

    describe('Edit mode', function() {
        it('should trigger edit mode for all fields', function() {
            field.render();

            expect(field.fields[0].tplName).toBe('detail');
            expect(field.fields[1].tplName).toBe('detail');
            expect(field.fields[0].$('input').length).toBe(0);
            expect(field.fields[1].$('input').length).toBe(0);

            field.setMode('edit');

            expect(field.fields[0].tplName).toBe('edit');
            expect(field.fields[1].tplName).toBe('edit');
            expect(field.fields[0].$('input').length).toBe(1);
            expect(field.fields[1].$('input').length).toBe(1);
        });
    });
});
