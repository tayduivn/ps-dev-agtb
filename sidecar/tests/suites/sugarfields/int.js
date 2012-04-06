describe("sugarfields", function() {

    describe("integer", function() {
        it("should format the value", function() {
            var fieldType = {sugarField:{type:'integer'}};
            var field = SUGAR.App.metadata.get(fieldType);
            field.controller = SugarTest.loadSugarField('int/int');

            expect(field.controller.format("122.65678")).toEqual("123");
            expect(field.controller.unformat("123.256")).toEqual("123");
        });

    });
});