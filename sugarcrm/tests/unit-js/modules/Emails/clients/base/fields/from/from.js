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

    describe('format()', function() {
        using('Different Model Values', [
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin',
                    email_address_used: 'will@example.com'
                }]),
                expectedFormattedValue: 'Will Westin',
                expectedTooltipText: 'Will Westin <will@example.com>'
            },
            {
                value: new Backbone.Collection([{
                    name: '',
                    email_address_used: 'will@example.com'
                }]),
                expectedFormattedValue: 'will@example.com',
                expectedTooltipText: 'will@example.com'
            },
            {
                value: new Backbone.Collection([{
                    email_address_used: 'will@example.com'
                }]),
                expectedFormattedValue: 'will@example.com',
                expectedTooltipText: 'will@example.com'
            },
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin',
                    email_address_used: ''
                }]),
                expectedFormattedValue: 'Will Westin',
                expectedTooltipText: 'Will Westin'
            },
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin',
                    email_address_used: '',
                    email: [{
                        email_address: 'primary@valid.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                }]),
                expectedFormattedValue: 'Will Westin',
                expectedTooltipText: 'Will Westin <primary@valid.com>'
            },
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin'
                }]),
                expectedFormattedValue: 'Will Westin',
                expectedTooltipText: 'Will Westin'
            },
            {
                value: new Backbone.Collection([{}]),
                expectedFormattedValue: '',
                expectedTooltipText: ''
            },
            {
                value: null,
                expectedFormattedValue: '',
                expectedTooltipText: ''
            }
        ], function(data) {
            it('should set instance variables and return formatted value properly', function() {
                var actual = field.format(data.value);

                expect(actual).toEqual(data.expectedFormattedValue);
                expect(field.tooltipText).toEqual(data.expectedTooltipText);
            });
        });
    });
});
