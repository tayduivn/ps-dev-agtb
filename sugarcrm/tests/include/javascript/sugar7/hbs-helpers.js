describe('Sugar7.View.Handlebars.helpers', function() {
    var app, savedHelpers;

    beforeEach(function () {
        app = SugarTest.app;
        savedHelpers = Handlebars.helpers;
        SugarTest.loadFile('../include/javascript/sugar7', 'hbs-helpers', 'js', function(d) {
            app.events.off('app:init');
            eval(d);
            app.events.trigger('app:init');
        });
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
    });

    afterEach(function() {
        Handlebars.helpers = savedHelpers;
        app = null;
        SugarTest.testMetadata.dispose();
        sinon.collection.restore();
    });

    describe('moduleIconLabel', function() {
        using('different values', [
            {
                // Exists in app_list_strings['moduleIconList']
                module: 'Accounts',
                expected: 'Ac'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has LBL_MODULE_NAME_SINGULAR defined.
                module: 'Contacts',
                expected: 'Co'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Doesn't have LBL_MODULE_NAME_SINGULAR defined.
                // Has LBL_MODULE_NAME defined.
                module: 'Leads',
                expected: 'Le'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has LBL_MODULE_NAME_SINGULAR defined.
                // Is a multi-word module.
                // Note: Product Templates maps to Product Catalog, hence 'PC'.
                module: 'ProductTemplates',
                expected: 'PC'
            },
            {
                // Doesn't exist in app_list_strings['moduleIconList']
                // Has no LBL_MODULE_NAME labels defined in mod strings.
                module: 'FakeModule',
                expected: 'Fa'
            }
        ], function(options) {
            it('should return an appropriate 2-letter icon label', function() {
                expect(Handlebars.helpers.moduleIconLabel(options.module)).toBe(options.expected);
            });
        });
    });
});
