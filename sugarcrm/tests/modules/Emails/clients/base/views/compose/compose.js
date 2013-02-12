describe("Emails.Views.Compose", function() {
    var app,
        view,
        dataProvider;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'editable');
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'compose', 'Emails');
        SugarTest.testMetadata.set();
        view = SugarTest.createView('base', 'Emails', 'compose', null, null, true);
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    it("Intialize - model should not be empty", function() {
        expect(view.model.isNotEmpty).toBe(true);
    });

    describe('Render', function() {
        var setTitleStub, hideFieldStub, toggleSenderOptionsStub, populateToRecipientsStub;

        beforeEach(function() {
            setTitleStub = sinon.stub(view, 'setTitle'),
            hideFieldStub = sinon.stub(view, 'hideField'),
            toggleSenderOptionsStub = sinon.stub(view, 'toggleSenderOptions'),
            populateToRecipientsStub = sinon.stub(view, 'populateToRecipients');
        });

        afterEach(function() {
            setTitleStub.restore();
            hideFieldStub.restore();
            toggleSenderOptionsStub.restore();
            populateToRecipientsStub.restore();
        });

        it("No recipients on context - title should be set no recipients populated", function() {
            view._render();
            expect(setTitleStub).toHaveBeenCalled();
            expect(populateToRecipientsStub.callCount).toEqual(0);
        });

        it("Recipients on context - call is made to populate them", function() {
            var dummyRecipientModel = {'foo':'bar'};
            view.context.set('recipientModel', dummyRecipientModel);
            view._render();
            expect(populateToRecipientsStub.callCount).toEqual(1);
            expect(populateToRecipientsStub.lastCall.args).toEqual([dummyRecipientModel]);
        });

        //test different sender recipient scenarios
        dataProvider = [
            {
                'testComment': 'model not new and has bcc => only cc hidden with link',
                'model': {'id':'123','bcc_addresses':'foo@bar.com'},
                'hideFieldCount': 1,
                'hideFieldLastCallArgs': ['cc_addresses'],
                'toggleSenderLastCallArgs': ["to_addresses", true, false]
            },
            {
                'testComment': 'model not new and both cc & bcc => neither hidden, no links',
                'model': {'id':'123','cc_addresses':'foo@bar.com','bcc_addresses':'foo@bar.com'},
                'hideFieldCount': 0,
                'hideFieldLastCallArgs': null,
                'toggleSenderLastCallArgs': ["to_addresses", false, false]
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.testComment, function() {
                if (data.model) {
                    view.model.set(data.model);
                }
                view._render();
                expect(hideFieldStub.callCount).toEqual(data.hideFieldCount);
                if (data.hideFieldLastCallArgs) {
                    expect(hideFieldStub.lastCall.args).toEqual(data.hideFieldLastCallArgs);
                }
                expect(toggleSenderOptionsStub.lastCall.args).toEqual(data.toggleSenderLastCallArgs);
            });
        });
    });

    describe('populateToRecipients', function() {
        var recipientModel, expectedResult, actualResult, contextTriggerStub;

        beforeEach(function() {
            expectedResult = {'id': '123', 'module': 'Foo'};
            recipientModel = new Backbone.Model({
                'id': expectedResult.id,
                '_module': expectedResult.module
            });
            contextTriggerStub = sinon.stub(view.context, 'trigger', function(trigger, recipient) {
                if (recipient) {
                    actualResult = recipient.attributes;
                }
            });
        });

        afterEach(function() {
            delete expectedResult;
            delete recipientModel;
            contextTriggerStub.restore();
        });

        it('should send email and name when email1 and name on model', function() {
            expectedResult.name = 'Tyler';
            expectedResult.email = 'foo@bar.com';
            recipientModel.set('name', expectedResult.name);
            recipientModel.set('email1', expectedResult.email);
            view.populateToRecipients(recipientModel);
            expect(actualResult).toEqual(expectedResult);
        });

        it('should send email only when email1 and no name on model', function() {
            expectedResult.email = 'foo@bar.com';
            recipientModel.set('email1', expectedResult.email);
            view.populateToRecipients(recipientModel);
            expect(actualResult).toEqual(expectedResult);
        });

        it('should send primary email and name when array on model', function() {
            expectedResult.name = 'Tyler';
            expectedResult.email = 'tyler@foo.com';
            recipientModel.set('email', [
                {'email_address': 'foo@bar.com'},
                {'email_address': expectedResult.email, 'primary_address': 1}
            ]);
            recipientModel.set('assigned_user_name', expectedResult.name);
            view.populateToRecipients(recipientModel);
            expect(actualResult).toEqual(expectedResult);
        });

        it('should not trigger event if no primary address on model', function() {
            recipientModel.set('email', [
                {'email_address': 'foo@bar.com'},
                {'email_address': 'tyler@foo.com'}
            ]);
            recipientModel.set('assigned_user_name', expectedResult.name);
            view.populateToRecipients(recipientModel);
            expect(contextTriggerStub.callCount).toBe(0);
        });

        it('should not trigger event if primary address is empty', function() {
            recipientModel.set('email', [
                {'email_address': 'foo@bar.com'},
                {'primary_address': 1}
            ]);
            view.populateToRecipients(recipientModel);
            expect(contextTriggerStub.callCount).toBe(0);
        });

        it('should not trigger event if no email at all', function() {
            view.populateToRecipients(recipientModel);
            expect(contextTriggerStub.callCount).toBe(0);
        });

    });

    describe('saveModel', function() {
        var apiCallStub, alertShowStub, alertDismissStub;

        beforeEach(function() {
            apiCallStub = sinon.stub(app.api, 'call', function(method, myURL, model, options) {
                options.success(model, null, options);
            });
            alertShowStub = sinon.stub(app.alert, 'show');
            alertDismissStub = sinon.stub(app.alert, 'dismiss');
        });

        afterEach(function() {
            apiCallStub.restore();
            alertShowStub.restore();
            alertDismissStub.restore();
        });

        it('should call mail api with correctly formatted model', function() {
            var actualModel,
                expectedStatus = 'ready';

            view.model.set('to_addresses', 'foo@bar.com');
            view.model.set('foo', 'bar');
            view.saveModel(expectedStatus, 'pending message', 'success message');

            expect(apiCallStub.lastCall.args[0]).toEqual('create');
            expect(apiCallStub.lastCall.args[1]).toMatch(/.*\/Mail/);

            actualModel = apiCallStub.lastCall.args[2];
            expect(actualModel.get('status')).toEqual(expectedStatus); //status set on model
            expect(actualModel.get('to_addresses')).toEqual([{email: 'foo@bar.com'}]); //email formatted correctly
            expect(actualModel.get('foo')).toEqual('bar'); //any other model attributes passed to api
        });

        it('should show pending message before call, then after call dismiss that message and show success', function() {
            var pending = 'pending message',
                success = 'success message';

            view.saveModel('ready', pending, success);

            expect(alertShowStub.firstCall.args[1].title).toEqual(pending);
            expect(alertDismissStub.firstCall.args[0]).toEqual(alertShowStub.firstCall.args[0]);
            expect(alertShowStub.secondCall.args[1].title).toEqual(success);
        })
    });

    describe("Signatures", function() {
        it("should retrieve a signature from the SignaturesApi when the signature ID is present", function() {
            var apiStub   = sinon.stub(app.api, "call"),
                id        = "abcd", // the actual ID doesn't matter
                signature = new app.Bean({id: id}), // no other attributes are needed for this test
                regex     = new RegExp(".*/Signatures/" + id + "$", "gi");

            view._updateEditorWithSignature(signature);
            expect(apiStub.lastCall.args[1]).toMatch(regex);

            apiStub.restore();
        });

        it("should not retrieve a signature from the SignaturesApi when the signature ID is not present", function() {
            var apiStub   = sinon.stub(app.api, "call"),
                signature = new app.Bean();

            view._updateEditorWithSignature(signature);
            expect(apiStub.callCount).toBe(0);

            apiStub.restore();
        });

        dataProvider = [
            {
                message:   "should format a signature with &lt; and/or &gt; to use < and > respectively",
                signature: "This &lt;signature&gt; has HTML-style brackets",
                expected:  "This <signature> has HTML-style brackets"
            },
            {
                message:   "should leave a signature as is if &lt; and &gt; are not found",
                signature: "This signature has no HTML-style brackets",
                expected:  "This signature has no HTML-style brackets"
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = view._formatSignature(data.signature);
                expect(actual).toBe(data.expected);
            });
        }, this);

        describe("insert a signature", function() {
            var field,
                fieldStub;

            beforeEach(function() {
                field = {
                    _content: "my message body is rockin'",

                    getEditorContent: function() {
                        return this._content;
                    },

                    setEditorContent: function(content) {
                        this._content = content;
                    }
                };

                fieldStub = sinon.stub(view, "getField");
                fieldStub.returns(field);
            });

            afterEach(function() {
                fieldStub.restore();
            });

            dataProvider = [
                {
                    message:   "should insert a signature because signature is an object and the signature_html attribute exists",
                    signature: {signature_html: "<b>Sincerely, John</b>"},
                    expected:  "my message body is rockin'<b>Sincerely, John</b>"
                },
                {
                    message:   "should not insert a signature because signature is not an object",
                    signature: "<b>Sincerely, John</b>",
                    expected:  "my message body is rockin'"
                },
                {
                    message:   "should not insert a signature because the signature_html attribute does not exist",
                    signature: {sig_html: "<b>Sincerely, John</b>"},
                    expected:  "my message body is rockin'"
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    view._insertSignature(data.signature);
                    var actual = field.getEditorContent();
                    expect(actual).toBe(data.expected);
                });
            }, this);
        });
    });
});
