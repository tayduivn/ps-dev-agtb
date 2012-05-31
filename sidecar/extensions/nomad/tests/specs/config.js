describe("Mobile Application configuration", function() {

    it("should have all properties defined", function() {
        var config = SugarTest.app.config;

        expect(config.appId).toEqual("nomad");
        expect(config.env).toBeDefined();
        expect(config.logLevel).toBeDefined();
        expect(config.logFormatter).toBeDefined();
        expect(config.logWriter).toBeDefined();
        expect(config.platform).toEqual("mobile");
        expect(config.maxQueryResult).toBeDefined();
        expect(config.serverUrl).toBeDefined();
        expect(config.debugSugarApi).toBeDefined();
        expect(config.metadataTypes).toBeDefined();
    });

});