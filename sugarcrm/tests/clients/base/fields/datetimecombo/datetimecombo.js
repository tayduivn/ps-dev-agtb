function forceTwoDigits(numstr) {
    return numstr.length === 1 ? '0' + numstr: numstr;
}

describe("datetimecombo field", function() {
    var app, baseDateField, field, myUser;

    beforeEach(function() {
        app = SugarTest.app;
        myUser = SUGAR.App.user;
        myUser.setPreference('datepref','m/d/Y');
        myUser.setPreference('timepref','H:i');

        baseDateField = SugarTest.createField("base", "date", "date", "edit");
        field = SugarTest.createField("base", "datetimecombo", "datetimecombo", "edit");

        // Convenience for any specs that want to avoid calling initialize. (note if
        // initialize called myUser's datepre/timepref will take precedence)
        field.usersDatePrefs = 'm/d/Y';
        field.userTimePrefs = 'H:i';
        field.stripIsoTZ = false; // let the browser interpret iso 8601 TZ
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field  = null;
        myUser = null;
        baseDateField = null;
    });

    describe("datetimecombo core", function() {
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
            expectedValValue = field.$("foo").val(); // 'arbitrary_value' from above ;-)
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
        it("should parse datetime from field if leaveDirty and this.dateValue not set", function() {
            var stub, returnedValue;
            field.leaveDirty = true;
            field.dateValue = '';
            field.timeValue = '';
            field.$el.text('12/01/2012 12:00am');
            returnedValue = field.format('xyz');
            expect(returnedValue['date']).toEqual('12/01/2012');
            expect(returnedValue['time']).toEqual('12:00am');
            expect(field.dateValue).toEqual('12/01/2012');
            expect(field.timeValue).toEqual('12:00am');
        });
        it("should unformat to iso 8601 compatible date string", function() {
            var yr='1999', m='01', d='23', actual;
            actual = field.unformat(yr+'-'+m+'-'+d);
            expect(actual.match(/1999\-01\-23T.*Z/)).toBeTruthy();
        });
        it("should unformat to same object passed in if falsy", function() {
            var stub;
            expect(field.unformat('')).toEqual('');
            expect(field.unformat(false)).toEqual(false);
            expect(field.unformat(null)).toEqual(null);
            stub = sinon.stub(app.logger, 'error')
            expect(field.unformat('yogabba')).toEqual('yogabba');
            expect(stub).toHaveBeenCalledOnce();
            stub.restore();
        });

        it('should build unformatted string per REST API required input', function() {
            var actual, expected;
            actual   = field._buildUnformatted('09/12/1970', '02', '00');
            expect(/1970\-09\-12T.*\:00\:00.*Z$/.test(actual)).toBeTruthy()

            // Regardless of user's prefs should still be API formatted
            field.usersDatePrefs = 'Y.m.d';
            field.userTimePrefs  = 'H.i s';
            actual   = field._buildUnformatted('1970.09.12', '02', '00');
            expect(/1970\-09\-12T.*\:00\:00.*Z$/.test(actual)).toBeTruthy()
        });
        it("should format properly when stripIsoTZ set", function() {
            var stub = sinon.stub(field, "_verifyDateString", function() { return true; });
            var date = '2012-04-09';
            var time = '09:50:58';
            field.stripIsoTZ = true;
            expect(field.format(date+' '+time)).toEqual({date: '04/09/2012', time: '10:00', amPm: 'am'});
            expect(stub).toHaveBeenCalledOnce();
            expect(stub).toHaveBeenCalledWith(date);
            stub.restore();
        });
    });

    describe("datetimecombo test with 'H:i' (24 hour) time format", function() {
        it("should format the date time combo according to date prefs", function() {
            var stubVerifyDateString, expectedValue, jsDate, unformatedValue, month, day, year;
            jsDate = new Date('2012-04-09T09:50:58Z');
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });

            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).date).toEqual('04/09/2012');
            expect(field.dateValue).toEqual('04/09/2012');

            // splitting on : since we're hardcoding in the beforeEach to use :
            var splitTimeValue = field.timeValue.split(":"),
                jsDateHrs = forceTwoDigits((jsDate.getHours() + 1).toString());
            expect(splitTimeValue[0]).toEqual(jsDateHrs);
            expect(splitTimeValue[1]).toEqual("00");
        });
        it("should format the date time combo according to time prefs", function() {
            var expectedValue, stubVerifyDateString, jsDate, unformatedValue, year, month, day, hours;
            jsDate = new Date('2012-04-09T09:50:58Z');
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });
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
            expect(actual['time']).toEqual('18:00');
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
            var jsDate, stubVerifyDateString, unformatedValue, hours;
            field.userTimePrefs = 'H:ia';  // 12 hrs
            // the field sets this based on h or H in timepref, but don't want to trigger _render ;=)
            field.showAmPm = true;
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });

            jsDate = new Date("September 12, 1970");
            jsDate.setHours(0,0,0,0);
            hours = forceTwoDigits(jsDate.getHours() + '');
            // Note TZ won't matter since it will be whatever user's local ... so we can
            // trust this will always resolve to correct hour. It's only if we were to
            // start to apply time zone preferences would we run into issues.
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue).time).not.toEqual('00:00');
        });
        it("should NOT convert 00am to 12am if on 24 hour time format", function() {
            var jsDate, stubVerifyDateString, unformatedValue;
            // the field sets this based on h or H in timepref, but don't want to trigger _render
            field.showAmPm = false;
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });
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
            var jsDate, stubVerifyDateString, unformatedValue, hours, minutes;
            jsDate = new Date('2012-04-09T09:50:58Z');
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });

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
        });

    });

});
