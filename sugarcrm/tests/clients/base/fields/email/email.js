describe("Email field", function() {

    var app, field, model, mock_addr;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base","email", "email", "edit");
        mock_addr =  [
            {
                email_address: "test1@test.com",
                primary_address: "1"
            },
            {
                email_address: "test2@test.com",
                primary_address: "0",
                opt_out: "1"
            }
        ];
        model = field.model;
        model.set({email:_.clone(mock_addr)});
        field.render();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("email", function() {
        it("should add email addresses on the model", function() {
            var mockEvent = {
                currentTarget: field.$el
            };
            field.$('.newEmail').val("test3@test.com");
            field.add(mockEvent);
            var emails = model.get('email');
            expect(emails[2].email_address).toEqual("test3@test.com");
        });
        it("should select another primary e-mail address if the primary is deleted", function(){
            var emails = model.get('email');
            expect(emails.length).toEqual(2);
            expect(emails[0].primary_address).toEqual("1");
            expect(emails[1].primary_address).toEqual("0");
            var mockEvent = {
                target: field.$el.find('button')[0]
            };
            field.remove(mockEvent);
            emails = model.get('email');
            expect(emails.length).toEqual(1);
            expect(emails[0].primary_address).toEqual("1");
        });
        it("should add an e-mail address automatically when newEmail input changes", function(){
            field.$('.newEmail').val("newEmail@test.com");
            field.$('.newEmail').change();
            expect(model.get('email')[2].email_address).toEqual("newEmail@test.com");
        });
        it("should update email addresses on the model", function() {
            field.$el.find('input').val("testChanged@test.com");
            var mockEvent = {
                currentTarget: field.$el.find('input')
            };
            field.updateExistingAddress(mockEvent);
            var emails = model.get('email');
            expect(emails[0].email_address).toEqual("testChanged@test.com");
        });
        it("should update email address properties on the model", function() {
            var mockEvent = {
                currentTarget: field.$el.find('button')[1]
            };
            field.updateExistingProperty(mockEvent);
            var emails = model.get('email');
            expect(emails[0].opt_out).toEqual("1");
        });
        it("should delete email addresses on the model", function() {
            var mockEvent = {
                target: field.$el.find('button')[0]
            };
            field.remove(mockEvent);
            var emails = model.get('email');
            expect(emails.length).toEqual(1);
        });

        it("should make an email address a link when metadata allows for links and the address is not opted out or invalid", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       "0",
                        invalid_email: "0"
                    }
                ],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeTruthy();
            expect(actual[1].hasAnchor).toBeTruthy();
        });

        it("should not make an email address a link when metadata doesn't allow for links", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       "0",
                        invalid_email: "0"
                    }
                ],
                actual;

            field.def.link = false;
            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
            expect(actual[1].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is opted out", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       "1",
                    invalid_email: "0"
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       "0",
                    invalid_email: "1"
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is opted out and invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       "1",
                    invalid_email: "1"
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should convert a string representing an email address into an array containing one object", function() {
            var expected = {
                    email_address:   "foo@bar.com",
                    primary_address: "1",
                    hasAnchor:       false,
                    _wasNotArray:    true
                },
                actual;

            actual = field.format(expected.email_address);
            expect(actual.length).toBe(1);
            expect(actual[0]).toEqual(expected);
        });

        it("should remove the hasAnchor property from the email address", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       "0",
                    invalid_email: "0",
                    hasAnchor:     true
                }],
                actual;

            actual = field.unformat(emails);
            expect(actual[0].hasAnchor).toBeUndefined();
        });

        it("should reset the email address to a string when _wasNotArray is true", function() {
            var expected = "foo@bar.com",
                emails   = [{
                    email_address:   expected,
                    primary_address: "1",
                    hasAnchor:       false,
                    _wasNotArray:    true
                }],
                actual;

            actual = field.unformat(emails);
            expect(actual).toBe(expected);
        });
    });
});
