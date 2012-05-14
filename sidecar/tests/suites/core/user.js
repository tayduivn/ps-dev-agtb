describe("User", function() {

    var app, user;

    beforeEach(function() {
        app = SugarTest.app;
        user = app.user;
        app.cache.set("app:user", {
                        "id": "1",
                        "full_name": "Administrator",
                        "user_name": "admin",
                        "timezone": "America\/Los_Angeles",
                        "datepref": "m\/d\/Y",
                        "timepref": "h:ia"
        });
    });

    it("should init itself with data from local storage", function() {
        user.init();
        expect(user.get('id')).toEqual("1");
        expect(user.get('full_name')).toEqual("Administrator");
        expect(user.get('user_name')).toEqual("admin");
        expect(user.get('timezone')).toEqual("America\/Los_Angeles");
        expect(user.get('datepref')).toEqual("m\/d\/Y");
        expect(user.get('timepref')).toEqual("h:ia");
    });

    it("should _reset with no args to clear", function() {
        user.init();
        user._reset();
        expect(user.get('id')).toBeUndefined();
        expect(user.get('full_name')).toBeUndefined();
        expect(app.cache.get('app:user')).toBeUndefined();
    });

    it("should do simple get and set", function() {
        user.init();
        user.set('foo', 'foo value');
        expect(user.get('foo')).toEqual('foo value');
        expect(SugarTest.storage["test:portal:app:user"].foo).toEqual("foo value");
    });

    it("should not nuke old user app settings but should reset server settings", function() {
        user.init();
        user.set("non-server-setting", "foo");

        user._reset({
            id: "1",
            full_name: "Administrator 2"
        });

        expect(user.get('id')).toEqual("1");
        expect(user.get('full_name')).toEqual("Administrator 2");
        expect(user.get('non-server-setting')).toEqual("foo");
    });

    it("should reset and clear user on logout if clear flag", function() {
        user.init();
        app.events.trigger("app:logout", true);
        expect(user.get('id')).toBeUndefined();
    });
    
    it("should login user", function() {
        var loginSuccessEventSpy = sinon.spy(),
            userReset = sinon.spy(app.user, '_reset');

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
        user.init();

        var newData = {
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

        expect(SugarTest.storage["test:portal:app:user"]).toEqual(newData.current_user);
    });


});
