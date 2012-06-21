$(function() {

    /**
     * Timeago plugin is a jQuery/Zepto plugin for sidecar that converts a date into a relative time with a timer
     * to keep it relative to now.
     *
     * Example initialization of plugin:
     *
     * $('span.relativetime').timeago({
     *   logger: SUGAR.App.logger,
     *   date: SUGAR.App.utils.date,
     *   lang: SUGAR.App.lang,
     *   template: SUGAR.App.template
     * });
     *
     * This plugin has a hard dependency with SideCar functions. Anyway, if you want to use your own on top of this
     * plugin, make sure that you will have defined:
     *   logger.debug()
     *   date.parse()
     *   date.format()
     *   date.UTCToLocalTime()
     *   date.getRelativeTimeLabel()
     *   lang.get()
     *   template.compile()
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
             * This function pulls the date from the 'title' attribute of the element,
             * converts it in a local date, then gets the relative time and display it.
             */
            var refresh = function() {
                var $this = $(this),
                    UTCDate = SugarDate.parse($this.attr('title')),
                    localDate = SugarDate.UTCtoLocalTime(UTCDate),
                    relativeTimeObj = SugarDate.getRelativeTimeLabel(localDate);

                if (relativeTimeObj.str) {
                    var relativeTimeTpl = SugarLang.get(relativeTimeObj.str),
                        relativeTime = SugarTemplate.compile(relativeTimeObj.str, relativeTimeTpl),
                        date = SugarDate.format(localDate, 'Y/m/d'),
                        time = SugarDate.format(localDate, 'H:i'),
                        ctx = {
                            date: date,
                            time: time,
                            relativetime: relativeTime(relativeTimeObj.value)
                        },
                        template = SugarTemplate.compile('LBL_TIME_RELATIVE', SugarLang.get('LBL_TIME_RELATIVE'));
                    $this.text(template(ctx));
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
            } else if (relativeTimeInterval) {
                SugarLog.debug('(relative time) Stopping the timer as there is no more date to convert');
                clearInterval(relativeTimeInterval);
                relativeTimeInterval = undefined;
            }
            return self;
        }
    });
});