describe("sugarfields", function() {

    describe("checkbox", function() {
        it("should format the value", function() {
            var value = "1";
            var obj = SugarTest.loadJsFile('../../sugarcrm/include/SugarFields/Fields/Bool/portal/bool');
            var result = obj.format(value);
            expect(result).toEqual(true);
        });
    });
});