describe('Emails.fields.recipients-fieldset', function() {
    var app;
    var field;
    var context;
    var model;
    var sandbox;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'detail', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients-fieldset', 'field', 'base', 'recipient-options', 'Emails');
        SugarTest.testMetadata.set();

        context = app.context.getContext({
            module: 'Emails'
        });
        context.prepare();
        model = context.get('model');
        model.set('outbound_email_id', new app.MixedBeanCollection());
        model.set('to', new app.MixedBeanCollection());
        model.set('cc', new app.MixedBeanCollection());
        model.set('bcc', new app.MixedBeanCollection());

        field = SugarTest.createField({
            client: 'base',
            name: 'recipients',
            type: 'recipients-fieldset',
            viewName: 'edit',
            module: context.get('module'),
            model: model,
            context: context,
            loadFromModule: true
        });
        sandbox = sinon.sandbox.create();
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
    });

    describe('Field Render Template', function() {
        var _buildRecipientsListSpy;

        beforeEach(function() {
            function mySpy(event) {
                this.recipients = 'foo@bar';
            }
            _buildRecipientsListSpy = sandbox.spy(mySpy);
            sandbox.stub(field, '_buildRecipientsList', _buildRecipientsListSpy);
        });

        using('empty and defined TO values',
            [
                [
                    {to: []},
                    {template: 'edit', called: false}
                ],
                [
                    {to: ['foo@bar.com']},
                    {template: 'detail', called: true}
                ]
            ],
            function(value, result) {
                it('should be detail if there is a to address or edit if to is empty', function() {
                    // Convert arrays into beans that the VirtualCollection plugin expects.
                    _.each(value, function(val, key) {
                        var models = _.map(val, function(email) {
                            return app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                email: email
                            });
                        });

                        field.model.set(key, models);
                    });

                    field._render();

                    // check field template name
                    expect(field.templateName).toBe(result.template);

                    // check to make sure the _buildRecipientsList is called in detail mode only
                    expect(_buildRecipientsListSpy.called).toBe(result.called);
                });
            }
        );
    });

    describe('Recipient Options', function() {
        var toggleFieldVisibilitySpy;
        var isRecipientOptionButtonActive;

        beforeEach(function() {
            toggleFieldVisibilitySpy = sandbox.spy(field, '_toggleFieldVisibility');
            sandbox.stub(field, '_renderRecipientOptions', function() {
                var template = app.template.getField('recipients-fieldset', 'recipient-options', field.module);
                field.$el.append(template({'module': field.module}));
            });
            sandbox.stub(field, '_handleRecipientOptionClick', function(event) {
                var $toggleButton = $(event.currentTarget);
                var fieldName = $toggleButton.data('toggle-field');

                this.toggleRecipientOption(fieldName);
            });
        });

        isRecipientOptionButtonActive = function(fieldName) {
            var selector = '[data-toggle-field="' + fieldName + '"]';
            return field.$(selector).hasClass('active');
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
                    // Convert arrays into beans that the VirtualCollection plugin expects.
                    _.each(value, function(val, key) {
                        var models = _.map(val, function(email) {
                            return app.data.createBean('Contacts', {
                                id: _.uniqueId(),
                                email: email
                            });
                        });

                        field.model.set(key, models);
                    });

                    field.templateName = 'edit';
                    field._render();

                    // check buttons
                    expect(isRecipientOptionButtonActive('cc')).toBe(result.ccActive);
                    expect(isRecipientOptionButtonActive('bcc')).toBe(result.bccActive);

                    // check field visibility
                    expect(toggleFieldVisibilitySpy.secondCall.args).toEqual(['cc', result.ccActive]);
                    expect(toggleFieldVisibilitySpy.thirdCall.args).toEqual(['bcc', result.bccActive]);
                });
            }
        );

        it('should toggle recipient option between active/inactive state when active flag not specified', function() {
            var fieldName = 'cc';
            field._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            field.toggleRecipientOption(fieldName);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(true);
            field.toggleRecipientOption(fieldName);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
        });

        it('should set recipient option to active when active flag is true', function() {
            var fieldName = 'cc';
            field._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            field.toggleRecipientOption(fieldName, true);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(true);
        });

        it('should set recipient option to inactive when active flag is false', function() {
            var fieldName = 'cc';
            field._render();
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
            field.toggleRecipientOption(fieldName, false);
            expect(isRecipientOptionButtonActive(fieldName)).toBe(false);
        });

        it('should toggle recipient option between active/inactive state when cc/bcc buttons clicked', function() {
            field._render();
            expect(isRecipientOptionButtonActive('bcc')).toBe(false);
            field.$('[data-toggle-field="bcc"]').click();
            expect(isRecipientOptionButtonActive('bcc')).toBe(true);
            field.$('[data-toggle-field="bcc"]').click();
            expect(isRecipientOptionButtonActive('bcc')).toBe(false);
        });
    });

});
