describe("Sugar App Language Manager", function() {
    var lang = SUGAR.App.lang,
        langfile = fixtures.language,
        appCache;

    it("exist in sugar App Instance", function() {
        expect(lang).toBeDefined();
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

        expect();
    });

    it("should save a hash of language strings to the language cache", function() {

    });

    describe("when requesting a language label", function() {
        it("should retreive the label from the language string store according to the module and label name", function() {

        });
    });
});