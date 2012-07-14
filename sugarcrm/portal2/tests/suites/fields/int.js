describe("sugarfields", function() {

    describe("integer", function() {
        it("should format the value", function() {
            var controller = SugarFieldTest.loadSugarField('int/int'),
                field = SugarFieldTest.createField("foo", "int", "detail", { number_group_seperator: "," });
            field = _.extend(field, controller);

            expect(field.format("123456.502")).toEqual("123,457");
            expect(field.unformat("123456.498")).toEqual("123456");
        });

    });
});
