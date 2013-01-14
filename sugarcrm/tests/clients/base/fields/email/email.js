describe("Email field", function() {

    var app, field, model, mock_addr;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit');
        SugarTest.testMetadata.set();
        field = SugarTest.createField("base","email", "email", "edit");
        mock_addr =  [
            {
                email_address: "test1@test.com",
                primary_address: "1"
            },
            {
                email_address: "test2@test.com",
                primary_address: "1",
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
            var values = [
                {emailAddress: "foo@foo.com"},
                {emailAddress: "foo@foo.com", opt_out:"1", invalid_email:"0"},
                {emailAddress: "foo@foo.com", opt_out:"0", invalid_email:"1"},
                {emailAddress: "foo@foo.com", opt_out:"0", invalid_email:"0"},
                {emailAddress: "foo@foo.com", opt_out:"1", invalid_email:"1"}
            ];
            model.set({email:values});
            field._render();
            expect(values[0].hasAnchor).toBeTruthy();
            expect(values[1].hasAnchor).toBeFalsy();
            expect(values[2].hasAnchor).toBeFalsy();
            expect(values[3].hasAnchor).toBeTruthy();
            expect(values[4].hasAnchor).toBeFalsy();
        });
    });
});
