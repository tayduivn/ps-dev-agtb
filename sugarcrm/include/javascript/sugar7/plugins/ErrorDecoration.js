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
(function (app) {
    app.events.on("app:init", function () {
        app.plugins.register('ErrorDecoration', ['view'], {

            /**
             * Clears validation errors on start and success.
             *
             * @param {Object} component
             * @param {Object} plugin
             * @return {void}
             */
            onAttach: function(component, plugin) {
                this.on('init', function() {
                    this.model.on('validation:start validation:success', this.clearValidationErrors, this);
                }, this);
            },

            /**
             * We need to add those events to the view to show/hide the tooltip that contains the error message
             */
            events:{
                'focus input':'showTooltip',
                'blur input':'hideTooltip',
                'focus textarea':'showTooltip',
                'blur textarea':'hideTooltip'
            },
            showTooltip:function (e) {
                _.defer(function () {
                    var $addon = this.$(e.currentTarget).next('.add-on');
                    if ($addon && _.isFunction($addon.tooltip)) {
                        $addon.tooltip('show');
                    }
                }, this);
            },
            hideTooltip:function (e) {
                var $addon = this.$(e.currentTarget).next('.add-on');
                if ($addon && _.isFunction($addon.tooltip)) $addon.tooltip('hide');
            },

            /**
             * Remove validation error decoration from fields
             *
             * @param fields Fields to remove error from
             */
            clearValidationErrors:function (fields) {
                fields = fields || _.toArray(this.fields);
                if (fields.length > 0) {
                    _.defer(function () {
                        _.each(fields, function (field) {
                            if (_.isFunction(field.clearErrorDecoration) && field.disposed !== true) {
                                field.isErrorState = false;
                                field.clearErrorDecoration();
                            }
                        });
                    }, fields);
                }
                _.defer(function() {
                    if (this.disposed) {
                        return;
                    }
                    this.$('.error').removeClass('error');
                    this.$('.error-tooltip').remove();
                }, this);
            }
        });
    });
})(SUGAR.App);
