describe('Emails.Field.ReplyAction', function() {
    var app;
    var field;
    var model;
    var user;
    var sandbox;
    var context;

    beforeEach(function() {
        app = SugarTest.app;
        user = SUGAR.App.user;

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('reply-action', 'field', 'base', 'reply-header-html', 'Emails');
        SugarTest.loadComponent('base', 'field', 'button');
        SugarTest.loadComponent('base', 'field', 'rowaction');
        SugarTest.loadComponent('base', 'field', 'emailaction');
        SugarTest.testMetadata.set();

        //used by formatDate in the reply header template
        user.setPreference('datepref', 'Y-m-d');
        user.setPreference('timepref', 'H:i');
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
        var recipients;

        beforeEach(function() {
            recipients = [];
            for (var i = 0; i < 5; i++) {
                recipients.push(app.data.createBean('Contacts', {
                    id: i,
                    name: 'Name' + i
                }));
            }
        });

        it('should return the original sender in the to field if reply', function() {
            var actual;
            field.model.set('from', app.data.createMixedBeanCollection([recipients[0]]));
            actual = field._getReplyRecipients(false);
            expect(actual).toEqual({
                to: [
                    {bean: recipients[0]}
                ],
                cc: []
            });
        });

        it('should return the original from, to and cc recipients if reply all', function() {
            var actual;
            var from = app.data.createMixedBeanCollection([recipients[0]]);
            var to = app.data.createMixedBeanCollection([
                recipients[1],
                recipients[2]
            ]);
            var cc = app.data.createMixedBeanCollection([
                recipients[3],
                recipients[4]
            ]);

            field.model.set({
                from: from,
                to: to,
                cc: cc
            });

            actual = field._getReplyRecipients(true);
            expect(actual).toEqual({
                to: [
                    {bean: recipients[0]},
                    {bean: recipients[1]},
                    {bean: recipients[2]}
                ],
                cc: [
                    {bean: recipients[3]},
                    {bean: recipients[4]}
                ]
            });
        });

        it('should ignore original bcc recipients if reply all', function() {
            var actual;
            var from = app.data.createMixedBeanCollection([recipients[0]]);
            var to = app.data.createMixedBeanCollection([recipients[1]]);
            var cc = app.data.createMixedBeanCollection([recipients[2]]);
            var bcc = app.data.createMixedBeanCollection([recipients[3]]);

            field.model.set({
                from: from,
                to: to,
                cc: cc,
                bcc: bcc
            });

            actual = field._getReplyRecipients(true);
            expect(actual).toEqual({
                to: [
                    {bean: recipients[0]},
                    {bean: recipients[1]}
                ],
                cc: [
                    {bean: recipients[2]}
                ]
            });
        });

        it('should correctly return email address only recipients during reply all', function() {
            var actual;
            var emailAddressRecipient = app.data.createBean('EmailAddresses', {
                email_address_used: 'foo@bar.com'
            });
            var from = app.data.createMixedBeanCollection([recipients[0]]);
            var to;

            emailAddressRecipient.module = 'EmailAddresses';
            to = app.data.createMixedBeanCollection([emailAddressRecipient]);

            field.model.set({
                from: from,
                to: to
            });

            actual = field._getReplyRecipients(true);
            expect(actual).toEqual({
                to: [
                    {bean: recipients[0]},
                    {email: 'foo@bar.com'}
                ],
                cc: []
            });
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
            var collection = new Backbone.Collection([
                {
                    name: 'Foo Bar',
                    email_address_used: 'foo@bar.com'
                },
                {
                    name: null,
                    email_address_used: 'bar@foo.com'
                }
            ]);
            var actual = field._formatEmailList(collection);
            expect(actual).toEqual('Foo Bar <foo@bar.com>, bar@foo.com');
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
