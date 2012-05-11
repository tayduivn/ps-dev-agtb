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
        app.cache.set("current_user", loginResponse.current_user);
        user.init();
        expect(user.get('id')).toEqual("1");
        expect(user.get('full_name')).toEqual("Administrator");
        expect(user.get('user_name')).toEqual("admin");
        expect(user.get('timezone')).toEqual("America\/Los_Angeles");
        expect(user.get('datepref')).toEqual("m\/d\/Y");
        expect(user.get('timepref')).toEqual("h:ia");
    });

    it("should _reset with no args to clear", function() {
        var user = app.user;
        app.cache.set("current_user", loginResponse.current_user)
        user.init();
        user._reset();
        expect(user.get('id')).toBeUndefined();
        expect(user.get('full_name')).toBeUndefined();
    });

    it("should do simple get and set", function() {
        var user = app.user;
        user.set('foo', 'foo value');
        expect(user.get('foo')).toEqual('foo value');
    });

    it("should nuke old user when new user comes to town", function() {
        var user = app.user;
        app.cache.set("current_user", loginResponse.current_user);
        user.init();
        expect(user.get('id')).toEqual('1');
        app.cache.set('current_user', {
            id: 99,
            yo:"gabagaba"});

        user.init();
        expect(user.get('id')).toEqual(99);
        expect(user.get('full_name')).toBeUndefined();
    });

    it("should reset and clear user on logout if clear flag", function() {
        SugarTest.seedApp();
        app = SugarTest.app;
        var user = app.user;
        app.cache.set("current_user", loginResponse.current_user);
        user.init();
        app.events.trigger("app:logout", true);
        expect(user.get('id')).toBeUndefined();
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
        var user = app.user, newData = null;
        app.cache.set("current_user", loginResponse.current_user);
        user.init();

        newData = {
            "current_user": {
                "id": "2",
                "full_name": "Vasia"
              }
        };

        user._reset(newData.current_user);

        expect(user.get('id')).toEqual("2");
        expect(user.get('full_name')).toEqual("Vasia");
        expect(user.get('user_name')).toBeUndefined();
        expect(user.get('timezone')).toBeUndefined();
        expect(user.get('datepref')).toBeUndefined();
        expect(user.get('timepref')).toBeUndefined();

        expect(SugarTest.storage["test:portal:current_user"]).toEqual(newData.current_user);
    });


});
