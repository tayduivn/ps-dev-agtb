describe("Emails.fields.recipients", function() {
    var app,
        field,
        model;

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

    it("should add new recipients to the field's value", function() {
        var dataProvider = [
            [
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>',
                "should add 1 recipient to the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
                ]),
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>',
                "should add 1 recipient to the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                    new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                    new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
                 ]),
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
                "should add 3 recipients to the field's value"
            ],
            [
                '"Will Westin" <will@example.com>,<sarah@example.com>,sally@example.com',
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"sarah@example.com" <sarah@example.com>,"sally@example.com" <sally@example.com>',
                "should add 3 recipients to the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
                ]),
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>',
                "should only add 1 recipient to the field's value because the 2nd is a duplicate"
            ]
        ];

        _.each(dataProvider, function(data) {
            var actual;
            // seed the model with a value to make sure we're only adding to it
            field.model.set("recipients", '"Max Jensen" <tom@example.com>');
            field._addRecipients(data[0]);
            actual = field.model.get("recipients");
            expect(actual).toBe(data[1]);
        }, this);
    });

    it("should remove recipients from the field's value", function() {
        var dataProvider = [
            [
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                '"Max Jensen" <tom@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
                "should remove 1 recipient from the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
                ]),
                '"Max Jensen" <tom@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
                "should remove 1 recipient from the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                    new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" })
                ]),
                '"Max Jensen" <tom@example.com>,"Sally Bronsen" <sally@example.com>',
                "should remove 2 recipients from the field's value"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "tom@example.com", name: "Max Jensen" }),
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                    new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                    new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
                ]),
                "",
                "should remove all recipients from the field's value"
            ],
            [
                '"Will Westin" <will@example.com>',
                '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
                "should not remove any recipients from the field's value"
            ]
        ];

        _.each(dataProvider, function(data) {
            var actual;
            // seed the model with a value to make sure there is the same data to delete for each test case
            field.model.set("recipients", '"Max Jensen" <tom@example.com>,"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>');
            field._removeRecipients(data[0]);
            actual = field.model.get("recipients");
            expect(actual).toBe(data[1]);
        }, this);
    });

    it("should replace the field's value with new recipients", function() {
        var dataProvider = [
            [
                undefined,
                "",
                "should reset the field's value to an empty string"
            ],
            [
                {},
                "",
                "should reset the field's value to an empty string"
            ],
            [
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                '"Will Westin" <will@example.com>',
                "should reset the field's value to contain 1 recipient"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" })
                ]),
                '"Will Westin" <will@example.com>',
                "should reset the field's value to contain 1 recipient"
            ],
            [
                new Backbone.Collection([
                    new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                    new Backbone.Model({ email: "sarah@example.com", name: "Sarah Smith" }),
                    new Backbone.Model({ email: "sally@example.com", name: "Sally Bronsen" })
                ]),
                '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>',
                "should reset the field's value to contain 3 recipients"
            ]
        ];

        _.each(dataProvider, function(data) {
            var actual;
            // seed the model with a value to make sure it gets replaced
            field.model.set("recipients", '"Max Jensen" <tom@example.com>');
            field._replaceRecipients(data[0]);
            actual = field.model.get("recipients");
            expect(actual).toBe(data[1]);
        }, this);
    });

    it("should turn a recipient Backbone model into a string formatted for display", function() {
        var dataProvider = [
            [
                new Backbone.Model({ email: "will@example.com", name: "Will Westin" }),
                '"Will Westin" <will@example.com>',
                "should get back a string with an email and a name"
            ],
            [
                new Backbone.Model({ email: "will@example.com", name: "" }),
                '"will@example.com" <will@example.com>',
                "should get back a string with an email and a name that matches the email"
            ],
            [
                new Backbone.Model({ email: "will@example.com"}),
                '"will@example.com" <will@example.com>',
                "should get back a string with an email and a name that matches the email"
            ]
        ];

        _.each(dataProvider, function(data) {
            var actual = field._formatRecipient(data[0]);
            expect(actual).toEqual(data[1]);
        }, this);
    });

    it("should turn a recipient string into an object with an email and name", function() {
        var dataProvider = [
            [
                '"Will Westin" <will@example.com>',
                { email: "will@example.com", name: "Will Westin" },
                "should get back an object with an email and a name"
            ],
            [
                '<will@example.com>',
                { email: "will@example.com", name: "" },
                "should get back an object with an email and an empty name"
            ],
            [
                'will@example.com',
                { email: "will@example.com", name: "" },
                "should get back an object with an email and an empty name"
            ]
        ];

        _.each(dataProvider, function(data) {
            var actual = field._unformatRecipient(data[0]);
            expect(actual).toEqual(data[1]);
        }, this);
    });

    it("should split the recipients string into an array of recipients Backbone models", function() {
        var dataProvider = [
                [
                    '"Will Westin" <will@example.com>',
                    1,
                    "should get back an array of 1 Backbone model"
                ],
                [
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>',
                    2,
                    "should get back an array of 2 Backbone models"
                ],
                [
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    6,
                    "should get back an array of 6 Backbone models"
                ],
                [
                    '"Will Westin" <will@example.com>;"Sarah Smith" <sarah@example.com>',
                    1,
                    "should get back an array of 1 Backbone model because ';' isn't a valid delimiter"
                ],
                [
                    '"Westin, Will" <will@example.com>,"Sarah Smith" <sarah@example.com>',
                    2,
                    "should get back an array of 2 Backbone models because the first ',' isn't a recognized as a delimiter"
                ]
            ];

        _.each(dataProvider, function(data) {
            var actual = field._splitRecipients(data[0]);
            expect(actual.length).toBe(data[1]);

            _.each(actual, function(model) {
                expect(model instanceof Backbone.Model).toBeTruthy();
            }, this);
        }, this);
    });

    it("should return a string that no longer contains the recipient", function() {
        var haystack    = '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
            dataProvider = [
                [
                    haystack,
                    '"Will Westin" <will@example.com>',
                    '"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should remove the first recipient"
                ],
                [
                    haystack,
                    '"Sally Bronsen" <sally@example.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should remove a middle recipient"
                ],
                [
                    haystack,
                    '"Chris Olliver" <chris@example.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>',
                    "should remove the last recipient"
                ],
                [
                    haystack,
                    '"Foo Bar" <foo@bar.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should not remove any recipients because the one to remove doesn't exist in the string"
                ],
                [
                    haystack,
                    '"Dr. Sarah Smith" <sarah@example.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should not remove any recipients because the one to remove isn't an exact match (prefix of Dr.)"
                ],
                [
                    haystack,
                    '"Sara Smith" <sara@example.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should not remove any recipients because the one to remove isn't an exact match (Sarah != Sara)"
                ],
                [
                    haystack + ',"Max Jensen" <tom@example.com>',
                    '"Max Jensen" <tom@example.com>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should remove both instances of the recipient"
                ],
                [
                    haystack,
                    '"sally bronsen" <Sally@Example.COM>',
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
                    "should remove a recipient regardless of character case"
                ],
                [
                    '"Wi|| $mith" <will@smith.com>',
                    '"Wi|| $mith" <will@smith.com>',
                    "",
                    "should remove a recipient that has special characters"
                ]
            ];

        _.each(dataProvider, function(data) {
            var actual = field._findAndRemoveRecipient(data[1], data[0]);
            expect(actual).toBe(data[2]);
        }, this);
    });

    it("should indicate whether or not the field contains the recipient", function() {
        var haystack    = '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Sally Bronsen" <sally@example.com>,"Max Jensen" <tom@example.com>,"Jim Brennan" <jim@example.com>,"Chris Olliver" <chris@example.com>',
            dataProvider = [
                [
                    haystack,
                    '"Will Westin" <will@example.com>',
                    true,
                    "should find the first recipient"
                ],
                [
                    haystack,
                    '"Sally Bronsen" <sally@example.com>',
                    true,
                    "should find a middle recipient"
                ],
                [
                    haystack,
                    '"Chris Olliver" <chris@example.com>',
                    true,
                    "should find the last recipient"
                ],
                [
                    haystack,
                    '"Foo Bar" <foo@bar.com>',
                    false,
                    "should not find the recipient because it doesn't exist in the string"
                ],
                [
                    haystack + ',"Max Jensen" <tom@example.com>',
                    '"Max Jensen" <tom@example.com>',
                    true,
                    "should find at least one of two matching recipients"
                ],
                [
                    '"Will Westin" <will@example.com>,"Sarah Smith" <sarah@example.com>,"Bronsen, Sally" <sally@example.com>',
                    '"Bronsen, Sally" <sally@example.come>',
                    false,
                    "should find a recipient even with a comma in the name"
                ]
            ];

        _.each(dataProvider, function(data) {
            var actual = field._hasRecipient(data[1], data[0]);
            expect(actual).toBe(data[2]);
        }, this);
    });
});
