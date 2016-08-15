describe('Emails.Field.From', function() {
    var app;
    var field;

    beforeEach(function() {
        app = SugarTest.app;

        field = SugarTest.createField({
            name: 'from',
            type: 'from',
            viewName: 'list',
            module: 'Emails',
            loadFromModule: true
        });
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
    });

    describe('format', function() {
        using('Different Model Values', [
            {
                value: [new Backbone.Model({
                    name: 'Will Westin',
                    email_address: 'will@example.com'
                })],
                expectedTooltipText: 'Will Westin <will@example.com>'
            },
            {
                value: [new Backbone.Model({
                    name: '',
                    email_address: 'will@example.com'
                })],
                expectedTooltipText: 'will@example.com'
            },
            {
                value: [new Backbone.Model({
                    email_address: 'will@example.com'
                })],
                expectedTooltipText: 'will@example.com'
            },
            {
                value: [new Backbone.Model({
                    name: 'Will Westin',
                    email_address: ''
                })],
                expectedTooltipText: 'Will Westin'
            },
            {
                value: [new Backbone.Model({
                    name: 'Will Westin'
                })],
                expectedTooltipText: 'Will Westin'
            },
            {
                value: [],
                expectedTooltipText: ''
            },
            {
                value: null,
                expectedTooltipText: ''
            }
        ], function(data) {
            it('should set tooltip instance variable properly', function() {
                field.format(data.value);

                expect(field.tooltipText).toEqual(data.expectedTooltipText);
            });
        });
    });
});
