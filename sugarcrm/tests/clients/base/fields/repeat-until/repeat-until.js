describe('View.Fields.Base.RepeatUntilField', function() {
    var app, field, createFieldProperties, sandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'date');
        SugarTest.testMetadata.set();
        createFieldProperties = {
            client: 'base',
            name: 'repeat_until',
            type: 'repeat-until',
            viewName: 'edit',
            module: module
        };
        field = SugarTest.createField(createFieldProperties);
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
    });

    using('different repeat until and end date values', [
        {
            expectation: 'should error when repeat until is before end date',
            action: 'edit',
            repeatUntil: '2014-11-17',
            endDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: true
        },
        {
            expectation: 'should error when repeat until is same as end date',
            action: 'edit',
            repeatUntil: '2014-11-18',
            endDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat until is after end date',
            action: 'edit',
            repeatUntil: '2014-11-19',
            endDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat until is not set',
            action: 'edit',
            repeatUntil: '',
            endDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when not in edit mode',
            action: 'detail',
            repeatUntil: '2014-11-17',
            endDate: '2014-11-18T13:00:00-05:00',
            isErrorExpected: false
        }
    ], function(value) {
        it(value.expectation, function() {
            var errors = {};

            field.model.fields = {
                'date_end': {
                    name: 'date_end',
                    type: 'date'
                }
            };
            field.action = value.action;
            field.model.set('repeat_until', value.repeatUntil, {silent: true});
            field.model.set('date_end', value.endDate, {silent: true});
            field._doValidateRepeatUntil(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
