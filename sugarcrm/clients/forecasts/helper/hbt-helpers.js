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

       percent = percent * 100;
       percent = Math.round(percent*10)/10; // Round to two decimal places.
       if ( percent > 1 ) {
            percent = parseInt(percent, 10) || 0; // then, round to a whole number.
       }
       return percent + '%';
   });

    /**
     * Builds a link to a module detail view for a record in the forecasts Module.
     * @method moduleLink
     * @return {String}
     */
    Handlebars.registerHelper('moduleHref', function(context, model, route) {
        return 'index.php?module='+route.module+'&action='+route.action+'&record='+model.get(route.recordID);
    });

    /**
     * Builds a link to set the current user in the forecasts Module.
     * @method moduleLink
     * @return {String}
     */
    Handlebars.registerHelper('userHref', function(context, model, route) {
        var linkStr;
        var selectedUser = {
            id: '',
            full_name: '',
            first_name: '',
            last_name: '',
            isManager: false,
            showOpps: model.get("show_opps")
        };
        var uid = Handlebars.Utils.escapeExpression(model.get(route.recordID));
        if (uid) {

            var options = {
                dataType: 'json',
                context: selectedUser,
                success: function(data) {
                    this.id = data.id;
                    this.full_name = data.full_name;
                    this.first_name = data.first_name;
                    this.last_name = data.last_name;
                    this.isManager = data.isManager;
                }
            };

            myURL = app.api.buildURL('Forecasts', 'user/' + uid);
            app.api.call('read', myURL, null, options);

            linkStr = $('<a href="#">'+this.value +'</a>');
            linkStr.on("click", function(){
                context.forecasts.set("selectedUser", selectedUser);
                return false;
            });
        } else {
            linkStr = this.value;
        }
        return linkStr;
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
        return new Handlebars.SafeString(label);
    });


    /**
     * Render the expected opportunities.  Moved the code to handle rendering the expected opportunities into a helper
     * function since we have to iterate through the collection
     *
     */
    Handlebars.registerHelper("expected_opportunity_column", function(tag) {
        if(this.name == 'include_expected')
        {
           //return tag == 'start' ? '<th>' : '</th>';
           return tag == 'start' ? '<th><div style="width: 100%; text-align: center;">' : '</div></th>';
        } else if (this.name == 'expected_amount') {
           return tag == 'start' ? ('<th colspan="4" style="text-align: right;"><i>' + app.lang.get('LBL_EXPECTED_OPPORTUNITIES', 'Forecasts') + '</i></th><th>') : '</th>';
        }
        return tag == 'start' ? '<th>' : '</th>';
    });

})(SUGAR.App);
