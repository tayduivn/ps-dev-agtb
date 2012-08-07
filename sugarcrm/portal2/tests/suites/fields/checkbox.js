describe("sugarfields", function() {

    describe("checkbox", function() {
        it("should format the value", function() {

            var controller = SugarFieldTest.loadSugarField('bool/bool'),
                field = SugarFieldTest.createField("checkbox", "detail");
            field = _.extend(field, controller);

            expect(field.format("0")).toEqual(false);
            expect(field.format("1")).toEqual(true);
        });
    });
});
