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
    });
});