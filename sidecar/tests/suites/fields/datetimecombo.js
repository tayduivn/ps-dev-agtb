describe("sugarfields", function() {

    describe("datetime", function() {
        it("should format the value", function() {

            var controller = SugarFieldTest.loadSugarField('datetimecombo/datetimecombo'),
                field = SugarFieldTest.createField("datetimecombo", "detail"),
                unformatedValue, expectedValue;
            field = _.extend(field, controller);

            unformatedValue = new Date(2012, 3, 9, 9, 50, 58);
            expectedValue =
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
