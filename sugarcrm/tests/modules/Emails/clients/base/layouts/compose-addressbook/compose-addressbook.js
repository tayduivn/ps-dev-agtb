describe('Emails.Base.Layout.ComposeAddressbook', function() {
    var app,
        layout,
        dataProvider;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        layout = SugarTest.createLayout('base', 'Emails', 'compose-addressbook', null, null, true);
    });
    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        SugarTest.testMetadata.dispose();
    });

    describe('load data', function() {
        var stubLayoutLoadData;

        beforeEach(function() {
            stubLayoutLoadData = sinon.stub(app.view.Layout.prototype, 'loadData');
        });
        afterEach(function() {
            stubLayoutLoadData.restore();
        });

        dataProvider = [
            {
                message: 'Should call loadData with all allowed modules when options.module_list is empty.',
                options: {}
            },
            {
                message: 'Should call loadData with all allowed modules when options.module_list only contains disallowed modules.',
                options: {module_list: ['Foo', 'Bar']}
            }
        ];
        _.each(dataProvider, function(data) {
            it(data.message, function() {
                layout.loadData(data.options, true);
                var expected = {module_list: layout.context.get('allowed_modules')};
                expect(stubLayoutLoadData.calledWithExactly(expected, true)).toBeTruthy();
            });
        }, this);

        dataProvider = [
            {
                message: 'Should call loadData with only allowed modules when options.module_list is an array containing disallowed modules.',
                options: {module_list: ['Accounts', 'Contacts', 'Foo', 'Bar']}
            },
            {
                 message: 'Should call loadData with only allowed modules when options.module_list is a string containing disallowed modules.',
                options: {module_list: 'Accounts,Contacts,Foo,Bar'}
            }
        ];
        _.each(dataProvider, function(data) {
            it(data.message, function() {
                layout.loadData(data.options, true);
                var expected = {module_list: ['Accounts', 'Contacts']};
                expect(stubLayoutLoadData.calledWithExactly(expected, true)).toBeTruthy();
            });
        }, this);
    });
});
