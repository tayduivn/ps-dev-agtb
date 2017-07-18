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
describe('Emails.Field.ReplyAction', function() {
    var app;
    var field;
    var model;
    var sandbox;
    var context;

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');

        SugarTest.testMetadata.init();

        _.each(metadata.modules, function(def, module) {
            SugarTest.testMetadata.updateModuleMetadata(module, def);
        });

        SugarTest.loadHandlebarsTemplate('reply-action', 'field', 'base', 'reply-header-html', 'Emails');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.routing.start();

        //used by formatDate in the reply header template
        app.user.setPreference('datepref', 'Y-m-d');
        app.user.setPreference('timepref', 'H:i');

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');

        field = SugarTest.createField({
            name: 'reply_action',
            type: 'reply-action',
            viewName: 'record',
            module: 'Emails',
            loadFromModule: true,
            model: model,
            context: context
        });

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        field.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete field.model;
        field = null;
        SugarTest.testMetadata.dispose();
        sandbox.restore();
    });

    describe('_getReplyRecipients', function() {
        beforeEach(function() {
            var parentId = _.uniqueId();
            var from = app.data.createBean('EmailParticipants', {
                _link: 'from',
                id: _.uniqueId(),
                parent: {
                    _acl: {},
                    type: 'Contacts',
                    id: parentId,
                    name: 'Ralph Turner'
                },
                parent_type: 'Contacts',
                parent_id: parentId,
                parent_name: 'Ralph Turner',
                email_address_id: _.uniqueId(),
                email_address: 'rturner@example.com'
            });

            field.model.set('from_collection', from);
        });

        it('should return the original sender in the to field if reply', function() {
            var sender = field.model.get('from_collection').first();
            var actual = field._getReplyRecipients(false);

            expect(actual.to.length).toBe(1);
            expect(actual.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(actual.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(actual.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(actual.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(actual.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(actual.cc.length).toBe(0);
        });

        it('should return the original from, to and cc recipients if reply all', function() {
            var parentId1 = _.uniqueId();
            var parentId2 = _.uniqueId();
            var parentId3 = _.uniqueId();
            var parentId4 = _.uniqueId();
            var sender = field.model.get('from_collection').first();
            var to = [
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId1,
                        name: 'Georgia Earl'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId1,
                    parent_name: 'Georgia Earl',
                    email_address_id: _.uniqueId(),
                    email_address: 'a@b.com'
                }),
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId2,
                        name: 'Nancy Holman'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId2,
                    parent_name: 'Nancy Holman',
                    email_address_id: _.uniqueId(),
                    email_address: 'b@c.com'
                })
            ];
            var cc = [
                app.data.createBean('EmailParticipants', {
                    _link: 'cc',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId3,
                        name: 'Wally Bibby'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId3,
                    parent_name: 'Wally Bibby',
                    email_address_id: _.uniqueId(),
                    email_address: 'c@d.com'
                })
            ];
            var bcc = [
                app.data.createBean('EmailParticipants', {
                    _link: 'bcc',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId4,
                        name: 'Rhonda Withers'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId4,
                    parent_name: 'Rhonda Withers',
                    email_address_id: _.uniqueId(),
                    email_address: 'e@f.com'
                })
            ];
            var actual;

            field.model.set('to_collection', to);
            field.model.set('cc_collection', cc);
            field.model.set('bcc_collection', bcc);

            actual = field._getReplyRecipients(true);

            expect(actual.to.length).toBe(3);
            expect(actual.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(actual.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(actual.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(actual.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(actual.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(actual.to[1].email.get('id')).toBe(field.model.get('to_collection').at(0).get('email_address_id'));
            expect(actual.to[1].email.get('email_address'))
                .toBe(field.model.get('to_collection').at(0).get('email_address'));
            expect(actual.to[1].bean.module).toBe(field.model.get('to_collection').at(0).get('parent_type'));
            expect(actual.to[1].bean.get('id')).toBe(field.model.get('to_collection').at(0).get('parent_id'));
            expect(actual.to[1].bean.get('name')).toBe(field.model.get('to_collection').at(0).get('parent_name'));
            expect(actual.to[2].email.get('id')).toBe(field.model.get('to_collection').at(1).get('email_address_id'));
            expect(actual.to[2].email.get('email_address'))
                .toBe(field.model.get('to_collection').at(1).get('email_address'));
            expect(actual.to[2].bean.module).toBe(field.model.get('to_collection').at(1).get('parent_type'));
            expect(actual.to[2].bean.get('id')).toBe(field.model.get('to_collection').at(1).get('parent_id'));
            expect(actual.to[2].bean.get('name')).toBe(field.model.get('to_collection').at(1).get('parent_name'));
            expect(actual.cc.length).toBe(1);
            expect(actual.cc[0].email.get('id')).toBe(field.model.get('cc_collection').at(0).get('email_address_id'));
            expect(actual.cc[0].email.get('email_address'))
                .toBe(field.model.get('cc_collection').at(0).get('email_address'));
            expect(actual.cc[0].bean.module).toBe(field.model.get('cc_collection').at(0).get('parent_type'));
            expect(actual.cc[0].bean.get('id')).toBe(field.model.get('cc_collection').at(0).get('parent_id'));
            expect(actual.cc[0].bean.get('name')).toBe(field.model.get('cc_collection').at(0).get('parent_name'));
        });

        it('should correctly return email address only recipients during reply all', function() {
            var sender = field.model.get('from_collection').first();
            var to = [
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    email_address_id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                })
            ];
            var actual;

            field.model.set('to_collection', to);

            actual = field._getReplyRecipients(true);

            expect(actual.to.length).toBe(2);
            expect(actual.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(actual.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(actual.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(actual.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(actual.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(actual.to[1].email.get('id')).toBe(field.model.get('to_collection').at(0).get('email_address_id'));
            expect(actual.to[1].email.get('email_address'))
                .toBe(field.model.get('to_collection').at(0).get('email_address'));
            expect(actual.to[1].bean).toBeUndefined();
            expect(actual.cc.length).toBe(0);
        });
    });

    describe('getting the team names', function() {
        var teams = [
            {id: 'West', name: 'West', primary: false},
            {id: 'East', name: 'East', primary: true}
        ];

        beforeEach(function() {
            field.model.set('team_name', teams);
        });

        afterEach(function() {
            field.model.unset('team_name');
        });

        it('should return the original teams in the team_name field if reply', function() {
            field.model.trigger('change');
            expect(field.model.get('team_name')).toEqual(teams);
        });
    });

    describe('_getReplySubject', function() {
        using('original subjects', [
            {
                original: '',
                reply: 'Re: '
            },
            {
                original: undefined,
                reply: 'Re: '
            },
            {
                original: 'My Subject',
                reply: 'Re: My Subject'
            },
            {
                original: 'Re: My Subject',
                reply: 'Re: My Subject'
            },
            {
                original: 'RE: re: Re: rE: My Subject',
                reply: 'Re: My Subject'
            },
            {
                original: 'RE: FWD: re: fwd: Re: Fwd: rE: fwD: My Subject',
                reply: 'Re: My Subject'
            }
        ], function(data) {
            it('should build the appropriate reply subject', function() {
                var actual = field._getReplySubject(data.original);
                expect(actual).toEqual(data.reply);
            });
        });
    });

    describe('_getReplyHeaderParams', function() {
        it('should produce proper reply header params', function() {
            var actual;
            var date = '2012-03-27 01:48';
            var expected = {
                from: '',
                to: '',
                cc: '',
                date: date,
                name: 'My Subject'
            };

            field.model.set({
                from: [], //_formatEmailList tested separately
                to: [], //_formatEmailList tested separately
                cc: [], //_formatEmailList tested separately
                date_sent: expected.date,
                name: expected.name
            });

            actual = field._getReplyHeaderParams();
            expect(actual).toEqual(expected);
        });
    });

    describe('_getReplyHeader', function() {
        using('various reply header parameters', [
            {
                params: {
                    from: 'A',
                    date: '2001-01-01 01:01:01',
                    to: 'B, C',
                    cc: 'D',
                    name: 'My Subject'
                },
                replyHeader: '-----\n' +
                    'From: A\n' +
                    'Date: 2001-01-01 01:01\n' +
                    'To: B, C\n' +
                    'Cc: D\n' +
                    'Subject: My Subject\n'
            },
            {
                params: {
                    from: 'A',
                    to: 'B, C',
                    name: 'My Subject'
                },
                replyHeader: '-----\n' +
                    'From: A\n' +
                    'To: B, C\n' +
                    'Subject: My Subject\n'
            },
            {
                params: {},
                replyHeader: '-----\n' +
                    'From: \n' +
                    'To: \n' +
                    'Subject: \n'
            }
        ], function(data) {
            it('should build the appropriate reply header', function() {
                var actual = field._getReplyHeader(data.params);
                expect(actual).toEqual(data.replyHeader);
            });
        });

    });

    describe('_formatEmailList', function() {
        it('should return empty string if recipient list is empty', function() {
            var actual = field._formatEmailList([]);
            expect(actual).toEqual('');
        });

        it('should format email list properly', function() {
            var parentId = _.uniqueId();
            var to = [
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    parent: {
                        _acl: {},
                        type: 'Contacts',
                        id: parentId,
                        name: 'Brandon Hunter'
                    },
                    parent_type: 'Contacts',
                    parent_id: parentId,
                    parent_name: 'Brandon Hunter',
                    email_address_id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                }),
                app.data.createBean('EmailParticipants', {
                    _link: 'to',
                    id: _.uniqueId(),
                    email_address_id: _.uniqueId(),
                    email_address: 'bar@foo.com'
                })
            ];
            var actual;

            field.model.set('to_collection', to);
            actual = field._formatEmailList(field.model.get('to_collection'));

            expect(actual).toEqual('Brandon Hunter <foo@bar.com>, bar@foo.com');
        });
    });

    describe('_getReplyBodyHtml', function() {
        it('should strip the signature class from any div tags', function() {
            var original = 'My Content <div class="signature">My Signature</div>';
            var expected = 'My Content <div>My Signature</div>';
            var actual;

            field.model.set('description_html', original);
            actual = field._getReplyBodyHtml();
            expect(actual).toEqual(expected);
        });

        it('should strip the reply content class from any div tags', function() {
            var original = 'My Content <div id="replycontent">My Reply Content</div>';
            var expected = 'My Content <div>My Reply Content</div>';
            var actual;

            field.model.set('description_html', original);
            actual = field._getReplyBodyHtml();
            expect(actual).toEqual(expected);
        });

        it('should return an empty string if email body is not set', function() {
            field.model.unset('description_html');
            expect(field._getReplyBodyHtml()).toEqual('');
        });
    });

    describe('_updateEmailOptions', function() {
        beforeEach(function() {
            sandbox.stub(app.user, 'getPreference')
                .withArgs('timepref')
                .returns('H:i');

            app.user.getPreference.withArgs('datepref').returns('Y-m-d');
        });

        using('client preferences', [
            {
                test: 'should not set description in email options when using sugar email client',
                type: 'sugar',
                expected: undefined
            },
            {
                test: 'should set description in email options when using external email client',
                type: 'external',
                expected: "\n-----\nFrom: \nTo: \nSubject: \n\n\Hello World!"
            }
        ], function(data) {
            it(data.test, function() {
                var newField;

                app.user.getPreference.withArgs('email_client_preference').returns({type: data.type});

                newField = SugarTest.createField({
                    name: 'reply_action',
                    type: 'reply-action',
                    viewName: 'record',
                    module: 'Emails',
                    loadFromModule: true,
                    model: model,
                    context: context
                });

                model.set('description', 'Hello World!');

                expect(newField.emailOptions.description).toBe(data.expected);
                newField.dispose();
            });
        });
    });
});
