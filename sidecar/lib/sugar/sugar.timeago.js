$(function() {

    /**
     * Timeago plugin is a jQuery/Zepto plugin for sidecar that converts a date into a relative time with a timer
     * to keep it relative to now.
     */
    var relativeTimeInterval;
    $.fn.extend({
        timeago: function() {
            var self = this;
            // Convert all dates
            self.each(refresh);

            // Add a timer to refresh the relative time each minute
            if (self.length > 0) {
                // Check if a timer is already set
                if (!relativeTimeInterval) {
                    SUGAR.App.logger.debug('(relative time) Starting a timer to convert ' + self.length + ' date');
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
                    SUGAR.App.logger.debug('(relative time) Stopping the timer as there is no more date to convert');
                    clearInterval(relativeTimeInterval);
                    relativeTimeInterval = undefined;
                }
            }
            return self;
        }
    });

    /**
     * This function pulls the date from the 'datetime' attribute of the element,
     * converts it in a local date, then gets the relative time and display it.
     */
    var refresh = function() {
        var $this = $(this),
            SugarDate = SUGAR.App.utils.date,
            UTCDate = SugarDate.parse($this.attr('datetime')),
            localDate = SugarDate.UTCtoLocalTime(UTCDate);

        txt = SugarDate.getRelativeTime(localDate) || false;
        if (txt) {
            var time = SugarDate.format(localDate, 'H:i');
            $this.text(txt + " at " + time);
        }
        return this;
    };
})(window.jQuery || window.Zepto);