describe("datetimecombo field", function() {
    var app, field;
    var app, field, myUser;

    beforeEach(function() {
        app = SugarTest.app;
        myUser = SUGAR.App.user;
        myUser.set('datepref','m/d/Y');
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        myUser = null;
        field = null;
    });

    describe("datetimecombo test with 'H:i' time format", function() {
        beforeEach(function() {
            myUser.set('timepref','H:i'); 
            field = SugarTest.createField("base","datetimecombo", "datetimecombo", "detail");
        });

        it("should format the date time combo", function() {
            var expectedValue, jsDate, unformatedValue, month, day, year;
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).date).toEqual('04/09/2012');
            month = jsDate.getMonth() + 1 + '';
            month = month.length === 1 ? '0' + month : month;
            day   = jsDate.getDate() + '';
            day   = day.length === 1 ? '0' + day : day;
            year  = jsDate.getFullYear() + '';

            // we round to nearest 15 minutes so if user's locale produces something like
            // 2:50, than that will be rounded up to 3:00; our test has :50 minutes so we add 1
            var hours = jsDate.getHours() + 1 + '';
            hours = hours.length === 1 ? '0' + hours : hours;

            expect(field.format(unformatedValue).dateTime).toEqual(
                month +'/'+ day +'/'+ year +' '+ hours +':'+'00');
                
            expect(field.format(unformatedValue).time).toEqual(hours + ':00');
            expect(field.format(unformatedValue).seconds).toEqual('00');
        });
        it("should NOT convert 00am to 12am if on 24 hour time format", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i');
            // the field sets this based on h or H in timepref, but don't want to trigger _render ;=)
            field.showAmPm = false;
            jsDate = new Date("September 12, 1970 00:00:00")
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).hours).not.toEqual('12');
            expect(field.format(unformatedValue).hours).toEqual('00');
        });
    });

    describe("datetimecombo test with 'h:i' time format", function() {
        beforeEach(function() {
            myUser.set('timepref','h:i'); 
            field = SugarTest.createField("base","datetimecombo", "datetimecombo", "detail");
        });
        it("should convert 00am to 12am if on 12 hour time format", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','h:i');
            // the field sets this based on h or H in timepref, but don't want to trigger _render ;=)
            field.showAmPm = true;
            jsDate = new Date("September 12, 1970 00:00:00")
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).hours).toEqual('12');
        });
    });
});
