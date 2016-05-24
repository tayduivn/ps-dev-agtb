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
                expectedFromName: 'Will Westin',
                expectedFromEmail: 'will@example.com',
                expectedFormattedValue: 'Will Westin'
            },
            {
                value: new Backbone.Collection([{
                    name: '',
                    email_address_used: 'will@example.com'
                }]),
                expectedFromName: '',
                expectedFromEmail: 'will@example.com',
                expectedFormattedValue: 'will@example.com'
            },
            {
                value: new Backbone.Collection([{
                    email_address_used: 'will@example.com'
                }]),
                expectedFromName: '',
                expectedFromEmail: 'will@example.com',
                expectedFormattedValue: 'will@example.com'
            },
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin',
                    email_address_used: ''
                }]),
                expectedFromName: 'Will Westin',
                expectedFromEmail: '',
                expectedFormattedValue: 'Will Westin'
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
                expectedFromName: 'Will Westin',
                expectedFromEmail: 'primary@valid.com',
                expectedFormattedValue: 'Will Westin'
            },
            {
                value: new Backbone.Collection([{
                    name: 'Will Westin'
                }]),
                expectedFromName: 'Will Westin',
                expectedFromEmail: '',
                expectedFormattedValue: 'Will Westin'
            },
            {
                value: new Backbone.Collection([{}]),
                expectedFromName: '',
                expectedFromEmail: '',
                expectedFormattedValue: ''
            },
            {
                value: null,
                expectedFromName: '',
                expectedFromEmail: '',
                expectedFormattedValue: ''
            }
        ], function(data) {
            it('should set instance variables and return formatted value properly', function() {
                var actual = field.format(data.value);

                expect(field.fromName).toEqual(data.expectedFromName);
                expect(field.fromEmail).toEqual(data.expectedFromEmail);
                expect(actual).toEqual(data.expectedFormattedValue);
            });
        });
    });
});
