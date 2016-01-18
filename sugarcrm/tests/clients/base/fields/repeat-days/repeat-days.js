describe('View.Fields.Base.RepeatDaysField', function() {
    var app, field, createFieldProperties, sandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'field', 'enum');
        SugarTest.testMetadata.set();
        createFieldProperties = {
            client: 'base',
            name: 'repeat_days',
            type: 'repeat-days',
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

    it('should default enum options to numbers 1 to 31', function() {
        expect(_.keys(field.def['options']).length).toEqual(31);
        expect(field.def['options'][1]).toEqual('1');
        expect(field.def['options'][31]).toEqual('31');
    });

    using('values on the model',[
        {
            inputValue: '11,1,2',
            expected: ['1','2','11']
        },
        {
            inputValue: '',
            expected: []
        },
        {
            inputValue: undefined,
            expected: undefined
        }
    ], function (value) {
        it('should format value on model to sorted array for select2', function() {
            var actual = field.format(value.inputValue);
            expect(actual).toEqual(value.expected);
        });
    });

    using('values on the DOM',[
        {
            inputValue: ['2','6','24'],
            expected: '2,6,24'
        },
        {
            inputValue: [],
            expected: ''
        },
        {
            inputValue: undefined,
            expected: undefined
        }
    ], function (value) {
        it('should unformat value on DOM to string for the model', function() {
            var actual = field.unformat(value.inputValue);
            expect(actual).toEqual(value.expected);
        });
    });

    using('variations of repeat selector and repeat days values',[
        {
            expectation: 'should error when repeat days has no value and repeat_selector is Each',
            repeatSelector: 'Each',
            repeatDays: '',
            isErrorExpected: true
        },
        {
            expectation: 'should not error when repeat days has a value and repeat_selector is Each',
            repeatSelector: 'Each',
            repeatDays: '31',
            isErrorExpected: false
        },
        {
            expectation: 'should not error when repeat days has no value and repeat_selector is not Each',
            repeatSelector: 'On',
            repeatDays: '',
            isErrorExpected: false
        }
    ], function (value) {
        it(value.expectation, function() {
            var errors = {};
            field.model.set('repeat_selector', value.repeatSelector, {silent: true});
            field.model.set(field.name, value.repeatDays, {silent: true});
                field._doValidateRepeatDays(null, errors, $.noop);
            expect(!_.isEmpty(errors)).toBe(value.isErrorExpected);
        });
    });
});
