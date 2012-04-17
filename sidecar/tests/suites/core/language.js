describe("Sugar App Language Manager", function() {
    var lang = SUGAR.App.lang,
        appCacheInstance;
    var appCache = SUGAR.App.cache;

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

    it("should retreive the label from the language string store according to the module and label name", function() {
        var setData = fixtures.language.Accounts,
            string;

        lang.setLabel("Accounts", setData);
        string = lang.get("LBL_ANNUAL_REVENUE", "Accounts");

        expect(string).toEqual("Annual Revenue");
    });

    it("should retreive the label from app strings if its not set in mod strings", function() {
        var setData = fixtures.language.Accounts,
            string;
        var appStrings = fixtures.metadata.appStrings;

        lang.setAppStrings(appStrings);

        string = lang.get("DATA_TYPE_DUE", "Accounts");

        expect(string).toEqual("Due");
    });

    it("should return the input if its not set at all", function() {
        var setData = fixtures.language.Accounts,
            string;
        var appStrings = fixtures.metadata.appStrings;

        lang.setAppStrings(appStrings);

        string = lang.get("THIS_LABEL_DOES_NOT_EXIST");

        expect(string).toEqual("THIS_LABEL_DOES_NOT_EXIST");
    });

    it("should save app list strings to the language cache and app cache", function() {
        var appListStrings = fixtures.metadata.appListStrings;

        lang.setAppListStrings(appListStrings);
        expect(lang.appListStrings).toEqual(appListStrings);
        expect(appCache.get("language:appListStrings")).toEqual(fixtures.metadata.appListStrings);
    });

    it("should save app strings to the language cache and app cache", function() {
        var appStrings = fixtures.metadata.appStrings;

        lang.setAppStrings(appStrings);

        expect(lang.appStrings).toEqual(appStrings);
        expect(appCache.get("language:appStrings")).toEqual(fixtures.metadata.appStrings);
    });
});