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

        expect(lang.langmap.Accounts).toEqual(setData);
    });

    it("should save a set of hashes to the language cache", function() {
        var setData = {
            Contacts: fixtures.language.Contacts,
            Opportunities: fixtures.language.Opportunities
        };

        lang.setLabels(setData);

        expect(lang.langmap.Contacts).toEqual(setData.Contacts);
        expect(lang.langmap.Opportunities).toEqual(setData.Opportunities);
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
});