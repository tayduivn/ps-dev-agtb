(function(app) {

    /**
    * Formats given number for display, adding commas where appropriate.
    * @method formatNumber
    * @param {number} The number to format.
    * @return {String} The formatted number.
    */
   Handlebars.registerHelper('formatNumber', function(number) {
       if (typeof number === "undefined" || number === null) {
           number = 0;
       }
       var parts = number.toString().split(".");
       parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
       return parts.join(".");
   });

   /**
    * Formats given number for display as a percentage. Rounds to 2 decimnal places. Includes percentage sign.
    * @method formatNumber
    * @throws {Error} if given number is greater than 1
    * @param {number} The number to format.
    * @return {String} The formatted number.
    */
   Handlebars.registerHelper('formatPercentage', function(percent) {
       if (typeof percent === "undefined" || percent === null) {
           percent = 0;
       }

       percent = percent * 100;
       percent = Math.round(percent*10)/10; // Round to two decimal places.
       if ( percent > 1 ) {
            percent = parseInt(percent, 10) || 0; // then, round to a whole number.
       }
       return percent + '%';
   });

    /**
     * Allows browser debugger to be launched from within a handlebar template using {{debugger}}
     * @method debugger
     */
    Handlebars.registerHelper("debugger", function() {
        debugger;
    });


    // todo:  putting in here, for now.  This should go back into core sidecar once we merge back into toffee
    /**
     * Retrieves a string by key.
     *
     * The helper queries {@link Core.LanguageHelper} module to retrieve an i18n-ed string.
     * @method str_format
     * @param {String} key Key of the label.
     * @param {String} module(optional) Module name.
     * @param Mixed args String or Array of arguments to substitute into string
     * @return {Handlebars.SafeString} The string for the given label key.
     */
    Handlebars.registerHelper("str_format", function(key, module, args) {
        module = _.isString(module) ? module : null;
        var label = app.lang.get(key, module);

        if (_.isString(args) || args.length == 1)
        {
            args = (_.isString(args)) ? args : args[0];
            label = label.replace('{0}', args);
            return new Handlebars.SafeString(label);
        }

        var len = args.length;
        for(var x=0; x < len; x++)
        {
            label = label.replace('{' + x + '}', args[x]);
        }
        return new Handlebars.SafeString(label);
    });


    /**
     * Render the expected opportunities.  Moved the code to handle rendering the expected opportunities into a helper
     * function since we have to iterate through the collection
     *
     */
    Handlebars.registerHelper("expected_opportunity_column", function(tag) {
        if(this.name == 'include_expected' || this.name == 'expected_commit_stage')
        {
           return tag == 'start' ? '<td><div style="font-weight: normal; width: 100%; text-align: center;">' : '</div></td>';
        } else if (this.name == 'expected_amount') {
           return tag == 'start' ? ('<th colspan="4" style="text-align: right;"><i>' + app.lang.get('LBL_EXPECTED_OPPORTUNITIES', 'Forecasts') + '</i></th><th>') : '</th>';
        }
        return tag == 'start' ? '<th>' : '</th>';
    });

})(SUGAR.App);
