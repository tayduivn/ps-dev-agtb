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
        var $element = $(field.getPlaceholder().toString());
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
    describe('switch_on_click', function() {
        beforeEach(function() {
            field.dispose();
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', {
                'name': 'main_dropdown',
                'type': 'actiondropdown',
                'switch_on_click': true,
                'buttons': [
                    {
                        'type' : 'rowaction',
                        'name' : 'test1'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test2'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test4'
                    }
                ]
            }, moduleName);
            var $element = $(field.getPlaceholder().toString());
            field.setElement($element);
            field.render();
        });
        afterEach(function() {
            field.dispose();
        });
        it('should switch the selected action against the default action', function() {
            var defaultAction = 0,
                selectedAction = 2,
                actualDefaultButton = field.fields[defaultAction],
                actualSelectedButton = field.fields[selectedAction];
            expect(actualDefaultButton.def.name).toBe('test1');

            //click dropdown toggle to display the dropdown actions
            field.$('[data-toggle=dropdown]').click();
            expect(actualSelectedButton.def.name).toBe('test3');

            //after the dropdown action is clicked, both buttons are switched
            actualSelectedButton.$el.click();
            expect(field.fields[defaultAction].def.name).toBe('test3');
            expect(field.fields[selectedAction].def.name).toBe('test1');

            //the default button place underneath the dropdown
            var $actualDropdown = field.$('.dropdown-menu'),
                searchSelectedButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + actualSelectedButton.sfId + '"]'),
                searchDefaultButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + actualDefaultButton.sfId + '"]');
            expect(searchSelectedButtonOnDropdown.length).toBe(0);
            expect(searchDefaultButtonOnDropdown.length).toBe(1);

            //the selected button place on the default action
            var $defaultPlaceholder = field.$('[data-toggle=dropdown]').prev(),
                searchSelectedButtonOnDefault = $defaultPlaceholder
                    .is('span[sfuuid="' + actualSelectedButton.sfId + '"]'),
                searchDefaultButtonOnDefault = $defaultPlaceholder
                    .is('span[sfuuid="' + actualDefaultButton.sfId + '"]');
            expect(searchSelectedButtonOnDefault).toBe(true);
            expect(searchDefaultButtonOnDefault).toBe(false);
        });
    });
    describe('no_default_action', function() {
        beforeEach(function() {
            field.dispose();
            field = SugarTest.createField('base', 'main_dropdown', 'actiondropdown', 'detail', {
                'name': 'main_dropdown',
                'type': 'actiondropdown',
                //'switch_on_click' option must be ignored when no_default_action is enabled
                'switch_on_click': true,
                'no_default_action': true,
                'buttons': [
                    {
                        'type' : 'rowaction',
                        'name' : 'test1'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test2'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test3'
                    },
                    {
                        'type' : 'rowaction',
                        'name' : 'test4'
                    }
                ]
            }, moduleName);
            var $element = $(field.getPlaceholder().toString());
            field.setElement($element);
            field.render();
        });
        afterEach(function() {
            field.dispose();
        });
        it('should place all buttons underneath the dropdown actions', function() {
            //click dropdown toggle to display the dropdown actions
            field.$('[data-toggle=dropdown]').click();
            var $defaultPlaceholder = field.$('[data-toggle=dropdown]').prev();

            //the default placeholder has to be empty
            expect($defaultPlaceholder.length).toBe(0);
            var $actualDropdown = field.$('.dropdown-menu');

            //all button fields have to place underneath the dropdown actions
            _.each(field.fields, function(button) {
                var checkButtonOnDropdown = $actualDropdown
                    .find('span[sfuuid="' + button.sfId + '"]');
                expect(checkButtonOnDropdown.length).toBe(1);
            }, this);
        });

        it('should not switch the selected buttons if no_default_action is true', function() {
            var defaultAction = 0,
                selectedAction = 2,
                actualDefaultButton = field.fields[defaultAction],
                actualSelectedButton = field.fields[selectedAction];
            expect(actualDefaultButton.def.name).toBe('test1');

            //click dropdown toggle to display the dropdown actions
            field.$('[data-toggle=dropdown]').click();
            expect(actualSelectedButton.def.name).toBe('test3');

            //after the dropdown action is clicked, both buttons are switched
            actualSelectedButton.$el.click();

            //after an action is selected, the button should remain as original condition
            expect(field.fields[defaultAction].def.name).toBe('test1');
            expect(field.fields[selectedAction].def.name).toBe('test3');
        });
    });
});
