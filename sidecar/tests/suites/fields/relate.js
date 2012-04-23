describe("sugarfields", function() {

    describe("datetime", function() {
        it("should format the value", function() {

            var controller = SugarFieldTest.loadSugarField('relate/relate');
            var field = SugarFieldTest.createField("datetimecombo", "detail");
            field = _.extend(field, controller);
            expect(field.format(unformatedValue)).toEqual(expectedValue);
        });
    });
});