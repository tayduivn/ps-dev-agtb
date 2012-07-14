describe("sugarfields", function() {

    describe("iframe", function() {
        it("should format the value", function() {

            var controller = SugarFieldTest.loadSugarField('iframe/iframe'),
                field = SugarFieldTest.createField("iframe", "detail");
            field = _.extend(field, controller);

            expect(field.unformat("http://")).toEqual("");
            expect(field.unformat("http://www.google.com")).toEqual("http://www.google.com");
        });
    });
});
