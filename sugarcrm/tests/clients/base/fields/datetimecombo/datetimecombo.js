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

    describe("datetimecombo", function() {
        it("should format the date time combo", function() {
            var myUser = SUGAR.App.user.getUser();
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');

            var jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();

            expectedValue =
            {
                date: '04/09/2012',
                time: '10:00',
                hours: '10',
                minutes: '00',
                seconds: '58',
                amPm: 'am'
            };
            var outdata = field.format(unformatedValue);
            delete outdata.dateTime;
            expect(outdata).toEqual(expectedValue);
        });
    });
});
