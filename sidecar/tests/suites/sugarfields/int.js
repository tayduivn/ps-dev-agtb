describe("sugarfields", function() {

    describe("integer", function() {
        it("should format the value", function() {
            var app = SUGAR.App;
            var field = app.sugarFieldManager.get({
                def: {
                    type: "integer",
                    number_group_seperator: ","
                },
                view: "detail",
                label: "",
                model: { "Contacts": { fields: { }}}
            });
            var controller = SugarTest.loadSugarField('int/int');
            field = _.extend(field, controller);
            expect(field.format("123456.502")).toEqual("123,457");
            expect(field.unformat("123456.498")).toEqual("123456");
        });

    });
});