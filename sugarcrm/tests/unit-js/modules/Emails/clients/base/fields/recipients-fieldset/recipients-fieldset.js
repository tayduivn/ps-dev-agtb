/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
describe('Emails.RecipientsFieldsetField', function() {
    var app;
    var field;
    var children;
    var context;
    var model;
    var sandbox;
    var to;
    var cc;
    var bcc;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadComponent('base', 'field', 'fieldset');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'recipient-options', 'Emails');
        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadComponent('base', 'field', 'email-recipients', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadComponent('base', 'field', 'outbound-email', 'Emails');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('enum', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        children = [
            SugarTest.createField({
                name: 'outbound_email_id',
                type: 'outbound-email',
                viewName: 'detail',
                fieldDef: {
                    name: 'outbound_email_id',
                    type: 'enum',
                    label: 'LBL_FROM',
                    options: {
                        '1': 'SugarCRM Sales <sales@sugarcrm.com>'
                    }
                },
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'to',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'cc',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            }),
            SugarTest.createField({
                name: 'bcc',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            })
        ];

        to = [
            app.data.createBean('Contacts', {
                _link: 'contacts_to',
                id: _.uniqueId(),
                name: 'Herbert Yates',
                email_address_used: 'hyates@example.com'
            }),
            app.data.createBean('Contacts', {
                _link: 'contacts_to',
                id: _.uniqueId(),
                name: 'Walter Quigley',
                email_address_used: 'wquigley@example.com'
            })
        ];

        cc = [
            app.data.createBean('Contacts', {
                _link: 'contacts_cc',
                id: _.uniqueId(),
                name: 'Wyatt Archer',
                email_address_used: 'warcher@example.com'
            })
        ];

        bcc = [
            app.data.createBean('Contacts', {
                _link: 'contacts_bcc',
                id: _.uniqueId(),
                name: 'Earl Hatcher',
                email_address_used: 'ehatcher@example.com'
            })
        ];

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('format', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');
        });

        it('should not add the label for TO', function() {
            var expected = 'Herbert Yates, Walter Quigley; CC: Wyatt Archer; BCC: Earl Hatcher';
            var actual;

            field.model.set('to', to);
            field.model.set('cc', cc);
            field.model.set('bcc', bcc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show TO', function() {
            var expected = 'Herbert Yates, Walter Quigley';
            var actual;

            field.model.set('to', to);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show CC', function() {
            var expected = 'CC: Wyatt Archer';
            var actual;

            field.model.set('cc', cc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });

        it('should only show BCC', function() {
            var expected = 'BCC: Earl Hatcher';
            var actual;

            field.model.set('bcc', bcc);
            field.render();

            actual = field.getFormattedValue();
            expect(actual).toBe(expected);
        });
    });

    describe('rendering in edit mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');

            sandbox.stub(field.view, 'getField');
            field.view.getField.withArgs('outbound_email_id').returns(
                _.findWhere(field.fields, {name: 'outbound_email_id'})
            );
        });

        it('should add the toggle buttons', function() {
            var $cc;
            var $bcc;

            sandbox.spy(app.template, 'getField').withArgs(field.type, 'recipient-options', field.module);
            field.render();

            $cc = field.$('button[data-toggle-field=cc]');
            $bcc = field.$('button[data-toggle-field=bcc]');

            expect(app.template.getField.withArgs(field.type, 'recipient-options', field.module)).toHaveBeenCalled();
            expect($cc.length).toBe(1);
            expect($cc.hasClass('active')).toBe(false);
            expect($bcc.length).toBe(1);
            expect($bcc.hasClass('active')).toBe(false);
        });

        using('without recipients', ['cc', 'bcc'], function(fieldName) {
            it('should not show the ' + fieldName.toUpperCase() + ' field on render', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();

                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:resize-editor', spy);
                field.render();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(true);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(false);
                expect(spy).toHaveBeenCalledTwice();
            });
        });

        using('with recipients', ['cc', 'bcc'], function(fieldName) {
            it('should show the ' + fieldName.toUpperCase() + ' field on render', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();
                var value = fieldName === 'cc' ? cc : bcc;

                field.model.set(fieldName, value);
                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:resize-editor', spy);
                field.render();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(false);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(true);
                expect(spy).toHaveBeenCalledTwice();
            });
        });

        using('toggle buttons', ['cc', 'bcc'], function(fieldName) {
            it('should toggle the ' + fieldName.toUpperCase() + ' field when clicking the button', function() {
                var recipientField = _.findWhere(field.fields, {name: fieldName});
                var $recipientField;
                var $toggleButton;
                var spy = sandbox.spy();

                field.view.getField.withArgs(fieldName).returns(recipientField);
                field.view.on('email-recipients:resize-editor', spy);
                field.render();

                // Account for the event to have been triggered twice during
                // render.
                expect(spy.callCount).toBe(2);

                // Click the button to show the field.
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');
                $toggleButton.click();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(false);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(true);
                expect(spy.callCount).toBe(3);

                // Click the button to hide the field.
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');
                $toggleButton.click();

                $recipientField = recipientField.$el.closest('.fieldset-group');
                $toggleButton = field.$('button[data-toggle-field=' + fieldName + ']');

                expect($recipientField.length).toBe(1);
                expect($recipientField.hasClass('hide')).toBe(true);
                expect($toggleButton.length).toBe(1);
                expect($toggleButton.hasClass('active')).toBe(false);
                expect(spy.callCount).toBe(4);
            });
        });
    });

    describe('rendering in detail mode', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.fields = children;
            field.model.set('outbound_email_id', '1');
            field.model.set('to', to);
            field.model.set('cc', cc);
            field.model.set('bcc', bcc);

            sandbox.stub(field.view, 'getField');
            field.view.getField.withArgs('outbound_email_id').returns(
                _.findWhere(field.fields, {name: 'outbound_email_id'})
            );
            field.view.getField.withArgs('to').returns(_.findWhere(field.fields, {name: 'to'}));
            field.view.getField.withArgs('cc').returns(_.findWhere(field.fields, {name: 'cc'}));
            field.view.getField.withArgs('bcc').returns(_.findWhere(field.fields, {name: 'bcc'}));
        });

        it('should render the string', function() {
            var $scroll;
            var $cc;
            var $bcc;
            var $fieldsetGroups;

            field.render();
            $scroll = field.$('.scroll');
            $cc = field.$('button[data-toggle-field=cc]');
            $bcc = field.$('button[data-toggle-field=bcc]');
            $fieldsetGroups = field.$('.fieldset-group');

            expect($scroll.text()).toBe('Herbert Yates, Walter Quigley; CC: Wyatt Archer; BCC: Earl Hatcher');
            expect($cc.length).toBe(0);
            expect($bcc.length).toBe(0);
            expect($fieldsetGroups.length).toBe(0);
        });

        describe('focus on the field', function() {
            beforeEach(function() {
                field.render();
                sandbox.stub(field, 'setMode');
            });

            it('should set the mode to edit', function() {
                field.action = 'detail';
                field.$('.fieldset-field').click();

                expect(field.setMode).toHaveBeenCalledOnce();
                expect(field.setMode.calledWith('edit')).toBe(true);
            });

            it('should not change the mode when already in edit mode', function() {
                field.action = 'edit';
                field.$('.fieldset-field').click();

                expect(field.setMode).not.toHaveBeenCalled();
            });
        });
    });

    describe('blur when focus goes away', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });

            sandbox.stub(field, 'setMode');
        });

        describe('the tinymce:focus event', function() {
            it('should set the mode to detail', function() {
                field.action = 'edit';
                field.view.trigger('tinymce:focus');

                expect(field.setMode).toHaveBeenCalledOnce();
                expect(field.setMode.calledWith('detail')).toBe(true);
            });

            it('should not change the mode when already in detail mode', function() {
                field.action = 'detail';
                field.view.trigger('tinymce:focus');

                expect(field.setMode).not.toHaveBeenCalled();
            });

            it('should not change the mode when the address book is open', function() {
                field.action = 'edit';
                field._addressBookState = 'open';
                field.view.trigger('tinymce:focus');

                expect(field.setMode).not.toHaveBeenCalled();
            });
        });

        describe('the email-recipients click event', function() {
            it('should set the mode to detail', function() {
                field.action = 'edit';
                $(document).trigger('click.email-recipients');

                expect(field.setMode).toHaveBeenCalledOnce();
                expect(field.setMode.calledWith('detail')).toBe(true);
            });

            it('should not change the mode when already in detail mode', function() {
                field.action = 'detail';
                $(document).trigger('click.email-recipients');

                expect(field.setMode).not.toHaveBeenCalled();
            });

            it('should not change the mode when the address book is open', function() {
                field.action = 'edit';
                field._addressBookState = 'open';
                $(document).trigger('click.email-recipients');

                expect(field.setMode).not.toHaveBeenCalled();
            });
        });
    });

    describe('changing modes', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'recipients',
                type: 'recipients-fieldset',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            sandbox.spy(field, '_super');
            // Don't need to render the field for these test cases.
            sandbox.stub(field, 'render');
        });

        using('combinations', [
            {createMode: true, mode: 'detail', hasRecipients: true, expected: 'detail'},
            {createMode: true, mode: 'detail', hasRecipients: false, expected: 'edit'},
            {createMode: true, mode: 'edit', hasRecipients: true, expected: 'edit'},
            {createMode: true, mode: 'edit', hasRecipients: false, expected: 'edit'},
            {createMode: false, mode: 'detail', hasRecipients: true, expected: 'detail'},
            {createMode: false, mode: 'detail', hasRecipients: false, expected: 'detail'},
            {createMode: false, mode: 'edit', hasRecipients: true, expected: 'edit'},
            {createMode: false, mode: 'edit', hasRecipients: false, expected: 'edit'}
        ], function(data) {
            it('should set mode to ' + data.expected, function() {
                if (data.hasRecipients) {
                    field.model.set('to', to);
                }

                field.view.createMode = data.createMode;
                field.setMode(data.mode);

                expect(field._super.calledWith('setMode', [data.expected])).toBe(true);
            });
        });
    });
});
