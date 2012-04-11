describe("sugarfields", function() {

    describe("datetime", function() {
        it("should format the value", function() {

            var field = SUGAR.App.sugarFieldManager.get({
                def: {
                    type: "datetimecombo"
                },
                view: "detail",
                label: "",
                model: { "Contacts": { fields: { }}}
            });
            var controller = SugarTest.loadSugarField('datetimecombo/datetimecombo');
            field = _.extend(field, controller);
            var unformatedValue = new Date(2012, 03, 09, 09, 50, 58);
            var expectedValue =
            {
                dateTime: unformatedValue,
                date: '2012-04-09',
                time: '10:50:58',
                hours: '10',
                minutes: '50',
                seconds: '58',
                amPm: 'am'
            };
            expect(field.format(unformatedValue)).toEqual(expectedValue);

        });
    });
});