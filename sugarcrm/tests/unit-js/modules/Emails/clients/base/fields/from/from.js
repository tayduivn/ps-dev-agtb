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
describe('Emails.BaseFromField', function() {
    var app;
    var context;
    var field;
    var from;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.loadHandlebarsTemplate('from', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('from', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('from', 'field', 'base', 'select2-result', 'Emails');
        SugarTest.loadHandlebarsTemplate('from', 'field', 'base', 'select2-selection', 'Emails');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        from = app.data.createBean('Contacts', {
            _link: 'contacts_from',
            id: _.uniqueId(),
            name: 'Harry Vickers',
            email_address_used: 'hvickers@example.com'
        });

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
                name: 'from',
                type: 'from',
                viewName: 'detail',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            field.model.set('from', from);

            expect(field.render).toHaveBeenCalledOnce();
        });

        it('should set data on Select2', function() {
            field = SugarTest.createField({
                name: 'from',
                type: 'from',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.render();

            sandbox.stub(field, 'render');
            sandbox.spy(field, 'getFormattedValue');
            field.model.set('from', from);

            expect(field.render).not.toHaveBeenCalled();
            expect(field.getFormattedValue).toHaveBeenCalledOnce();
            expect(field.$(field.fieldTag).select2('data')).toBe(from);
        });
    });

    describe('responding to DOM changes', function() {
        beforeEach(function() {
            field = SugarTest.createField({
                name: 'from',
                type: 'from',
                viewName: 'edit',
                module: model.module,
                model: model,
                context: context,
                loadFromModule: true
            });
            field.model.set('from', from);
            field.render();
        });

        it('should not complete the selection with an invalid link', function() {
            var event = new $.Event('select2-selecting');

            sandbox.spy(event, 'preventDefault');
            event.choice = app.data.createBean('Contacts', {
                _link: 'contacts_to',
                id: _.uniqueId(),
                name: 'Eugene Kushner',
                email_address_used: 'ek@example.com'
            });

            field.$(field.fieldTag).trigger(event);

            expect(event.preventDefault).toHaveBeenCalled();
            expect(field.model.get('from').at(0)).toBe(from);
        });

        it('should change the sender', function() {
            var event = new $.Event('change');
            var newSender = app.data.createBean('Contacts', {
                _link: 'contacts_from',
                id: _.uniqueId(),
                name: 'Ira Carr',
                email_address_used: 'icarr@example.com'
            });
            var actual;

            event.added = [newSender];
            field.$(field.fieldTag).trigger(event);
            actual = field.model.get('from');

            expect(actual.length).toBe(1);
            expect(actual.at(0)).toBe(newSender);
        });

        it('should remove the sender', function() {
            var event = new $.Event('change');

            event.removed = [from];
            field.$(field.fieldTag).trigger(event);

            expect(field.model.get('from').length).toBe(0);
        });
    });

    it('should format the model in the collection', function() {
        var field = SugarTest.createField({
            name: 'from',
            type: 'from',
            viewName: 'detail',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });
        var actual;

        field.model.set('from', from);
        actual = field.getFormattedValue();

        expect(actual).toBe(from);
        expect(actual.name).toBe('Harry Vickers');
        expect(actual.email_address).toBe('hvickers@example.com');
        expect(field.tooltip).toBe('Harry Vickers <hvickers@example.com>');
    });
});
