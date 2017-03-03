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
describe('Emails.BaseEmailRecipientsField', function() {
    var app;
    var context;
    var field;
    var to;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'select2-result', 'Emails');
        SugarTest.loadHandlebarsTemplate('email-recipients', 'field', 'base', 'select2-selection', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

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

    describe('responding to data changes', function() {
        it('should render the field', function() {
            field = SugarTest.createField({
                name: 'to',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            field.model.set('to', to);

            expect(field.render).toHaveBeenCalledOnce();
        });

        it('should set data on Select2', function() {
            field = SugarTest.createField({
                name: 'to',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            sandbox.spy(field, 'getFormattedValue');
            sandbox.spy(field, '_decorateInvalidRecipients');
            sandbox.spy(field, '_enableDragDrop');
            field.model.set('to', to);

            expect(field.render).not.toHaveBeenCalled();
            expect(field.getFormattedValue).toHaveBeenCalledOnce();
            expect(field._decorateInvalidRecipients).toHaveBeenCalledOnce();
            expect(field._enableDragDrop).toHaveBeenCalledOnce();
            expect(field.$(field.fieldTag).select2('data').length).toBe(to.length);
        });
    });

    describe('responding to DOM changes', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'to',
                type: 'email-recipients',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();
        });

        it('should not complete the selection with an invalid link', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            event.choice = app.data.createBean('Contacts', {
                _link: 'contacts_cc',
                id: _.uniqueId(),
                name: 'Eugene Kushner',
                email_address_used: 'ek@example.com'
            });

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('to').length).toBe(0);
        });

        it('should not complete the selection when it is a duplicate', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            field.model.set('to', to);
            event.choice = to[1];

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('to').length).toBe(2);
        });

        it('should add to the collection', function() {
            var event = new $.Event('change');
            var actual;

            field.model.set('to', to);
            event.added = [
                app.data.createBean('Contacts', {
                    _link: 'contacts_to',
                    id: _.uniqueId(),
                    name: 'Ira Carr',
                    email_address_used: 'icarr@example.com'
                })
            ];

            field.$(field.fieldTag).trigger(event);
            actual = field.model.get('to');

            expect(actual.length).toBe(3);
        });

        it('should remove the recipient', function() {
            var event = new $.Event('change');

            field.model.set('to', to);
            event.removed = [to[1]];

            field.$(field.fieldTag).trigger(event);

            expect(field.model.get('to').length).toBe(1);
        });
    });

    describe('format', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'to',
                type: 'email-recipients',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
        });

        it('should format the models in the collection', function() {
            var actual;

            field.model.set('to', to);
            actual = field.getFormattedValue();

            expect(actual.length).toBe(to.length);
            expect(actual[0].name).toBe('Herbert Yates');
            expect(actual[0].email_address).toBe('hyates@example.com');
            expect(actual[1].name).toBe('Walter Quigley');
            expect(actual[1].email_address).toBe('wquigley@example.com');
        });
    });

    it('should decorate invalid recipients', function() {
        var invalid = app.data.createBean('Contacts', {
            _link: 'contacts_to',
            id: _.uniqueId(),
            name: 'Francis Humphrey',
            email_address_used: 'foo'
        });
        var invalidSelector = '.select2-search-choice [data-invalid="true"]';

        field = SugarTest.createField({
            name: 'to',
            type: 'email-recipients',
            viewName: 'edit',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });

        field.model.set('to', to);
        expect(field.$(invalidSelector).length).toBe(0);

        field.model.get('to').add(invalid);
        expect(field.$(invalidSelector).length).toBe(1);
        expect(field.$('.select2-choice-danger').length).toBe(1);
        expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);

        // Make sure it is still decorated after a full render.
        field.render();
        expect(field.$(invalidSelector).length).toBe(1);
        expect(field.$('.select2-choice-danger').length).toBe(1);
        expect(field.$('[data-title=ERR_INVALID_EMAIL_ADDRESS]').length).toBe(1);
    });

    it('should open the address book and add the selected recipients', function() {
        var recipients = app.data.createMixedBeanCollection([
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Aaron Fitzgerald',
                email: 'afitz@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Isaac Hopper',
                email: 'ihopper@example.com'
            }),
            app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Grace Beal',
                email: 'gbeal@example.com'
            })
        ]);
        var spy = sandbox.spy();

        app.drawer = {
            open: function(def, onClose) {
                onClose(recipients);
            }
        };

        field = SugarTest.createField({
            name: 'to',
            type: 'email-recipients',
            viewName: 'edit',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });
        field.view.on('address-book-state', spy);
        field.model.set('to', to);

        field.$('.btn').click();

        expect(field.model.get('to').length).toBe(5);
        expect(spy).toHaveBeenCalledTwice();
        expect(spy).toHaveBeenCalledWith('open');
        expect(spy).toHaveBeenCalledWith('closed');

        delete app.drawer;
    });
});
