describe("Emails.fields.recipients", function() {
    var app,
        field,
        model,
        dataProvider;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate("recipients", "field", "base", "edit", "Emails");
        SugarTest.testMetadata.set();

        field = SugarTest.createField("../modules/Emails/clients/base", "recipients", "recipients", "edit");
        model = field.model;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("format", function() {
        it("Should convert a collection of recipients to a string.", function() {
            var recipients = new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com"},
                    {email: "jim@example.com", name: "Jim Brennan"}
                ]),
                expected   = '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"sally@example.com" <sally@example.com>,"Jim Brennan" <jim@example.com>',
                actual;

            actual = field.format(recipients);
            expect(actual).toBe(expected);

            delete recipients;
        });
    });

    describe("unformat", function() {
        it("Should convert a string of recipients to a collection.", function() {
            var recipients = '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"sally@example.com" <sally@example.com>,"Jim Brennan" <jim@example.com>',
                expected   = new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: ""},
                    {email: "jim@example.com", name: "Jim Brennan"}
                ]),
                actual;

            actual = field.unformat(recipients);
            expect(actual.length).toBe(expected.length);

            delete expected;
        });
    });

    describe("_addRecipients", function() {
        /**
         * @note When saying "should add X recipient(s) to the field's value," it is meant that
         * Emails.field.recipients._addRecipients() actually returns a new Backbone collection representing the
         * collection that should be used to replace the field's current value.
         */
        dataProvider = [
            {
                message:    "Should add one recipient to the field's value when the parameter is a Backbone model.",
                recipients: new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                expected:   1
            },
            {
                message:    "Should add one recipient to the field's value when the parameter is a standard object.",
                recipients: {email: "will@example.com", name: "Will Westin"},
                expected:   1
            },
            {
                message:    "Should add one recipient to the field's value when parameter is a Backbone collection containing one model.",
                recipients: new Backbone.Collection([{email: "will@example.com", name: "Will Westin"}]),
                expected:   1
            },
            {
                message:    "Should add three recipients to the field's value when the parameter is a Backbone collection containing three models.",
                recipients: new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ]),
                expected:   3
            },
            {
                message:    "Should add three recipients to the field's value when the parameter is an array containing three objects.",
                recipients: [
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ],
                expected:   3
            },
            {
                message:    "Should add three recipients to the field's value when the parameter is an array containing three Backbone models.",
                recipients: [
                    new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                    new Backbone.Model({email: "sarah@example.com", name: "Sarah Smith"}),
                    new Backbone.Model({email: "sally@example.com", name: "Sally Bronsen"})
                ],
                expected:   3
            },
            {
                message:    "Should add three recipients to the field's value when the parameter is a string containing three recipients.",
                recipients: '"Will Westin" <will@example.com>,<sarah@example.com>,sally@example.com',
                expected:   3
            },
            {
                message:    "Should add one recipient to the field's value because the second recipient is a duplicate of the first.",
                recipients: new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "will@example.com", name: "Will Westin"}
                ]),
                expected:   1
            },
            {
                message:    "Should not add the recipient to the field's value when the recipient doesn't have an email address.",
                recipients: {id: "abcd", name: "Will Westin"},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                // seed the model with a value to make sure we're only adding to it
                var recipients = new Backbone.Collection([{email: "tom@example.com", name: "Max Jensen"}]);
                field.model.set("recipients", recipients, {silent: true});

                var expected = data.expected + recipients.length, // the number of recipients expected after addition
                    actual   = field._addRecipients(data.recipients);

                expect(actual.length).toBe(expected);

                delete data.recipients;
                delete recipients;
            });
        }, this);
    });

    describe("_removeRecipients", function() {
        /**
         * @note When saying "should remove X recipient(s) from the field's value," it is meant that
         * Emails.field.recipients._removeRecipients() actually returns a new Backbone collection representing the
         * collection that should be used to replace the field's current value.
         */
        dataProvider = [
            {
                message:    "Should remove one recipient from the field's value when the parameter is a Backbone model.",
                recipients: new Backbone.Model({id: "will@example.com"}),
                expected:   1
            },
            {
                message:    "Should remove one recipient from the field's value when the parameter is a standard object.",
                recipients: {id: "will@example.com"},
                expected:   1
            },
            {
                message:    "Should remove one recipient from the field's value when the parameter is a Backbone collection containing one model.",
                recipients: new Backbone.Collection([{id: "will@example.com"}]),
                expected:   1
            },
            {
                message:    "Should remove three recipients from the field's value when the parameter is a Backbone collection containing three models.",
                recipients: new Backbone.Collection([
                    {id: "will@example.com"},
                    {id: "sarah@example.com"},
                    {id: "sally@example.com"}
                ]),
                expected:   3
            },
            {
                message:    "Should remove three recipients from the field's value when the parameter is an array containing three objects.",
                recipients: [
                    {id: "will@example.com"},
                    {id: "sarah@example.com"},
                    {id: "sally@example.com"}
                ],
                expected:   3
            },
            {
                message:    "Should remove three recipients from the field's value when the parameter is an array containing three Backbone models.",
                recipients: [
                    new Backbone.Model({id: "will@example.com"}),
                    new Backbone.Model({id: "sarah@example.com"}),
                    new Backbone.Model({id: "sally@example.com"})
                ],
                expected:   3
            },
            {
                message:    "Should not remove any recipients from the field's value when the parameter is a string.",
                recipients: '"Will Westin" <will@example.com>',
                expected:   0
            },
            {
                message:    "Should not remove any recipients from the field's value when the parameter isn't found in the value.",
                recipients: {id: "foo@bar.com"},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                // seed the model with a value to make sure there is the same data to delete for each test case
                var recipients = new Backbone.Collection([
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"},
                    {id: "will@example.com", email: "will@example.com", name: "Will Westin"},
                    {id: "sarah@example.com", email: "sarah@example.com", name: "Sarah Smith"},
                    {id: "sally@example.com", email: "sally@example.com", name: "Sally Bronsen"}
                ]);
                field.model.set("recipients", recipients, {silent: true});

                var expected = recipients.length - data.expected, // the number of recipients expected after removal
                    actual   = field._removeRecipients(data.recipients);

                expect(actual.length).toBe(expected);

                delete data.recipients;
                delete recipients;
            });
        }, this);
    });

    describe("_replaceRecipients", function() {
        /**
         * @note When saying "should reset the field's value to," it is meant that
         * Emails.field.recipients._replaceRecipients() actually returns a new Backbone collection representing the
         * collection that should be used to replace the field's current value.
         */
        dataProvider = [
            {
                message:    "Should reset the field's value to an empty Backbone collection when the parameter is undefined.",
                recipients: undefined,
                expected:   0
            },
            {
                message:    "Should reset the field's value to an empty Backbone collection when the parameter is an empty object.",
                recipients: {},
                expected:   0
            },
            {
                message:    "Should reset the field's value to an empty Backbone collection when the parameter is an empty Backbone collection.",
                recipients: new Backbone.Collection(),
                expected:   0
            },
            {
                message:    "Should reset the field's value to an empty Backbone collection when the parameter is an empty array.",
                recipients: [],
                expected:   0
            },
            {
                message:    "Should reset the field's value to contain at least one recipient when the parameter is not empty.",
                recipients: [
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ],
                expected:   3
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                // seed the model with a value to make sure there is the same data to replace for each test case
                var recipients = new Backbone.Collection([
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"},
                    {id: "chris@example.com", email: "chris@example.com", name: "Chris Olliver"}
                ]);
                field.model.set("recipients", recipients, {silent: true});

                var actual = field._replaceRecipients(data.recipients);
                expect(actual.length).toBe(data.expected);

                delete data.recipients;
                delete recipients;
            });
        }, this);

        it("Should not replace an existing recipient when the email address is found in both the existing and new collections.", function() {
            var expected           = {
                    id:    "abcd",
                    email: "will@example.com",
                    name:  "Chris Olliver"
                },
                existingRecipients = new Backbone.Collection([
                    expected,
                    {id: "sarah@example.com", email: "sarah@example.com", name: "Sarah Smith"},
                    {id: "tom@example.com", email: "tom@example.com", name: "Max Jensen"}
                ]),
                newRecipients      = [
                    {email: expected.email, name: "Will Westin"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ];

            field.model.set("recipients", existingRecipients, {silent: true});

            var actual = field._replaceRecipients(newRecipients);
            expect(actual.length).toBe(2);
            expect(actual.models[0].attributes).toEqual(expected);
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

        afterEach(function() {
            delete bean;
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

        afterEach(function() {
            delete parameter;
            delete expected;
        });

        init();

        it("Should return an empty object when the parameter is not an object.", function() {
            expected = {};

            var actual = field._translateRecipient(undefined);
            expect(actual).toEqual(expected);
        });

        it("Should return an empty object when the parameter is a Backbone model without a possible value for the id attribute.", function() {
            delete expected.id;
            delete expected.email;
            parameter = new Backbone.Model(expected);
            expected  = {};

            var actual = field._translateRecipient(parameter);
            expect(actual).toEqual(expected);
        });

        it("Should return an object when the parameter is a Backbone model with an id attribute.", function() {
            delete expected.email;
            parameter = new Backbone.Model(expected);

            var actual = field._translateRecipient(parameter);
            expect(actual).toEqual(expected);
        });

        it("Should return an object when the parameter is a Backbone model with an email attribute, but no id attribute.", function() {
            expected.id = expected.email;
            parameter   = new Backbone.Model({module: expected.module, name: expected.name, email: expected.email});

            var actual = field._translateRecipient(parameter);
            expect(actual).toEqual(expected);
        });

        dataProvider = [
            {
                message:   "Should return an object when the parameter is a Backbone model with the email1 or email attribute.",
                recipient: new Backbone.Model(expected)
            },
            {
                message:   "Should prioritize the parameter's properties when the parameter is an object with the id, module, name, email, and bean properties.",
                recipient: _.extend({bean: new Backbone.Model({id: "efgh", module: "Leads", name: "Sarah Smith", email1: "sarah@example.com"})}, expected)
            },
            {
                message:   "Should fall back to the bean's attributes when the parameter is an object with the bean property and without the id, module, name, and email properties.",
                recipient: {bean: new Backbone.Model(expected)}
            },
            {
                message:   "Should get name and email from the parameter's properties and id and module from the bean's attributes when the parameter is an object with the name, email, and bean properties.",
                recipient: {
                    name:  expected.name,
                    email: expected.email,
                    bean:  new Backbone.Model({id: expected.id, module: expected.module, name: "Sarah Smith", email1: "sarah@example.com"})
                }
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field._translateRecipient(data.recipient);
                expect(actual).toEqual(expected);
            });
        }, this);
    });

    describe("_formatRecipient", function() {
        dataProvider = [
            {
                message:   "Should return a string with an email and a name.",
                recipient: {email: "will@example.com", name: "Will Westin"},
                expected:  '"Will Westin" <will@example.com>'
            },
            {
                message:   "Should return a string with an email and a name that matches the email.",
                recipient: {email: "will@example.com", name: ""},
                expected:  '"will@example.com" <will@example.com>'
            },
            {
                message:   "Should return a string with an email and a name that matches the email.",
                recipient: {email: "will@example.com"},
                expected:  '"will@example.com" <will@example.com>'
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var recipient = new Backbone.Model(data.recipient),
                    actual    = field._formatRecipient(recipient);
                expect(actual).toEqual(data.expected);

                delete recipient;
            });
        }, this);
    });

    describe("_unformatRecipient", function() {
        dataProvider = [
            {
                message:   "Should return an object with an email and a name.",
                recipient: '"Will Westin" <will@example.com>',
                expected:  {email: "will@example.com", name: "Will Westin"}
            },
            {
                message:   "Should return an object with an email and a name even when white space surrounds the recipient string.",
                recipient: '    "Tom Terrific"      <tom@terrific.com>     ',
                expected:  {email: "tom@terrific.com", name: "Tom Terrific"}
            },
            {
                message:   "Should return an object with an email and an empty name when the email address is in brackets.",
                recipient: '<will@example.com>',
                expected:  {email: "will@example.com", name: ""}
            },
            {
                message:   "Should return an object with an email and an empty name.",
                recipient: 'will@example.com',
                expected:  {email: "will@example.com", name: ""}
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field._unformatRecipient(data.recipient);
                expect(actual).toEqual(data.expected);
            });
        }, this);
    });

    describe("_splitRecipients", function() {
        dataProvider = [
            {
                message:    "Should return an array of one recipient object.",
                recipients: '"Will Westin" <will@example.com>',
                expected:   1
            },
            {
                message:    "Should return an array of two recipient objects.",
                recipients: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>',
                expected:   2
            },
            {
                message:    "Should return an array of six recipient objects.",
                recipients: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                expected:   6
            },
            {
                message:    "Should return an array of one recipient object because ';' isn't a valid delimiter",
                recipients: '"Will Westin" <will@example.com>;"Sarah Smith" <sarah@example.com>',
                expected:   1
            },
            {
                message:    "Should return an array of two recipient objects because the first ',' isn't a recognized as a delimiter.",
                recipients: '"Westin, Will" <will@example.com>,"Sarah Smith" <sarah@example.com>',
                expected:   2
            },
            {
                message:    "Should return an array of two recipient objects even when there is white space around the delimiter.",
                recipients: '"Will Westin" <will@example.com> ,     "Sarah Smith" <sarah@example.com>',
                expected:   2
            },
            {
                message:    "Should return an empty array when the parameter is undefined.",
                recipients: undefined,
                expected:   0
            },
            {
                message:    "Should return an empty array when the parameter is an integer.",
                recipients: 1,
                expected:   0
            },
            {
                message:    "Should return an empty array when the parameter is an object.",
                recipients: {param: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>'},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field._splitRecipients(data.recipients);
                expect(actual.length).toBe(data.expected);
            });
        }, this);
    });
});
