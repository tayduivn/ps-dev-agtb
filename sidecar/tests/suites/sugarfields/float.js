describe("sugarfields", function() {

    describe("float", function() {
        it("should format the value", function() {

            var app = SUGAR.App;
            var field = app.view.createField({
                def: {
                    type: "float",
                    round: 3,
                    precision: 4,
                    number_group_seperator: ",",
                    decimal_seperator: "."
                },
                view: "detail",
                label: "",
                model: { "Contacts": { fields: { }}}
            });
            var controller = SugarTest.loadSugarField('float/float');
            field = _.extend(field, controller);
            expect(field.format("12351616461.2551616")).toEqual("12,351,616,461.2550");
            expect(field.unformat("12,351,616,461.2550")).toEqual("12351616461.2550");
        });

    });
});