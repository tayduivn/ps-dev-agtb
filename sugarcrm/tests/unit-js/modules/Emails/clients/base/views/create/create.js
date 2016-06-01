describe('Emails.Views.Create', function() {
    var app,
        view,
        dataProvider,
        sandbox;

    beforeEach(function() {
        var context,
            viewName = 'create',
            moduleName = 'Emails';
        app = SugarTest.app;
        app.drawer = { on: $.noop, off: $.noop, getHeight: $.noop, close: $.noop, reset: $.noop };

        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('create', 'view', 'base', 'recipient-options', moduleName);
        SugarTest.loadComponent('base', 'view', 'record');
        SugarTest.loadComponent('base', 'view', 'create');

        // set UserSignatures metadata
        SugarTest.testMetadata.updateModuleMetadata('UserSignatures', {
            fields: {
                name: {
                    name: 'name',
                    vname: 'LBL_NAME',
                    type: 'varchar',
                    len: 255,
                    comment: 'Name of this bean'
                }
            },
            favoritesEnabled: true,
            views: [],
            layouts: [],
            _hash: 'bc6fc50d9d0d3064f5d522d9e15968fa'
        });

        SugarTest.testMetadata.set();
        SugarTest.app.data.declareModels();
        context = app.context.getContext();
        context.set({
            module: moduleName,
            create: true
        });
        context.prepare();

        view = SugarTest.createView('base', moduleName, viewName, null, context, true);

        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        app.drawer = undefined;
        view.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        sandbox.restore();
    });

    it('Initialize - model should not be empty', function() {
        expect(view.model.isNotEmpty).toBe(true);
    });

    describe('Render', function() {
        var setTitleStub, prepopulateStub;

        beforeEach(function() {
            setTitleStub = sandbox.stub(view, 'setTitle');
            prepopulateStub = sandbox.stub(view, 'prepopulate');
        });

        it('No prepopulate on context - title should be set no fields pre-populated', function() {
            sandbox.stub(view, '_setAttachmentVisibility');
            sandbox.stub(view, 'notifyConfigurationStatus');
            view._render();
            expect(setTitleStub).toHaveBeenCalled();
            expect(prepopulateStub.callCount).toEqual(0);
        });

        it('prepopulate on context - call is made to populate them', function() {
            var dummyPrepopulate = {name: 'Foo!'};

            sandbox.stub(view, '_setAttachmentVisibility');
            sandbox.stub(view, 'notifyConfigurationStatus');
            view.context.set('prepopulate', dummyPrepopulate);
            view._render();
            expect(prepopulateStub.callCount).toEqual(1);
            expect(prepopulateStub.lastCall.args).toEqual([dummyPrepopulate]);
        });

        it('No email client preference error - should not disable the send button or alert user', function() {
            var alertShowStub = sandbox.stub(app.alert, 'show');

            sandbox.stub(view, '_setAttachmentVisibility');
            sandbox.stub(app.user, 'getPreference')
                .withArgs('email_client_preference')
                .returns({type: 'sugar'});

            view._render();

            expect(alertShowStub.callCount).toBe(0);
        });

        it('Email client preference error - should disable the send button and alert user', function() {
            var alertShowStub = sandbox.stub(app.alert, 'show'),
                sendField = {setDisabled: $.noop},
                spyOnField = sandbox.spy(sendField, 'setDisabled');

            sandbox.stub(view, '_setAttachmentVisibility');
            sandbox.stub(app.user, 'getPreference')
                .withArgs('email_client_preference')
                .returns({type: 'sugar', error: {code: 101, message: 'LBL_EMAIL_INVALID_USER_CONFIGURATION'}});
            sandbox.stub(view, 'getField')
                .withArgs('send_button')
                .returns(sendField);

            view._render();

            expect(alertShowStub.callCount).toBe(1);
            expect(spyOnField.calledOnce).toBe(true);
            expect(view._userHasConfiguration).toBe(false);
        });
    });

    describe('prepopulate', function() {
        var populateRelatedStub, modelSetStub, populateForModulesStub, flag, mockCollection;

        beforeEach(function() {
            flag = false;
            populateRelatedStub = sandbox.stub(view, '_populateRelated', function() {
                flag = true;
            });

            populateForModulesStub = sandbox.stub(view, '_populateForModules', function() {
                flag = true;
            });

            mockCollection = {add: sandbox.stub()};
            view.model.set({to: mockCollection, cc: mockCollection, bcc: mockCollection});

            modelSetStub = sandbox.stub(view.model, 'set', function() {
                flag = true;
            });
        });

        it('Should trigger recipient add on context if to, cc, or bcc value is passed in.', function() {
            view.prepopulate({
                to: [{email: 'to@foo.com'}, {email: 'too@foo.com'}],
                cc: [{email: 'cc@foo.com'}],
                bcc: [{email: 'bcc@foo.com'}]
            });
            expect(mockCollection.add.callCount).toBe(3); // once for each recipient type passed in
        });

        it('should call _populateRelated if related value passed', function() {
            runs(function() {
                view.prepopulate({related: {id: '123'}});
            });

            waitsFor(function() {
                return flag;
            }, '_populateRelated() should have been called but timeout expired', 1000);

            runs(function() {
                expect(populateRelatedStub.callCount).toBe(1);
                expect(populateForModulesStub.callCount).toBe(1);
            });
        });

        it('should set other values if passed', function() {
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

    describe('_populateRelated', function() {
        var relatedModel, fetchedModel, parentId, parentValue, inputValues, fetchedValues;

        beforeEach(function() {
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
            relatedModel.module = fetchedModel.module = 'foo';
            sandbox.stub(relatedModel, 'fetch', function(params) {
                params.success(fetchedModel);
            });
            sandbox.stub(view, 'getField', function() {
                return {
                    isAvailableParentType: function() {
                        return true;
                    },
                    setValue: function(model) {
                        parentId = model.id;
                        parentValue = model.value;
                    }
                };
            });
        });

        afterEach(function() {
            parentId = undefined;
            parentValue = undefined;
        });

        it('should set the parent_name field with id and name on the relatedModel passed in', function() {
            view._populateRelated(relatedModel);
            expect(parentId).toEqual(inputValues.id);
            expect(parentValue).toEqual(inputValues.name);
        });

        it('should set the parent_name field with id and name on the fetched model when no name on the relatedModel passed in', function() {
            relatedModel.unset('name');
            view._populateRelated(relatedModel);
            expect(parentId).toEqual(fetchedValues.id);
            expect(parentValue).toEqual(fetchedValues.name);
        });

        it('should not set the parent_name field at all if no id on related Model', function() {
            relatedModel.unset('id');
            view._populateRelated(relatedModel);
            expect(parentId).toBeUndefined();
            expect(parentValue).toBeUndefined();
        });
    });

    describe('populateForCases', function() {
        var configStub,
            caseSubjectMacro = '[CASE:%1]',
            relatedModel,
            contacts;

        beforeEach(function() {
            var relatedCollection;

            configStub = sandbox.stub(app.metadata, 'getConfig', function() {
                return {
                    'inboundEmailCaseSubjectMacro': caseSubjectMacro
                };
            });

            view.model.set('to', new app.data.createMixedBeanCollection());

            relatedModel = app.data.createBean('Cases', {
                id: '123',
                case_number: '100',
                name: 'My Case'
            });

            relatedCollection = app.data.createBeanCollection('Contacts');
            contacts = [];
            sandbox.stub(relatedCollection, 'fetch', function(params) {
                params.success({models: contacts});
            });

            sandbox.stub(relatedModel, 'getRelatedCollection', function() {
                return relatedCollection;
            });
        });

        afterEach(function() {
            configStub.restore();
        });

        it('should populate only the subject and when cases does not have any related contacts', function() {
            view._populateForCases(relatedModel);
            expect(view.model.get('name')).toEqual('[CASE:100] My Case');
            expect(view.model.get('to').length).toBe(0);
        });

        it('should populate both the subject and "to" field when cases has related contacts', function() {
            contacts = [
                app.data.createBean('Contacts', {email: 'abc@foo.com'}),
                app.data.createBean('Contacts', {email: 'def@foo.com'})
            ];

            view._populateForCases(relatedModel);
            expect(view.model.get('name')).toEqual('[CASE:100] My Case');
            expect(view.model.get('to').length).toEqual(2);
        });
    });

    describe('Recipient Options', function() {
        var toggleFieldVisibilitySpy,
            isRecipientOptionButtonActive;

        beforeEach(function() {
            toggleFieldVisibilitySpy = sandbox.spy(view, '_toggleFieldVisibility');
            sandbox.stub(view, '_renderRecipientOptions', function() {
                var template = app.template.getView('create.recipient-options', view.module);
                view.$el.append(template({'module' : view.module}));
            });
            sandbox.stub(view, '_setAttachmentVisibility');
        });

        isRecipientOptionButtonActive = function(fieldName) {
            var selector = '[data-toggle-field="' + fieldName + '"]';
            return view.$(selector).hasClass('active');
        };

        using('CC/BCC values',
            [
                [
                    {cc: [], bcc: []},
                    {ccActive: false, bccActive: false}
                ],
                [
                    {cc: ['foo@bar.com'], bcc: []},
                    {ccActive: true, bccActive: false}
                ],
                [
                    {cc: [], bcc: ['foo@bar.com']},
                    {ccActive: false, bccActive: true}
                ],
                [
                    {cc: ['foo@bar.com'], bcc: ['bar@foo.com']},
                    {ccActive: true, bccActive: true}
                ]
            ],
            function(value, result) {
                it('should add recipient options on render and initialize cc/bcc fields appropriately', function() {
                    view.model.set(value);
                    view._render();

                    // check buttons
                    expect(isRecipientOptionButtonActive('cc')).toBe(result.ccActive);
                    expect(isRecipientOptionButtonActive('bcc')).toBe(result.bccActive);

                    // check field visibility
                    expect(toggleFieldVisibilitySpy.firstCall.args).toEqual(['cc', result.ccActive]);
                    expect(toggleFieldVisibilitySpy.secondCall.args).toEqual(['bcc', result.bccActive]);
                });
            }
        );

        it('should toggle recipient option between active/inactive state when active flag not specified', function() {
            var fieldName = 'cc';
            view._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            view.toggleRecipientOption(fieldName);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(true);
            view.toggleRecipientOption(fieldName);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
        });

        it('should set recipient option to active when active flag is true', function() {
            var fieldName = 'cc';
            view._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            view.toggleRecipientOption(fieldName, true);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(true);
            view.toggleRecipientOption(fieldName, true);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(true);
        });

        it('should set recipient option to inactive when active flag is false', function() {
            var fieldName = 'cc';
            view._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            view.toggleRecipientOption(fieldName, false);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
        });

        it('should toggle recipient option between active/inactive state when cc/bcc buttons clicked', function() {
            view._render();
            expect(isRecipientOptionButtonActive('bcc')).toBe(false);
            view.$('[data-toggle-field="bcc"]').click();
            expect(isRecipientOptionButtonActive('bcc')).toBe(true);
            view.$('[data-toggle-field="bcc"]').click();
            expect(isRecipientOptionButtonActive('bcc')).toBe(false);
        });
    });

    describe('Send', function() {
        var saveStub, alertShowStub;

        beforeEach(function() {
            saveStub = sandbox.stub(view, 'save');
            alertShowStub = sandbox.stub(app.alert, 'show');

            view.model.off('change');
        });

        it('should send email when to, subject and html_body fields are populated', function() {
            view.model.set('to', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when cc, subject and html_body fields are populated', function() {
            view.model.set('cc', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should send email when bcc, subject and html_body fields are populated', function() {
            view.model.set('bcc', 'foo@bar.com');
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveStub.calledOnce).toBe(true);
            expect(alertShowStub.called).toBe(false);
        });

        it('should show error alert when address fields are empty', function() {
            view.model.set('name', 'foo');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveStub.calledOnce).toBe(false);
            expect(alertShowStub.called).toBe(true);
        });

        it('should show confirmation alert message when subject field is empty', function() {
            view.model.unset('name');
            view.model.set('description_html', 'bar');

            view.send();

            expect(saveStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when html_body field is empty', function() {
            view.model.set('name', 'foo');
            view.model.unset('description_html');

            view.send();

            expect(saveStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        it('should show confirmation alert message when subject and html_body fields are empty', function() {
            view.model.unset('name');
            view.model.unset('description_html');

            view.send();

            expect(saveStub.called).toBe(false);
            expect(alertShowStub.calledOnce).toBe(true);
        });

        using(
            'content with variables and related to is not set',
            [
                [
                    'Hi $contact_name',
                    'How are you?',
                    ''
                ],
                [
                    'Hello there',
                    'Hi, $account_name, how are you?',
                    ''
                ],
                [
                    'Read this!',
                    '<b>What do you think?</b>',
                    '$contact_name, What do you think?'
                ],
                [
                    'Hi $contact_name',
                    'Hi, $account_name, how are you?',
                    '$contact_name, What do you think?'
                ]
            ],
            function(subject, htmlBody, textBody) {
                it('should show confirmation alert when content has variables and related to is not set', function() {
                    view.model.set('to', 'foo@bar.com');
                    view.model.set('name', subject);
                    view.model.set('description_html', htmlBody);
                    view.model.set('description', textBody);
                    view.send();

                    expect(saveStub).not.toHaveBeenCalled();
                    expect(alertShowStub).toHaveBeenCalled();
                });
            }
        );

        using(
            'content with variables and related to is set',
            [
                [
                    'Hi $contact_name',
                    'How are you?',
                    ''
                ],
                [
                    'Hello there',
                    'Hi, $account_name, how are you?',
                    ''
                ],
                [
                    'Read this!',
                    '<b>What do you think?</b>',
                    '$contact_name, What do you think?'
                ],
                [
                    'Hi $contact_name',
                    'Hi, $account_name, how are you?',
                    '$contact_name, What do you think?'
                ]
            ],
            function(subject, htmlBody, textBody) {
                it('should send email when content has variables and related to is set', function() {
                    view.model.set('to', 'foo@bar.com');
                    view.model.set('name', subject);
                    view.model.set('description_html', htmlBody);
                    view.model.set('description', textBody);
                    view.model.set('parent_type', 'Contacts');
                    view.model.set('parent_id', _.uniqueId());
                    view.send();

                    expect(saveStub).toHaveBeenCalled();
                    expect(alertShowStub).not.toHaveBeenCalled();
                });
            }
        );

        it('should send email when content does not have variables', function() {
            view.model.set('to', 'foo@bar.com');
            view.model.set('name', 'Read this!');
            view.model.set('description_html', '<b>What do you think?</b>');
            view.model.set('description', 'What do you think?');
            view.send();

            expect(saveStub).toHaveBeenCalled();
            expect(alertShowStub).not.toHaveBeenCalled();
        });
    });

    describe('insert templates', function() {
        describe('confirm template', function() {
            beforeEach(function() {
                sandbox.stub(view, '_insertTemplate');
                sandbox.stub(app.alert, 'show');
            });

            it('should warn the user about replacing the content', function() {
                var template = app.data.createBean('EmailTemplates', {
                    id: _.uniqueId(),
                    name: 'template',
                    body_html: 'foo bar'
                });

                sandbox.stub(view, '_getFullContent').returns('previous content');
                view._confirmTemplate(template);

                expect(view._insertTemplate).not.toHaveBeenCalled();
                expect(app.alert.show).toHaveBeenCalled();
            });

            it('should not warn the user about replacing the content', function() {
                var template = app.data.createBean('EmailTemplates', {
                    id: _.uniqueId(),
                    name: 'template',
                    body_html: 'foo bar'
                });

                sandbox.stub(view, '_getFullContent').returns('');
                view._confirmTemplate(template);

                expect(view._insertTemplate).toHaveBeenCalled();
                expect(app.alert.show).not.toHaveBeenCalled();
            });
        });

        describe('inserting attachments', function() {
            beforeEach(function() {
                sandbox.stub(app.alert, 'show');
                app.config.maxAggregateEmailAttachmentsBytes = 10000000;
            });

            it('should show warning with a single attachment totaling over 10MB', function() {
                var Bean = SUGAR.App.Bean;
                var attachmentModel = new Bean({
                    id: _.uniqueId(),
                    _action: 'create',
                    file_size: 11000000
                });

                sandbox.stub(view, 'getField')
                    .withArgs('attachments')
                    .returns({
                        _attachments: app.data.createBeanCollection('Notes', attachmentModel)
                    });

                view._checkAttachmentLimit();
                expect(app.alert.show).toHaveBeenCalledWith('email-attachment-status');
            });

            it('should show warning with multiple attachments totaling over 10MB', function() {
                var Bean = SUGAR.App.Bean;
                var attachmentModel = new Bean({
                    id: _.uniqueId(),
                    _action: 'create',
                    file_size: 4000000
                });
                var attachmentModel2 = new Bean({
                    id: _.uniqueId(),
                    _action: 'create',
                    file_size: 4000000
                });
                var attachmentModel3 = new Bean({
                    id: _.uniqueId(),
                    _action: 'create',
                    file_size: 4000000
                });

                sandbox.stub(view, 'getField')
                    .withArgs('attachments')
                    .returns({
                        _attachments: app.data.createBeanCollection('Notes', [
                            attachmentModel,
                            attachmentModel2,
                            attachmentModel3
                        ])
                    });

                view._checkAttachmentLimit();
                expect(app.alert.show).toHaveBeenCalledWith('email-attachment-status');
            });

            it('should not show warning with an attachment under 10MB', function() {
                var Bean = SUGAR.App.Bean;
                var attachmentModel = new Bean({
                    id: _.uniqueId(),
                    _action: 'create',
                    file_size: 9000000
                });

                sandbox.stub(view, 'getField')
                    .withArgs('attachments')
                    .returns({
                        _attachments: app.data.createBeanCollection('Notes', attachmentModel)
                    });

                view._checkAttachmentLimit();
                expect(app.alert.show).not.toHaveBeenCalledWith('email-attachment-status');
            });

            it('should not show warning with multiple attachments totaling over 10MB when one is queued for removal',
                function() {
                    var Bean = SUGAR.App.Bean;
                    var attachmentModel = new Bean({
                        id: _.uniqueId(),
                        _action: 'create',
                        file_size: 4000000
                    });
                    var attachmentModel2 = new Bean({
                        id: _.uniqueId(),
                        _action: 'create',
                        file_size: 4000000
                    });
                    var attachmentModel3 = new Bean({
                        id: _.uniqueId(),
                        _action: 'delete',
                        file_size: 4000000
                    });

                    sandbox.stub(view, 'getField')
                        .withArgs('attachments')
                        .returns({
                            _attachments: app.data.createBeanCollection('Notes', [
                                attachmentModel,
                                attachmentModel2,
                                attachmentModel3
                            ])
                        });

                    view._checkAttachmentLimit();
                    expect(app.alert.show).not.toHaveBeenCalledWith('email-attachment-status');
                }
            );
        });

        describe('replacing templates', function() {
            var updateEditorWithSignatureStub;

            beforeEach(function() {
                sandbox.spy(view, 'trigger');
                updateEditorWithSignatureStub = sandbox.stub(view, '_updateEditorWithSignature');
                view.model.off('change');
            });

            it('should not populate editor if template parameter is not an object', function() {
                view._insertTemplate(null);
                expect(view.trigger).not.toHaveBeenCalledWith('email_attachments:template:add', null);
                expect(updateEditorWithSignatureStub.callCount).toBe(0);
                expect(view.model.get('name')).toBeUndefined();
                expect(view.model.get('description_html')).toBeUndefined();
            });

            it('should not set content of subject when the template does not include a subject', function() {
                var Bean = SUGAR.App.Bean,
                    bodyHtml = '<h1>Test</h1>',
                    templateModel = new Bean({
                        id: '1234',
                        body_html: bodyHtml
                    });

                view._insertTemplate(templateModel);
                expect(view.trigger).toHaveBeenCalledWith('email_attachments:template:add', templateModel);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBeUndefined();
                expect(view.model.get('description_html')).toBe(bodyHtml);
            });

            it('should set content of editor with html version of template', function() {
                var Bean = SUGAR.App.Bean,
                    bodyHtml = '<h1>Test</h1>',
                    subject = 'This is my subject',
                    templateModel = new Bean({
                        id: '1234',
                        subject: subject,
                        body_html: bodyHtml
                    });

                view._insertTemplate(templateModel);
                expect(view.trigger).toHaveBeenCalledWith('email_attachments:template:add', templateModel);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBe(subject);
                expect(view.model.get('description_html')).toBe(bodyHtml);
            });

            it('should set content of editor with text only version of template', function() {
                var Bean = SUGAR.App.Bean,
                    bodyHtml = '<h1>Test</h1>',
                    bodyText = 'Test',
                    subject = 'This is my subject',
                    templateModel = new Bean({
                        id: '1234',
                        subject: subject,
                        body_html: bodyHtml,
                        body: bodyText,
                        text_only: 1
                    });

                view._insertTemplate(templateModel);
                expect(view.trigger).toHaveBeenCalledWith('email_attachments:template:add', templateModel);
                expect(updateEditorWithSignatureStub.callCount).toBe(1);
                expect(view.model.get('name')).toBe(subject);
                expect(view.model.get('description_html')).toBe(bodyText);
            });

            it('should call to insert the signature that was marked as the last one selected', function() {
                var bodyHtml = '<h1>Test</h1>',
                    subject = 'This is my subject',
                    templateModel = new app.Bean({
                        id: '1234',
                        subject: subject,
                        body_html: bodyHtml
                    }),
                    signature = new app.Bean({id: 'abcd'});

                view._lastSelectedSignature = signature;

                view._insertTemplate(templateModel);
                expect(view.trigger).toHaveBeenCalledWith('email_attachments:template:add', templateModel);
                expect(updateEditorWithSignatureStub).toHaveBeenCalledWith(signature);
            });
        });
    });

    describe('Signatures', function() {
        var ajaxSpy;

        beforeEach(function() {
            ajaxSpy = sandbox.spy($, 'ajax');
            view.model.off('change');

            sandbox.stub(view, '_insertInEditor', function() {
                return view.model.get('description_html');
            });
        });

        it('should retrieve a signature when the signature ID is present', function() {
            var id = 'abcd',
                signature = new app.Bean({id: id});

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', /.*rest\/v10\/UserSignatures\/.*/,
                [200, { 'Content-Type': 'application/json'}, JSON.stringify({})]);

            view._updateEditorWithSignature(signature);
            expect(ajaxSpy.getCall(0).args[0].url).toContain('rest/v10/UserSignatures');
        });

        it('should not retrieve a signature when the signature ID is not present', function() {
            var signature = new app.Bean();

            view._updateEditorWithSignature(signature);
            expect(ajaxSpy.callCount).toBe(0);
        });

        it('should change the last selected signature, on success, to the one that is retrieved', function() {
            var id = 'abcd',
                signature = new app.Bean({id: id}),
                results = {
                    id: id,
                    name: 'Signature A',
                    signature: 'Regards',
                    signature_html: '&lt;p&gt;Regards&lt;/p&gt;'
                };

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', new RegExp('.*rest\/v10\/UserSignatures\/' + id + '.*'), [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature.attributes).toEqual(results);
        });

        it('should not change the last selected signature, on success, when no signature is returned', function() {
            var id = 'abcd',
                signature = new app.Bean({id: id}),
                results = [];

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', new RegExp('.*rest\/v10\/UserSignatures\/' + id + '.*'), [
                200,
                {'Content-Type': 'application/json'},
                JSON.stringify(results)
            ]);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();
        });

        it('should not change the last selected signature on error', function() {
            var id = 'abcd',
                signature = new app.Bean({id: id});

            SugarTest.seedFakeServer();
            SugarTest.server.respondWith('GET', new RegExp('.*rest\/v10\/UserSignatures\/' + id + '.*'), [404, {}, '']);

            view._lastSelectedSignature = null;
            view._updateEditorWithSignature(signature);
            SugarTest.server.respond();

            expect(view._lastSelectedSignature).toBeNull();
        });

        describe('signature helpers', function() {
            dataProvider = [
                {
                    message: 'should format a signature with &lt; and/or &gt; to use < and > respectively',
                    signature: 'This &lt;signature&gt; has HTML-style brackets',
                    expected: 'This <signature> has HTML-style brackets'
                },
                {
                    message: 'should leave a signature as is if &lt; and &gt; are not found',
                    signature: 'This signature has no HTML-style brackets',
                    expected: 'This signature has no HTML-style brackets'
                }
            ];

            _.each(dataProvider, function(data) {
                it(data.message, function() {
                    var actual = view._formatSignature(data.signature);
                    expect(actual).toBe(data.expected);
                });
            }, this);
        });

        describe('insert a signature', function() {
            var signatureTagBegin = '<div class="signature">',
                signatureTagEnd = '</div>',
                signature;

            beforeEach(function() {
                sandbox.restore();
                sandbox.stub(view, '_insertInEditor', function(content) {
                    return view.model.get('description_html') + content;
                });
            });

            it('should append the signature to the email body', function() {
                var id = 'abcd',
                    htmlBody = 'my message body is awesome!';

                signature = new app.Bean({
                    id: id,
                    name: 'Signature A',
                    signature: 'Regards',
                    signature_html: '&lt;p&gt;Regards&lt;/p&gt;'
                });

                signature.set('signature_html', view._formatSignature(signature.get('signature_html')));
                view.model.set('description_html', htmlBody);
                var expectedBody = htmlBody + signatureTagBegin + signature.get('signature_html') + signatureTagEnd;
                var actualReturn = view._insertSignature(signature);
                expect(actualReturn).toBe(true);
                expect(view.model.get('description_html')).toBe(expectedBody);
            });

            it('should remove a nested signature from the email body', function() {
                var id = 'abcd',
                    message = 'my message body is awesome!' +
                        '<div class="signature"><div class="signature">SIG</div><p>Regards</p></div>',
                    htmlBody = 'my message body is awesome!';

                signature = new app.Bean({
                    id: id,
                    name: 'Signature A',
                    signature: 'Regards',
                    signature_html: '&lt;p&gt;Regards&lt;/p&gt;'
                });

                signature.set('signature_html', view._formatSignature(signature.get('signature_html')));
                view.model.set('description_html', message);
                var expectedBody = htmlBody + signatureTagBegin + signature.get('signature_html') + signatureTagEnd;
                var actualReturn = view._insertSignature(signature);
                expect(actualReturn).toBe(true);
                expect(view.model.get('description_html')).toBe(expectedBody);
            });

            it('should remove a signature marked for removal', function() {
                var id = 'abcd',
                    message = '<div class="signature remove"><p>Regards, Jim</p></div>' +
                        'my message body is awesome!<div class="signature"><p>Regards</p></div>',
                    htmlBody = 'my message body is awesome!';

                signature = new app.Bean({
                    id: id,
                    name: 'Signature A',
                    signature: 'Regards',
                    signature_html: '&lt;p&gt;Regards&lt;/p&gt;'
                });

                signature.set('signature_html', view._formatSignature(signature.get('signature_html')));
                view.model.set('description_html', message);
                var expectedBody = htmlBody + signatureTagBegin + signature.get('signature_html') + signatureTagEnd;
                var actualReturn = view._insertSignature(signature);
                expect(actualReturn).toBe(true);
                expect(view.model.get('description_html')).toBe(expectedBody);
            });
        });
    });

    describe('InitializeSendEmailModel', function() {
        beforeEach(function() {
            view.model.off('change');
        });

        it('should populate the related field according to how the Mail API expects it', function() {
            var sendModel,
                parentId = '123',
                parentType = 'Foo';
            view.model.set('parent_id', parentId);
            view.model.set('parent_type', parentType);
            sendModel = view.initializeSendEmailModel();
            expect(sendModel.get('related')).toEqual({id: parentId, type: parentType});
        });
    });

    describe('ResizeEditor', function() {
        var $drawer, $editor;

        beforeEach(function() {
            var mockHtml = '<div><div class="drawer">' +
                    '<div class="headerpane"></div>' +
                    '<div class="record"><div class="mce-stack-layout"><div class="mce-stack-layout-item"><iframe frameborder="0"></iframe></div></div></div>' +
                    '<div class="show-hide-toggle"></div>' +
                    '</div></div>',
                drawerHeight = view.MIN_EDITOR_HEIGHT + 300,
                otherHeight = 50,
                editorHeight = drawerHeight - (otherHeight * 2) - view.EDITOR_RESIZE_PADDING - view.ATTACHMENT_FIELD_HEIGHT;

            view.$el = $(mockHtml);
            $drawer = view.$('.drawer');
            $drawer.height(drawerHeight);
            $editor = view.$('.mce-stack-layout .mce-stack-layout-item iframe');
            $editor.height(editorHeight);

            view.$('.headerpane').height(otherHeight);
            view.$('.record').height(editorHeight);
            view.$('.show-hide-toggle').height(otherHeight);

            sandbox.stub(app.drawer, 'getHeight', function() {
                return $drawer.height();
            });
        });

        it('should increase the height of the editor when drawer height increases', function() {
            var editorHeightBefore = $editor.height(),
                drawerHeightBefore = $drawer.height();

            //increase drawer height by 100 pixels
            $drawer.height(drawerHeightBefore + 100);

            view.resizeEditor();
            //editor should be increased to fill the space
            expect($editor.height()).toEqual(editorHeightBefore + 100);
        });

        it('should decrease the height of the editor when drawer height decreases', function() {
            var editorHeightBefore = $editor.height(),
                drawerHeightBefore = $drawer.height();

            //decrease drawer height by 100 pixels
            $drawer.height(drawerHeightBefore - 100);

            view.resizeEditor();
            //editor should be decreased to account for decreased drawer height
            expect($editor.height()).toEqual(editorHeightBefore - 100);
        });

        it('should ensure that editor maintains minimum height when drawer shrinks beyond that', function() {
            //decrease drawer height to 50 pixels below min editor height
            $drawer.height(view.MIN_EDITOR_HEIGHT - 50);

            view.resizeEditor();
            //editor should maintain min height
            expect($editor.height()).toEqual(view.MIN_EDITOR_HEIGHT);
        });

        it('should resize editor to fill empty drawer space but with a padding to prevent scrolling', function() {
            var editorHeightBefore = $editor.height(),
                editorHeightPlusPadding = editorHeightBefore + view.EDITOR_RESIZE_PADDING;

            //add the resize padding on
            $editor.height(editorHeightPlusPadding);
            view.$('.record').height(editorHeightPlusPadding);

            //padding should be added back
            view.resizeEditor();
            expect($editor.height()).toEqual(editorHeightBefore);
        });
    });
});
