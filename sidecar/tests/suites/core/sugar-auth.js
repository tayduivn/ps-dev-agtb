describe("sugarAuth", function () {

    // setup to be run before every test
    beforeEach(function () {
        //this.App = SUGAR.App.init({el: "#sidecar", rest:SUGAR.App.config.baseUrl});
        this.user_name = 'admin';
        this.validPassword = 'asdf';
        this.invalidPassword = 'invalid';
        this.api = SUGAR.Api.getInstance({baseUrl: "/rest/v10"});
        this.auth = SUGAR.App.sugarAuth;
        this.callbacks = {
            success: function(data){
                //console.log(data);
            },
            error: function(data){
                //console.log(data);
            }
        }

        this.server = sinon.fakeServer.create();
    });

    // teardown to be run after every test
    afterEach(function () {
        this.server.restore();
    });

    it("should not be authenticated when initialized", function () {
        //make expectations (then)
        expect(this.auth.isAuthenticated()).toBeFalsy();
    });

    it("should login successfully with correct passwords", function () {
        //TODO add spy to check api call
        var apiCallSpy = sinon.spy(this.api, 'call');
        var callbacksSpy = sinon.spy(this.callbacks, 'success');
        this.server.respondWith("POST", "/rest/v10/login",
            [200, {  "Content-Type":"application/json"},
                JSON.stringify(fixtures.api['rest/v10/login']['POST'])]);

        //make expectations (then)
        var result = this.auth.login({
            username: this.user_name,
            password: this.validPassword
        },this.callbacks);

        this.server.respond(); //tell server to respond to pending async call

        expect(apiCallSpy).toHaveBeenCalled();
        expect(callbacksSpy).toHaveBeenCalled();
        expect(this.auth.isAuthenticated()).toBeTruthy();

        this.callbacks.success.restore();
        this.api.call.restore();
    });

    it("should not login successfully with incorrect passwords", function () {
        var apiCallSpy = sinon.spy(this.api, 'call');
        var callbacksSpy = sinon.spy(this.callbacks, 'error');
        this.server.respondWith("POST", "/rest/v10/login",
            [404, {  "Content-Type":"application/json"},
            ""]);

        //make expectations (then)
        var result = this.auth.login({
            username: this.user_name,
            password: this.invalidPassword
        }, this.callbacks);
        this.server.respond(); //tell server to respond to pending async call

        expect(apiCallSpy).toHaveBeenCalled();
        expect(callbacksSpy).toHaveBeenCalled();
        expect(this.auth.isAuthenticated()).toBeFalsy();

        this.callbacks.error.restore();
        this.api.call.restore();
    });

    it("should logout", function () {
        var callbacksSpy = sinon.spy(this.callbacks, 'success');
        var apiCallSpy = sinon.spy(this.api, 'call');
        //make expectations (then)

        this.server.respondWith("POST", "/rest/v10/logout",
            [200, {  "Content-Type":"application/json"},
                ""]);
        var result = this.auth.logout(this.callbacks);

        this.server.respond(); //tell server to respond to pending async call

        expect(callbacksSpy).toHaveBeenCalled();
        expect(apiCallSpy).toHaveBeenCalled();
        expect(this.auth.isAuthenticated()).toBeFalsy();

        this.api.call.restore();
        this.callbacks.success.restore();
    });

});