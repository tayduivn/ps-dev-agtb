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
    var setUseSugarClient;
    var userPrefs;
    var sandbox;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        app.data.declareModels();
        app.data.declareModel('EmailParticipants');
        app.data.declareModel('EmailAddresses');

        field = SugarTest.createField('base', 'label', 'label');
        field.plugins = ['EmailClientLaunch'];
        SugarTest.loadPlugin('EmailClientLaunch');
        SugarTest.app.plugins.attach(field, 'field');

        sandbox = sinon.sandbox.create();
        sandbox.stub(app.utils, 'openEmailCreateDrawer');
        userPrefs = app.user.get('preferences');
    });

    afterEach(function() {
        sandbox.restore();
        field.dispose();
        app.user.set('preferences', userPrefs);
    });

    setUseSugarClient = function(useSugarClient) {
        app.user.set('preferences', {email_client_preference: {type: useSugarClient ? 'sugar' : 'mailto'}});
    };

    describe('Launch Email Client', function() {
        beforeEach(function() {
            sandbox.stub(app.controller.context, 'reloadData');
        });

        it('should launch the Sugar Email Client if user profile says internal', function() {
            setUseSugarClient(true);
            field.launchEmailClient({});
            expect(app.utils.openEmailCreateDrawer).toHaveBeenCalledWith('compose-email');
        });

        it('should not launch the Sugar Email Client if user profile says external', function() {
            setUseSugarClient(false);
            field.launchEmailClient({});
            expect(app.utils.openEmailCreateDrawer).not.toHaveBeenCalled();
        });

        it('should clean to, cc, and bcc recipient lists before launching Sugar Email Client', function() {
            sandbox.spy(field, '_retrieveValidRecipients');

            field.launchSugarEmailClient({
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar1@baz.com'
                    })
                }],
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar2@baz.com'
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'bar3@baz.com'
                    })
                }]
            });
            expect(field._retrieveValidRecipients.callCount).toBe(3);
        });

        it('should refresh app context if module is Emails', function() {
            var drawerCloseCallback;
            var model = app.data.createBean('Emails');

            app.controller.context.set('module', 'Emails');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.utils.openEmailCreateDrawer.lastCall.args[2];
            drawerCloseCallback(field.context, model);
            expect(app.controller.context.reloadData).toHaveBeenCalled();
        });

        it('should not refresh app context if module is not Emails', function() {
            var drawerCloseCallback;
            var model = app.data.createBean('Emails');

            app.controller.context.set('module', 'Tasks');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.utils.openEmailCreateDrawer.lastCall.args[2];
            drawerCloseCallback(model);
            expect(app.controller.context.reloadData).not.toHaveBeenCalled();
        });

        it('should not refresh app context if drawer is canceled - no model', function() {
            var drawerCloseCallback;

            app.controller.context.set('module', 'Emails');
            setUseSugarClient(true);
            field.launchEmailClient({});
            drawerCloseCallback = app.utils.openEmailCreateDrawer.lastCall.args[2];
            drawerCloseCallback();
            expect(app.controller.context.reloadData).not.toHaveBeenCalled();
        });
    });

    describe('Retrieve Valid Recipients', function() {
        it('should not return bean recipients that do not have a valid email address', function() {
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo1@bar.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo2@bar.com',
                primary_address: true,
                invalid_email: true,
                opt_out: true
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(0);
        });

        it('should return bean recipients that have a valid email address', function() {
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo1@bar.com',
                primary_address: false,
                invalid_email: true,
                opt_out: false
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo2@bar.com',
                primary_address: false,
                invalid_email: false,
                opt_out: true
            }, {
                email_address_id: _.uniqueId(),
                email_address: 'foo3@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{bean: bean}];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBeUndefined();
            expect(validRecipients[0].get('email_address')).toBeUndefined();
        });

        it('should use the specified email address', function() {
            var email = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'abc@bar.com'
            });
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('abc@bar.com');
        });

        it('should not return an email address if it does not have id', function() {
            var email = app.data.createBean('EmailAddresses', {
                email_address: 'abc@bar.com'
            });
            var recipients = [{
                email: email
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(0);
        });

        it('should not use the specified email address if it does not have id', function() {
            var email = app.data.createBean('EmailAddresses', {
                email_address: 'abc@bar.com'
            });
            var emails = [{
                email_address_id: _.uniqueId(),
                email_address: 'foo@bar.com',
                primary_address: true,
                invalid_email: false,
                opt_out: false
            }];
            var bean = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                name: 'Phil Thomas',
                email: emails
            });
            var recipients = [{
                email: email,
                bean: bean
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent').type).toBe('Contacts');
            expect(validRecipients[0].get('parent').id).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent').name).toBe('Phil Thomas');
            expect(validRecipients[0].get('parent_type')).toBe('Contacts');
            expect(validRecipients[0].get('parent_id')).toBe(bean.get('id'));
            expect(validRecipients[0].get('parent_name')).toBe('Phil Thomas');
            expect(validRecipients[0].get('email_address_id')).toBeUndefined();
            expect(validRecipients[0].get('email_address')).toBeUndefined();
        });

        it('should not set the parent fields when a bean is not provided', function() {
            var email = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'abc@bar.com'
            });
            var recipients = [{
                email: email
            }];
            var validRecipients = field._retrieveValidRecipients(recipients);

            expect(validRecipients.length).toBe(1);
            expect(validRecipients[0].get('parent')).toBeUndefined();
            expect(validRecipients[0].get('parent_type')).toBeUndefined();
            expect(validRecipients[0].get('parent_id')).toBeUndefined();
            expect(validRecipients[0].get('parent_name')).toBeUndefined();
            expect(validRecipients[0].get('email_address_id')).toBe(email.get('id'));
            expect(validRecipients[0].get('email_address')).toBe('abc@bar.com');
        });
    });

    describe('Should Add Email Options', function() {
        it('should set a copy of the related model in email options', function() {
            var model = app.data.createBean('Contacts', {
                id: _.uniqueId(),
                foo: 'bar'
            });

            field.addEmailOptions({related: model});

            expect(field.emailOptions.related).not.toBe(model);
            expect(field.emailOptions.related.toJSON()).toEqual(model.toJSON());
        });

        it('should not specify related on email options if model specified has no module', function() {
            field.addEmailOptions({related: {foo: 'bar'}});
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

        it('should return mailto with only to addresses', function() {
            var options = {
                to: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo1@bar.com'
                    })
                }, {
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo2@bar.com'
                    })
                }]
            };
            var url = field._buildMailToURL(options);
            expect(url).toEqual('mailto:foo1@bar.com,foo2@bar.com');
        });

        it('should return mailto with cc and bcc addresses in querystring', function() {
            var url = field._buildMailToURL({
                cc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo1@bar.com'
                    })
                }],
                bcc: [{
                    email: app.data.createBean('EmailAddresses', {
                        id: _.uniqueId(),
                        email_address: 'foo2@bar.com'
                    })
                }]
            });
            var expectedParams = {
                cc: 'foo1@bar.com',
                bcc: 'foo2@bar.com'
            };
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });

        it('should return mailto with subject and text body in querystring', function() {
            var expectedParams = {
                subject: 'Foo',
                body: 'Bar!'
            };
            var url = field._buildMailToURL({
                name: expectedParams.subject,
                description: expectedParams.body,
                description_html: '<b>' + expectedParams.body + '</b>'
            });
            expect(url).toEqual('mailto:?' + $.param(expectedParams));
        });
    });

    describe('Format Recipients To String', function() {
        using(
            'strings for email',
            [{
                expected: '',
                recipient: {}
            }, {
                expected: '',
                recipient: {
                    email: ''
                }
            }, {
                expected: 'foo@bar.com',
                recipient: {
                    email: 'foo@bar.com'
                }
            }],
            function(data) {
                it('should format the email addresses in a string', function() {
                    var actual = field._formatRecipientsToString(data.recipient);

                    expect(actual).toBe(data.expected);
                });
            }
        );

        it('should not return the email address when there is not one', function() {
            var recipient = {
                email: app.data.createBean('EmailAddresses')
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        using(
            'email addresses',
            [{
                expected: 'foo@bar.com',
                email: {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                }
            }, {
                expected: 'foo@bar.com',
                email: {
                    email_address: 'foo@bar.com'
                }
            }],
            function(data) {
                it('should return the email address when there is one', function() {
                    var recipient = {
                        email: app.data.createBean('EmailAddresses', data.email)
                    };
                    var actual = field._formatRecipientsToString(recipient);

                    expect(actual).toBe(data.expected);
                });
            }
        );

        it('should not return the email address when the bean is of the wrong module', function() {
            var recipient = {
                email: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        it('should return the primary address of the bean', function() {
            var recipient = {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should return the primary address of the bean when the email address does not have one', function() {
            var recipient = {
                email: app.data.createBean('EmailAddresses'),
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should not return the primary address of the bean if it is invalid', function() {
            var recipient = {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'foo@bar.com',
                        primary_address: false,
                        invalid_email: true,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('');
        });

        it('should return the email address over the primary address of the bean', function() {
            var recipient = {
                email: 'foo@bar.com',
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'biz@baz.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            };
            var actual = field._formatRecipientsToString(recipient);

            expect(actual).toBe('foo@bar.com');
        });

        it('should return more than one email address', function() {
            var recipients = [{
                email: app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'foo@bar.com'
                })
            }, {
                bean: app.data.createBean('Contacts', {
                    id: _.uniqueId(),
                    name: 'Helen Smith',
                    email: [{
                        email_address_id: _.uniqueId(),
                        email_address: 'biz@baz.com',
                        primary_address: true,
                        invalid_email: false,
                        opt_out: false
                    }]
                })
            }, {
                email: app.data.createBean('EmailAddresses', {
                    id: _.uniqueId(),
                    email_address: 'qux@qar.com'
                })
            }];
            var actual = field._formatRecipientsToString(recipients);

            expect(actual).toBe('foo@bar.com,biz@baz.com,qux@qar.com');
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
            var to = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'foo@bar.com'
            });
            var cc = app.data.createBean('EmailAddresses', {
                id: _.uniqueId(),
                email_address: 'foo2@bar.com'
            });

            $link.data({
                to: [{
                    email: to
                }],
                subject: 'Bar!!!'
            });
            field.emailOptions = {
                cc: [{
                    email: cc
                }],
                subject: 'Bar'
            };
            actual = field._retrieveEmailOptions($link);
            expect(actual).toEqual({
                to: [{
                    email: to
                }],
                cc: [{
                    email: cc
                }],
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
