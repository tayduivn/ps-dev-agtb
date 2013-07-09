describe('Base.Field.Actiondropdown', function() {

    var app, field, view, moduleName = 'Contacts';

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('record', 'view', 'base');
        app = SugarTest.app;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('button', 'field', 'base', 'detail');
        SugarTest.loadHandlebarsTemplate('rowaction', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadComponent('base', 'field', 'actiondropdown');
        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();

        field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', {
            'name': 'main_dropdown',
            'type': 'actiondropdown',
            'buttons': [
                {
                    'type' : 'rowaction',
                    'name' : 'test1'
                },
                {
                    'type' : 'rowaction',
                    'name' : 'test2'
                }
            ]
        }, moduleName);
        $element = $(field.getPlaceholder().toString());
        field.setElement($element);
        field.render();

        _.each(field.fields, function(rowaction) {
            rowaction.setElement(field.$("span[sfuuid='" + rowaction.sfId + "']"));
            rowaction.render();
        });
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    it('should render button html nested on the buttons', function() {
        expect(field.fields.length).toBe(2);
        _.each(field.fields, function(button) {
            var actualPlaceholderCount = field.$el.find("span[sfuuid='" + button.sfId + "']").length;
            expect(actualPlaceholderCount).toBe(1);
        });
    });

    it('should populate proper dropdown list when a nested button is hidden', function() {

        expect(field.fields.length).toBeGreaterThan(1);


        var button = field.fields[1];
        var actualPlaceholderCount = field.$('.dropdown-menu').find('span[sfuuid="' + button.sfId + '"]').length;
        expect(actualPlaceholderCount).toBe(1);

        //second button should be at the primary position when the first one is hidden
        field.fields[0].hide();
        expect(field.fields[0].$el.is(':hidden')).toBe(true);
        actualPlaceholderCount = field.$('.dropdown-menu').find('span[sfuuid="' + button.sfId + '"]').length;
        expect(actualPlaceholderCount).toBe(0);

        //the button position should be restored when the first one is shown once again
        field.fields[0].show();
        actualPlaceholderCount = field.$('.dropdown-menu').find('span[sfuuid="' + button.sfId + '"]').length;
        expect(actualPlaceholderCount).toBe(1);
    });

    it('should have btn-group class when more than one button is visible', function() {
        expect(field.$el.hasClass('btn-group')).toBe(true);
    });

    it('should not have btn-group class when only one button is visible', function() {
        field.fields[0].hide();
        expect(field.$el.hasClass('btn-group')).toBe(false);
    });
});
