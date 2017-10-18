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

    function createParticipant(link, email, parentType, parentName) {
        var parentId = _.uniqueId();

        return app.data.createBean('EmailParticipants', {
            _link: link,
            id: _.uniqueId(),
            parent: {
                _acl: {},
                type: parentType,
                id: parentId,
                name: parentName
            },
            parent_type: parentType,
            parent_id: parentId,
            parent_name: parentName,
            email_address_id: _.uniqueId(),
            email_address: email
        });
    }

    beforeEach(function() {
        var metadata = SugarTest.loadFixture('emails-metadata');
        var parent;

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

        // Used by formatDate in the reply header template.
        app.user.setPreference('datepref', 'm/d/Y');
        app.user.setPreference('timepref', 'H:i');

        parent = app.data.createBean('Contacts', {
            id: _.uniqueId(),
            name: 'Eric Johns'
        });

        context = app.context.getContext({module: 'Emails'});
        context.prepare(true);
        model = context.get('model');
        model.set({
            id: _.uniqueId(),
            name: 'My Subject',
            // Create a datetime string that will work in any timezone.
            date_sent: app.date('2012-03-27 01:48').format(),
            description: 'this is the plain-text body',
            description_html: '<p>this is the <b>html</b> body</p>',
            parent: {
                _acl: {},
                type: parent.module,
                id: parent.get('id'),
                name: parent.get('name')
            },
            parent_type: parent.module,
            parent_id: parent.get('id'),
            parent_name: parent.get('name'),
            team_name: [
                {
                    id: 'West',
                    name: 'West',
                    primary: false
                },
                {
                    id: 'East',
                    name: 'East',
                    primary: true
                }
            ]
        });
        model.set('from_collection', [
            createParticipant('from', 'rturner@example.com', 'Contacts', 'Ralph Turner')
        ]);
        model.set('to_collection', [
            createParticipant('to', 'gearl@example.com', 'Contacts', 'Georgia Earl'),
            createParticipant('to', 'nholman@example.com', 'Contacts', 'Nancy Holman'),
            app.data.createBean('EmailParticipants', {
                _link: 'to',
                id: _.uniqueId(),
                email_address_id: _.uniqueId(),
                email_address: 'bhunter@example.com'
            })
        ]);
        model.set('cc_collection', [
            createParticipant('cc', 'wbibby@example.com', 'Contacts', 'Wally Bibby')
        ]);
        model.set('bcc_collection', [
            createParticipant('bcc', 'rwithers@example.com', 'Contacts', 'Rhonda Withers')
        ]);

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

    it('should use the emailaction templates', function() {
        expect(field.type).toBe('emailaction');
    });

    describe('email options', function() {
        it('should set the email signature location to above', function() {
            expect(field.emailOptions.signature_location).toBe('above');
        });

        it('should add the reply_to_id', function() {
            expect(field.emailOptions.reply_to_id).toBe(model.get('id'));
        });

        it('should add the sender to the To field', function() {
            var sender = model.get('from_collection').first();

            expect(field.emailOptions.to.length).toBe(1);
            expect(field.emailOptions.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(field.emailOptions.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(field.emailOptions.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(field.emailOptions.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(field.emailOptions.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(field.emailOptions.cc).toBeUndefined();
        });

        it('should add the sender and To recipients to the To field and CC recipients to the CC field', function() {
            var sender = model.get('from_collection').first();

            field.def.reply_all = true;
            model.trigger('change', model);

            expect(field.emailOptions.to.length).toBe(4);
            expect(field.emailOptions.to[0].email.get('id')).toBe(sender.get('email_address_id'));
            expect(field.emailOptions.to[0].email.get('email_address')).toBe(sender.get('email_address'));
            expect(field.emailOptions.to[0].bean.module).toBe(sender.get('parent_type'));
            expect(field.emailOptions.to[0].bean.get('id')).toBe(sender.get('parent_id'));
            expect(field.emailOptions.to[0].bean.get('name')).toBe(sender.get('parent_name'));
            expect(field.emailOptions.to[1].email.get('id'))
                .toBe(model.get('to_collection').at(0).get('email_address_id'));
            expect(field.emailOptions.to[1].email.get('email_address'))
                .toBe(model.get('to_collection').at(0).get('email_address'));
            expect(field.emailOptions.to[1].bean.module).toBe(model.get('to_collection').at(0).get('parent_type'));
            expect(field.emailOptions.to[1].bean.get('id')).toBe(model.get('to_collection').at(0).get('parent_id'));
            expect(field.emailOptions.to[1].bean.get('name')).toBe(model.get('to_collection').at(0).get('parent_name'));
            expect(field.emailOptions.to[2].email.get('id'))
                .toBe(model.get('to_collection').at(1).get('email_address_id'));
            expect(field.emailOptions.to[2].email.get('email_address'))
                .toBe(model.get('to_collection').at(1).get('email_address'));
            expect(field.emailOptions.to[2].bean.module).toBe(model.get('to_collection').at(1).get('parent_type'));
            expect(field.emailOptions.to[2].bean.get('id')).toBe(model.get('to_collection').at(1).get('parent_id'));
            expect(field.emailOptions.to[2].bean.get('name')).toBe(model.get('to_collection').at(1).get('parent_name'));
            expect(field.emailOptions.to[3].email.get('id'))
                .toBe(model.get('to_collection').at(2).get('email_address_id'));
            expect(field.emailOptions.to[3].email.get('email_address'))
                .toBe(model.get('to_collection').at(2).get('email_address'));
            expect(field.emailOptions.to[3].bean).toBeUndefined();
            expect(field.emailOptions.cc.length).toBe(1);
            expect(field.emailOptions.cc[0].email.get('id'))
                .toBe(model.get('cc_collection').at(0).get('email_address_id'));
            expect(field.emailOptions.cc[0].email.get('email_address'))
                .toBe(model.get('cc_collection').at(0).get('email_address'));
            expect(field.emailOptions.cc[0].bean.module).toBe(model.get('cc_collection').at(0).get('parent_type'));
            expect(field.emailOptions.cc[0].bean.get('id')).toBe(model.get('cc_collection').at(0).get('parent_id'));
            expect(field.emailOptions.cc[0].bean.get('name')).toBe(model.get('cc_collection').at(0).get('parent_name'));
        });

        using('subjects', [
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
            it('should add the reply subject', function() {
                model.set('name', data.original);
                expect(field.emailOptions.name).toBe(data.reply);
            });
        });

        describe('building the reply content', function() {
            it('should add the email body', function() {
                var expected = "\n-----\n" +
                    "From: Ralph Turner <rturner@example.com>\n" +
                    "Date: 03/27/2012 01:48\n" +
                    "To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n" +
                    "Cc: Wally Bibby <wbibby@example.com>\n" +
                    "Subject: My Subject\n\n" +
                    'Here is my plain-text content.';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not add the email body', function() {
                sandbox.stub(field, 'useSugarEmailClient').returns(true);

                model.set('description', 'Here is my plain-text content.');

                expect(field.emailOptions.description).toBeUndefined();
            });

            it('should add the email HTML body', function() {
                var body = 'And this is my <b>HTML</b> content.<br /><br />' +
                    '<div class="signature">My signature</div><br />' +
                    '<div id="replycontent">Some reply content</div>';
                var expected = '<div></div><div id="replycontent">' + "\n" +
                    "<hr>\n" +
                    "<p>\n" +
                    "    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n" +
                    "    <strong>Date:</strong> 03/27/2012 01:48<br/>\n" +
                    '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                    "Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n" +
                    "    <strong>Cc:</strong> Wally Bibby &lt;wbibby@example.com&gt;<br/>\n" +
                    "    <strong>Subject:</strong> My Subject<br/>\n" +
                    "</p>\n" +
                    'And this is my <b>HTML</b> content.<br /><br />' +
                    '<div>My signature</div><br />' +
                    '<div>Some reply content</div>' +
                    '</div>';

                model.set('description_html', body);

                expect(field.emailOptions.description_html).toBe(expected);
            });

            it('should not include Date in the email body', function() {
                var expected = "\n-----\n" +
                    "From: Ralph Turner <rturner@example.com>\n" +
                    "To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n" +
                    "Cc: Wally Bibby <wbibby@example.com>\n" +
                    "Subject: My Subject\n\n" +
                    'this is the plain-text body';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);

                model.unset('date_sent');

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not include Date in the email HTML body', function() {
                var expected = '<div></div><div id="replycontent">' + "\n" +
                    "<hr>\n" +
                    "<p>\n" +
                    "    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n" +
                    // The template includes whitespace, but it won't be rendered in the client.
                    "    \n" +
                    '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                    "Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n" +
                    "    <strong>Cc:</strong> Wally Bibby &lt;wbibby@example.com&gt;<br/>\n" +
                    "    <strong>Subject:</strong> My Subject<br/>\n" +
                    "</p>\n" +
                    '<p>this is the <b>html</b> body</p>' +
                    '</div>';

                model.unset('date_sent');

                expect(field.emailOptions.description_html).toBe(expected);
            });

            it('should not include CC in the email body', function() {
                var expected = "\n-----\n" +
                    "From: Ralph Turner <rturner@example.com>\n" +
                    "Date: 03/27/2012 01:48\n" +
                    "To: Georgia Earl <gearl@example.com>, Nancy Holman <nholman@example.com>, bhunter@example.com\n" +
                    "Subject: My Subject\n\n" +
                    'this is the plain-text body';

                sandbox.stub(field, 'useSugarEmailClient').returns(false);
                model.get('cc_collection').reset([]);

                expect(field.emailOptions.description).toBe(expected);
            });

            it('should not include CC in the email HTML body', function() {
                var expected = '<div></div><div id="replycontent">' + "\n" +
                    "<hr>\n" +
                    "<p>\n" +
                    "    <strong>From:</strong> Ralph Turner &lt;rturner@example.com&gt;<br/>\n" +
                    "    <strong>Date:</strong> 03/27/2012 01:48<br/>\n" +
                    '    <strong>To:</strong> Georgia Earl &lt;gearl@example.com&gt;, ' +
                    "Nancy Holman &lt;nholman@example.com&gt;, bhunter@example.com<br/>\n" +
                    // The template includes whitespace, but it won't be rendered in the client.
                    "    \n" +
                    "    <strong>Subject:</strong> My Subject<br/>\n" +
                    "</p>\n" +
                    '<p>this is the <b>html</b> body</p>' +
                    '</div>';

                model.get('cc_collection').reset([]);

                expect(field.emailOptions.description_html).toBe(expected);
            });
        });

        describe('adding the related record', function() {
            it('should add the parent', function() {
                expect(field.emailOptions.related.module).toBe(model.get('parent').type);
                expect(field.emailOptions.related.get('id')).toBe(model.get('parent').id);
                expect(field.emailOptions.related.get('name')).toBe(model.get('parent').name);
            });

            it('should add using the parent fields', function() {
                var parent = app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Cedric Harper'
                });

                model.unset('parent');
                model.set({
                    parent_type: parent.module,
                    parent_id: parent.get('id'),
                    parent_name: parent.get('name')
                });

                expect(field.emailOptions.related.module).toBe(parent.module);
                expect(field.emailOptions.related.get('id')).toBe(parent.get('id'));
                expect(field.emailOptions.related.get('name')).toBe(parent.get('name'));
            });
        });

        it('should add the selected teams', function() {
            expect(field.emailOptions.team_name).toEqual([
                {
                    id: 'West',
                    name: 'West',
                    primary: false
                },
                {
                    id: 'East',
                    name: 'East',
                    primary: true
                }
            ]);
        });
    });
});
