$(function() {

    /**
     * Timeago plugin is a jQuery/Zepto plugin for sidecar that converts a date into a relative time with a timer
     * to keep it relative to now.
     *
     * Example initialization of plugin:
     *
     * $('time.relativetime').timeago({
     *     logger:  SUGAR.App.logger,
     *     date: SUGAR.App.utils.date
     * });
     */
    var relativeTimeInterval;
    $.fn.extend({
        timeago: function(options) {
            var self = this;

            options = options || {};

            // required
            if (!options.date || !options.logger || !options.template || !options.lang) return;

            var SugarDate = options.date,
                SugarLog = options.logger,
                SugarTemplate = options.template,
                SugarLang = options.lang;


            /**
             * This function pulls the date from the 'datetime' attribute of the element,
             * converts it in a local date, then gets the relative time and display it.
             */
            var refresh = function() {
                var $this = $(this),
                    UTCDate = SugarDate.parse($this.attr('datetime')),
                    localDate = SugarDate.UTCtoLocalTime(UTCDate),
                    relativeTimeObj = SugarDate.getRelativeTimeLabel(localDate);

                if (relativeTimeObj.str) {
                    var relativeTimeTpl = SugarLang.get(relativeTimeObj.str),
                    relativeTime = SugarTemplate.compile(relativeTimeObj.str, relativeTimeTpl),
                    hour = SugarDate.format(localDate, 'H:i');
                    $this.text(relativeTime(relativeTimeObj.value) + " at " + hour);
                }
                return this;
            };

            // Convert all dates
            self.each(refresh);

            // Add a timer to refresh the relative time each minute
            if (self.length > 0) {
                // Check if a timer is already set
                if (!relativeTimeInterval) {
                    SugarLog.debug('(relative time) Starting a timer to convert ' + self.length + ' date');
                } else {
                    clearInterval(relativeTimeInterval);
                }
                // Set a new timer
                relativeTimeInterval = setInterval(function() {
                    self.each(refresh);
                }, 60 * 1000);
            } else {
                // Remove the timer if no more date elements is in the DOM
                if (relativeTimeInterval) {
                    SugarLog.debug('(relative time) Stopping the timer as there is no more date to convert');
                    clearInterval(relativeTimeInterval);
                    relativeTimeInterval = undefined;
                }
            }
            return self;
        }
    });
});