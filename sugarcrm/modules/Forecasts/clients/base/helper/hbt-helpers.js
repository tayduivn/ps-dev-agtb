/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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

    /**
     * log some crap
     * @method consoleLogger
     * @param Mixed args anything to dump to console
     */
    Handlebars.registerHelper("consoleLog", function(param) {
       console.log(param);
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

})(SUGAR.App);
