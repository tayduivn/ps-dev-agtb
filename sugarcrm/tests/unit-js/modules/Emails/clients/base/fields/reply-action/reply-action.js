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
            var from = app.data.createBean('Contacts', {
                _link: 'contacts_from',
                id: _.uniqueId(),
                name: 'Ralph Turner'
            });

            field.model.set('from', from);
        });

        it('should return the original sender in the to field if reply', function() {
            var actual = field._getReplyRecipients(false);
            var expected = {
                to: [{
                    bean: field.model.get('from').first()
                }],
                cc: []
            };

            expect(actual).toEqual(expected);
        });

        it('should return the original from, to and cc recipients if reply all', function() {
            var to = [
                app.data.createBean('Contacts', {
                    _link: 'contacts_to',
                    id: _.uniqueId(),
                    name: 'Georgia Earl',
                    email_address_used: 'a@b.com'
                }),
                app.data.createBean('Contacts', {
                    _link: 'contacts_to',
                    id: _.uniqueId(),
                    name: 'Nancy Holman',
                    email_address_used: 'b@c.com'
                })
            ];
            var cc = [
                app.data.createBean('Contacts', {
                    _link: 'contacts_cc',
                    id: _.uniqueId(),
                    name: 'Wally Bibby',
                    email_address_used: 'c@d.com'
                })
            ];
            var bcc = [
                app.data.createBean('Contacts', {
                    _link: 'contacts_bcc',
                    id: _.uniqueId(),
                    name: 'Rhonda Withers',
                    email_address_used: 'e@f.com'
                })
            ];
            var expected;
            var actual;

            field.model.set('to', to);
            field.model.set('cc', cc);
            field.model.set('bcc', bcc);

            expected = {
                to: [
                    {bean: field.model.get('from').first()},
                    {bean: field.model.get('to').at(0)},
                    {bean: field.model.get('to').at(1)}
                ],
                cc: [{
                    bean: field.model.get('cc').first()
                }]
            };
            actual = field._getReplyRecipients(true);

            expect(actual).toEqual(expected);
        });

        it('should correctly return email address only recipients during reply all', function() {
            var to = [
                app.data.createBean('EmailAddresses', {
                    _link: 'email_addresses_to',
                    email_address: 'foo@bar.com',
                    email_address_used: 'foo@bar.com'
                })
            ];
            var expected;
            var actual;

            field.model.set('to', to);

            expected = {
                to: [
                    {bean: field.model.get('from').first()},
                    {bean: field.model.get('to').first()}
                ],
                cc: []
            };
            actual = field._getReplyRecipients(true);

            expect(actual).toEqual(expected);
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
            var to = [
                app.data.createBean('Contacts', {
                    _link: 'contacts_to',
                    id: _.uniqueId(),
                    name: 'Brandon Hunter',
                    email_address_used: 'foo@bar.com'
                }),
                app.data.createBean('EmailAddresses', {
                    _link: 'email_addresses_to',
                    id: _.uniqueId(),
                    email_address: 'bar@foo.com',
                    email_address_used: 'bar@foo.com'
                })
            ];
            var actual;

            field.model.set('to', to);
            actual = field._formatEmailList(field.model.get('to'));

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
