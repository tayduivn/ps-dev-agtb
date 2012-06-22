(function(app) {

    /**
     * Utils module provides several utility methods used throughout the app such as number formatting.
     *
     * @class Utils.Utils
     * @singleton
     * @alias SUGAR.App.utils
     */
    app.augment('utils', {

        /**
         * Capitalizes a string.
         * @param {String} s The string to capitalize.
         * @return {String} Capitalized string or an empty string if `s` is undefined or null.
         */
        capitalize: function(s) {
            return s ? (s.charAt(0).toUpperCase() + (s.length > 1 ? s.substr(1) : "")) : "";
        },


        /**
         * Capitalizes a hyphenated string. So my-string becomes MyString.
         * @param {String} s The string to capitalize.
         * @return {String} Capitalized string or an empty string if `s` is undefined or null.
         */
        capitalizeHyphenated: function(s) {
            var self = this, words, capitalized = '';

            if(!s || s.lastIndexOf('-') === -1) {
                capitalized = self.capitalize(s);
            } else {
                words = s.split('-');
                _.each(words, function(word) {
                    capitalized += self.capitalize(word);
                });
            }
            return capitalized;
        },

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

            while (numberGroupSeperator !== '' && regex.test(numberArray[0])) {
                numberArray[0] = numberArray[0].toString().replace(regex, '$1' + numberGroupSeperator + '$2');
            }

            return numberArray[0] + (numberArray.length > 1 && numberArray[1] !== '' ? decimalSeperator + numberArray[1] : '');
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
            if (typeof numberGroupSeperator === 'undefined' || typeof decimalSeperator === 'undefined') {
                return numberString;
            }

            // parse out number group seperators
            if (numberGroupSeperator !== '') {
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
        },


        date: {
            /**
             * Parses date strings into js Date()s
             * @param {String} date date string
             * @param {String} oldFormat date format string. If not specified parse will guess the date format
             * @return {Object} javascript date object
             */
            parse: function(date, oldFormat) {
                var jsDate     = new Date("Jan 1, 1970 00:00:00"),
                    part       = "",
                    dateRemain, j, c, i, v, timeformat;

                //if already a Date return it
                if (date instanceof Date) return date;

                // TODO add user prefs support

                if (!oldFormat) {
                    oldFormat = this.guessFormat(date);
                }

                if (oldFormat === false) {
                    //Check if date is a timestamp
                    if (/^\d+$/.test(date)) {
                        return new Date(date);
                    }

                    //we cant figure out the format so return false
                    return false;
                }

                dateRemain = $.trim(date);
                oldFormat = $.trim(oldFormat) + " "; // Trailing space to read as last separator.
                for (j = 0; j < oldFormat.length; j++) {
                    c = oldFormat.charAt(j);
                    if (c === ':' || c === '/' || c === '-' || c === '.' || c === " " || c === 'a' || c === "A") {
                        i = dateRemain.indexOf(c);
                        if (i === -1) i = dateRemain.length;
                        v = dateRemain.substring(0, i);
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
                                if (v > 0 === false) return false;
                                jsDate.setYear(v);
                                break;
                            case 'h':
                                //Read time, assume minutes are at end of date string (we do not accept seconds)
                                timeformat = oldFormat.substring(oldFormat.length - 4);
                                if (timeformat.toLowerCase() === "i a " || timeformat.toLowerCase() === c + "ia ") {
                                    if (dateRemain.substring(dateRemain.length - 2).toLowerCase() === 'pm') {
                                        v = v * 1;
                                        if (v < 12) {
                                            v += 12;
                                        }
                                    }
                                }
                                jsDate.setHours(v);
                                break;
                            case 'H':
                                jsDate.setHours(v);
                                break;
                            case 'i':
                                v = v.substring(0, 2);
                                jsDate.setMinutes(v);
                                break;
                            case 's':
                                jsDate.setSeconds(v);
                                break;
                        }
                        part = "";
                    } else {
                        part = c;
                    }
                }
                return jsDate;
            },

            /**
             * Guesses format of date strings
             * @param {String} date string
             * @return {string}
             */
            guessFormat: function(date) {
                if (typeof date !== "string")
                    return false;
                //Is there a time
                var time = "", dateSep, dateParts, dateFormat, timeFormat, timeParts,
                    timeSep, ampmCase, timeEnd;

                if (date.indexOf(" ") !== -1) {
                    time = date.substring(date.indexOf(" ") + 1, date.length);
                    date = date.substring(0, date.indexOf(" "));
                }

                //First detect if the date contains "-" or "/"
                dateSep = "/";
                if (date.indexOf("/") !== -1) {
                }
                else if (date.indexOf("-") !== -1) {
                    dateSep = "-";
                }
                else if (date.indexOf(".") !== -1) {
                    dateSep = ".";
                }
                else {
                    return false;
                }

                dateParts = date.split(dateSep);
                dateFormat = "";

                if (dateParts[0].length === 4) {
                    dateFormat = "Y" + dateSep + "m" + dateSep + "d";
                }
                else if (dateParts[2].length === 4) {
                    dateFormat = "m" + dateSep + "d" + dateSep + "Y";
                }
                else {
                    return false;
                }

                timeFormat = "";
                timeParts = [];

                // Check for time
                if (time !== "") {

                    // start at the i, we always have minutes
                    timeParts.push("i");

                    timeSep = ":";

                    if (time.indexOf(".") === 2) {
                        timeSep = ".";
                    }

                    // if its long we have seconds
                    if (time.split(timeSep).length === 3) {
                        timeParts.push("s");
                    }
                    ampmCase = '';

                    // check for am/pm
                    timeEnd = time.substring(time.length - 2, time.length);
                    if (timeEnd.toLowerCase() === "am" || timeEnd.toLowerCase() === "pm") {
                        timeParts.unshift("h");
                        if (timeEnd.toLowerCase() === timeEnd) {
                            ampmCase = 'lower';
                        } else {
                            ampmCase = 'upper';
                        }
                    } else {
                        timeParts.unshift("H");
                    }

                    timeFormat = timeParts.join(timeSep);

                    // check for space between am/pm and time
                    if (time.indexOf(" ") !== -1) {
                        timeFormat += " ";
                    }

                    // deal with upper and lowercase am pm
                    if (ampmCase && ampmCase === 'upper') {
                        timeFormat += "A";
                    } else if (ampmCase && ampmCase === 'lower') {
                        timeFormat += "a";
                    }

                    dateFormat = dateFormat + " " + timeFormat;
                }

                return dateFormat;
            },

            /**
             * Formats javascript date objects into date strings
             * @param {Object} date
             * @param {String} format date format string such as "Y-m-d H:i:s"
             * @return {String} formatted date string
             */
            format: function(date, format) {
                if (!date) return "";
                // TODO: add support for userPrefs
                var out = "", i, c, d, h, m, s;
                for (i = 0; i < format.length; i++) {
                    c = format.charAt(i);
                    switch (c) {
                        case 'm':
                            m = date.getMonth() + 1;
                            out += m < 10 ? "0" + m : m;
                            break;
                        case 'd':
                            d = date.getDate();
                            out += d < 10 ? "0" + d : d;
                            break;
                        case 'Y':
                            out += date.getFullYear();
                            break;
                        case 'H':
                        case 'h':
                            h = date.getHours();
                            if (c === "h") h = h > 12 ? h - 12 : h;
                            out += h < 10 ? "0" + h : h;
                            break;
                        case 'i':
                            m = date.getMinutes();
                            out += m < 10 ? "0" + m : m;
                            break;
                        case 's':
                            s = date.getSeconds();
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
            /**
             * rounds javascrit date to the nearest 15 minutes
             * @param {Object} date javascript Date
             * @return {Object} javascript Date()
             */
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
                    date.setHours(date.getHours() + 1);
                }

                date.setMinutes(min);

                return date;
            },

            /**
             * converts a UTC date to a local time date
             * @param {Object} date javascript Date format UTC
             * @return {Object} javascript Date() format local time
             */
            UTCtoLocalTime: function(date) {
                //if not a Date return it
                if (!(date instanceof Date)) return date;

                var year = date.getFullYear(),
                    month = date.getMonth(),
                    day = date.getDate(),
                    hours = date.getHours(),
                    minutes = date.getMinutes(),
                    seconds = date.getSeconds(),
                    milliseconds = date.getMilliseconds();

                // Push the UTC tag to convert the date into local date
                return new Date(Date.UTC(year, month, day, hours, minutes, seconds, milliseconds));
            },
            /**
             * converts a date object into a relative time
             * @param {Object} date
             * @return {Object} object containing relative time key string and value to push to the template
             */
            getRelativeTimeLabel: function(date) {

                var rightNow = new Date();

                var diff = rightNow - date;
                var second = 1000,
                    minute = second * 60,
                    hour = minute * 60,
                    day = hour * 24,
                    ctx = { str : "", value: undefined};

                if (isNaN(diff) || diff < 0) {
                    return ctx; // return blank string if unknown
                }
                if (diff < second * 2) {
                    // within 2 seconds
                    ctx.str = 'LBL_TIME_AGO_NOW';
                    return ctx;
                }
                if (diff < minute) {
                    ctx.str = 'LBL_TIME_AGO_SECONDS';
                    ctx.value = Math.floor(diff / second);
                    return ctx;
                }
                if (diff < minute * 2) {
                    ctx.str = 'LBL_TIME_AGO_MINUTE';
                    return ctx;
                }
                if (diff < hour) {
                    ctx.str = 'LBL_TIME_AGO_MINUTES';
                    ctx.value = Math.floor(diff / minute);
                    return ctx;
                }
                if (diff < hour * 2) {
                    ctx.str = 'LBL_TIME_AGO_HOUR';
                    return ctx;
                }
                if (diff < day) {
                    ctx.str = 'LBL_TIME_AGO_HOURS';
                    ctx.value = Math.floor(diff / hour);
                    return ctx;
                }
                if (diff > day && diff < day * 2) {
                    ctx.str = 'LBL_TIME_AGO_DAY';
                    return ctx;
                }
                if (diff < day * 365) {
                    ctx.str = 'LBL_TIME_AGO_DAYS';
                    ctx.value = Math.floor(diff / day);
                    return ctx;
                }
                else {
                    ctx.str = 'LBL_TIME_AGO_YEAR';
                    return ctx;
                }
            }
        },
        cookie: {
            /**
             * Sets a cookie
             * @param {String} cName cookie name
             * @param {String} value
             * @param {Number} exdays days until expiration
             */
            setCookie: function setCookie(cName, value, exdays) {
                var exdate = new Date(), c_value;
                exdate.setDate(exdate.getDate() + exdays);
                c_value = escape(value) + ((exdays === null) ? "" : "; expires=" + exdate.toUTCString());
                document.cookie = cName + "=" + c_value;
            },

            /**
             * Gets a cookie
             * @param {String} cName
             * @return {String}
             */
            getCookie: function(cName) {
                var i, x, y, ARRcookies = document.cookie.split(";");
                for (i = 0; i < ARRcookies.length; i++) {
                    x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
                    y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
                    x = x.replace(/^\s+|\s+$/g, "");
                    if (x === cName) {
                        return unescape(y);
                    }
                }
            }
        },

        /**
         * Checks if an email address is valid
         * @param {String} address
         * @return {Boolean}
         */
        isValidEmailAddress: function(address) {
            return (/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/.test(address));
        }
    });
}(SUGAR.App));
