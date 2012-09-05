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
            var myUser = SUGAR.App.user, expectedValue, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).date).toEqual('04/09/2012');
            expect(field.format(unformatedValue).dateTime).toEqual('04/09/2012 03:00');
            // we round to nearest 15 minutes so 2:50 is 3:00 in this case
            expect(field.format(unformatedValue).time).toEqual('03:00');
            expect(field.format(unformatedValue).seconds).toEqual('00');
        });
    });
});
