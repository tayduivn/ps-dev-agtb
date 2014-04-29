describe('Base.Field.TextArea', function() {
    var app, field, template,
        module = 'Bugs',
        fieldName = 'foo';

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        template = SugarTest.loadHandlebarsTemplate('textarea', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        fieldDef = {
            settings: {
                max_display_chars: 8
            }
        };
        field = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        field.dispose();
        sinon.collection.restore();
    });

    describe('initialize', function() {
        it('should initialize settings to the values in `this.def` appropriately', function() {
            fieldDef.settings.collapsed = false;
            var testField = SugarTest.createField('base', fieldName, 'textarea', 'detail', fieldDef, module);

            expect(testField._settings.max_display_chars).toEqual(fieldDef.settings.max_display_chars);
            expect(testField._settings.collapsed).toEqual(fieldDef.settings.collapsed);
            expect(testField.collapsed).toEqual(testField._settings.collapsed);
        });
    });

    describe('format', function() {
        beforeEach(function() {
            field.action = 'detail';
        });

        using('various field actions', [
            {
                action: 'list',
                longExists: false
            },
            {
                action: 'edit',
                longExists: false
            },
            {
                action: 'disabled',
                longExists: false
            },
            {
                action: 'detail',
                longExists: true
            }
        ], function(value) {
            it('should only set a `long` value if in detail mode', function() {
                field.action = value.action;
                var returnVal = field.format('testvalue');

                expect(returnVal.hasOwnProperty('long')).toBe(value.longExists);
            });
        });

        // max_display_chars was kept to '8' for this test.
        using('various field values', [
            {
                fieldVal: 'testvalue',
                shortExists: true,
                expectedShortValue: 'testvalu'
            },
            {
                fieldVal: 'testvalu',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: 'testval',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: '',
                shortExists: false,
                expectedShortValue: undefined
            },
            {
                fieldVal: '結葉鮮敬。対好速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対好速'
            },
            {
                fieldVal: '結葉鮮敬。対好\n速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対好'
            },
            {
                fieldVal: '結葉鮮敬。対\n好速残',
                shortExists: true,
                expectedShortValue: '結葉鮮敬。対\n好'
            }
        ], function(value) {
            it('should set a proper `short` value if the field value exceeds `max_display_chars`', function() {
                var returnVal = field.format(value.fieldVal);

                expect(returnVal.hasOwnProperty('short')).toBe(value.shortExists);
                expect(returnVal.hasOwnProperty('long')).toBe(true);
                expect(returnVal.long).toEqual(value.fieldVal);
                expect(returnVal.short).toEqual(value.expectedShortValue);
            });
        });
    });

    describe('toggleCollapsed', function() {
        using('values', [true, false], function(value) {
            it('should toggle the value of `collapsed` and call render', function() {
                var renderStub = sinon.collection.stub(field, 'render');

                field.collapsed = value;
                field.toggleCollapsed();

                expect(field.collapsed).toBe(!value);
                expect(renderStub).toHaveBeenCalled();
            });
        });
    });
});