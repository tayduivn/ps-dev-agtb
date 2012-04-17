describe("sugarfields", function() {

    describe("datetime", function() {
        it("should format the value", function() {

            var controller = SugarFieldTest.loadSugarField('datetimecombo/datetimecombo');
            var field = SugarFieldTest.createField("datetimecombo", "detail");
            field = _.extend(field, controller);

            var unformatedValue = new Date(2012, 3, 9, 9, 50, 58);
            var expectedValue =
            {
                dateTime: unformatedValue,
                date: '2012-04-09',
                time: '10:00:58',
                hours: '10',
                minutes: '00',
                seconds: '58',
                amPm: 'am'
            };
            expect(field.format(unformatedValue)).toEqual(expectedValue);
        });
    });
});
