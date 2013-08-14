describe("Forgotpassword View", function() {

    var view, app;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.addViewDefinition('forgotpassword', {
            "panels": [
                {
                    "fields": [
                        {
                            "name": "username"
                        },
                        {
                            "name": "email"
                        }
                    ]
                }
            ]
        });
        SugarTest.testMetadata.set();
        view = SugarTest.createView("base", "Forgotpassword", "forgotpassword");
        app = SUGAR.App;
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        view = null;
    });

    describe("Declare password Bean", function() {
        it("should have declared a Bean with the fields metadata", function() {
            expect(view.model.fields).toBeDefined();
            expect(_.size(view.model.fields)).toBeGreaterThan(0);
            expect(_.size(view.model.fields.username)).toBeDefined();
            expect(_.size(view.model.fields.password)).toBeDefined();
        });
    });
    it("should be able make and handle successful password reset requests", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);
        var renderSpy = sinon.stub(view, 'render');
        //setup fake REST end-point for enum
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*rest\/v10\/password\/request\/*/,
            [200, { 'Content-Type': 'application/json'}, JSON.stringify(true)]);

        view.forgotPassword();
        SugarTest.server.respond();

        expect(view._showResult).toBeTruthy();
        expect(view._showSuccess).toBeTruthy();
        expect(view.resultLabel).toEqual('LBL_PASSWORD_REQUEST_SENT');
    });

    it("should be able make and handle failed password reset requests", function() {
        view.model = new Backbone.Model();
        view.model.set('username','test');
        view.model.set('email',[{email_address:'test@test.com'}]);
        var renderSpy = sinon.stub(view, 'render');
        //setup fake REST end-point for enum
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith('GET', /.*rest\/v10\/password\/request\/*/,
            [424, { 'Content-Type': 'application/json'}, JSON.stringify({message:'LBL_PASSWORD_REQUEST_ERROR'})]);

        view.forgotPassword();
        SugarTest.server.respond();

        expect(view._showResult).toBeTruthy();
        expect(view._showSuccess).toBeFalsy();
        expect(view.resultLabel).toEqual('LBL_PASSWORD_REQUEST_ERROR');
    });
})
;
