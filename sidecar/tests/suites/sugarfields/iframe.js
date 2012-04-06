describe("sugarfields", function() {

    describe("iframe", function() {
        it("should format the value", function() {
            app = SUGAR.App;
            var field = app.sugarFieldManager.get({
                def: {
                    type: "iframe"
                },
                view: "detail",
                label: "",
                model: { "Contacts": { fields: { }}}
            });
            var controller = SugarTest.loadSugarField('iframe/iframe');
            field = _.extend(field, controller);
            expect(field.unformat("http://")).toEqual("");
            expect(field.unformat("http://www.google.com")).toEqual("http://www.google.com");
        });

    });
});