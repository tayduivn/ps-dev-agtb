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
    * Formats given number for display as a percentage. Rounds to 2 decimal places. Includes percentage sign.
    * @method formatNumber
    * @throws {Error} if given number is greater than 1
    * @param {number} The number to format.
    * @return {String} The formatted number.
    */
   Handlebars.registerHelper('formatPercentage', function(percent) {
       if (typeof percent === "undefined" || percent === null) {
           percent = 0;
       }

       if (percent > 1) {
           throw new Error("[formatPercentage] Invalid number passed to helper");
       } else if (percent < 1) {
           percent = percent.toString().split(".")[1];
           percent = parseInt(percent, 10);
           percent = Math.round(percent*10)/10; // Round to two decimal places.
       }

       return (parseInt(percent, 10) || 0) + '%';
   });

    /**
     * Builds a link in the forecasts Module.
     * @method moduleLink
     * @return {String}
     */
    Handlebars.registerHelper('moduleHref', function(context, model, route) {
        return 'index.php?module='+route.module+'&action='+route.action+'&record='+model.get(route.recordID);
    });

    /**
     * Allows browser debugger to be launched from within a handlebar template using {{debugger}}
     * @method debugger
     */
    Handlebars.registerHelper("debugger", function() {
        console.log("debugger");
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
     * @return {String} The string for the given label key.
     */
    Handlebars.registerHelper("str_format", function(key, module, args) {
        module = _.isString(module) ? module : null;
        var label = app.lang.get(key, module);

        if ((typeof args == 'String') || args.length == 1)
        {
            args = (typeof args == 'String') ? args : args[0];
            return label.replace('{0}', args);
        }

        var len = args.length;
        for(var x=0; x < len; x++)
        {
            label = label.replace('{' + x + '}', args[x]);
        }
        return label;
    });




})(SUGAR.App);
