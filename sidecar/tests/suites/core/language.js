describe("Sugar App Language Manager", function() {
    var lang = SUGAR.App.lang,
        appCache,
        appCacheInstance;

    appCache = {
        cache: {},
        get: function(key) {
            return this.cache[key];
        },

        set: function(key, val) {
            this.cache[key] = val;
        }
    };

    beforeEach(function() {
        // Save instance of app cache
        appCacheInstance = SUGAR.App.cache;
        SUGAR.App.cache = appCache;
    });

    afterEach(function() {
        // Restore cache
        SUGAR.App.cache = appCacheInstance;
    });

    lecache = appCache;

    it("exist in sugar App Instance", function() {
        expect(lang).toBeDefined();
    });

    it("should save a hash of language strings to the language cache", function() {
        var setData = fixtures.language.Accounts;
        lang.setLabel("Accounts", setData);

        expect(lang.modStrings.Accounts).toEqual(setData);
    });

    it("should save a set of hashes to the language cache", function() {
        var setData = {
            Contacts: fixtures.language.Contacts,
            Opportunities: fixtures.language.Opportunities
        };

        lang.setLabels(setData);

        expect(lang.modStrings.Contacts).toEqual(setData.Contacts);
        expect(lang.modStrings.Opportunities).toEqual(setData.Opportunities);
    });

    it("should have saved the changed language cache to app cache", function() {
        expect(appCache.cache["language:labels"]).toEqual(fixtures.language);
    });

    it("should retreive the label from the language string store according to the module and label name", function() {
        var setData = fixtures.language.Accounts,
            string;

        lang.setLabel("Accounts", setData);
        string = lang.get("LBL_ANNUAL_REVENUE", "Accounts");

        expect(string).toEqual("Annual Revenue");
    });

    it("should save app list strings to the language cache and app cache", function() {
        var appListStrings = fixtures.metadata.appListStrings;

        lang.setAppListStrings(appListStrings);

        expect(lang.appListStrings).toEqual(appListStrings);
        expect(appCache.cache["language:appListStrings"]).toEqual(fixtures.metadata.appListStrings);
    });

    it("should save app strings to the language cache and app cache", function() {
        var appStrings = fixtures.metadata.appStrings;

        lang.setAppStrings(appStrings);

        expect(lang.appStrings).toEqual(appStrings);
        expect(appCache.cache["language:appStrings"]).toEqual(fixtures.metadata.appStrings);
    });
});