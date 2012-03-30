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
    app.augment('utils', {
        /**
         * Formats Numbers
         *
         * @param {Number} value number to be formatted eg 2.134
         * @param {Number} round number of digits to right of decimal to round at
         * @param {Number} precision number of digits to right of decimal to take precision at
         * @param {String} numberGroupSeperator character seperator for number groups of 3 digits to the left of the decimal to add
         * @param {String} decimalSeperator character to replace decimal in arg number with
         * @return {String} formatted number string
         */
        formatNumber: function(value, round, precision, numberGroupSeperator, decimalSeperator) {
            // TODO: ADD LOCALIZATION SUPPORT FOR CURRENT USER

            if (_.isString(value)) {
                value = parseFloat(value, 10);
            }

            value = parseFloat(value.toFixed(round), 10).toFixed(precision).toString();
            return (_.isString(numberGroupSeperator) && _.isString(decimalSeperator)) ? this.addNumberSeperators(value, numberGroupSeperator, decimalSeperator) : value;
        },

        /**
         * Adds number seperators to a number string
         * @param {String} numberString string of number to be modified of the format nn.nnn
         * @param {String} numberGroupSeperator character seperator for number groups of 3 digits to the left of the decimal to add
         * @param {String} decimalSeperator character to replace decimal in arg number with
         * @return {String}
         */
        addNumberSeperators: function(numberString, numberGroupSeperator, decimalSeperator) {
            var numberArray = numberString.split("."),
                regex = /(\d+)(\d{3})/;

            while (numberGroupSeperator != '' && regex.test(numberArray[0])) {
                numberArray[0] = numberArray[0].toString().replace(regex, '$1' + numberGroupSeperator + '$2');
            }

            return numberArray[0] + (numberArray.length > 1 && numberArray[1] != '' ? decimalSeperator + numberArray[1] : '');
        },

        /**
         * Unformats number strings
         * @param {String} numberString
         * @param {String} numberGroupSeperator
         * @param {String} decimalSeperator
         * @param {Boolean} toFloat
         * @return {String} formatted number string
         */
        unformatNumberString: function(numberString, numberGroupSeperator, decimalSeperator, toFloat) {
            toFloat = toFloat || false;

            if (typeof numberGroupSeperator == 'undefined' || typeof decimalSeperator == 'undefined') {
                return numberString;
            }

            // parse out number group seperators
            if (numberGroupSeperator != '') {
                var num_grp_sep_re = new RegExp('\\' + numberGroupSeperator, 'g');
                numberString = numberString.replace(num_grp_sep_re, '');
            }

            // parse out decimal seperators
            numberString = numberString.replace(decimalSeperator, '.');

            // convert to float
            if (numberString.length > 0 && toFloat) {
                numberString = parseFloat(numberString);
            }

            return numberString;
        }
    });
}(SUGAR.App));