describe("sugarfields", function() {

    describe("iframe", function() {
        it("should format the value", function() {
            var field = SugarTest.createField("base","iframe", "iframe", "detail");
            expect(field.unformat("http://")).toEqual("");
            expect(field.unformat("http://www.google.com")).toEqual("http://www.google.com");
        });
    });
});
