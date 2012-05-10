describe("User", function() {

    var app = SugarTest.app, loginResponse;

    beforeEach(function() {
        loginResponse = {
            "current_user": {
                "id": "1",
                "full_name": "Administrator",
                "user_name": "admin",
                "timezone": "America\/Los_Angeles",
                "datepref": "m\/d\/Y",
                "timepref": "h:ia"
              }
        };
    });

    it("should init itself with data from local storage", function() {
        var user = app.user;
        app.cache.set("current_user", JSON.stringify(loginResponse.current_user));
        user.init();
        expect(user.id).toEqual("1");
        expect(user.full_name).toEqual("Administrator");
        expect(user.user_name).toEqual("admin");
        expect(user.timezone).toEqual("America\/Los_Angeles");
        expect(user.datepref).toEqual("m\/d\/Y");
        expect(user.timepref).toEqual("h:ia");

    });

    it("should login user", function() {
        var loginSuccessEventSpy = sinon.spy(),
            userReset = sinon.spy(app.user, '_reset');

        SugarTest.seedApp();
        app = SugarTest.app;
        app.events.on("app:login:success", loginSuccessEventSpy);
        
        SugarTest.seedFakeServer();
        SugarTest.server.respondWith("POST", /.*\/rest\/v10\/login.*/,
            [200, {  "Content-Type": "application/json"},
                JSON.stringify({current_user:'jimbo'})]);

        app.login({username:'scooby',password:'pass'}, null, {
            success: function() {},
            error: function() {}
        });
        SugarTest.server.respond();

        expect(userReset).toHaveBeenCalled();
        expect(userReset.calledWith('jimbo')).toBeTruthy();
        expect(loginSuccessEventSpy).toHaveBeenCalled();
    });

    it("should reset itself with new data", function() {
        var user = app.user;
        app.cache.set("current_user", JSON.stringify(loginResponse.current_user));
        user.init();

        var newData = {
            "current_user": {
                "id": "2",
                "full_name": "Vasia"
              }
        };

        user._reset(newData.current_user);

        expect(user.id).toEqual("2");
        expect(user.full_name).toEqual("Vasia");
        expect(user.user_name).toBeUndefined();
        expect(user.timezone).toBeUndefined();
        expect(user.datepref).toBeUndefined();
        expect(user.timepref).toBeUndefined();

        expect(SugarTest.storage["test:portal:current_user"]).toEqual(JSON.stringify(newData.current_user));
    });


});
