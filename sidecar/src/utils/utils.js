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
                },

                date: {
                    parse: function(date, oldFormat) {
                        //if already a Date return it
                        if (date instanceof Date) return date;

                        // TODO add user prefs support

                        if (oldFormat == null || oldFormat == "") {
                            oldFormat = this.guessFormat(date);
                        }

                        if (oldFormat == false) {
                            //Check if date is a timestamp
                            if (/^\d+$/.test(date)) {
                                return new Date(date);
                            }

                            //we cant figure out the format so return false
                            return false;
                        }

                        var jsDate = new Date("Jan 1, 1970 00:00:00");
                        var part = "";
                        var dateRemain = $.trim(date);
                        oldFormat = $.trim(oldFormat) + " "; // Trailing space to read as last separator.
                        for (var j = 0; j < oldFormat.length; j++) {
                            var c = oldFormat.charAt(j);
                            if (c == ':' || c == '/' || c == '-' || c == '.' || c == " " || c == 'a' || c == "A") {
                                var i = dateRemain.indexOf(c);
                                if (i == -1) i = dateRemain.length;
                                var v = dateRemain.substring(0, i);
                                dateRemain = dateRemain.substring(i + 1);
                                switch (part) {
                                    case 'm':
                                        if (!(v > 0 && v < 13)) return false;
                                        jsDate.setMonth(v - 1);
                                        break;
                                    case 'd':
                                        if (!(v > 0 && v < 32)) return false;
                                        jsDate.setDate(v);
                                        break;
                                    case 'Y':
                                        if (!(v > 0)) return false;
                                        jsDate.setYear(v);
                                        break;
                                    case 'h':
                                        //Read time, assume minutes are at end of date string (we do not accept seconds)
                                        var timeformat = oldFormat.substring(oldFormat.length - 4);
                                        if (timeformat.toLowerCase() == "i a " || timeformat.toLowerCase() == c + "ia ") {
                                            if (dateRemain.substring(dateRemain.length - 2).toLowerCase() == 'pm') {
                                                v = v * 1;
                                                if (v < 12) {
                                                    v += 12;
                                                }
                                            }
                                        }
                                    case 'H':
                                        jsDate.setHours(v);
                                        break;
                                    case 'i':
                                        v = v.substring(0, 2);
                                        jsDate.setMinutes(v);
                                        break;
                                }
                                part = "";
                            } else {
                                part = c;
                            }
                        }
                        return jsDate;
                    },

                    guessFormat: function(date) {
                        if (typeof date != "string")
                            return false;
                        //Is there a time
                        var time = "";
                        if (date.indexOf(" ") != -1) {
                            time = date.substring(date.indexOf(" ") + 1, date.length);
                            date = date.substring(0, date.indexOf(" "));
                        }

                        //First detect if the date contains "-" or "/"
                        var dateSep = "/";
                        if (date.indexOf("/") != -1) {
                        }
                        else if (date.indexOf("-") != -1) {
                            dateSep = "-";
                        }
                        else if (date.indexOf(".") != -1) {
                            dateSep = ".";
                        }
                        else {
                            return false;
                        }

                        var dateParts = date.split(dateSep);
                        var dateFormat = "";

                        if (dateParts[0].length == 4) {
                            dateFormat = "Y" + dateSep + "m" + dateSep + "d";
                        }
                        else if (dateParts[2].length == 4) {
                            dateFormat = "m" + dateSep + "d" + dateSep + "Y";
                        }
                        else {
                            return false;
                        }

                        var timeFormat = "";


                        var timeParts = [];

                        // Check for time
                        if (time != "") {

                            // start at the i, we always have minutes
                            timeParts.push("i");

                            var timeSep = ":";

                            if (time.indexOf(".") == 2) {
                                timeSep = ".";
                            }

                            // if its long we have seconds
                            if (time.split(timeSep).length == 3) {
                                timeParts.push("s");
                            }
                            var ampmCase = '';

                            // check for am/pm
                            var timeEnd = time.substring(time.length - 2, time.length);
                            if (timeEnd.toLowerCase() == "am" || timeEnd.toLowerCase() == "pm") {
                                timeParts.unshift("h");
                                if (timeEnd.toLowerCase() == timeEnd) {
                                    ampmCase = 'lower';
                                } else {
                                    ampmCase = 'upper';
                                }
                            } else {
                                timeParts.unshift("H");
                            }

                            timeFormat = timeParts.join(timeSep);

                            // check for space between am/pm and time
                            if (time.indexOf(" ") != -1) {
                                timeFormat += " ";
                            }

                            // deal with upper and lowercase am pm
                            if (ampmCase && ampmCase == 'upper') {
                                timeFormat += "A";
                            } else if (ampmCase && ampmCase == 'lower') {
                                timeFormat += "a";
                            }

                            dateFormat = dateFormat + " " + timeFormat;
                        }

                        return dateFormat;
                    },

                    format: function(date, format) {
                        if (!date) return "";
                        // TODO: add support for userPrefs
                        var out = "";
                        for (var i = 0; i < format.length; i++) {
                            var c = format.charAt(i);
                            switch (c) {
                                case 'm':
                                    var m = date.getMonth() + 1;
                                    out += m < 10 ? "0" + m : m;
                                    break;
                                case 'd':
                                    var d = date.getDate();
                                    out += d < 10 ? "0" + d : d;
                                    break;
                                case 'Y':
                                    out += date.getFullYear();
                                    break;
                                case 'H':
                                case 'h':
                                    var h = date.getHours();
                                    if (c == "h") h = h > 12 ? h - 12 : h;
                                    out += h < 10 ? "0" + h : h;
                                    break;
                                case 'i':
                                    var m = date.getMinutes();
                                    out += m < 10 ? "0" + m : m;
                                    break;
                                case 's':
                                    var s = date.getSeconds();
                                    out += s < 10 ? "0" + s : s;
                                    break;
                                case 'a':
                                    if (date.getHours() < 12)
                                        out += "am";
                                    else
                                        out += "pm";
                                    break;
                                case 'A':
                                    if (date.getHours() < 12)
                                        out += "AM";
                                    else
                                        out += "PM";
                                    break;
                                default :
                                    out += c;
                            }
                        }
                        return out;
                    },
                    roundTime: function(date) {
                        if (!date.getMinutes) return 0;
                        var min = date.getMinutes();

                        if (min < 1) {
                            min = 0;
                        }
                        else if (min < 16) {
                            min = 15;
                        }
                        else if (min < 31) {
                            min = 30;
                        }
                        else if (min < 46) {
                            min = 45;
                        }
                        else {
                            min = 0;
                            date.setHours(date.getHours() + 1)
                        }

                        return date;
                    }
                }
            }
        }

        return instance || init();
    }()))
}(SUGAR.App));