describe("sugarfields", function() {

    describe("checkbox", function() {
        it("should format the value", function() {
            var fieldType = {type:'checkbox'};
            var field = SUGAR.App.metadata.getField(fieldType);
            field.controller = SugarTest.loadSugarField('bool/bool');

            expect(field.controller.format("0")).toEqual(false);
            expect(field.controller.format("1")).toEqual(true);
        });

    });
});