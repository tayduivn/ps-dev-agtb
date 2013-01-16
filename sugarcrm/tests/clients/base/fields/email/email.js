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
            runs(function(){
                field.$('.newEmail').val("newEmail@test.com");
                field.$('.newEmail').change();
            });
            waitsFor(function(){
                if(model.get('email').length == 3){
                    return model.get('email')[2].email_address == "newEmail@test.com";
                }
                return false;
            }, "new e-mail address", 250);
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
        it("should add an e-mail hyperlink only to addresses that are not opt-out or invalid", function(){
            field = SugarTest.createField("base","email", "email", "details");
            var values = [
                {email_address: "foo1@foo.com"},
                {email_address: "foo2@foo.com", opt_out:"1", invalid_email:"0"},
                {email_address: "foo3@foo.com", opt_out:"0", invalid_email:"1"},
                {email_address: "foo4@foo.com", opt_out:"0", invalid_email:"0"},
                {email_address: "foo5@foo.com", opt_out:"1", invalid_email:"1"}
            ];
            model = field.model;
            model.set({email:values});
            field.render();
            // The hasAnchor property should not be part of values after render.
            expect(values[0].hasAnchor).toBeUndefined();
            expect(field.$('div[title="foo1@foo.com"] a').length).toEqual(1);
            expect(field.$('div[title="foo2@foo.com"] a').length).toEqual(0);
            expect(field.$('div[title="foo3@foo.com"] a').length).toEqual(0);
            expect(field.$('div[title="foo4@foo.com"] a').length).toEqual(1);
            expect(field.$('div[title="foo5@foo.com"] a').length).toEqual(0);
        });
    });
});
