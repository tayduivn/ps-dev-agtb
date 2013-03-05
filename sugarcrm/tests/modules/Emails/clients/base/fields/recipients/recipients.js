describe("Emails.fields.recipients", function() {
    var app,
        field,
        model,
        dataProvider,
        haystack = '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>';

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

    // add recipients
    dataProvider = [
        {
            message:    "should add 1 recipient to the field's value",
            recipients: new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>'
        },
        {
            message:    "should add 1 recipient to the field's value",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
            ]),
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>'
        },
        {
            message:    "should add 3 recipients to the field's value",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
            ]),
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>'
        },
        {
            message:    "should add 3 recipients to the field's value",
            recipients: '"Will Westin" <will@example.com>,<sarah@example.com>,sally@example.com',
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"sarah@example.com" <sarah@example.com>,"sally@example.com" <sally@example.com>'
        },
        {
            message:    "should only add 1 recipient to the field's value because the 2nd is a duplicate",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
            ]),
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>'
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual;
            // seed the model with a value to make sure we're only adding to it
            field.model.set("recipients", '"Max Jensen" <tom@example.com>');
            field._addRecipients(data.recipients);
            actual = field.model.get("recipients");
            expect(actual).toBe(data.expected);
        });
    }, this);

    // remove recipients
    dataProvider = [
        {
            message:    "should remove 1 recipient from the field's value",
            recipients: new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
            expected:   '"Max Jensen" <tom@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>'
        },
        {
            message:    "should remove 1 recipient from the field's value",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
            ]),
            expected:   '"Max Jensen" <tom@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>'
        },
        {
            message:    "should remove 2 recipients from the field's value",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" })
            ]),
            expected:   '"Max Jensen" <tom@example.com>,"Sally Bronsen" <sally@example.com>'
        },
        {
            message:    "should remove all recipients from the field's value",
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "tom@example.com", name: "Max Jensen" }),
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
            ]),
            expected:   ""
        },
        {
            message:    "should not remove any recipients from the field's value",
            recipients: '"Will Westin" <will@example.com>',
            expected:   '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>'
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual;
            // seed the model with a value to make sure there is the same data to delete for each test case
            field.model.set("recipients", '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>');
            field._removeRecipients(data.recipients);
            actual = field.model.get("recipients");
            expect(actual).toBe(data.expected);
        });
    }, this);

    // replace recipients
    dataProvider = [
        {
            recipients: undefined,
            expected:   "",
            message:    "should reset the field's value to an empty string"
        },
        {
            recipients: {},
            expected:   "",
            message:    "should reset the field's value to an empty string"
        },
        {
            recipients: new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
            expected:   '"Will Westin" <will@example.com>',
            message:    "should reset the field's value to contain 1 recipient"
        },
        {
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
            ]),
            expected:   '"Will Westin" <will@example.com>',
            message:    "should reset the field's value to contain 1 recipient"
        },
        {
            recipients: new Backbone.Collection([
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
            ]),
            expected:   '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
            message:    "should reset the field's value to contain 3 recipients"
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual;
            // seed the model with a value to make sure it gets replaced
            field.model.set("recipients", '"Max Jensen" <tom@example.com>');
            field._replaceRecipients(data.recipients);
            actual = field.model.get("recipients");
            expect(actual).toBe(data.expected);
        });
    }, this);

    // format recipient
    dataProvider = [
        {
            message:   "should get back a string with an email and a name",
            recipient: new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
            expected:  '"Will Westin" <will@example.com>'
        },
        {
            message:   "should get back a string with an email and a name that matches the email",
            recipient: new Backbone.Model({ email: "will@example.com", name: "" }),
            expected:  '"will@example.com" <will@example.com>'
        },
        {
            message:   "should get back a string with an email and a name that matches the email",
            recipient: new Backbone.Model({ email: "will@example.com"}),
            expected:  '"will@example.com" <will@example.com>'
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual = field._formatRecipient(data.recipient);
            expect(actual).toEqual(data.expected);
        });
    }, this);

    // unformat recipient
    dataProvider = [
        {
            message:   "should get back an object with an email and a name",
            recipient: '"Will Westin" <will@example.com>',
            expected:  { email: "will@example.com", name: "Will Westin" }
        },
        {
            message:   "should get back an object with an email and a name even when white space surrounds recipient string",
            recipient: '    "Tom Terrific"      <tom@terrific.com>     ',
            expected:  { email: "tom@terrific.com", name: "Tom Terrific" }
        },
        {
            message:   "should get back an object with an email and an empty name when email address in brackets",
            recipient: '<will@example.com>',
            expected:  { email: "will@example.com", name: "" }
        },
        {
            message:   "should get back an object with an email and an empty name",
            recipient: 'will@example.com',
            expected:  { email: "will@example.com", name: "" }
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual = field._unformatRecipient(data.recipient);
            expect(actual).toEqual(data.expected);
        });
    }, this);

    // split recipients
    dataProvider = [
        {
            message:    "should get back an array of 1 Backbone model",
            recipients: '"Will Westin" <will@example.com>',
            expected:   1
        },
        {
            message:    "should get back an array of 2 Backbone models",
            recipients: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>',
            expected:   2
        },
        {
            message:    "should get back an array of 6 Backbone models",
            recipients: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
            expected:   6
        },
        {
            message:    "should get back an array of 1 Backbone model because ';' isn't a valid delimiter",
            recipients: '"Will Westin" <will@example.com>;"Sarah Smith" <sarah@example.com>',
            expected:   1
        },
        {
            message:    "should get back an array of 2 Backbone models because the first ',' isn't a recognized as a delimiter",
            recipients: '"Westin, Will" <will@example.com>,"Sarah Smith" <sarah@example.com>',
            expected:   2
        },
        {
            message:    "should get back an array of 2 Backbone models even when there is white space around the delimiter",
            recipients: '"Will Westin" <will@example.com> ,     "Sarah Smith" <sarah@example.com>',
            expected:   2
        },
        {
            message:    "should get back an empty array when the parameter is undefined",
            recipients: undefined,
            expected:   0
        },
        {
            message:    "should get back an empty array when the parameter is an integer",
            recipients: 1,
            expected:   0
        },
        {
            message:    "should get back an empty array when the parameter is an object",
            recipients: {param: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>'},
            expected:   0
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual = field._splitRecipients(data.recipients);
            expect(actual.length).toBe(data.expected);

            _.each(actual, function(model) {
                expect(model instanceof Backbone.Model).toBeTruthy();
            }, this);
        });
    }, this);

    // find and remove a recipient
    dataProvider = [
        {
            message:  "should remove the first recipient",
            haystack: haystack,
            needle:   '"Will Westin" <will@example.com>',
            expected: '"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should remove a middle recipient",
            haystack: haystack,
            needle:   '"Sally Bronsen" <sally@example.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should remove the last recipient",
            haystack: haystack,
            needle:   '"Chris Olliver" <chris@example.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>'
        },
        {
            message:  "should not remove any recipients because the one to remove doesn't exist in the string",
            haystack: haystack,
            needle:   '"Foo Bar" <foo@bar.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should not remove any recipients because the one to remove isn't an exact match (prefix of Dr.)",
            haystack: haystack,
            needle:   '"Dr. Sarah Smith" <sarah@example.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should not remove any recipients because the one to remove isn't an exact match (Sarah != Sara)",
            haystack: haystack,
            needle:   '"Sara Smith" <sara@example.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should remove both instances of the recipient",
            haystack: haystack + ',"Max Jensen" <tom@example.com>',
            needle:   '"Max Jensen" <tom@example.com>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should remove a recipient regardless of character case",
            haystack: haystack,
            needle:   '"sally bronsen" <Sally@Example.COM>',
            expected: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>'
        },
        {
            message:  "should remove a recipient that has special characters",
            haystack: '"Wi|| $mith" <will@smith.com>',
            needle:   '"Wi|| $mith" <will@smith.com>',
            expected: ""
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual = field._findAndRemoveRecipient(data.needle, data.haystack);
            expect(actual).toBe(data.expected);
        });
    }, this);

    // is a recipient in the field's value?
    dataProvider = [
        {
            message:  "should find the first recipient",
            haystack: haystack,
            needle:   '"Will Westin" <will@example.com>',
            expected: true
        },
        {
            message:  "should find a middle recipient",
            haystack: haystack,
            needle:   '"Sally Bronsen" <sally@example.com>',
            expected: true
        },
        {
            message:  "should find the last recipient",
            haystack: haystack,
            needle:   '"Chris Olliver" <chris@example.com>',
            expected: true
        },
        {
            message:  "should not find the recipient because it doesn't exist in the string",
            haystack: haystack,
            needle:   '"Foo Bar" <foo@bar.com>',
            expected: false
        },
        {
            message:  "should find at least one of two matching recipients",
            haystack: haystack + ',"Max Jensen" <tom@example.com>',
            needle:   '"Max Jensen" <tom@example.com>',
            expected: true
        },
        {
            message:  "should find a recipient even with a comma in the name",
            haystack: '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Bronsen, Sally" <sally@example.com>',
            needle:   '"Bronsen, Sally" <sally@example.com>',
            expected: true
        }
    ];

    _.each(dataProvider, function(data) {
        it(data.message, function() {
            var actual = field._hasRecipient(data.needle, data.haystack);
            expect(actual).toBe(data.expected);
        });
    }, this);
});
