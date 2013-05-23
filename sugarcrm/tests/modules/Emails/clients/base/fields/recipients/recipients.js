describe("Emails.fields.recipients", function() {
    var app,
        field,
        context,
        model,
        dataProvider,
        origTooltip;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("recipients", "field", "base", "edit", "Emails");
        SugarTest.testMetadata.set();

        origTooltip = $.fn.tooltip;
        $.fn.tooltip = function(){};

        context = app.context.getContext({
            module: "Emails"
        });
        context.prepare();
        model = context.get('model');
        field = SugarTest.createField("base", "recipients", "recipients", "edit", undefined, context.get('module'), model, context, true);
    });

    afterEach(function() {
        field.dispose();
        $.fn.tooltip = origTooltip;
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
    });

    describe("format", function() {
        it("Should convert a collection of recipient models to an array of recipient objects.", function() {
            var recipients = new Backbone.Collection([
                    {id: '123', email: "will@example.com", name: "Will Westin"},
                    {email: "jim@example.com", name: "Jim Brennan"},
                    {email: "sally@example.com"}
                ]),
                actual = field.format(recipients);

            expect(Array.isArray(actual)).toBe(true);
            expect(actual.length).toBe(3);

            expect(actual[0].id).toBe(recipients.models[0].get('id'));
            expect(actual[0].email).toBe(recipients.models[0].get('email'));
            expect(actual[0].name).toBe(recipients.models[0].get('name'));

            expect(actual[1].id).toBe(recipients.models[1].get('email'));
            expect(actual[1].email).toBe(recipients.models[1].get('email'));
            expect(actual[1].name).toBe(recipients.models[1].get('name'));

            expect(actual[2].id).toBe(recipients.models[2].get('email'));
            expect(actual[2].email).toBe(recipients.models[2].get('email'));
            expect(actual[2].name).toBe(undefined);
        });
    });

    describe("unformat", function() {
        it("Should convert an array of recipient object to a collection of recipient models.", function() {
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
            expect(actual.length).toBe(2);
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
        it("Should have 3 recipients when 1 recipient is added to a field that already has 2 recipients", function() {
            var existingRecipients = new Backbone.Collection([
                    {id: "sarah@example.com", email: "sarah@example.com", name: "Sarah Smith"},
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"}
                ]),
                newRecipients = [
                    {email: "foo@example.com", name: "Foo Bar"}
                ],
                actual;

            field.render();
            field.model.set("recipients", existingRecipients);
            field._addressbookDrawerCallback(newRecipients);

            actual = field.getFieldElement().select2('data');
            expect(actual.length).toEqual(3);
        });

        it("Should not add any recipients when the recipient added already exists in the field.", function() {
            var existingRecipients = new Backbone.Collection([
                    {id: "sarah@example.com", email: "sarah@example.com", name: "Sarah Smith"},
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"}
                ]),
                newRecipients = [
                    {email: "sarah@example.com", name: "Sarah Smith"}
                ],
                actual;

            field.render();
            field.model.set("recipients", existingRecipients);
            field._addressbookDrawerCallback(newRecipients);

            actual = field.getFieldElement().select2('data');
            expect(actual.length).toEqual(2);
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
                message:  "Should return an object with all properties when the bean has the id, module, email, and first_name attributes.",
                bean:     {id: expected.id, module: expected.module, first_name: "Will", email1: expected.email},
                expected: {id: expected.id, module: expected.module, name: "Will", email: expected.email}
            },
            {
                message:  "Should return an object with all properties when the bean has the id, module, email, and last_name attributes.",
                bean:     {id: expected.id, module: expected.module, last_name: "Westin", email1: expected.email},
                expected: {id: expected.id, module: expected.module, name: "Westin", email: expected.email}
            },
            {
                message:  "Should return an object with all properties when the bean has the id, module, email, first_name, and last_name attributes.",
                bean:     {id: expected.id, module: expected.module, first_name: "Will", last_name: "Westin", email1: expected.email},
                expected: expected
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
        var parameter,
            expected;

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

        it("Should return an empty object when the parameter is a Backbone model without a possible value for the id attribute.", function() {
            parameter = new Backbone.Model({
                module: "Contacts",
                name:   "Will Westin"
            });

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(true);
        });

        it("Should return an object when the parameter is a Backbone model with an id attribute.", function() {
            parameter = new Backbone.Model({
                id:     "abcd",
                module: "Contacts",
                name:   "Will Westin"
            });

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(false);
            expect(actual.id).toBe(parameter.get('id'));
            expect(actual.module).toBe(parameter.get('module'));
            expect(actual.name).toBe(parameter.get('name'));
        });

        it("Should return an object when the parameter is a Backbone model with an email attribute, but no id attribute.", function() {
            parameter   = new Backbone.Model({
                module: "Contacts",
                name:   "Will Westin",
                email:  "will@example.com"
            });

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(false);
            expect(actual.id).toBe(parameter.get('email'));
            expect(actual.module).toBe(parameter.get('module'));
            expect(actual.name).toBe(parameter.get('name'));
            expect(actual.email).toBe(parameter.get('email'));
        });

        it("Should prioritize the parameter's properties when the parameter is an object with the id, module, name, email, and bean properties.", function() {
            parameter = {
                bean: new Backbone.Model({
                    id: "efgh",
                    module: "Leads",
                    name: "Sarah Smith",
                    email1: "sarah@example.com"
                }),
                id:     "abcd",
                module: "Contacts",
                name:   "Will Westin",
                email:  "will@example.com"
            }

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(false);
            expect(actual.id).toBe(parameter.id);
            expect(actual.module).toBe(parameter.module);
            expect(actual.name).toBe(parameter.name);
            expect(actual.email).toBe(parameter.email);
        });

        it("Should fall back to the bean's attributes when the parameter is an object with the bean property and without the id, module, name, and email properties.", function() {
            parameter = {
                bean: new Backbone.Model({
                    id: "efgh",
                    module: "Leads",
                    name: "Sarah Smith",
                    email1: "sarah@example.com"
                })
            }

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(false);
            expect(actual.id).toBe(parameter.bean.get('id'));
            expect(actual.module).toBe(parameter.bean.get('module'));
            expect(actual.name).toBe(parameter.bean.get('name'));
            expect(actual.email).toBe(parameter.bean.get('email1'));
        });

        it("Should get name and email from the parameter's properties and id and module from the bean's attributes when the parameter is an object with the name, email, and bean properties.", function() {
            parameter = {
                bean: new Backbone.Model({
                    id: "efgh",
                    module: "Leads",
                    name: "Sarah Smith",
                    email1: "sarah@example.com"
                }),
                name:   "Will Westin",
                email:  "will@example.com"
            }

            var actual = field._translateRecipient(parameter);
            expect(_.isEmpty(actual)).toBe(false);
            expect(actual.id).toBe(parameter.bean.get('id'));
            expect(actual.module).toBe(parameter.bean.get('module'));
            expect(actual.name).toBe(parameter.name);
            expect(actual.email).toBe(parameter.email);
        });
    });

});
