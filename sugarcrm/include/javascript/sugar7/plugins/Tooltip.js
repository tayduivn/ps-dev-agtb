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
    app.events.on('app:init', function() {
        app.plugins.register('Tooltip', ['layout', 'view', 'field'], {
            _$pluginTooltips: null, //array of all initialized tooltips
            _pluginTooltipCssSelector: '[rel=tooltip]', //CSS selector used to find tooltips

            /**
             * Initialize tooltips on render and destroy tooltip before render for views and fields.
             * Initialize tooltips on initialize for layouts.
             */
            onAttach: function() {
                if ((this instanceof app.view.View) || (this instanceof app.view.Field)) {
                    this.before('render', function() {
                        this.destroyAllPluginTooltips();
                    }, this);
                    this.on('render', function() {
                        this.initializeAllPluginTooltips();
                    }, this);
                } else if (this instanceof app.view.Layout) {
                    this.on('init', function() {
                        this.initializeAllPluginTooltips();
                    }, this);
                }
            },

            /**
             * Destroy tooltips on dispose.
             */
            onDetach: function() {
                this.destroyAllPluginTooltips();
            },

            /**
             * Create all tooltips in this component.
             */
            initializeAllPluginTooltips: function() {
                this.removePluginTooltips();
                this.addPluginTooltips();
            },

            /**
             * Destroy all tooltips that have been created in this component.
             */
            destroyAllPluginTooltips: function() {
                this.removePluginTooltips();
                this._$pluginTooltips = null;
            },

            /**
             * Create tooltips within a given element.
             * @param {jQuery} $element (optional)
             */
            addPluginTooltips: function($element) {
                var $tooltips = this._getPluginTooltips($element);
                this._$pluginTooltips  = this._$pluginTooltips || [];
                this._$pluginTooltips.push(app.utils.tooltip.initialize($tooltips));

                //hide tooltip when clicked
                $tooltips.on('click.tooltip', function() {
                    $(this).tooltip('hide');
                });
            },

            /**
             * Destroy tooltips within a given element.
             * @param {jQuery} $element (optional)
             */
            removePluginTooltips: function($element) {
                var $tooltips = this._getPluginTooltips($element);
                app.utils.tooltip.destroy($tooltips);
            },

            /**
             * Within a given element, get all elements that have 'rel' attribute with 'tooltip' as its value.
             * @param {jQuery} $element
             * @returns {jQuery}
             * @private
             */
            _getPluginTooltips: function($element) {
                return $element ? $element.find(this._pluginTooltipCssSelector) : this.$(this._pluginTooltipCssSelector);
            }
        });
    });
})(SUGAR.App);

(function($) {
    $(function() {
        if (!Modernizr.touch) {
            return;
        }
        /**
         * {@inheritDoc}
         * Deactivate tooltip plugin on touch devices.
         */
        $.fn.tooltip = function() {
            return this;
        };
    });
})(jQuery);
