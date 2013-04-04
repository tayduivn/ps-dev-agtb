describe("Email field", function() {

    var app, field, model, mock_addr;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'detail');
        SugarTest.loadComponent('base', 'field', 'listeditable');
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

    describe("adding an email address", function() {
        it("should add email addresses on the model", function() {
            field.$('.newEmail').val("test3@test.com");
            field.$('.newEmail').trigger('change');
            var emails = model.get('email');
            expect(emails[2]).toBeDefined();
            expect(emails[2].email_address).toEqual("test3@test.com");
        });
        it("should add an e-mail address automatically when newEmail input changes", function(){
            field.$('.newEmail').val("newEmail@test.com");
            field.$('.newEmail').change();
            expect(model.get('email')[2].email_address).toEqual("newEmail@test.com");
        });
        it("should not allow duplicates", function(){
            field._addNewAddress('test2@test.com');
            expect(model.get('email').length).toEqual(2);
        });
    });

    describe("updating an email address", function() {
        it("should update email addresses on the model", function() {
            field.$('input:first').val("testChanged@test.com");
            field.$('input:first').trigger('change');
            var emails = model.get('email');
            expect(emails[0].email_address).toEqual("testChanged@test.com");
        });
        it("should update email address properties on the model", function() {
            var emails = model.get('email');
            expect(emails[0].opt_out).toBeUndefined();
            field.$('[data-emailproperty=opt_out]:first').trigger('click');

            emails = model.get('email');
            expect(emails[0].opt_out).toEqual("1");
            field.$('[data-emailproperty=opt_out]:first').trigger('click');

            emails = model.get('email');
            expect(emails[0].opt_out).toEqual("0");
        });
        it("should make sure one and only one email is set as primary", function() {
            var emails = model.get('email');
            emails[0].primary_address = '1';
            emails[1].primary_address = '0';
            expect(emails[0].primary_address).toEqual('1');
            expect(emails[1].primary_address).toEqual('0');

            //Should cancel the click on primary_address button
            field.$('[data-emailproperty=primary_address]:first').trigger('click');
            emails = model.get('email');
            expect(emails[0].primary_address).toEqual('1');
            expect(emails[1].primary_address).toEqual('0');

            //Should unset the first email as the primary email
            field.$('[data-emailproperty=primary_address]:last').trigger('click');
            emails = model.get('email');
            expect(emails[0].primary_address).toEqual('0');
            expect(emails[1].primary_address).toEqual('1');
        });
    });

    describe("removing an email address", function() {
        it("should select another primary e-mail address if the primary is deleted", function(){
            var emails = model.get('email');
            expect(emails.length).toEqual(2);
            expect(emails[0].primary_address).toEqual("1");
            expect(emails[1].primary_address).toEqual("0");

            field.$('.removeEmail:first-child').trigger('click');
            emails = model.get('email');
            expect(emails.length).toEqual(1);
            expect(emails[0].primary_address).toEqual("1");
        });
        it("should delete email addresses on the model", function() {
            var emails = model.get('email');
            expect(emails.length).toEqual(2);
            field.$('.removeEmail:first').trigger('click');
            emails = model.get('email');
            expect(emails.length).toEqual(1);
        });
    });

    describe("decorating error", function() {
        it("should decorate each invalid email fields", function(){
            var $inputs = field.$('input');
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({email: ["test2@test.com"]});
            expect(field.$('.add-on').length).toEqual(1);
            expect(field.$('.add-on').data('original-title')).toEqual('ERROR_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(1);
        });
        it("should decorate the first field if there isn't any primary address set", function(){
            var $inputs = field.$('input');
            var emails = model.get('email');
            emails[0].primary_address = '0';
            emails[1].primary_address = '0';
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({primaryEmail: true});
            expect(field.$('.add-on').length).toEqual(1);
            expect(field.$('.add-on').data('original-title')).toEqual('ERROR_PRIMARY_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(0);
        });
    });

    describe("format and unformat", function() {
        it("should create flag email strings", function() {
            var testAddresses =[
                {
                    email_address: "test1@test.com",
                    primary_address: "1"
                },
                {
                    email_address: "test2@test.com",
                    primary_address: "1",
                    opt_out: "1"
                }
            ];;
            field.addFlagLabels(testAddresses);
            expect(testAddresses[0].flagLabel).toEqual("(LBL_EMAIL_PRIMARY)");
            expect(testAddresses[1].flagLabel).toEqual("(LBL_EMAIL_PRIMARY, LBL_EMAIL_OPT_OUT)");
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
                    _wasNotArray:    true,
                    flagLabel: "(LBL_EMAIL_PRIMARY)"
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

        it("should still work when model value is not already set on edit in list view (SP-604)", function() {
            var expected = "abc@abc.com",
                emails = "abc@abc.com",
                actual;

            field.view.action = "list";
            field.model.set({email : ""});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

            field.model.set({email : undefined});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

        });

        it("should return only a single primary email address as the value in the list view", function() {
            field.view.action = 'list';
            field.render();

            var new_email_address = 'test@blah.co',
                new_assigned_email = field.unformat(new_email_address),
                expected = new_email_address,
                actual;

            actual = (_.find(new_assigned_email, function(email){
                return email.primary_address;
            })).email_address;
            expect(actual).toBe(expected);
        });

    });
});
