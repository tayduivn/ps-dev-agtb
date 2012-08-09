describe("sugarfields", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","datetimecombo", "datetimecombo", "detail");
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("datetime", function() {
        it("should format the value", function() {
            var unformatedValue, expectedValue;
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
