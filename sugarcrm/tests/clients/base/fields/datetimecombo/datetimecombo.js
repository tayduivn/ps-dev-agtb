function forceTwoDigits(numstr) {
    return numstr.length === 1 ? '0' + numstr: numstr;
}

describe("datetimecombo field", function() {
    var app, baseDateField, field, myUser;

    beforeEach(function() {
        app = SugarTest.app;
        myUser = SUGAR.App.user;
        myUser.set('datepref','m/d/Y');
        myUser.set('timepref','H:i');

        baseDateField = SugarTest.createField("base", "basedate", "basedate", "edit");
        field = SugarTest.createField("base", "datetimecombo", "datetimecombo", "edit");

        // Convenience for any specs that want to avoid calling initialize. (note if 
        // initialize called myUser's datepre/timepref will take precedence)
        field.usersDatePrefs = 'm/d/Y';
        field.userTimePrefs = 'H:i';
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field  = null;
        myUser = null;
        baseDateField = null;
    });

    describe("datetimecombo init", function() {
        var datepickerStub, jqFn, expectedValValue;
        
        // Essentially, the following stubs out this.$('doesnt_matter').<val & datepicker>
        beforeEach(function(){
            datepickerStub = sinon.stub();
            jqFn = sinon.stub(field, '$', function() {
                return {
                    'val': function() { 
                        return 'arbitrary_value';
                    },
                    'datepicker': datepickerStub
                };
            });
            expectedValValue = field.$("foo").val(); // 1970-09-12 from above ;-)
        });

        afterEach(function(){
            jqFn.restore();
            expectedValValue = null;
        });

        it("should set our internal date value so hbt picks up", function() {
            field._presetDateValues();
            expect(field.dateValue).toEqual(expectedValValue);
            expect(field.timeValue).toEqual(expectedValValue);
        });
    });

    describe("datetimecombo test with 'H:i' (24 hour) time format", function() {
        it("should format the date time combo according to date prefs", function() {
            var expectedValue, jsDate, unformatedValue, month, day, year;
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).date).toEqual('04/09/2012');
            expect(field.dateValue).toEqual('04/09/2012');
            expect(field.timeValue).toEqual("03:00");
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
        it("should format value for display_default", function() {
            var today = new Date(), 
                actual, stub, parts,
                originalType = field.view.name;

            stub = sinon.stub(field.model, 'set');
            field.view.name = 'edit';
            field.def.display_default = 'now&06:00pm';

            // If no value passed in it checks for display_default
            actual = field.format(null);

            // Expectations on value object returned
            expect(stub).toHaveBeenCalled();
            expect(actual.amPm).toEqual('pm');
            expect(actual.hours).toEqual('06');
            expect(actual.minutes).toEqual('00');
            expect(actual.seconds).toEqual('00');
            expect(actual['time']).toEqual('18:00');
            // Test the date part
            parts = actual.date.split('/');
            expect(parseInt(parts[0], 10)).toEqual(today.getMonth()+1);
            expect(parseInt(parts[1], 10)).toEqual(today.getDate());
            expect(parseInt(parts[2], 10)).toEqual(today.getFullYear());

            stub.restore();
            field.view.name = originalType;
        });

        it("should format value for required", function() {
            var today = new Date(), 
                actual, stub, parts,
                originalType = field.view.name;

            stub = sinon.stub(field.model, 'set');
            field.view.name = 'edit';
            field.def.required = true;

            // If no value or display_default it checks for def.required
            actual = field.format(null);

            // Expectations on value object returned
            expect(stub).toHaveBeenCalled();
            expect(actual.amPm).toEqual('am');
            expect(actual.hours).toEqual('00');
            expect(actual.minutes).toEqual('00');
            expect(actual.seconds).toEqual('00');
            expect(actual['time']).toEqual('00:00');
            // Test the date part
            parts = actual.date.split('/');
            expect(parseInt(parts[0], 10)).toEqual(today.getMonth()+1);
            expect(parseInt(parts[1], 10)).toEqual(today.getDate());
            expect(parseInt(parts[2], 10)).toEqual(today.getFullYear());

            stub.restore();
            field.view.name = originalType;
        });
        it("should return value from format if NOT edit view and no value", function() {
            var originalType = field.view.name;
            field.view.name = 'not_edit';
            expect(field.format(null)).toEqual(null);
            field.view.name = originalType;
        });
        it("should convert 00am to 12am if on 12 hour time format", function() {
            var jsDate, unformatedValue, hours;
            field.userTimePrefs = 'H:ia';  // 12 hrs
            // the field sets this based on h or H in timepref, but don't want to trigger _render ;=)
            field.showAmPm = true;
            jsDate = new Date("September 12, 1970");
            jsDate.setHours(0,0,0,0);
            hours = forceTwoDigits(jsDate.getHours() + '');
            // Note TZ won't matter since it will be whatever user's local ... so we can
            // trust this will always resolve to correct hour. It's only if we were to 
            // start to apply time zone preferences would we run into issues.
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).time).not.toEqual('00:00');
            expect(field.format(unformatedValue).hours).toEqual('12');
        });
        it("should NOT convert 00am to 12am if on 24 hour time format", function() {
            var jsDate, unformatedValue;
            // the field sets this based on h or H in timepref, but don't want to trigger _render
            field.showAmPm = false;
            jsDate = new Date("September 12, 1970 00:00:00");
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).time).toEqual('00:00');
        });
    });

    describe("datetimecombo test with 'h:i' time format on detail page", function() {
        beforeEach(function() {
            field.userTimePrefs = 'h:i';
            field.view.name = 'detail';
        });
        afterEach(function() {
            field.view.name= 'edit';
        });

        it("should not perform any rounding unless on edit view", function() {
            var jsDate, unformatedValue, hours, minutes;
            jsDate = new Date('2012-04-09T09:50:58Z');
            unformatedValue = jsDate.toISOString();
            // don't round minutes up (so don't round up 50 here to 00) if non-edit view 
            hours   = forceTwoDigits(jsDate.getHours() + '');
            minutes = forceTwoDigits(jsDate.getMinutes() + '');
            expect(field.format(unformatedValue).time).toEqual(hours + ':' + minutes);
            expect(field.format(unformatedValue).time).not.toEqual(hours + ':' + '00');
        });
    });

    describe("datetimecombo helpers", function() {
        it("should set timepicker when all arguments passed in", function() {
            var setHoursStub, timepickerSpy, valSpy, el, actual;

            setHoursStub = sinon.stub(Date.prototype, 'setHours');
            timepickerSpy = sinon.spy();
            valSpy        = sinon.spy();
            el = {
                'timepicker': timepickerSpy,
                'val': valSpy
            };

            actual = field._setTimepickerValue(el, null, null);

            // setHours(0,0,0,0) called since no hours or minutes
            expect(setHoursStub.args[0][0]).toEqual(0);
            expect(setHoursStub.args[0][1]).toEqual(0);
            expect(setHoursStub.args[0][2]).toEqual(0);
            expect(setHoursStub.args[0][3]).toEqual(0);
            expect(timepickerSpy.args[0][0]).toEqual('setTime');
            expect(valSpy).toHaveBeenCalled();

            setHoursStub.restore();
        });
        it("should set timepicker when just hours supplied", function() {
            var setMinutesStub, setHoursStub, timepickerSpy, valSpy, el, actual;

            setHoursStub = sinon.stub(Date.prototype, 'setHours');
            setMinutesStub = sinon.stub(Date.prototype, 'setMinutes');
            timepickerSpy = sinon.spy();
            valSpy        = sinon.spy();
            el = {
                'timepicker': timepickerSpy,
                'val': valSpy
            };
            actual = field._setTimepickerValue(el, '09', null);

            // setHours(0,0,0,0) called since no hours or minutes
            expect(setHoursStub.args[0][0]).toEqual('09');
            expect(setMinutesStub).not.toHaveBeenCalled();
            expect(timepickerSpy.args[0][0]).toEqual('setTime');
            expect(valSpy).toHaveBeenCalled();

            setMinutesStub.restore();
            setHoursStub.restore();
        });

        it("should set timepicker when just minutes supplied", function() {
            var setMinutesStub, setHoursStub, timepickerSpy, valSpy, el, actual;

            setHoursStub = sinon.stub(Date.prototype, 'setHours');
            setMinutesStub = sinon.stub(Date.prototype, 'setMinutes');
            timepickerSpy = sinon.spy();
            valSpy        = sinon.spy();
            el = {
                'timepicker': timepickerSpy,
                'val': valSpy
            };
            actual = field._setTimepickerValue(el, null, '59');

            // setHours(0,0,0,0) called since no hours or minutes
            expect(setMinutesStub.args[0][0]).toEqual('59');
            expect(setHoursStub).not.toHaveBeenCalled();
            expect(timepickerSpy.args[0][0]).toEqual('setTime');
            expect(valSpy).toHaveBeenCalled();

            setMinutesStub.restore();
            setHoursStub.restore();
        });
        it("should set if no time", function() {
            expect(field._setIfNoTime(null, null, null).amPm).toEqual('am');
            expect(field._setIfNoTime(null, null, 'am').amPm).toEqual('am');
            // Remember that if no hour/minutes this guy forces to 12am ;)
            expect(field._setIfNoTime(null, null, 'pm').amPm).toEqual('am');
        });

        it("hours and minutes with 12am and 00pm edge cases in mind", function() {
            // Sensibly defaults to 12:00am when no hours or minutes passed
            expect(field._setIfNoTime(null, null, 'pm').hours).toEqual('00');
            expect(field._setIfNoTime(null, null, 'pm').minutes).toEqual('00');
            expect(field._setIfNoTime(null, null, 'pm').amPm).toEqual('am');

            // Respects nominal path
            expect(field._setIfNoTime('05', '06', 'pm').hours).toEqual('05');
            expect(field._setIfNoTime('05', '06', 'pm').minutes).toEqual('06');
            expect(field._setIfNoTime('05', '06', 'pm').amPm).toEqual('pm');

            // Edge cases: converts 12am to 00 and also 00pm to 12
            expect(field._setIfNoTime('12', '00', 'am').hours).toEqual('00');
            expect(field._setIfNoTime('12', '00', 'am').amPm).toEqual('am');

            expect(field._setIfNoTime('00', '00', 'pm').hours).toEqual('12');
            expect(field._setIfNoTime('00', '00', 'pm').amPm).toEqual('pm');
        });

        it("sould get hours minutes defaulting to now if no value in timepicker element", function() {
            var actual = field._getHoursMinutes({"val": function() { return null; }});
            expect(actual.hours).toEqual('00');
            expect(actual.minutes).toEqual('00');
            expect(actual.amPm).toEqual('am');
        });
        it("should get hours minutes for nominal cases", function() {
            var actual = field._getHoursMinutes({"val": function() { return '12:34 am'; }});
            expect(actual.hours).toEqual('00');
            expect(actual.minutes).toEqual('34');
            expect(actual.amPm).toEqual('am');

            actual = field._getHoursMinutes({"val": function() { return '12:34 pm'; }});
            expect(actual.hours).toEqual('12');
            expect(actual.minutes).toEqual('34');
            expect(actual.amPm).toEqual('pm');

            actual = field._getHoursMinutes({"val": function() { return '00:00 am'; }});
            expect(actual.hours).toEqual('00');
            expect(actual.minutes).toEqual('00');
            expect(actual.amPm).toEqual('am');

            actual = field._getHoursMinutes({"val": function() { return '23:59'; }});
            expect(actual.hours).toEqual('23');
            expect(actual.minutes).toEqual('59');
            expect(actual.amPm).toEqual('pm');
        });

    });

});
