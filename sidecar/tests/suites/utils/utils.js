describe("utils", function() {

    var utils = SUGAR.App.utils;
    describe("number formatter", function() {
        it("should round up numbers", function() {
            var value = 2.3899;
            var round = 2;
            var precision = 2;
            var number_group_seperator = ",";
            var decimal_seperator = ".";
            var result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator)
            expect(result).toEqual("2.39");
        });

        it("should round down numbers", function() {
            var value = 2.3822;
            var round = 2;
            var precision = 2;
            var number_group_seperator = ",";
            var decimal_seperator = ".";
            var result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator)
            expect(result).toEqual("2.38");
        });

        it("should set precision on numbers", function() {
            var value = 2.3828;
            var round = 4;
            var precision = 2;
            var number_group_seperator = ",";
            var decimal_seperator = ".";
            var result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator)
            expect(result).toEqual("2.38");
        });

        it("should add the correct number group seperator", function() {
            var value = 2123.3828;
            var round = 4;
            var precision = 2;
            var number_group_seperator = " ";
            var decimal_seperator = ".";
            var result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator)
            expect(result).toEqual("2 123.38");
        });

        it("should add the correct decimal seperator", function() {
            var value = 2123.3828;
            var round = 4;
            var precision = 2;
            var number_group_seperator = "";
            var decimal_seperator = ",";
            var result = utils.formatNumber(value, round, precision, number_group_seperator, decimal_seperator)
            expect(result).toEqual("2123,38");
        });

        it("should unformat number strings to unformatted number strings", function() {
            var value = '2,123 3828';
            var number_group_seperator = ",";
            var decimal_seperator = " ";
            var toFloat = false;
            var result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual("2123.3828");
        });

        it("should unformat number strings to floats", function() {
            var value = '2,123 3828';
            var number_group_seperator = ",";
            var decimal_seperator = " ";
            var toFloat = true;
            var result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual(2123.3828);
        });
    });
    describe('date', function() {
        it("should guess date string formats", function() {
            var value = '2012-03-27 00:48:32';
            var result = utils.date.guessFormat(value);
            expect(result).toEqual('Y-m-d H:i');
        });
        it("should parse date strings into javascript date objects", function() {
            var value = '2,123 3828';
            var number_group_seperator = ",";
            var decimal_seperator = " ";
            var toFloat = true;
            var result = utils.date.parse('2012-03-27 00:48:32');
            expect(result.toString()).toEqual('Tue Mar 27 2012 00:48:00 GMT-0700 (PDT)');
        });
        it("should format date objects into strings", function() {
            var value = '2,123 3828';
            var number_group_seperator = ",";
            var decimal_seperator = " ";
            var toFloat = true;
            var result = utils.unformatNumberString(value, number_group_seperator, decimal_seperator, toFloat);
            expect(result).toEqual(2123.3828);
        });
    });
});