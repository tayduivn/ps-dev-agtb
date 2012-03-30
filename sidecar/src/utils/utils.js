/**
 * Utils
 * @ignore
 */
(function(app) {
    /**
     * @class utils
     * @singleton
     * utils provides several utility methods used throughout the app such as number formatting
     */

    app.augment('utils', (function() {

        var instance;

        /**
         * init
         * @private
         * @param args
         */
        function init() {

            instance = new Utils();
            return instance
        }

        function Utils() {

            return {
                /**
                 * Formats Numbers
                 *
                 * @param {Number} value number to be formatted eg 2.134
                 * @param {Integer} round number of digits to right of decimal to round at
                 * @param {Integer} precision number of digits to right of decimal to take precision at
                 * @param {String} number_group_seperator character seperator for number groups of 3 digits to the left of the decimal to add
                 * @param {String} decimal_seperator character to replace decimal in arg number with
                 * @return {String} formatted number string
                 */
                formatNumber: function(value, round, precision, number_group_seperator, decimal_seperator) {
                    // TODO: ADD LOCALIZATION SUPPORT FOR CURRENT USER

                    if (value=='') return null;

                    if (_.isString(value)) {
                        value = parseFloat(value, 10);
                    }

                    value = parseFloat(value.toFixed(round), 10).toFixed(precision).toString();
                    return (_.isString(number_group_seperator) && _.isString(decimal_seperator)) ? this.addNumberSeperators(value, number_group_seperator, decimal_seperator) : value;
                },

                /**
                 * Adds number seperators to a number string
                 * @param {String} number_string string of number to be modified of the format nn.nnn
                 * @param {String} number_group_seperator character seperator for number groups of 3 digits to the left of the decimal to add
                 * @param {String} decimal_seperator character to replace decimal in arg number with
                 * @return {String}
                 */
                addNumberSeperators: function(number_string, number_group_seperator, decimal_seperator) {
                    var number_array = number_string.split(".");
                    var regex = /(\d+)(\d{3})/;
                    while (number_group_seperator != '' && regex.test(number_array[0])) {
                        number_array[0] = number_array[0].toString().replace(regex, '$1' + number_group_seperator + '$2');
                    }
                    return number_array[0] + (number_array.length > 1 && number_array[1] != '' ? decimal_seperator + number_array[1] : '');
                },

                /**
                 * Unformats number strings
                 * @param number_string
                 * @param number_group_seperator
                 * @param decimal_seperator
                 * @param toFloat
                 */
                unformatNumberString: function(n, number_group_seperator, decimal_seperator, toFloat) {
                    toFloat = toFloat || false;
                    if (typeof number_group_seperator == 'undefined' || typeof decimal_seperator == 'undefined') return number_string;
                        // parse out number group seperators
                        if (number_group_seperator != '') {
                            var num_grp_sep_re = new RegExp('\\' + number_group_seperator, 'g');
                            n = n.replace(num_grp_sep_re, '');
                        }

                        // parse out decimal seperators
                        n = n.replace(decimal_seperator, '.');

                        // convert to float
                        if (n.length > 0 && toFloat) {
                            n = parseFloat(n);
                        }

                        // return
                        return n;
                }
            }
        }

        return instance || init();
    }()))
}(SUGAR.App));