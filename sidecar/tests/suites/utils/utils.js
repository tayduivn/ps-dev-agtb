describe("utils", function() {

    var utils = SUGAR.App.utils;
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
            expect(result.toString()).toEqual('Tue Mar 27 2012 01:48:00 GMT-0700 (PDT)');

        });

        it("should format date objects into strings", function() {
            var value = new Date(1332838080000),
                format = 'Y-m-d H:i:sA',
                result = utils.date.format(value, format);
            expect(result).toEqual('2012-03-27 01:48:00AM');
            
            format = 'Y-m-d H:i:sa';
            result = utils.date.format(value, format);
            expect(result).toEqual('2012-03-27 01:48:00am');
        });

        it("should format date objects into strings", function() {
            var value = '2012-03-27 01:48:32',
                format = 'Y-m-d h:i a',
                result = utils.date.parse(value, format);
            expect(result.toString()).toEqual('Tue Mar 27 2012 01:48:00 GMT-0700 (PDT)');
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
