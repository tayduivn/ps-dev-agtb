describe("Core Application configuration", function() {

    it("should have all appropriate properties defined", function() {
        var config = SUGAR.App.config;

        expect(config.appId).toEqual("core-app");
        expect(config.platform).toEqual("core");
    });

});