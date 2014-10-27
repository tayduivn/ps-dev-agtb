describe('View.Fields.Base.RepeatCountField', function() {
    var app, field, createFieldProperties,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'int');
        SugarTest.testMetadata.set();
        createFieldProperties = {
            client: 'base',
            name: 'repeat_count',
            type: 'repeat-count',
            viewName: 'edit',
            module: module
        };
        app.config.calendar = {
            maxRepeatCount: 1000
        };
        field = SugarTest.createField(createFieldProperties);
    });

    afterEach(function() {
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    using('repeat count values',[
        {
            expectation: 'should error when repeat_count is greater than the max config value',
            repeatCount: 1001,
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat_count is equal to the max config value',
            repeatCount: 1000,
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat_count is less than the max config value',
            repeatCount: 999,
            isErrorExpected: false
        }
    ], function (value) {
        it(value.expectation, function() {
            var errors = {};
            field.model.set(field.name, value.repeatCount, {silent: true});
            field._doValidateRepeatCountMax(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
