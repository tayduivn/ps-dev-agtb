describe("int field", function() {

    describe("integer", function() {
        it("should format the value", function() {
            var field = SugarTest.createField("base","int", "int", "detail", { number_group_seperator: "," });
            expect(field.format("123456.502")).toEqual("123,457");
            expect(field.unformat("123456.498")).toEqual("123456");
        });

    });
});
