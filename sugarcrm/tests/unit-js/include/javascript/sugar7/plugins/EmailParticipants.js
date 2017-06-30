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

    describe('preparing a model to be added to the collection', function() {
        var bean;

        beforeEach(function() {
            var parentId = _.uniqueId();

            bean = app.data.createBean('EmailParticipants', {
                _link: 'to_link',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: _.uniqueId(),
                email_address: 'hrhodes@example.com'
            });
        });

        it('should define the data that is needed for Select2', function() {
            var result;

            result = field.prepareModel(bean);

            // The model is modified, but it is returned so that `prepareModel`
            // can be used as the map callback.
            expect(result).toBe(bean);
            expect(result.locked).toBe(false);
            expect(bean.locked).toBe(false);
            // Derived from `app.utils.isValidEmailAddress`.
            expect(result.invalid).toBe(false);
            expect(bean.invalid).toBe(false);
        });

        it('should lock the selection', function() {
            field.def.readonly = true;

            field.prepareModel(bean);

            expect(bean.locked).toBe(true);
        });

        using(
            'attributes to set',
            [
                // No change. Has parent and email address.
                {},
                // Has parent but no email address.
                {
                    email_address_id: '',
                    email_address: ''
                },
                // Has parent but no email address.
                {
                    email_address_id: ''
                }
            ],
            function(attrs) {
                it('should mark the participant as valid', function() {
                    // Update the attributes so the model has the data we need
                    // for this test case.
                    bean.set(attrs);

                    field.prepareModel(bean);

                    expect(bean.invalid).toBe(false);
                });
            }
        );

        using(
            'attributes to set',
            [
                // Invalid email address.
                {
                    email_address: 'foo'
                },
                // No parent and invalid email address.
                {
                    parent: {},
                    email_address: 'foo'
                },
                // No parent and no email address.
                {
                    parent: {},
                    email_address_id: '',
                    email_address: ''
                },
                // No parent and no email address.
                {
                    parent: {},
                    email_address_id: ''
                }
            ],
            function(attrs) {
                it('should mark the participant as invalid', function() {
                    // Update the attributes so the model has the data we need
                    // for this test case.
                    bean.set(attrs);

                    field.prepareModel(bean);

                    expect(bean.invalid).toBe(true);
                });
            }
        );

        it('should define the url to the model', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(true);

            field.prepareModel(bean);

            expect(bean.href).toBe('#Contacts/' + bean.get('parent_id'));
        });

        it('should not define the url to the model if the user does not have access', function() {
            sandbox.stub(app.acl, 'hasAccessToModel').returns(false);

            field.prepareModel(bean);

            expect(bean.href).toBeUndefined();
        });

        it('should not define the url to the model if the parent record does not exist', function() {
            bean.unset('parent');

            field.prepareModel(bean);

            expect(bean.href).toBeUndefined();
        });
    });

    describe('formatting a model for email headers', function() {
        it('should return just an email address', function() {
            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to_link',
                id: _.uniqueId(),
                email_address_id: _.uniqueId(),
                email_address: 'rhodes@example.com'
            });
            var actual;

            field.prepareModel(bean);
            actual = field.formatForHeader(bean);

            expect(actual).toBe('rhodes@example.com');
        });

        it('should return a name and email address', function() {
            var parentId = _.uniqueId();

            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to_link',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: _.uniqueId(),
                email_address: 'hrhodes@example.com'
            });
            var actual;

            field.prepareModel(bean);
            actual = field.formatForHeader(bean);

            expect(actual).toBe('Haley Rhodes <hrhodes@example.com>');
        });

        it('should surround the name with quotes', function() {
            var parentId = _.uniqueId();

            var bean = app.data.createBean('EmailParticipants', {
                _link: 'to_link',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: _.uniqueId(),
                email_address: 'hrhodes@example.com'
            });
            var actual;

            field.prepareModel(bean);
            actual = field.formatForHeader(bean, true);

            expect(actual).toBe('"Haley Rhodes" <hrhodes@example.com>');
        });

        using('quotes', [true, false], function(surroundNameWithQuotes) {
            it('should return just a name', function() {
                var parentId = _.uniqueId();
                var bean = app.data.createBean('EmailParticipants', {
                    _link: 'to_link',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Haley Rhodes'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Haley Rhodes'
                });
                var actual;

                field.prepareModel(bean);
                actual = field.formatForHeader(bean, surroundNameWithQuotes);

                expect(actual).toBe('Haley Rhodes');
            });
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
                callback: function(response) {
                    expect(response.more).toBe(false);
                    expect(response.results.length).toBe(1);
                    expect(response.results[0].get('_link')).toBe('to_link');
                    expect(response.results[0].get('parent_type')).toBe('Contacts');
                    expect(response.results[0].get('parent_id')).toBe(data.records[0].id);
                    expect(response.results[0].get('parent_name')).toBe(data.records[0].name);
                    expect(response.results[0].get('email_address_id')).toBeUndefined();
                    expect(response.results[0].get('email_address')).toBeUndefined();
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
                _module: 'Contacts',
                name: 'Yolanda Grace',
                email: term
            }];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });

        it('should create a choice when the term is a valid email address', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            // Act as if the user has selected the choice in order to see that
            // the event is trigger.
            field.model.get(field.name).add(actual);
            sandbox.spy(field.model, 'trigger');

            SugarTest.server.respond();

            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.invalid).toBe(false);
            expect(field.model.trigger).toHaveBeenCalledOnce();
            expect(field.model.trigger.args[0][0]).toBe('change:' + field.name);
            expect(field.model.trigger.args[0][1]).toBe(field.model);
            expect(field.model.trigger.args[0][2]).toBe(field.model.get(field.name));
        });

        it('should not trigger the event if the choice has already been selected', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            // Act as if the user has not selected the choice in order to see
            // that the event is not triggered.
            sandbox.spy(field.model, 'trigger');

            SugarTest.server.respond();

            expect(actual.get('email_address_id')).toBe(address.id);
            expect(actual.invalid).toBe(false);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not patch the new choice when the request responds with an invalid email address', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: true,
                opt_out: false
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Nothing changed.
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.invalid).toBe(true);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not patch the new choice when the request responds with an opted out email address', function() {
            var term = 'test@example.com';
            var address = {
                _module: 'EmailAddresses',
                _acl: {
                    fields: {}
                },
                id: _.uniqueId(),
                email_address: term,
                email_address_caps: term.toUpperCase(),
                invalid_email: false,
                opt_out: true
            };
            var response = [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(address)
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Nothing changed.
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.invalid).toBe(true);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not patch the new choice when the request fails', function() {
            var term = 'test@example.com';
            var response = [
                500,
                {'Content-Type': 'application/json'},
                JSON.stringify({
                    error: 'fatal_error',
                    error_message: 'Your request failed to complete.'
                })
            ];
            var data = [];
            var actual;

            SugarTest.server.respondWith('POST', /.*\/rest\/v10\/EmailAddresses/, response);

            actual = options.createSearchChoice(term, data);

            expect(actual.module).toBe('EmailParticipants');
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.get('email_address')).toBe(term);
            // The choice is seen as invalid while the request is in flight.
            expect(actual.invalid).toBe(true);

            sandbox.spy(field.model, 'trigger');
            SugarTest.server.respond();

            // Nothing changed.
            expect(actual.get('email_address_id')).toBeUndefined();
            expect(actual.invalid).toBe(true);
            expect(field.model.trigger).not.toHaveBeenCalled();
        });

        it('should not create a choice when the term is an invalid email address', function() {
            var term = 'test';
            var data = [];
            var actual;

            actual = options.createSearchChoice(term, data);

            expect(actual).toBeUndefined();
        });
    });

    describe('validation', function() {
        var bean;

        beforeEach(function() {
            var parentId = _.uniqueId();

            bean = app.data.createBean('EmailParticipants', {
                _link: 'to_link',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Haley Rhodes'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Haley Rhodes',
                email_address_id: _.uniqueId(),
                email_address: 'hrhodes'
            });
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
