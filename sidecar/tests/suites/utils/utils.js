describe("utils", function() {

    var utils = SUGAR.App.utils;

    describe("strings", function() {
        it("should capititalize a string", function() {
            var result = utils.capitalize('abc');
            expect(result).toEqual("Abc");
            result = utils.capitalize('a');
            expect(result).toEqual("A");
            result = utils.capitalize('aBC');
            expect(result).not.toEqual("Abc");
            expect(result).toEqual("ABC");//preserves subsequent chars
        });
        it("should return empty string from capititalize for falsy input", function() {
            var result = utils.capitalize(undefined);
            expect(result).toEqual("");
            result = utils.capitalize(null);
            expect(result).toEqual("");
            result = utils.capitalize();
            expect(result).toEqual("");
        });

        it("should capititalize hyphenated strings", function() {
            var result = utils.capitalizeHyphenated('abc-def');
            expect(result).toEqual("AbcDef");
            result = utils.capitalizeHyphenated('a');
            expect(result).toEqual("A");
            result = utils.capitalizeHyphenated('aBC-dEF');
            expect(result).not.toEqual("AbcDef");
            expect(result).toEqual("ABCDEF");//preserves subsequent chars
        });

        it("should return empty string from capitalizeHyphenated for falsy input", function() {
            var result = utils.capitalizeHyphenated(undefined);
            expect(result).toEqual("");
            result = utils.capitalizeHyphenated(null);
            expect(result).toEqual("");
            result = utils.capitalizeHyphenated();
            expect(result).toEqual("");
        });
    });

    describe("number formatter", function() {
        it("should round up numbers", function() {
            var value = 2.3899,
                round = 2,
                precision = 2,
                number_group_seperator = ",",
                decimal_seperator = ".",
                result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator);

            expect(result).toEqual("2.39");
        });

        it("should round down numbers", function() {
            var value = 2.3822,
                round = 2,
                precision = 2,
                number_group_seperator = ",",
                decimal_seperator = ".",
                result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator);
            expect(result).toEqual("2.38");
        });

        it("should set precision on numbers", function() {
            var value = 2.3828,
                round = 4,
                precision = 2,
                number_group_seperator = ",",
                decimal_seperator = ".",
                result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator);
            expect(result).toEqual("2.38");
        });

        it("should add the correct number group seperator", function() {
            var value = 2123.3828,
                round = 4,
                precision = 2,
                number_group_seperator = " ",
                decimal_seperator = ".",
                result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator);
            expect(result).toEqual("2 123.38");
        });

        it("should add the correct decimal seperator", function() {
            var value = 2123.3828,
                round = 4, 
                precision = 2, 
                number_group_seperator = "", 
                decimal_seperator = ",",
                result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator);
            expect(result).toEqual("2123,38");
        });

        it("should unformat number strings to unformatted number strings", function() {
            var value = '2,123 3828',
                number_group_seperator = ",",
                decimal_seperator = " ",
                toFloat = false,
                result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual("2123.3828");
        });

        it("should unformat number strings to floats", function() {
            var value = '2,123 3828',
                number_group_seperator = ",",
                decimal_seperator = " ",
                toFloat = true,
                result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual(2123.3828);
        });

        it("should return an empty value", function() {
            var value = '',
                number_group_seperator = ",",
                decimal_seperator = " ",
                toFloat = true,
                result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual('');
        });
    });

    describe('date', function() {
        it("should guess date string formats with seconds", function() {
            var value = '2012-03-27 01:48:00AM',
                result = utils.date.guessFormat(value);
            expect(result).toEqual('Y-m-d h:i:sA');
        });

        it("should guess date string formats without seconds", function() {
            var value = '2012-03-27 01:48 AM',
                result = utils.date.guessFormat(value);
            expect(result).toEqual('Y-m-d h:i A');
        });

        it("should guess date string formats without ampm", function() {
            var value = '2012-03-27 01:48:58',
                result = utils.date.guessFormat(value);
            expect(result).toEqual('Y-m-d H:i:s');
        });

        it("should parse date strings into javascript date objects", function() {
            var result = utils.date.parse('2012-03-27 01:48:32');
            expect(result.getDate()).toEqual(27);
            expect(result.getFullYear()).toEqual(2012);
            expect(result.getMonth()).toEqual(2);
            expect(result.getHours()).toEqual(1);
            expect(result.getMinutes()).toEqual(48);
            expect(result.getSeconds()).toEqual(32);
        });

        it("should format date objects into strings", function() {
            var value = new Date(Date.parse("Tue, 15 May 2012 01:48:00")),
                format = 'Y-m-d H:i:sA',
                result = utils.date.format(value, format);
            expect(result).toEqual('2012-05-15 01:48:00AM');
            format = 'Y-m-d H:i:sa';
            result = utils.date.format(value, format);
            expect(result).toEqual('2012-05-15 01:48:00am');
        });

        it("should format date objects into strings", function() {
            var value = '2012-03-27 01:48:32',
                format = 'Y-m-d h:i a',
                result = utils.date.parse(value, format);

            expect(result.getDate()).toEqual(27);
            expect(result.getFullYear()).toEqual(2012);
            expect(result.getMonth()).toEqual(2);
            expect(result.getHours()).toEqual(1);
            expect(result.getMinutes()).toEqual(48);
            expect(result.getSeconds()).toEqual(0);// no 's' specified
        });

        it("should format date objects into strings with seconds included", function() {
            var value = '2012-03-27 01:48:32',
                format = 'Y-m-d h:i:s a',
                result = utils.date.parse(value, format);

            expect(result.getDate()).toEqual(27);
            expect(result.getFullYear()).toEqual(2012);
            expect(result.getMonth()).toEqual(2);
            expect(result.getHours()).toEqual(1);
            expect(result.getMinutes()).toEqual(48);
            expect(result.getSeconds()).toEqual(32);// 's' specified
        });


        it("should format date objects given timestamp and no format", function() {
            var result = utils.date.parse(1332838080000);
            expect(result.getTime()).toEqual(1332838080000);
        });

        it("should return false if bogus inputs", function() {
            var result = utils.date.parse('XyXyZyW');
            expect(result).toEqual(false);
        });

        it("should round time to nearest fifteen minutes", function() {
            var ts     = Date.parse("April 1, 2012 10:01:50"),
                date   = new Date(ts),
                result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(15);

            ts     = Date.parse("April 1, 2012 10:16:50");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(30);

            ts     = Date.parse("April 1, 2012 10:29:50");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(30);

            ts     = Date.parse("April 1, 2012 10:30:50");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(30);

            ts     = Date.parse("April 1, 2012 10:31:50");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(45);

            ts     = Date.parse("April 1, 2012 10:44:50");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getHours()).toEqual(10);
            expect(result.getMinutes()).toEqual(45);

            ts     = Date.parse("April 1, 2012 10:46:00");
            date   = new Date(ts);
            result = utils.date.roundTime(date);
            expect(result.getMinutes()).toEqual(0);
            expect(result.getHours()).toEqual(11);
        });

        it("should convert a UTC date into a local date", function() {
            var date     = new Date("April 1, 2012 10:31:50"),
                offset   = date.getTimezoneOffset(),
                UTC     = new Date("April 1, 2012 10:31:50 UTC");

            if (offset !== 0) {
                expect(date.toString()).not.toEqual(UTC.toString());
                expect(utils.date.UTCtoLocalTime(UTC).toString()).not.toEqual(date.toString());
            }
        });

        it("should convert into relative time", function() {
            var ts                      = new Date().getTime(),
                LBL_TIME_AGO_NOW        = new Date(ts - 1*1000),
                LBL_TIME_AGO_SECONDS    = new Date(ts - 10*1000),
                LBL_TIME_AGO_MINUTE     = new Date(ts - 70*1000),
                LBL_TIME_AGO_MINUTES    = new Date(ts - 130*1000),
                LBL_TIME_AGO_HOUR       = new Date(ts - 3610*1000),
                LBL_TIME_AGO_HOURS      = new Date(ts - 7230*1000),
                LBL_TIME_AGO_DAY        = new Date(ts - 90000*1000),
                LBL_TIME_AGO_DAYS       = new Date(ts - 200000*1000),
                LBL_TIME_AGO_YEAR       = new Date(ts - 400*84600*1000);

            console.log(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_SECONDS).str);
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_NOW).str).toEqual("LBL_TIME_AGO_NOW");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_SECONDS).str).toEqual("LBL_TIME_AGO_SECONDS");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_MINUTE).str).toEqual("LBL_TIME_AGO_MINUTE");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_MINUTES).str).toEqual("LBL_TIME_AGO_MINUTES");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_HOUR).str).toEqual("LBL_TIME_AGO_HOUR");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_HOURS).str).toEqual("LBL_TIME_AGO_HOURS");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_DAY).str).toEqual("LBL_TIME_AGO_DAY");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_DAYS).str).toEqual("LBL_TIME_AGO_DAYS");
            expect(utils.date.getRelativeTimeLabel(LBL_TIME_AGO_YEAR).str).toEqual("LBL_TIME_AGO_YEAR");
        });
   });
   
    describe("cookie", function() {
        it("should set cookie values", function() {
            var result = "", cName, value, i, x, y,
                ARRcookies = document.cookie.split(";");
            cName = "sidecarCookie";
            value = 'asdf';
            SUGAR.App.utils.cookie.setCookie(cName, value, 1);

            ARRcookies = document.cookie.split(";");
            for (i = 0; i < ARRcookies.length; i++) {
                x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
                y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
                x = x.replace(/^\s+|\s+$/g, "");
                if (x === cName) {
                    result = unescape(y);
                }
            }
            expect(result).toEqual(value);
            SUGAR.App.utils.cookie.setCookie(cName, "", 1);
        });
        it("should get cookie values", function() {
            var result = "",
                cName = "sidecarCookie",
                value = 'asdfasdf',
                exdays = 1,
                exdate = new Date(), c_value;
            exdate.setDate(exdate.getDate() + exdays);
            c_value = escape(value) + ((exdays === null) ? "" : "; expires=" + exdate.toUTCString());
            document.cookie = cName + "=" + c_value;
            result = SUGAR.App.utils.cookie.getCookie(cName);
            expect(result).toEqual(value);
            value = "";
            c_value = escape(value) + ((exdays === null) ? "" : "; expires=" + exdate.toUTCString());
        });
    });
});

