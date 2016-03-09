describe('Leads Status Field', function() {
    var app, field, model, options;

    beforeEach(function() {
        app = SugarTest.app;

        model = app.data.createBean('Leads');
        options = {
            'New': 'New',
            'Converted': 'Converted',
            'Dead': 'Dead'
        };

        SugarTest.loadComponent('base', 'field', 'enum');
        field = SugarTest.createField({
            name: 'status',
            type: 'status',
            viewName: 'detail',
            module: 'Leads',
            model: model,
            loadFromModule: true
        });
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field = null;
    });

    it('should filter out "Converted" as an option if value is not already Converted', function() {
        var newOptions;
        field.model.set('status', 'New');
        newOptions = field._filterOptions(options);
        expect(newOptions['Converted']).toBeUndefined();
    });

    it('should not filter out "Converted" as an option if value is already Converted', function() {
        var newOptions;
        field.model.set('status', 'Converted');
        newOptions = field._filterOptions(options);
        expect(newOptions['Converted']).not.toBeUndefined();
    });
});
