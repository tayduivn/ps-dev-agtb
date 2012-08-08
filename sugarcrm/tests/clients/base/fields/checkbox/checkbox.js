describe("sugarfields", function() {

    describe("checkbox", function() {
        it("should format the value", function() {
            var field = SugarTest.createField("base","checkbox", "bool", "detail");
            expect(field.format("0")).toEqual(false);
            expect(field.format("1")).toEqual(true);
        });
    });
});

