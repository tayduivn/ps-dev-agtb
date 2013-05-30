describe("Emails.fields.recipients", function() {
    var app,
        field,
        context,
        model,
        dataProvider,
        tooltipStub;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("recipients", "field", "base", "edit", "Emails");
        SugarTest.testMetadata.set();

        context = app.context.getContext({
            module: "Emails"
        });
        context.prepare();
        model = context.get('model');
        field = SugarTest.createField("base", "recipients", "recipients", "edit", undefined, context.get('module'), model, context, true);

        tooltipStub = sinon.stub(field, '_initializeTooltips');
    });

    afterEach(function() {
        field.dispose();
        tooltipStub.restore();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    describe("format", function() {
        dataProvider = [
            {
                message:    "Should return an array of one recipient when the parameter is a Backbone model.",
                recipients: new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                expected:   1
            },
            {
                message:    "Should return an array of one recipient when the parameter is a standard object.",
                recipients: {email: "will@example.com", name: "Will Westin"},
                expected:   1
            },
            {
                message:    "Should return an array of one recipient when the parameter is a Backbone collection containing one model.",
                recipients: new Backbone.Collection([{email: "will@example.com", name: "Will Westin"}]),
                expected:   1
            },
            {
                message:    "Should return an array of three recipients when the parameter is a Backbone collection containing three models.",
                recipients: new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ]),
                expected:   3
            },
            {
                message:    "Should return an array of three recipients when the parameter is an array containing three objects.",
                recipients: [
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ],
                expected:   3
            },
            {
                message:    "Should return an array of three recipients when the parameter is an array containing three Backbone models.",
                recipients: [
                    new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                    new Backbone.Model({email: "sarah@example.com", name: "Sarah Smith"}),
                    new Backbone.Model({email: "sally@example.com", name: "Sally Bronsen"})
                ],
                expected:   3
            },
            {
                message:    "Should return an array of zero recipients when the recipient doesn't have an email address.",
                recipients: {id: "abcd", name: "Will Westin"},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field.format(data.recipients);

                expect(Array.isArray(actual)).toBe(true);
                expect(actual.length).toBe(data.expected);
            });
        }, this);
    });

    describe("unformat", function() {
        it("Should convert an array of recipient objects to a collection of recipient models.", function() {
            var recipients = [{
                    id: '123',
                    email: "will@example.com",
                    name: "Will Westin"
                }, {
                    id: '1234',
                    email: "sally@example.com"
                }],
                actual = field.unformat(recipients);

            expect(actual instanceof Backbone.Collection).toBe(true);
            expect(actual.length).toBe(recipients.length);
        });
    });

    describe("setContentBefore", function() {
        it("Should set the data-content-before attribute of the select2-choices ul delement for the to_address field.", function() {
            var actual = 'Test string';

            field.render();
            field.setContentBefore(actual);

            expect(field.$('.select2-choices').attr('data-content-before')).toBe(actual);
        });
    });

    describe("Setting recipients to field", function() {
        dataProvider = [
            {
                message:    "Should not set any recipients when undefined is set on the model",
                recipients: undefined,
                expected:   0
            },
            {
                message:    "Should not set any recipients when an empty object is set on the model",
                recipients: {},
                expected:   0
            },
            {
                message:    "Should not set any recipients when an empty collection is set on the model",
                recipients: new Backbone.Collection(),
                expected:   0
            },
            {
                message:    "Should not set any recipients when an empty array is set on the model",
                recipients: [],
                expected:   0
            },
            {
                message:    "Should set one recipient when an object is set on the model",
                recipients: {email: "will@example.com", name: "Will Westin"},
                expected:   1
            },
            {
                message:    "Should set three recipients when an array of three recipients is set on the model",
                recipients: [
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ],
                expected:   3
            },
            {
                message:    "Should set two recipients when a collection of two recipients is set on the model",
                recipients: new Backbone.Collection([
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"},
                    {id: "chris@example.com", email: "chris@example.com", name: "Chris Olliver"}
                ]),
                expected:   2
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual;

                field.render();
                field.model.set("recipients", data.recipients);

                actual = field.getFieldElement().select2('data');
                expect(actual.length).toBe(data.expected);
            });
        });
    });

    describe("Adding recipients to field", function() {
        dataProvider = [
            {
                message:    "Should have three recipients when one recipient is added to a field that already has two recipients.",
                recipients: {email: "foo@example.com", name: "Foo Bar"},
                expected:   1
            },
            {
                message:    "Should not add any recipients when the recipient added already exists in the field.",
                recipients: {email: "sarah@example.com", name: "Sarah Smith"},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var existingRecipients = new Backbone.Collection([
                        {id: "sarah@example.com", email: "sarah@example.com", name: "Sarah Smith"},
                        {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"}
                    ]),
                    actual,
                    expected;

                field.render();
                field.model.set("recipients", existingRecipients);
                field._addRecipients(data.recipients);

                expected = data.expected + existingRecipients.length; // the number of recipients expected after addition
                actual   = field.getFieldElement().select2('data');
                expect(actual.length).toBe(expected);
            });
        });
    });

    describe("_getDataFromBean", function() {
        var expected,
            bean;

        function init() {
            expected = {
                id:     "abcd",
                module: "Contacts",
                name:   "Will Westin",
                email:  "will@example.com"
            };
        }

        beforeEach(function() {
            init();
        });

        init();

        dataProvider = [
            {
                message:  "Should return an object with all properties when the bean has the id, module, name, and email1 attributes.",
                bean:     {id: expected.id, module: expected.module, name: expected.name, email1: "will@example.com"},
                expected: expected
            },
            {
                message:  "Should return an object with all properties when the bean has the id, module, full_name, and email1 attributes.",
                bean:     {id: expected.id, module: expected.module, full_name: expected.name, email1: "will@example.com"},
                expected: expected
            },
            {
                message:  "Should return an object with all properties when the bean has the id, module, name, and email attributes, where email is a string.",
                bean:     expected,
                expected: expected
            },
            {
                message:  "Should return an object with all properties when the bean has the id, module, name, and email attributes, where email is an array of email objects and one is the primary.",
                bean:     {
                    id:     expected.id,
                    module: expected.module,
                    name:   expected.name,
                    email:  [
                        {email_address: "bill@example.com", primary_address: "0"},
                        {email_address: expected.email, primary_address: "1"},
                        {email_address: "william@example.com", primary_address: "0"}
                    ]
                },
                expected: expected
            },
            {
                message:  "Should return an object with all properties except name when the bean has the id, module, and email attributes.",
                bean:     {id: expected.id, module: expected.module, full_name: "", email1: expected.email},
                expected: {id: expected.id, module: expected.module, email: expected.email}
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                bean = new Backbone.Model(data.bean);
                expect(field._getDataFromBean(bean)).toEqual(data.expected);
            });
        }, this);

        it("Should get the module from the bean's module property instead of the bean's attributes when the bean has the module property.", function() {
            bean        = new Backbone.Model({id: expected.id, module: "Leads", name: expected.name, email1: expected.email});
            bean.module = expected.module;

            var actual = field._getDataFromBean(bean);
            expect(actual).toEqual(expected);
        });

        dataProvider = [
            {
                message: "Should return an object without the name property when the bean doesn't have any of the potential name attributes.",
                bean:    {id: expected.id, module: expected.module, email1: expected.email},
                empty:   "name"
            },
            {
                message: "Should return an object without the email property when the bean doesn't have any of the potential email attributes.",
                bean:    {id: expected.id, module: expected.module, name: expected.name, email1: null, email: null},
                empty:   "email"
            },
            {
                message: "Should return an object without the email property when the bean doesn't have a specified primary email address.",
                bean:    {
                    id:     expected.id,
                    module: expected.module,
                    name:   expected.name,
                    email:  [
                        {email_address: "bill@example.com", primary_address: "0"},
                        {email_address: expected.email, primary_address: "0"},
                        {email_address: "william@example.com", primary_address: "0"}
                    ]
                },
                empty:   "email"
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                delete expected[data.empty];
                bean = new Backbone.Model(data.bean);

                var actual = field._getDataFromBean(bean);
                expect(actual).toEqual(expected);
            });
        }, this);
    });

    describe("_translateRecipient", function() {
        dataProvider = [
            {
                message:   "Should return an empty object when the recipient has no possible value for the id attribute.",
                recipient: {module: "Contacts", name: "Will Westin"},
                expected:  {}
            },
            {
                message:   "Should return an object when the recipient is a Backbone model with an id attribute.",
                recipient: new Backbone.Model({id: "abcd", module: "Contacts", name: "Will Westin"}),
                expected:  {id: "abcd", module: "Contacts", name: "Will Westin"}
            },
            {
                message:   "Should return an object when the recipient is a Backbone model with an email attribute, but no id attribute.",
                recipient: new Backbone.Model({module: "Contacts", name: "Will Westin", email: "will@example.com"}),
                expected:  {id: "will@example.com", module: "Contacts", name: "Will Westin", email: "will@example.com"}
            },
            {
                message:   "Should return an object when the recipient is a Backbone model with the email1 or email attribute.",
                recipient: new Backbone.Model({
                    id: "abcd",
                    module: "Contacts",
                    name: "Will Westin",
                    email: "will@example.com"
                }),
                expected:  {id: "abcd", module: "Contacts", name: "Will Westin", email: "will@example.com"}
            },
            {
                message:   "Should prioritize the recipient's properties when the parameter is an object with the id, module, name, email, and bean properties.",
                recipient: {
                    id: "abcd",
                    module: "Contacts",
                    name: "Will Westin",
                    email: "will@example.com",
                    bean: new Backbone.Model({
                        id: "efgh",
                        module: "Leads",
                        name: "Sarah Smith",
                        email1: "sarah@example.com"})
                },
                expected:  {id: "abcd", module: "Contacts", name: "Will Westin", email: "will@example.com"}
            },
            {
                message:   "Should fall back to the bean's attributes when the recipient is an object with the bean property and without the id, module, name, and email properties.",
                recipient: {
                    bean: new Backbone.Model({
                        id: "efgh",
                        module: "Leads",
                        name: "Sarah Smith",
                        email1: "sarah@example.com"
                    })
                },
                expected:  {id: "efgh", module: "Leads", name: "Sarah Smith", email: "sarah@example.com"}
            },
            {
                message:   "Should get name and email from the recipient's properties and id and module from the bean's attributes when the parameter is an object with the name, email, and bean properties.",
                recipient: {
                    name:  "Will Westin",
                    email: "will@example.com",
                    bean: new Backbone.Model({
                        id: "efgh",
                        module: "Leads",
                        name: "Sarah Smith",
                        email1: "sarah@example.com"
                    })
                },
                expected:  {id: "efgh", module: "Leads", name: "Will Westin", email: "will@example.com"}
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field._translateRecipient(data.recipient);
                expect(actual).toEqual(data.expected);
            });
        }, this);
    });
});
