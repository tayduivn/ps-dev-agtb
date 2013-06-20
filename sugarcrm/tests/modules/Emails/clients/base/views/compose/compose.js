describe("Emails.Views.Compose", function() {
    var app,
        view,
        dataProvider;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');
        SugarTest.loadComponent('base', 'view', 'compose', 'Emails');
        SugarTest.testMetadata.set();

        var context = app.context.getContext();
        context.set({
            module: 'Emails',
            create: true
        });
        context.prepare();

        view = SugarTest.createView('base', 'Emails', 'compose', null, context, true);
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    it("Intialize - model should not be empty", function() {
        expect(view.model.isNotEmpty).toBe(true);
    });

    describe('Render', function() {
        var setTitleStub, hideFieldStub, toggleSenderOptionsStub, prepopulateStub;

        beforeEach(function() {
            setTitleStub = sinon.stub(view, 'setTitle');
            hideFieldStub = sinon.stub(view, 'hideField');
            toggleSenderOptionsStub = sinon.stub(view, 'toggleSenderOptions');
            prepopulateStub = sinon.stub(view, 'prepopulate');
        });

        afterEach(function() {
            setTitleStub.restore();
            hideFieldStub.restore();
            toggleSenderOptionsStub.restore();
            prepopulateStub.restore();
        });

        it("No prepopulate on context - title should be set no fields pre-populated", function() {
            view._render();
            expect(setTitleStub).toHaveBeenCalled();
            expect(prepopulateStub.callCount).toEqual(0);
        });

        it("prepopulate on context - call is made to populate them", function() {
            var dummyPrepopulate = {subject: 'Foo!'};
            view.context.set('prepopulate', dummyPrepopulate);
            view._render();
            expect(prepopulateStub.callCount).toEqual(1);
            expect(prepopulateStub.lastCall.args).toEqual([dummyPrepopulate]);
        });

        //test different sender recipient scenarios
        dataProvider = [
            {
                'testComment': 'no cc or bcc => both hidden with links',
                'model': null,
                'hideFieldCount': 2,
                'hideFieldLastCallArgs': null,
                'toggleSenderLastCallArgs': ["to_addresses", true, true]
            },
            {
                'testComment': 'has cc => only bcc hidden with link',
                'model': {'cc_addresses':'foo@bar.com'},
                'hideFieldCount': 1,
                'hideFieldLastCallArgs': ['bcc_addresses'],
                'toggleSenderLastCallArgs': ["to_addresses", false, true]
            },
            {
                'testComment': 'has bcc => only cc hidden with link',
                'model': {'bcc_addresses':'foo@bar.com'},
                'hideFieldCount': 1,
                'hideFieldLastCallArgs': ['cc_addresses'],
                'toggleSenderLastCallArgs': ["to_addresses", true, false]
            },
            {
                'testComment': 'both cc & bcc => neither hidden, no links',
                'model': {'cc_addresses':'foo@bar.com','bcc_addresses':'foo@bar.com'},
                'hideFieldCount': 0,
                'hideFieldLastCallArgs': null,
                'toggleSenderLastCallArgs': ["to_addresses", false, false]
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.testComment, function() {
                view.model.off('change');
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

    describe("prepopulate", function () {
        var populateRelatedStub, modelSetStub, flag;

        beforeEach(function () {
            flag = false;
            populateRelatedStub = sinon.stub(view, 'populateRelated', function() {
                flag = true;
            });
            modelSetStub = sinon.stub(view.model, 'set', function() {
                flag = true;
            });
        });

        afterEach(function () {
            populateRelatedStub.restore();
            modelSetStub.restore();
        });

        it("Should trigger recipient add on context if to_addresses, cc_addresses, or bcc_addresses value is passed in.", function() {
            runs(function() {
                view.prepopulate({
                    to_addresses: [{email: "to@foo.com"}, {email: "too@foo.com"}],
                    cc_addresses: [{email: "cc@foo.com"}],
                    bcc_addresses: [{email: "bcc@foo.com"}]
                });
            });

            waitsFor(function() {
                return flag;
            }, 'model.set() should have been called but timeout expired', 1000);

            runs(function() {
                expect(modelSetStub.callCount).toBe(3); // once for each recipient type passed in
            });
        });

        it("should call populateRelated if related value passed", function () {
            runs(function() {
                view.prepopulate({related: {id: '123'}});
            });

            waitsFor(function() {
                return flag;
            }, 'populateRelated() should have been called but timeout expired', 1000);

            runs(function() {
                expect(populateRelatedStub.callCount).toBe(1);
            });
        });

        it("should set other values if passed", function () {
            runs(function() {
                view.prepopulate({foo: 'bar'});
            });

            waitsFor(function() {
                return flag;
            }, 'model.set() should have been called but timeout expired', 1000);

            runs(function() {
                expect(modelSetStub.calledOnce).toBe(true);
            });
        });
    });

    describe("populateRelated", function () {
        var relatedModel, fetchStub, fetchedModel, getFieldStub, parentId, parentValue, inputValues, fetchedValues;

        beforeEach(function () {
            inputValues = {
                id: '123',
                name: 'Input Name'
            };
            fetchedValues = {
                id: inputValues.id,
                name: 'Fetched Name'
            };
            relatedModel = new Backbone.Model(inputValues);
            fetchedModel = new Backbone.Model(fetchedValues);
            fetchStub = sinon.stub(relatedModel, 'fetch', function (params) {
                params.success(fetchedModel);
            });
            getFieldStub = sinon.stub(view, 'getField', function () {
                return {
                    setValue: function(model) {
                        parentId = model.id;
                        parentValue = model.value;
                    }
                };
            });
        });

        afterEach(function () {
            fetchStub.restore();
            getFieldStub.restore();
            parentId = undefined;
            parentValue = undefined;
        });

        it("should set the parent_name field with id and name on the relatedModel passed in", function () {
            view.populateRelated(relatedModel);
            expect(parentId).toEqual(inputValues.id);
            expect(parentValue).toEqual(inputValues.name);
        });

        it("should set the parent_name field with id and name on the fetched model when no name on the relatedModel passed in", function () {
            relatedModel.unset('name');
            view.populateRelated(relatedModel);
            expect(parentId).toEqual(fetchedValues.id);
            expect(parentValue).toEqual(fetchedValues.name);
        });

        it("should not set the parent_name field at all if no id on related Model", function () {
            relatedModel.unset('id');
            view.populateRelated(relatedModel);
            expect(parentId).toBeUndefined();
            expect(parentValue).toBeUndefined();
        });
    });

    describe('saveModel', function() {
        var apiCallStub, alertShowStub, alertDismissStub, disableButtonStub;

        beforeEach(function() {
            apiCallStub = sinon.stub(app.api, 'call', function(method, myURL, model, options) {
                options.success(model, null, options);
            });
            alertShowStub = sinon.stub(app.alert, 'show');
            alertDismissStub = sinon.stub(app.alert, 'dismiss');
            disableButtonStub = sinon.stub(view, 'setMainButtonsDisabled');

            app.drawer = {close: function() {}};

            view.model.off('change');
        });

        afterEach(function() {
            apiCallStub.restore();
            alertShowStub.restore();
            alertDismissStub.restore();
            disableButtonStub.restore();

            delete app.drawer;
        });

        it('should call mail api with correctly formatted model', function() {
            var actualModel,
                expectedStatus = 'ready',
                to_addresses   = new Backbone.Collection([{id: "1234", email: "foo@bar.com"}]);

            view.model.set('to_addresses', to_addresses);
            view.model.set('foo', 'bar');
            view.saveModel(expectedStatus, 'pending message', 'success message');

            expect(apiCallStub.lastCall.args[0]).toEqual('create');
            expect(apiCallStub.lastCall.args[1]).toMatch(/.*\/Mail/);

            actualModel = apiCallStub.lastCall.args[2];
            expect(actualModel.get('status')).toEqual(expectedStatus); //status set on model
            expect(actualModel.get('to_addresses')).toEqual(to_addresses); //email formatted correctly
            expect(actualModel.get('foo')).toEqual('bar'); //any other model attributes passed to api

            delete to_addresses;
        });

        it('should show pending message before call, then after call dismiss that message and show success', function() {
            var pending = 'pending message',
                success = 'success message';

            view.saveModel('ready', pending, success);

            expect(alertShowStub.firstCall.args[1].title).toEqual(pending);
            expect(alertDismissStub.firstCall.args[0]).toEqual(alertShowStub.firstCall.args[0]);
            expect(alertShowStub.secondCall.args[1].messages).toEqual(success);
        })
    });

    describe('Send', function() {
        var saveModelStub, alertShowStub;

        beforeEach(function() {
            saveModelStub = sinon.stub(view, 'saveModel');
            alertShowStub = sinon.stub(app.alert, 'show');

            view.model.off('change');
        });

        afterEach(function() {
            saveModelStub.restore();
            alertShowStub.restore();
        });

        it('should send email when to, subject and html_body fields are populated', function() {
            view.model.set('to_addresses', 'foo@bar.com');
            view.model.set('subject', 'foo');
            view.model.set('html_body', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when cc, subject and html_body fields are populated', function() {
            view.model.set('cc_addresses', 'foo@bar.com');
            view.model.set('subject', 'foo');
            view.model.set('html_body', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when bcc, subject and html_body fields are populated', function() {
            view.model.set('bcc_addresses', 'foo@bar.com');
            view.model.set('subject', 'foo');
            view.model.set('html_body', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should show error alert when address fields are empty', function() {
            view.model.set('subject', 'foo');
            view.model.set('html_body', 'bar');

            view.send();

            expect(saveModelStub.calledOnce).toBe(false);
            expect(alertShowStub.called).toBe(true);
        });

        it('should show confirmation alert message when subject field is empty', function() {
            view.model.unset('subject');
            view.model.set('html_body', 'bar');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when html_body field is empty', function() {
            view.model.set('subject', 'foo');
            view.model.unset('html_body');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when subject and html_body fields are empty', function() {
            view.model.unset('subject');
            view.model.unset('html_body');

            view.send();

            expect(saveModelStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });
    });

    describe("insert templates", function() {
        describe("replacing templates", function() {
            var insertTemplateAttachmentsStub,
                createBeanCollectionStub,
                updateEditorWithSignatureStub;

            beforeEach(function() {
                insertTemplateAttachmentsStub = sinon.stub(view, 'insertTemplateAttachments');
                createBeanCollectionStub      = sinon.stub(app.data, 'createBeanCollection', function() {
                    return {fetch:function(){}}
                });
                updateEditorWithSignatureStub = sinon.stub(view, "_updateEditorWithSignature");

                view.model.off('change');
            });

            afterEach(function() {
                insertTemplateAttachmentsStub.restore();
                createBeanCollectionStub.restore();
                updateEditorWithSignatureStub.restore();
            });

            it('should not populate editor if template parameter is not an object', function() {
                view.insertTemplate(null);
                expect(createBeanCollectionStub.callCount).toBe(0);
                expect(insertTemplateAttachmentsStub.callCount).toBe(0);
                expect(updateEditorWithSignatureStub.callCount).toBe(0);
                expect(view.model.get("subject")).toBeUndefined();
                expect(view.model.get("html_body")).toBeUndefined();
            });

            it("should not set content of subject when the template doesn't include a subject", function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    templateModel = new Bean({
                        id:        '1234',
                        body_html: bodyHtml
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('subject')).toBeUndefined();
                expect(view.model.get("html_body")).toBe(bodyHtml);
            });

            it('should set content of editor with html version of template', function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    subject       = 'This is my subject',
                    templateModel = new Bean({
                        id:        '1234',
                        subject:   subject,
                        body_html: bodyHtml
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('subject')).toBe(subject);
                expect(view.model.get("html_body")).toBe(bodyHtml);
            });

            it('should set content of editor with text only version of template', function() {
                var Bean          = SUGAR.App.Bean,
                    bodyHtml      = '<h1>Test</h1>',
                    bodyText      = 'Test',
                    subject       = 'This is my subject',
                    templateModel = new Bean({
                        id:         '1234',
                        subject:    subject,
                        body_html:  bodyHtml,
                        body:       bodyText,
                        text_only:  1
                    });

                view.insertTemplate(templateModel);
                expect(createBeanCollectionStub.callCount).toBe(1);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('subject')).toBe(subject);
                expect(view.model.get("html_body")).toBe(bodyText);
            });

            it("should call to insert the signature that was marked as the last one selected", function() {
                var bodyHtml      = '<h1>Test</h1>',
                    subject       = 'This is my subject',
                    templateModel = new app.Bean({
                        id:        '1234',
                        subject:   subject,
                        body_html: bodyHtml
                    }),
                    signature     = new app.Bean({id: "abcd"});

                view._lastSelectedSignature = signature;

                view.insertTemplate(templateModel);
                expect(updateEditorWithSignatureStub).toHaveBeenCalledWith(signature);
            })
        });
    });

    describe("Signatures", function() {
        beforeEach(function() {
            view.model.off('change');
        });

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

        it("should change the last selected signature, on success, to the one that is retrieved", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id}),
                results   = {
                    id:             id,
                    name:           "Signature A",
                    signature:      "Regards",
                    signature_html: "&lt;p&gt;Regards&lt;/p&gt;"
                };

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*\/rest\/v10\/Signatures\/" + id + ".*"), [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toEqual(results);
        });

        it("should not change the last selected signature, on success, when no signature is returned", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id}),
                results   = [];

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*\/rest\/v10\/Signatures\/" + id + ".*"), [
                200,
                {"Content-Type": "application/json"},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();
        });

        it("should not change the last selected signature on error", function() {
            var id        = "abcd",
                signature = new app.Bean({id: id});
                //alertStub = sinon.stub(app.alert);

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith("GET", new RegExp(".*\/rest\/v10\/Signatures\/" + id + ".*"), [404, {}, ""]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();

            //alertStub.restore();
        });

        describe("signature helpers", function() {
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

            var tag = "<signature />";

            dataProvider = [
                {
                    message:  "should prepend the signature block at the absolute beginning when <body> is not found",
                    body:     "my message body is rockin'",
                    prepend:  true,
                    expected: tag + "my message body is rockin'"
                },
                {
                    message:  "should prepend the signature block inside <body> when <body> is found",
                    body:     "<html><head></head><body>my message body is rockin'",
                    prepend:  true,
                    expected: "<html><head></head><body>" + tag + "my message body is rockin'"
                },
                {
                    message:  "should append the signature block at the absolute end when </body> is not found",
                    body:     "my message body is rockin'",
                    prepend:  false,
                    expected: "my message body is rockin'" + tag
                },
                {
                    message:  "should append the signature block inside </body> when </body> is found",
                    body:     "<html><head></head><body>my message body is rockin'</body></html>",
                    prepend:  false,
                    expected: "<html><head></head><body>my message body is rockin'" + tag + "</body></html>"
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    var actual = view._insertSignatureTag(data.body, tag, data.prepend);
                    expect(actual).toBe(data.expected);
                });
            }, this);
        });

        describe("insert a signature", function() {
            var htmlBody = "my message body is rockin'";

            dataProvider = [
                {
                    message:        "should append a signature when it is an object, the signature_html attribute exists, there is no existing signature, and the user preference says not to prepend the signature",
                    body:           htmlBody,
                    signature:      {signature_html: "<b>Sincerely, John</b>"},
                    expectedReturn: true,
                    expectedBody:   htmlBody + "<br class=\"signature-begin\" /><b>Sincerely, John</b><br class=\"signature-end\" />"
                },
                {
                    message:        "should insert a signature that runs from open tag until EOF when there is no close tag",
                    body:           htmlBody + "<br class=\"signature-begin\" /><b>Sincerely, John</b>" + htmlBody,
                    signature:      {signature_html: "<i>Regards, Jim</i>"},
                    expectedReturn: true,
                    expectedBody:   htmlBody + "<br class=\"signature-begin\" /><i>Regards, Jim</i><br class=\"signature-end\" />"
                },
                {
                    message:        "should insert a signature that runs from BOF until close tag when there is no open tag",
                    body:           htmlBody + "<b>Sincerely, John</b><br class=\"signature-end\" />" + htmlBody,
                    signature:      {signature_html: "<i>Regards, Jim</i>"},
                    expectedReturn: true,
                    expectedBody:   "<br class=\"signature-begin\" /><i>Regards, Jim</i><br class=\"signature-end\" />" + htmlBody
                },
                {
                    message:        "should not insert a signature because signature is not an object",
                    body:           htmlBody,
                    signature:      "<b>Sincerely, John</b>",
                    expectedReturn: false,
                    expectedBody:   htmlBody
                },
                {
                    message:        "should not insert a signature because the signature_html attribute does not exist",
                    body:           htmlBody,
                    signature:      {sig_html: "<b>Sincerely, John</b>"},
                    expectedReturn: false,
                    expectedBody:   htmlBody
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    view.model.set("html_body", data.body);
                    var actualReturn = view._insertSignature(data.signature),
                        actualBody   = view.model.get("html_body");
                    expect(actualReturn).toBe(data.expectedReturn);
                    expect(actualBody).toBe(data.expectedBody);
                });
            }, this);
        });
    });
    
    describe('InitializeSendEmailModel', function() {
        beforeEach(function() {
            view.model.off('change');
        });

        it('should populate the send model attachments/documents correctly with both attachments and sugar documents', function() {
            var sendModel,
                attachment1 = {id:'123',type:'upload'},
                attachment2 = {id:'123',type:'document'},
                attachment3 = {id:'123',type:'foo'};

            view.model.set('attachments', [attachment1,attachment2,attachment3]);
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('attachments')).toEqual([attachment1]);
            expect(sendModel.get('documents')).toEqual([attachment2]);
        });

        it('should populate the send model attachments/documents as empty when attachments not set', function() {
            var sendModel;
            view.model.unset('attachments');
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('attachments')).toEqual([]);
            expect(sendModel.get('documents')).toEqual([]);
        });

        it("should populate the related field according to how the Mail API expects it", function () {
            var sendModel,
                parentId = '123',
                parentType = 'Foo';
            view.model.set('parent_id', parentId);
            view.model.set('parent_type', parentType);
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('related')).toEqual({id: parentId, type: parentType});
        });
    });

});
