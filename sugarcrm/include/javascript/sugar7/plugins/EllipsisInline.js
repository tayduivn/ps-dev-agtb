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
        /**
         * When ellipsis_inline class is added to an element, the CSS will ellipsify the text
         * and this plugin will show a tooltip when an ellipsis exists.
         */
        app.plugins.register('EllipsisInline', ['view', 'field'], {

            events:{
                'mouseenter .ellipsis_inline': '_showEllipsisTooltip',
                'mouseleave .ellipsis_inline': '_hideEllipsisTooltip'
            },

            _$ellipsisTooltips: null, //array of all initialized tooltips

            /**
             * Initialize tooltips on render and destroy tooltip before render.
             */
            onAttach: function() {
                this.before('render', function() {
                    this.destroyEllipsisTooltips();
                }, this);

                this.on('render', function() {
                    this.initializeEllipsisTooltips();
                }, this);
            },

            /**
             * Destory all tooltips on dispose.
             */
            onDetach: function() {
                this.destroyEllipsisTooltips();
            },

            /**
             * Create tooltips for all elements that have `ellipsis_inline` class.
             */
            initializeEllipsisTooltips: function() {
                app.utils.tooltip.destroy(this._$ellipsisTooltips);
                this._$ellipsisTooltips = app.utils.tooltip.initialize(this.$('.ellipsis_inline'), {
                    trigger: 'manual'
                });
            },

            /**
             * Destroy all tooltips that have been created.
             */
            destroyEllipsisTooltips: function() {
                app.utils.tooltip.destroy(this._$ellipsisTooltips);
                this._$ellipsisTooltips = null;
            },

            /**
             * Show tooltip.
             * @param {Event} event
             * @private
             */
            _showEllipsisTooltip: function(event) {
                var target = event.currentTarget;
                if (this._shouldShowEllipsisTooltip(target)) {
                    $(target).tooltip('show');
                }
            },

            /**
             * Hide tooltip.
             * @param {Event} event
             * @private
             */
            _hideEllipsisTooltip: function(event) {
                var target = event.currentTarget;
                if (this._shouldHideEllipsisTooltip(target)) {
                    $(target).tooltip('hide');
                }
            },

            /**
             * Show tooltip if it exists on the target and if the ellipsis is shown.
             * @param {DOM} target
             * @returns {boolean}
             * @private
             */
            _shouldShowEllipsisTooltip: function(target) {
                return app.utils.tooltip.has(target) && (target.offsetWidth < target.scrollWidth);
            },

            /**
             * Hide tooltip if it exists on the target and if it is currently displayed.
             * @param {DOM} target
             * @returns {boolean}
             * @private
             */
            _shouldHideEllipsisTooltip: function(target) {
                return app.utils.tooltip.has(target) && $(target).data('tooltip').tip().hasClass('in');
            }

        });
    });
})(SUGAR.App);
