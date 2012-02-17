describe("Sugar App Language Manager", function() {
    var lang = SUGAR.App.lang,
        langfile = fixtures.language,
        appCache,
        setSpy,
        getSpy,
        appCacheInstance;

    appCache = {
        cache: {},
        get: function(key) {

            return this.cache[key];
        },

        set: function(key, val) {
            console.log("Setting le cache");
            this.cache[key] = val;
        }
    };

    lecache = appCache;

    // Save instance of app cache
    appCacheInstance = SUGAR.App.cache;
    SUGAR.App.cache = appCache;

    it("exist in sugar App Instance", function() {
        expect(lang).toBeDefined();
    });

    it("should save a hash of language strings to the language cache", function() {
        var setData = {
            LBL_MONEY: "Money"
        };

        console.log(lang);
        lang.setLabel("SampleSet", setData);

        expect(lang.langmap.SampleSet).toEqual(setData);
    });

    it("should save a set of hashes to the language cache", function() {
        var setData = {
            TestSet: {
                LBL_TEST: "Test String"
            },
            NextTestSet: {
                LBL_NEXTTEST: "Next String"
            }
        };

        lang.setLabels(setData);

        expect(lang.langmap.TestSet).toEqual(setData.TestSet);
        expect(lang.langmap.NextTestSet).toEqual(setData.NextTestSet);
    });

    it("should have saved the changed language cache to app cache", function() {
        expect(appCache.cache).toEqual({
            TestSet: {
                LBL_TEST: "Test String"
            },
            NextTestSet: {
                LBL_NEXTTEST: "Next String"
            },
            SampleSet: {
                LBL_MONEY: "Money"
            }
        });
    });

    it("should retreive the label from the language string store according to the module and label name", function() {
        var string = lang.get("LBL_MONEY", "SampleSet");
        expect(string).toEqual("Money");
    });

    SUGAR.App.cache = appCacheInstance;
});