describe("date field", function() {

    var app, field;

    beforeEach(function() {
        app = SugarTest.app;
        field = SugarTest.createField("base","date", "date", "detail");
        // To avoid calling initialize we just set these here
        field.usersDatePrefs = 'm/d/Y';
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        delete Handlebars.templates;
        field = null;
    });

    describe("date", function() {

        it("should unformat", function() {
            var isoDateString, actual, parts, now;

            field.serverDateFormat = 'Y/m/d';
            now = new Date();
            isoDateString = now.toISOString();

            actual = field.unformat( (new Date().toISOString()) );
            parts = actual.split('/');

            expect(parseInt(parts[0], 10)).toEqual(now.getFullYear());
            expect(parseInt(parts[1], 10)).toEqual(now.getMonth()+1);
            expect(parseInt(parts[2], 10)).toEqual(now.getDate());
        });
        it("should format the value", function() {
            var jsDate, unformatedValue;
            jsDate = new Date("March 13, 2012");
            unformatedValue = jsDate.toISOString();
            expect(field.format(unformatedValue)).toEqual('03/13/2012');
        });

        it("should format value for display_default", function() {
            var today = new Date(), 
                actual, stub, parts,
                originalType = field.view.name;

            stub = sinon.stub(field.model, 'set');
            field.view.name = 'edit';

            field.def.display_default = 'now';
            actual = field.format(null);
            parts = actual.split('/');
            expect(parseInt(parts[0], 10)).toEqual( (today.getMonth()+1) );
            expect(parseInt(parts[1], 10)).toEqual( (today.getDate()) );
            expect(parseInt(parts[2], 10)).toEqual( (today.getFullYear()) );
            expect(stub).toHaveBeenCalled();

            stub.restore();
            field.view.name = originalType;
        });

        it("should return value from format if NOT edit view and no value", function() {
            var originalType = field.view.name;
            field.view.name = 'not_edit';
            expect(field.format(null)).toEqual(null);
            field.view.name = originalType;
        });
    });
    describe("basedatepicker core functions", function() {

        it('should return today date if date string passed in is falsy', function() {
            var stub = sinon.stub(app.date, 'format');
            field.usersDatePrefs = 'Y.m.d';
            field._getTodayDateStringIfNoDate();
            expect(stub).toHaveBeenCalledOnce();
            expect(stub.args[0][1]).toEqual('Y.m.d');
            stub.restore();
        });
        it("should return date string passed in if it's truthy", function() {
            var actual, stub = sinon.stub(app.date, 'format');
            field.usersDatePrefs = 'Y.m.d';
            actual = field._getTodayDateStringIfNoDate('1970.09.12');
            expect(stub).not.toHaveBeenCalled();
            expect(actual).toEqual('1970.09.12');
            stub.restore();
        });
        it("should return true if edit view", function() {
            field.options = {
                def: {
                    view: 'foo'
                },
                viewName: 'bar'
            };
            expect(field._isEditView('edit')).toBeTruthy();

            field.options.def.view ='edit';
            expect(field._isEditView()).toBeTruthy();

            // Set options.viewName
            field.options.def.view = '';
            field.options.viewName = 'edit';
            expect(field._isEditView()).toBeTruthy();
        });
        it("should return false if it can't find view name or is not edit view", function() {
            expect(field._isEditView()).toBeFalsy();
            // Set options empty
            field.options = {def: {}};
            expect(field._isEditView()).toBeFalsy();
        });

        it("should set server date string depending on whether stripIsoTZ is true or false",function() {
            var today = new Date();
            field.stripIsoTZ = false; // let the browser interpret iso 8601 TZ
            expect(field._setServerDateString(today).match(/Z$/)).toBeTruthy();
            expect(field._setServerDateString(today).match(/T/)).toBeTruthy();
            field.stripIsoTZ = true;
            expect(field._setServerDateString(today).match(/Z$/)).toBeFalsy();
            expect(field._setServerDateString(today).match(/T/)).toBeTruthy(); // should still have T delimiter
            field.stripIsoTZ = false;
        });

        it("should identify if view is new edit view with no value or not", function() {
            var originalType,
                isNewStub = sinon.stub(field.model, 'isNew', function() { return true; });

            originalType    = field.view.name;
            field.view.name = 'edit';

            // Since we've forced model.isNew to return true, omitted 'value', and to edit view:
            expect(field._isNewEditViewWithNoValue(null)).toBeTruthy();

            // Tests - anytime 'value' supplied returns false
            expect(field._isNewEditViewWithNoValue("nope")).toBeFalsy();

            // Type not edit view returns false
            field.view.name = 'not_edit';
            expect(field._isNewEditViewWithNoValue(null)).toBeFalsy();

            // Tests only on "new" model
            field.view.name = 'edit';
            isNewStub.restore(); // Prevents 'Attempted to wrap already wrapped'
            isNewStub = sinon.stub(field.model, 'isNew', function() { return false; });
            expect(field._isNewEditViewWithNoValue(null)).toBeFalsy();

            // Reset back to previous "state"
            field.view.name = originalType;
            isNewStub.restore();
        });
        it('should patch dom_cal_* for datepicker languageDictionary format', function() {
            var actual, expected, stub_appListStrings;

            stub_appListStrings = sinon.stub(app.metadata, 'getStrings', function() {
                return {
                    dom_cal_day_long: {0: "", 1: "Sunday", 2: "Monday", 3: "Tuesday", 4: "Wednesday", 5: "Thursday", 6: "Friday", 7: "Saturday"},
                    dom_cal_day_short: {0: "", 1: "Sun", 2: "Mon", 3: "Tue", 4: "Wed", 5: "Thu", 6: "Fri", 7: "Sat"},
                    dom_cal_month_long: {0: "", 1: "January", 2: "February", 3: "March", 4: "April", 5: "May", 6: "June", 7: "July", 8: "August", 9: "September", 10: "October", 11: "November", 12: "December"},
                    dom_cal_month_short: {0: "", 1: "Jan", 2: "Feb", 3: "Mar", 4: "Apr", 5: "May", 6: "Jun", 7: "Jul", 8: "Aug", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dec"}
                };
            });
            expected = {
                    day: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
                    daysMin:  [ 'Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' ],
                    months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December", "January"],
                    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "Jan"]
                };

            actual = field._patchDatepickerMeta();

            expect(actual.day).toEqual(expected.day);
            expect(actual.daysMin).toEqual(expected.daysMin);
            expect(actual.months).toEqual(expected.months);
            expect(actual.monthsShort).toEqual(expected.monthsShort);
            stub_appListStrings.restore();
        });
        it("should pad one character int strings to two character equivalents", function() {
            expect(field._forceTwoDigits('9')).toEqual('09');
            expect(field._forceTwoDigits('0')).toEqual('00');
            expect(field._forceTwoDigits('-1')).toEqual('-1');
            expect(field._forceTwoDigits('10')).toEqual('10');
            expect(field._forceTwoDigits('99')).toEqual('99');
            expect(field._forceTwoDigits('100')).toEqual('100');
        });

    });

    describe("basedatepicker datepicker related", function() {
        var jqFn, expectedValValue, datepickerStub;

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
            var datetimeComboField;

            field.type = 'date';
            field._presetDateValues();
            expect(field.dateValue).toEqual(expectedValValue);
            expect(field.timeValue).toBeFalsy();
        });

        it('should update model when datepicker selected for datetimecombo', function() {
            var stub, stubDatepickerInputValue, stubVerifyDateString, conditionallyCalledStub, expected;

            field.type ='datetimecombo';
            stub = sinon.stub(field, '_buildUnformatted');
            stubDatepickerInputValue = sinon.stub(field, '_getDatepickerValue', function() { return '11-11-1999'; });
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });
            field._getHoursMinutes = function() {}; // since defined in the child classes ;)
            field._setTimepickerValue = function() {};

            expected = {
                hours: '23',
                minutes: '59'
            };
            conditionallyCalledStub = sinon.stub(field, '_getHoursMinutes', function() {
                return expected;
            });

            // Test datetimecombo path - we update just before hiding datepicker
            field.hideDatepicker({});
            expect(stub.args[0][1]).toEqual(expected.hours);
            expect(stub.args[0][2]).toEqual(expected.minutes);
        });

        it('should update model when datepicker selected for type date', function() {
            var stub, stubDatepickerInputValue, stubVerifyDateString, conditionallyCalledStub, expected;

            field.type ='date';
            stub = sinon.stub(field, '_buildUnformatted');
            stubDatepickerInputValue = sinon.stub(field, '_getDatepickerValue', function() { return '11-11-1999'; });
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return true; });

            // Test date path - we update just before hiding datepicker
            field.hideDatepicker({});
            expected = {
                hours: '00',
                minutes: '00'
            };
            expect(stub.args[0][1]).toEqual(expected.hours);
            expect(stub.args[0][2]).toEqual(expected.minutes);
        });

        it('should update model when datepicker dismissed but leaves bogus date value so error handling kicks in', function() {
            var stub, stubBadDatepickerInputValue, stubVerifyDateString, conditionallyCalledStub, expected;

            field.type ='datetimecombo';
            stub = sinon.stub(field, '_buildUnformatted');
            // purposely bad date value..we now leave these in so sidecar error handling uniformly can handle upstream
            stubBadDatepickerInputValue = sinon.stub(field, '_getDatepickerValue', function() { return '13-32-123456789'; });
            stubVerifyDateString = sinon.stub(field, '_verifyDateString', function() { return false; });

            field._getHoursMinutes = function() {}; // since defined in the child classes ;)
            field._setTimepickerValue = function() {};
            expected = {
                hours: '23',
                minutes: '59'
            };
            conditionallyCalledStub = sinon.stub(field, '_getHoursMinutes', function() {
                return expected;
            });
            field.hideDatepicker({});
            expect(stub.called).toBeFalsy();
        });


    });
});
