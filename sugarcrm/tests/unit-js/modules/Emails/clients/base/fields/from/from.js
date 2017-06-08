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
        var parentId = _.uniqueId();

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

        from = app.data.createBean('EmailParticipants', {
            _link: 'from_link',
            id: _.uniqueId(),
            parent: {
                _acl: {},
                type: 'Contacts',
                id: parentId,
                name: 'Harry Vickers'
            },
            parent_type: 'Contacts',
            parent_id: parentId,
            parent_name: 'Harry Vickers',
            email_address_id: _.uniqueId(),
            email_address: 'hvickers@example.com'
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
            field.model.trigger('sync');
            field.render();
        });

        it('should change the sender', function() {
            var event = new $.Event('change');
            var parentId = _.uniqueId();
            var newSender = app.data.createBean('EmailParticipants', {
                _link: 'from_link',
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Ira Carr'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Ira Carr',
                email_address_id: _.uniqueId(),
                email_address: 'icarr@example.com'
            });
            var actual;
            var json;

            event.added = [newSender];
            field.$(field.fieldTag).trigger(event);
            actual = field.model.get('from');

            expect(actual.length).toBe(1);
            expect(actual.at(0)).toBe(newSender);

            // Assert that the new sender will be linked on the next sync.
            json = field.model.toJSON({fields: ['from']});
            expect(json.from_link.create.length).toBe(1);
            expect(json.from_link.create[0].parent_type).toBe(newSender.get('parent_type'));
            expect(json.from_link.create[0].parent_id).toBe(newSender.get('parent_id'));
            expect(json.from_link.delete.length).toBe(1);
            expect(json.from_link.delete[0]).toBe(from.get('id'));
        });

        it('should remove the sender', function() {
            var event = new $.Event('change');

            event.removed = [from];
            field.$(field.fieldTag).trigger(event);

            expect(field.model.get('from').length).toBe(0);

            // Assert that the current sender will be unlinked on the next sync.
            json = field.model.toJSON({fields: ['from']});
            expect(json.from_link.delete.length).toBe(1);
            expect(json.from_link.delete[0]).toBe(from.get('id'));
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
        expect(actual.locked).toBe(false);
        expect(actual.invalid).toBe(false);
        expect(actual.get('parent_name')).toBe('Harry Vickers');
        expect(actual.get('email_address')).toBe('hvickers@example.com');
        expect(field.tooltip).toBe('Harry Vickers <hvickers@example.com>');
    });

    describe('rendering in disabled mode', function() {
        it('should disable the select2 element', function() {
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
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(false);

            field.setDisabled();
            expect(field.$(field.fieldTag).select2('container').hasClass('select2-container-disabled')).toBe(true);
        });
    });
});
