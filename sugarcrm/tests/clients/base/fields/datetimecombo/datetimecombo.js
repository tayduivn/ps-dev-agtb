function forceTwoDigits(numstr) {
    return numstr.length === 1 ? '0' + numstr: numstr;
}

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

/*
--- cheat sheet --- 
H 24-hour format of an hour with leading zeros 00 through 23.
h 12-hour format of an hour with leading zeros 01 through 12.
i Numeric representation of minutes with leading zeros 00 through 59.

SugarCRM formats:

    date_formats
        'Y-m-d'=>'2006-12-23',
        'd-m-Y' => '23-12-2006',
        'm-d-Y'=>'12-23-2006',
        'Y/m/d'=>'2006/12/23',
        'd/m/Y' => '23/12/2006',
        'm/d/Y'=>'12/23/2006',
        'Y.m.d' => '2006.12.23',
        'd.m.Y' => '23.12.2006',
        'm.d.Y' => '12.23.2006'
    time formats:
      'H:i'=>'23:00', 
      'H.i'=>'23.00', 
      'h:ia'=>'11:00pm', 
      'h:iA'=>'11:00PM',
      'h.ia'=>'11.00pm', 
      'h.iA'=>'11.00PM' 
      Note above that 'h' is always tied to 'a' or 'A' so that's why production code checks work ;=)
*/
    describe("datetimecombo test with 'H:i' (24 hour) time format", function() {
        beforeEach(function() {
            myUser.set('timepref','H:i'); 
            field = SugarTest.createField("base","datetimecombo", "datetimecombo", "edit");
        });
        it("should format the date time combo according to date prefs", function() {
            var expectedValue, jsDate, unformatedValue, month, day, year;
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).date).toEqual('04/09/2012');
        });
        it("should format the date time combo according to time prefs", function() {
            var expectedValue, jsDate, unformatedValue, year, month, day, hours;
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            month = forceTwoDigits(jsDate.getMonth() + 1 + '');
            day   = forceTwoDigits(jsDate.getDate() + '');
            year  = forceTwoDigits(jsDate.getFullYear() + '');
            // we round to nearest 15 minutes so if user's locale produces something like
            // 2:50, than that will be rounded up to 3:00; our test has :50 minutes so we add 1
            hours = forceTwoDigits(jsDate.getHours() + 1 + '');

            expect(field.format(unformatedValue).date).toEqual(
                month +'/'+ day +'/'+ year);
            expect(field.format(unformatedValue).time).toEqual(hours + ':00');
            expect(field.format(unformatedValue).seconds).toEqual('00');
        });
        it("should convert 00am to 12am if on 12 hour time format", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue, hours;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','h:ia'); // 12 hrs
            // the field sets this based on h or H in timepref, but don't want to trigger _render ;=)
            field.showAmPm = true;
            jsDate = new Date("September 12, 1970 00:00:00")
            hours = forceTwoDigits(jsDate.getHours() + 1 + '');
            // Note TZ won't matter since it will be whatever user's local ... so we can
            // trust this will always resolve to correct hour. It's only if we were to 
            // start to apply time zone preferences would we run into issues.
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).time).not.toEqual('00:00');
        });
        it("should NOT convert 00am to 12am if on 24 hour time format", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','H:i'); // 24 hrs
            // the field sets this based on h or H in timepref, but don't want to trigger _render
            field.showAmPm = false;
            jsDate = new Date("September 12, 1970 00:00:00")
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).time).toEqual('00:00');
        });
    });

    describe("datetimecombo test with 'h:i' time format on detail page", function() {
        beforeEach(function() {
            myUser.set('timepref','h:i'); 
            field = SugarTest.createField("base","datetimecombo", "datetimecombo", "detail");
        });
        it("should not perform any rounding unless on edit view", function() {
            var myUser = SUGAR.App.user, jsDate, unformatedValue;
            myUser.set('datepref','m/d/Y');
            myUser.set('timepref','h:i');
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            // don't round minutes up (so don't round up 50 here to 00) if non-edit view 
            hours   = forceTwoDigits(jsDate.getHours() + '');
            minutes = forceTwoDigits(jsDate.getMinutes() + '');
            expect(field.format(unformatedValue).time).toEqual(hours + ':' + minutes);
            expect(field.format(unformatedValue).time).not.toEqual(hours + ':' + '00');
        });
    });
});
