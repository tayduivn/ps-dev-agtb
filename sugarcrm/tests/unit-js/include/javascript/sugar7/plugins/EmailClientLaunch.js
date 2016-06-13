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
describe('EmailClientLaunch Plugin', function() {
    var app;
    var field;
    var originalDrawer;
    var setUseSugarClient;
    var userPrefs;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField('base', 'label', 'label');
        field.plugins = ['EmailClientLaunch'];
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.app.plugins.attach(field, 'field');

        sandbox = sinon.sandbox.create();

        originalDrawer = app.drawer;
        app.drawer = {
            open: sandbox.stub()
        };
        userPrefs = app.user.get('preferences');
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.drawer = originalDrawer;
        app.user.set('preferences', userPrefs);
    });

    setUseSugarClient = function(useSugarClient) {
        app.user.set('preferences', {email_client_preference: {type: useSugarClient ? 'sugar' : 'mailto'}});
    };

    describe('Launch Email Client', function() {
        var retrieveValidRecipientsStub;

        beforeEach(function() {
            retrieveValidRecipientsStub = sandbox.stub(
                field,
                '_retrieveValidRecipients',
                function(recipients) {
                    return recipients;
                }
            );
            sandbox.stub(app.controller.context, 'reloadData');
        });

        it('should launch the Sugar Email Client if user profile says internal', function() {
            setUseSugarClient(true);
            field.launchEmailClient({});
            expect(app.drawer.open.callCount).toBe(1);
        });

        it('should not launch the Sugar Email Client if user profile says external', function() {
            setUseSugarClient(false);
            field.launchEmailClient({});
            expect(app.drawer.open.callCount).toBe(0);
        });

        it('should clean to, cc, and bcc recipient lists before launching Sugar Email Client', function() {
            field.launchSugarEmailClient({
                to: [{email: 'bar1@baz.com'}],
                cc: [{email: 'bar2@baz.com'}],
                bcc: [{email: 'bar3@baz.com'}]
            });
            expect(retrieveValidRecipientsStub.callCount).toBe(3);
        });

        it('should refresh app context if module is Emails', function() {
            var drawerCloseCallback;
            var model = app.data.createBean('Emails');

            app.controller.context.set('module', 'Emails');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.drawer.open.lastCall.args[1];
            drawerCloseCallback(field.context, model);
            expect(app.controller.context.reloadData).toHaveBeenCalled();
        });

        it('should not refresh app context if module is not Emails', function() {
            var drawerCloseCallback;
            var model = app.data.createBean('Emails');

            app.controller.context.set('module', 'Tasks');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.drawer.open.lastCall.args[1];
            drawerCloseCallback(model);
            expect(app.controller.context.reloadData).not.toHaveBeenCalled();
        });

        it('should not refresh app context if drawer is canceled - no model', function() {
            var drawerCloseCallback;

            app.controller.context.set('module', 'Emails');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.drawer.open.lastCall.args[1];
            drawerCloseCallback();
            expect(app.controller.context.reloadData).not.toHaveBeenCalled();
        });
    });

    describe('Retrieve Valid Recipients', function() {
        it('should not return bean recipients that do not have a valid email address', function() {
            var emails = [
                {email_address: 'foo1@bar.com', primary_address: false, invalid_email: true, opt_out: false},
                {email_address: 'foo2@bar.com', primary_address: true, invalid_email: true, opt_out: true}
            ];
            var bean = new Backbone.Model({email: emails});
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients).toEqual([]);
        });

        it('should specify valid email address for bean recipients the in list', function() {
            var emails = [
                {email_address: 'foo1@bar.com', primary_address: false, invalid_email: true, opt_out: false},
                {email_address: 'foo2@bar.com', primary_address: false, invalid_email: false, opt_out: true},
                {email_address: 'foo3@bar.com', primary_address: true, invalid_email: false, opt_out: false}
            ];
            var bean = new Backbone.Model({email: emails});
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);
            expect(_.first(validRecipients).get('email')).toEqual('foo3@bar.com');
        });

        it('should leave bean recipients that have email address specified', function() {
            var emails = [
                {email_address: 'foo@bar.com', primary_address: true, invalid_email: false, opt_out: false}
            ];
            var bean = new Backbone.Model({email: emails});
            var recipients = [{
                email: 'abc@bar.com',
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(_.first(validRecipients).get('email')).toEqual('abc@bar.com');
        });

        it('should set EmailAddresses module when just have email address specified', function() {
            var recipients;
            var validRecipients;

            app.data.declareModel('EmailAddresses');
            recipients = {
                email: 'abc@bar.com'
            };
            validRecipients = field._retrieveValidRecipients(recipients);

            expect(_.first(validRecipients).get('email')).toEqual('abc@bar.com');
            expect(_.first(validRecipients).module).toEqual('EmailAddresses');
        });
    });

    describe('Should Add Email Options', function() {
        it('should set a copy of the related model in email options', function() {
            var module = 'Contacts';
            var model = app.data.createBean(module);
            sandbox.stub(app.data, 'createBean', function() {
                var bean = new Backbone.Model();
                bean.copy = function(copyFrom) {
                    bean.set('foo', copyFrom.get('foo'));
                };
                return bean;
            });

            model.set('id', '123');
            model.set('foo', 'bar');
            model.module = module;
            field.addEmailOptions({related: model});
            expect(field.emailOptions.related).not.toBe(model);
            expect(field.emailOptions.related.toJSON()).toEqual(model.toJSON());
        });

        it('should not specify related on email options if model specified has no module', function() {
            field.addEmailOptions({related: new Backbone.Model()});
            expect(field.emailOptions.related).toBeUndefined();
        });

        it('should overlay email options with new values, not replace the whole set', function() {
            field.emailOptions = {foo: 'bar', bar: 'foo'};
            field.addEmailOptions({bar: 'yes', baz: 'no'});
            expect(field.emailOptions).toEqual({
                foo: 'bar',
                bar: 'yes',
                baz: 'no'
            });
        });
    });

    describe('Build mailto: Url', function() {
        it('should return an empty mailto if no options passed', function() {
            var url = field._buildMailToURL({});
            expect(url).toBe('mailto:');
        });

        it('should return mailto with only to address', function() {
            var email1 = 'foo@bar.com';
            var email2 = 'foo2@bar.com';
            var url = field._buildMailToURL({
                to: [
                    {email: email1},
                    {email: email2}
                ]
            });
            expect(url).toEqual('mailto:' + email1 + ',' + email2);
        });

        it('should return mailto with cc and bcc addresses in querystring', function() {
            var email1 = 'foo@bar.com';
            var email2 = 'foo2@bar.com';
            var url = field._buildMailToURL({
                cc: [
                    {email: email1}
                ],
                bcc: [
                    {email: email2}
                ]
            });
            var expectedParams = {
                cc: email1,
                bcc: email2
            };
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });

        it('should return mailto with subject and text body in querystring', function() {
            var expectedParams = {
                subject: 'Foo',
                body: 'Bar!'
            };
            var url = field._buildMailToURL({
                subject: expectedParams.subject,
                text_body: expectedParams.body,
                html_body: '<b>' + expectedParams.body + '</b>'
            });
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });
    });

    describe('Format Recipients To String', function() {
        it('should return an empty string if no recipients', function() {
            var actual = field._formatRecipientsToString([]);
            expect(actual).toEqual('');
        });

        it('should return a single address if only email string passed in', function() {
            var expected = 'foo@bar.com';
            var actual = field._formatRecipientsToString(expected);
            expect(actual).toEqual(expected);
        });

        it('should return emails passed in different forms', function() {
            var bean;
            var actual;
            var email1 = 'foo1@bar.com';
            var email2 = 'foo2@bar.com';
            var email3 = 'foo3@bar.com';

            bean = new Backbone.Model({
                email: [{
                    email_address: email3,
                    primary_address: true,
                    invalid_email: false,
                    opt_out: false
                }]
            });
            actual = field._formatRecipientsToString([
                email1,
                {email: email2},
                {bean: bean}
            ]);
            expect(actual).toEqual(email1 + ',' + email2 + ',' + email3);
        });

        it('should not return emails in bean form if no valid email on bean', function() {
            var email1 = 'foo1@bar.com';
            var email2 = 'foo2@bar.com';
            var bean = new Backbone.Model({email: [{
                email_address: email2,
                invalid_email: true
            }]});
            var actual = field._formatRecipientsToString([
                email1,
                {bean: bean}
            ]);
            expect(actual).toEqual(email1);
        });
    });

    describe('Retrieving Email Options', function() {
        it('should return empty object if no options on link or controller', function() {
            var actual;
            var $link = $('<a href="#">Foo!</a>');

            field.emailOptions = undefined;
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({});
        });

        it('should return options from controller combined with options from link', function() {
            var actual;
            var $link = $('<a href="#">Foo!</a>');

            $link.data({
                to: 'foo@bar.com',
                subject: 'Bar!!!'
            });
            field.emailOptions = {
                cc: 'foo2@bar.com',
                subject: 'Bar'
            };
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({
                to: 'foo@bar.com',
                cc: 'foo2@bar.com',
                subject: 'Bar!!!'
            });

        });
    });

    describe('Setting email links on attach', function() {
        it('should set href to mailto link on render if client is external', function() {
            setUseSugarClient(false);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('mailto:');
        });

        it('should set href to void link on render if client is internal', function() {
            setUseSugarClient(true);
            field.$el = $('<div><a href="#" data-action="email">Foo</a></div>');
            field.trigger('render');
            expect(field.$('a').attr('href')).toEqual('javascript:void(0)');
        });
    });
});
