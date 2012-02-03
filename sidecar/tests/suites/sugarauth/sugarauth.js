describe("sugarAuth", function () {

    // setup to be run before every test
    beforeEach(function () {

        this.user_name = 'test';
        this.validPassword = 'Sugar123!';
        this.invalidPassword = 'invalid';
        this.auth = SUGAR.App.sugarAuth.getInstance();
    });

    // teardown to be run after every test
    afterEach(function () {

    });

    it("should not be authenticated when initialized", function () {
        //make expectations (then)
        expect(this.auth.isAuthenticated()).toBeFalsy();
    });

    it("should login successfully with correct passwords", function () {
        //TODO add spy to check api call
        //make expectations (then)
        var result = this.auth.login({
            user_name: this.user_name,
            password: this.validPassword
        });
        //login returned true
        expect(result).toBeTruthy();
        //is authenticated
        expect(this.auth.isAuthenticated()).toBeTruthy();
    });

    it("should not login successfully with correct passwords", function () {
        //TODO add spy to check api call
        //make expectations (then)
        var result = this.auth.login({
            user_name: this.user_name,
            password: this.invalidPassword
        });
        //login returned false
        expect(result).toBeFalsy();
        //is not authenticated
        expect(this.auth.isAuthenticated()).toBeTruthy();
    });

    it("should logout", function () {
        //TODO add spy to check api call
        //make expectations (then)
        var result = this.auth.logout();
        //login returned false
        expect(result).toBeTruthy();
        //is not authenticated
        expect(this.auth.isAuthenticated()).toBeFalsy();
    });

});