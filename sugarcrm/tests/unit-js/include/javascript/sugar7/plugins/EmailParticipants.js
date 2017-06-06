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
describe('Plugins.EmailParticipants', function() {
    var app;
    var context;
    var field;
    var model;
    var sandbox;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadPlugin('EmailParticipants');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'to',
            type: 'email-recipients',
            viewName: 'detail',
            module: model.module,
            model: model,
            context: context,
            loadFromModule: true
        });

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        model.off();
        sandbox.restore();
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
    });

    describe('check if the collection field wraps the link', function() {
        it('should wrap the link', function() {
            var actual = field.hasLink('contacts_to');

            expect(actual).toBe(true);
        });

        it('should not wrap the link', function() {
            var actual = field.hasLink('attachments');

            expect(actual).toBe(false);
        });
    });

    describe('preparing a model to be added to the collection', function() {
        var bean;

        beforeEach(function() {
            bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Haley Rhodes',
                email: [{
                    email_address: 'hrhodes@example.com',
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false
                }, {
                    email_address: 'rhodes@example.com',
                    primary_address: false,
                    invalid_email: false,
                    opt_out: false
                }]
            });
        });

        it('should define the data that is needed for Select2', function() {
            var result;

            bean.set('_link', 'contacts_to');
            result = field.prepareModel(bean);

            // The model is modified, but it is returned so that `prepareModel`
            // can be used as the map callback.
            expect(result).toBe(bean);
            expect(result.locked).toBe(false);
            expect(bean.locked).toBe(false);
            // Derived from `app.utils.getRecordName`.
            expect(result.name).toBe('Haley Rhodes');
            expect(bean.name).toBe('Haley Rhodes');
            // The primary email address is used.
            expect(result.email_address).toBe('hrhodes@example.com');
            expect(bean.email_address).toBe('hrhodes@example.com');
            // Derived from `app.utils.isValidEmailAddress`.
            expect(result.invalid).toBe(false);
            expect(bean.invalid).toBe(false);
        });

        it('should lock the selection', function() {
            bean.set('_link', 'contacts_to');
            field.def.readonly = true;
            field.prepareModel(bean);

            expect(bean.locked).toBe(true);
        });

        it('should discover the link when none is provided', function() {
            var actual;
            var result;

            result = field.prepareModel(bean);
            actual = bean.get('_link');

            expect(actual).toBe('contacts_to');
            expect(result).toBe(bean);
        });

        it('should discover the link when the wrong one is provided', function() {
            var actual;
            var result;

            bean.set('_link', 'attachments');
            result = field.prepareModel(bean);
            actual = bean.get('_link');

            expect(actual).toBe('contacts_to');
            expect(result).toBe(bean);
        });

        it('should return null when the link cannot be discovered', function() {
            var actual;

            bean = app.data.createBean('Notes', {
                id: _.uniqueId(),
                name: 'Quote.pdf'
            });
            actual = field.prepareModel(bean);

            expect(actual).toBeNull();
        });

        it('should return null when the link is not wrapped by the collection', function() {
            var actual;

            bean = app.data.createBean('Notes', {
                _link: 'attachments',
                id: _.uniqueId(),
                name: 'Quote.pdf'
            });
            actual = field.prepareModel(bean);

            expect(actual).toBeNull();
        });

        it('should prioritize email_address_used over all other email addresses', function() {
            bean.set('_link', 'contacts_to');
            bean.set('email_address_used', 'rhodes@example.com');
            bean.set('email_address', 'haley@example.com');
            field.prepareModel(bean);

            expect(bean.email_address).toBe('rhodes@example.com');
        });

        // EmailAddresses records have an email_address field. So this allows
        // us to extract that instead of calling
        // `app.utils.getPrimaryEmailAddress`, which would return nothing
        // because EmailAddresses records don't have a primary email address or
        // even an `email` field (as expected by
        // `app.utils.getPrimaryEmailAddress`).
        it('should prioritize email_address over the primary email address', function() {
            bean.set('_link', 'contacts_to');
            bean.set('email_address', 'haley@example.com');
            field.prepareModel(bean);

            expect(bean.email_address).toBe('haley@example.com');
        });

        it('should define the url to the model', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(true);

            bean.set('_link', 'contacts_to');
            field.prepareModel(bean);

            expect(bean.href).toBe('#Contacts/' + bean.get('id'));
        });

        it('should not define the url to the model', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(false);

            bean.set('_link', 'contacts_to');
            field.prepareModel(bean);

            expect(bean.href).toBeUndefined();
        });
    });

    describe('formatting a model for email headers', function() {
        it('should return just an email address', function() {
            var bean = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'rhodes@example.com'
            });
            var actual;

            field.prepareModel(bean);
            actual = field.formatForHeader(bean);

            expect(actual).toBe('rhodes@example.com');
        });

        it('should return a name and email address', function() {
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Haley Rhodes',
                email: [{
                    email_address: 'hrhodes@example.com',
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false
                }]
            });
            var actual;

            field.prepareModel(bean);
            actual = field.formatForHeader(bean);

            expect(actual).toBe('Haley Rhodes <hrhodes@example.com>');
        });
    });

    describe('searching for participants', function() {
        var options;

        beforeEach(function() {
            SugarTest.seedFakeServer();
            options = field.getSelect2Options();
        });

        afterEach(function() {
            SugarTest.server.restore();
        });

        it('should search for participants that match the query', function() {
            //FIXME: The following record should be included in data.records after MAR-4523 is completed.
            // {
            //     id: _.uniqueId(),
            //     _module: 'EmailAddresses',
            //     name: '',
            //     email: 'haley@example.com'
            // }
            var data = {
                next_offset: -1,
                records: [{
                    id: _.uniqueId(),
                    _module: 'Contacts',
                    name: 'Haley Rhodes',
                    email: 'hrhodes@example.com'
                }]
            };
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?q=haley&max_num=10/;
            var query = {
                term: 'haley',
                callback: function(data) {
                    expect(data.more).toBe(false);
                    expect(data.results.length).toBe(1);
                }
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(data)
            ];

            SugarTest.server.respondWith('GET', url, response);
            options.query(query);
            SugarTest.server.respond();
        });

        it('should return no results on error', function() {
            var url = /.*\/rest\/v10\/Mail\/recipients\/find\?q=haley&max_num=10/;
            var query = {
                term: 'haley',
                callback: function(data) {
                    expect(data.more).toBe(false);
                    expect(data.results.length).toBe(0);
                }
            };
            var response = [
                500,
                {'Content-Type': 'application/json'},
                JSON.stringify({error: 'fatal_error', error_description: 'Your request failed.'})
            ];

            SugarTest.server.respondWith('GET', url, response);
            options.query(query);
            SugarTest.server.respond();
        });

        it('should not create a choice when matches were found', function() {
            var term = 'test@example.com';
            var data = [{
                id: _.uniqueId(),
                _module: 'EmailAddresses',
                name: '',
                email: term
            }];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });

        //FIXME: This should start working after MAR-4523 is completed.
        it('should create a choice when the term is a valid email address', function() {
            var term = 'test@example.com';
            var data = [];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailAddresses');
            expect(actual.get('email_address')).toBe(term);
            expect(actual.email_address).toBe(term);
        });

        it('should not create a choice when the term is an invalid email address', function() {
            var term = 'test';
            var data = [];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });
    });

    describe('invalid participants', function() {
        var bean;

        beforeEach(function() {
            bean = app.data.createBean('Contacts', {
                _link: 'contacts_to',
                id: _.uniqueId(),
                name: 'Haley Rhodes',
                email: [{
                    email_address: 'hrhodes',
                    primary_address: false,
                    invalid_email: true,
                    opt_out: false
                }]
            });
        });

        it('should mark the participant as invalid', function() {
            var result = field.prepareModel(bean);

            // Derived from `app.utils.isValidEmailAddress`.
            expect(result.invalid).toBe(true);
            expect(bean.invalid).toBe(true);
        });

        it('should invalidate the field', function() {
            var cbSpy = sandbox.spy();
            var eventSpy = sandbox.spy();

            model.on('error:validation:' + field.name, eventSpy);

            field.prepareModel(bean);
            model.get(field.name).add(bean);

            runs(function() {
                model.doValidate(null, cbSpy);
            });

            waitsFor(function() {
                return cbSpy.called;
            });

            runs(function() {
                expect(cbSpy).toHaveBeenCalledWith(false);
                expect(eventSpy).toHaveBeenCalledOnce();
                expect(eventSpy.firstCall.args[0][field.type]).toBe(true);
            });
        });
    });
});
