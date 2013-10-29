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
    app.events.on("app:init", function() {

        /**
         * Handlebar helper to get the letters used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * If the module has an icon string defined, use it, otherwise fall back to the module's translated name.
         * If there are spaces in the name, (e. g. Revenue Line Items or Product Catalog), it takes the initials
         * from the first two words, instead of the first two letters (e. g. RL and PC, instead of Re and Pr)
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconLabel', function(module) {
            var name = app.lang.getAppListStrings('moduleIconList')[module] ||
                    app.lang.getAppListStrings('moduleListSingular')[module] ||
                    module,
                space = name.indexOf(" ");

            return (space != -1) ? name.substring(0 , 1) + name.substring(space + 1, space + 2) : name.substring(0, 2);
        });

        /**
         * Handlebar helper to get the Tooltip used for the icons shown in various headers for each module, based on the
         * translated singular module name.  This does not always match the name of the module in the model,
         * i. e. Product == Revenue Line Item
         * @param {String} module to which the icon belongs
         */
        Handlebars.registerHelper('moduleIconToolTip', function(module) {
            return app.lang.getAppListStrings('moduleListSingular')[module] || module;
        });

        /**
         * Handlebar helper to translate any dropdown values to have the appropriate labels
         * @param {String} value The value to be translated.
         * @param {String} key The dropdown list name.
         */
        Handlebars.registerHelper('getDDLabel', function(value, key) {
            return app.lang.getAppListStrings(key)[value] || value;
        });

        /**
         * Handlebar helper to retrieve a view template as a sub template
         * @param {String} key Key for the template to retrieve.
         * @param {Object} data Data to pass into the compiled template
         * @param {Object} options (optional) Optional parameters
         * @return {String} String Template
         */
        Handlebars.registerHelper('subViewTemplate', function(key, data, options) {
            var template =  app.template.getView(key, options.hash.module);
            return template ? template(data) : '';
        });

        /**
         * Handlebar helper to retrieve a field template as a sub template
         * @param {String} fieldName determines which field to use.
         * @param {String} view determines which template within the field to use.
         * @param {Object} data Data to pass into the compiled template
         * @param {Object} options (optional) Optional parameters
         * @return {String} String Template
         */
        Handlebars.registerHelper('subFieldTemplate', function(fieldName, view, data, options) {
            var template =  app.template.getField(fieldName, view, options.hash.module);
            return template ? template(data) : '';
        });

        /**
         * Handlebar helper to retrieve a  ayout template as a sub template
         * @param {String} key Key for the template to retrieve.
         * @param {Object} data Data to pass into the compiled template
         * @param {Object} options (optional) Optional parameters
         * @return {String} String Template
         */
        Handlebars.registerHelper('subLayoutTemplate', function(key, data, options) {
            var template =  app.template.getLayout(key, options.hash.module);
            return template ? template(data) : '';
        });
    });
})(SUGAR.App);
